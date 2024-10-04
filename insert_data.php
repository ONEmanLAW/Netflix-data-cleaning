<?php
// Connexion à la base de données PostgreSQL
$dsn = 'pgsql:host=bdd.h91.co;dbname=exo1_hugo';
$user = 'hugo';
$password = 'tp1_hugo';

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion à la base de données réussie.\n"; // Vérification de connexion

    // Préparation des requêtes
    $insertProfil = $pdo->prepare("INSERT INTO profils (nom_profil) VALUES (:nom_profil) ON CONFLICT (nom_profil) DO NOTHING RETURNING id_profil;");
    $selectProfil = $pdo->prepare("SELECT id_profil FROM profils WHERE nom_profil = :nom_profil;");
    
    $insertSerie = $pdo->prepare("INSERT INTO series (nom_serie) VALUES (:nom_serie) ON CONFLICT (nom_serie) DO NOTHING RETURNING id_serie;");
    $selectSerie = $pdo->prepare("SELECT id_serie FROM series WHERE nom_serie = :nom_serie;");
    
    $insertSaison = $pdo->prepare("INSERT INTO saisons (num_saison, id_serie) VALUES (:num_saison, :id_serie) ON CONFLICT (num_saison, id_serie) DO NOTHING RETURNING id_saison;");
    $selectSaison = $pdo->prepare("SELECT id_saison FROM saisons WHERE num_saison = :num_saison AND id_serie = :id_serie;");
    
    $insertEpisode = $pdo->prepare("INSERT INTO episodes (id_saison, num_episode) VALUES (:id_saison, :num_episode) ON CONFLICT (id_saison, num_episode) DO NOTHING RETURNING id_episode;");
    $selectEpisode = $pdo->prepare("SELECT id_episode FROM episodes WHERE id_saison = :id_saison AND num_episode = :num_episode;");
    
    $insertFilm = $pdo->prepare("INSERT INTO films (nom_film) VALUES (:nom_film) ON CONFLICT (nom_film) DO NOTHING RETURNING id_film;");
    $selectFilm = $pdo->prepare("SELECT id_film FROM films WHERE nom_film = :nom_film;");
    
    // Mise à jour pour inclure id_saison et id_serie dans l'insertion de visionnages
    $insertVisionnage = $pdo->prepare("INSERT INTO visionnages (id_profil, id_episode, id_film, id_saison, id_serie, debut_visionnage, duree_visionnage) VALUES (:id_profil, :id_episode, :id_film, :id_saison, :id_serie, :debut_visionnage, :duree_visionnage);");
    
    $insertAppareil = $pdo->prepare("INSERT INTO appareils (type_appareil) VALUES (:type_appareil) ON CONFLICT (type_appareil) DO NOTHING RETURNING id_appareil;");
    $selectAppareil = $pdo->prepare("SELECT id_appareil FROM appareils WHERE type_appareil = :type_appareil;");
    
    $insertVisionnageAppareil = $pdo->prepare("INSERT INTO visionnage_appareil (id_visionnage, id_appareil) VALUES (:id_visionnage, :id_appareil);");

    // Ouverture du fichier CSV
    if (($handle = fopen('ViewingActivity.csv', 'r')) !== false) {
        // Sauter l'en-tête
        fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            // Extraction des données
            $nomProfil = trim($data[0]);
            $debutVisionnageString = trim($data[1]);
            $dureeVisionnage = trim($data[2]);
            $attributes = trim($data[3]);
            $title = trim($data[4]);
            $deviceType = trim($data[6]);

            // Débogage de l'extraction des données
            echo "Traitement de la ligne :\n";
            echo "Profil : $nomProfil, Début : $debutVisionnageString, Durée : $dureeVisionnage, Attributes : $attributes, Title : $title, Device Type : $deviceType\n";

            // Conditions pour filtrer les lignes
            if (!empty($attributes) && strpos($attributes, 'User_Interaction') === false) {
                echo "Ligne ignorée en raison d'attributs non valides.\n";
                continue; // Ignorer cette ligne si elle ne respecte pas les conditions
            }

            if (!empty($data[5])) {
                echo "Ligne ignorée en raison de Supplemental Video Type non vide.\n";
                continue; // Ignorer cette ligne si la colonne est non vide
            }

            // Vérifier plusieurs formats de date
            $debutVisionnage = DateTime::createFromFormat('Y/m/d - H:i:s', $debutVisionnageString);
            if (!$debutVisionnage) {
                $debutVisionnage = DateTime::createFromFormat('Y-m-d H:i:s', $debutVisionnageString);
            }

            if (!$debutVisionnage) {
                echo "Erreur lors de la création de DateTime pour la chaîne : $debutVisionnageString.\n";
                continue; // Ignorer cette ligne si la date est invalide
            }

            // Gérer les profils
            if ($insertProfil->execute([':nom_profil' => $nomProfil])) {
                $idProfil = $insertProfil->fetchColumn();
                if (!$idProfil) {
                    $selectProfil->execute([':nom_profil' => $nomProfil]);
                    $idProfil = $selectProfil->fetchColumn();
                }
                echo "Profil inséré ou trouvé : $nomProfil avec ID $idProfil\n"; // Débogage
            }

            // Déterminer si c'est une série ou un film
            $isSerie = strpos($title, 'Saison') !== false;

            if ($isSerie) {
                if (preg_match('/^(.*?):\s*Saison\s*(\d+):.*?\s*\(Épisode\s*(\d+)\)$/u', trim($title), $matches)) {
                    $nomSerie = $matches[1]; 
                    $numSaison = $matches[2]; 
                    $numEpisode = $matches[3]; 

                    // Gérer les séries
                    if ($insertSerie->execute([':nom_serie' => $nomSerie])) {
                        $idSerie = $insertSerie->fetchColumn();
                        if (!$idSerie) {
                            $selectSerie->execute([':nom_serie' => $nomSerie]);
                            $idSerie = $selectSerie->fetchColumn();
                        }
                    }

                    // Gérer les saisons
                    if ($insertSaison->execute([':num_saison' => $numSaison, ':id_serie' => $idSerie])) {
                        $idSaison = $insertSaison->fetchColumn();
                        if (!$idSaison) {
                            $selectSaison->execute([':num_saison' => $numSaison, ':id_serie' => $idSerie]);
                            $idSaison = $selectSaison->fetchColumn();
                        }
                    }

                    // Gérer les épisodes
                    if ($insertEpisode->execute([':id_saison' => $idSaison, ':num_episode' => $numEpisode])) {
                        $idEpisode = $insertEpisode->fetchColumn();
                        if (!$idEpisode) {
                            $selectEpisode->execute([':id_saison' => $idSaison, ':num_episode' => $numEpisode]);
                            $idEpisode = $selectEpisode->fetchColumn();
                        }
                    }

                    // Insérer dans visionnages
                    if (isset($idEpisode)) {
                        $insertVisionnage->execute([
                            ':id_profil' => $idProfil,
                            ':id_episode' => $idEpisode,
                            ':id_film' => null,
                            ':id_saison' => $idSaison,
                            ':id_serie' => $idSerie,
                            ':debut_visionnage' => $debutVisionnage->format('Y-m-d H:i:s'),
                            ':duree_visionnage' => $dureeVisionnage
                        ]);
                        echo "Visionnage inséré pour le profil $nomProfil, Épisode ID $idEpisode.\n"; 
                    }

                } else {
                    echo "Format du titre non valide : $title. Ignorer.\n";
                    continue; 
                }

            } else {
                // Si c'est un film
                if ($insertFilm->execute([':nom_film' => $title])) {
                    $idFilm = $insertFilm->fetchColumn();
                    if (!$idFilm) {
                        $selectFilm->execute([':nom_film' => $title]);
                        $idFilm = $selectFilm->fetchColumn();
                    }

                    $insertVisionnage->execute([
                        ':id_profil' => $idProfil,
                        ':id_episode' => null,
                        ':id_film' => $idFilm,
                        ':id_saison' => null, 
                        ':id_serie' => null,   
                        ':debut_visionnage' => $debutVisionnage->format('Y-m-d H:i:s'),
                        ':duree_visionnage' => $dureeVisionnage
                    ]);
                    echo "Visionnage inséré pour le profil $nomProfil, Film ID $idFilm.\n"; 
                }
            }

            // Gérer les appareils
            if ($insertAppareil->execute([':type_appareil' => $deviceType])) {
                $idAppareil = $insertAppareil->fetchColumn();
                if (!$idAppareil) {
                    $selectAppareil->execute([':type_appareil' => $deviceType]);
                    $idAppareil = $selectAppareil->fetchColumn();
                }

                $insertVisionnageAppareil->execute([
                    ':id_visionnage' => $pdo->lastInsertId(),
                    ':id_appareil' => $idAppareil
                ]);
            }

        }

        fclose($handle);
    } else {
        echo "Erreur lors de l'ouverture du fichier.\n";
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
?>
