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
    $insertSerie = $pdo->prepare("INSERT INTO series (nom_serie) VALUES (:nom_serie) ON CONFLICT (nom_serie) DO NOTHING RETURNING id_serie;");
    $insertSaison = $pdo->prepare("INSERT INTO saisons (num_saison, id_serie) VALUES (:num_saison, :id_serie) ON CONFLICT (num_saison, id_serie) DO NOTHING RETURNING id_saison;");
    $insertEpisode = $pdo->prepare("INSERT INTO episodes (id_saison, num_episode) VALUES (:id_saison, :num_episode) ON CONFLICT (id_saison, num_episode) DO NOTHING RETURNING id_episode;");
    $insertFilm = $pdo->prepare("INSERT INTO films (nom_film) VALUES (:nom_film) ON CONFLICT (nom_film) DO NOTHING RETURNING id_film;");
    
    // Mise à jour pour inclure id_saison et id_serie dans l'insertion de visionnages
    $insertVisionnage = $pdo->prepare("INSERT INTO visionnages (id_profil, id_episode, id_film, id_saison, id_serie, debut_visionnage, duree_visionnage) VALUES (:id_profil, :id_episode, :id_film, :id_saison, :id_serie, :debut_visionnage, :duree_visionnage);");
    
    $insertAppareil = $pdo->prepare("INSERT INTO appareils (type_appareil) VALUES (:type_appareil) ON CONFLICT (type_appareil) DO NOTHING RETURNING id_appareil;");
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

            // Vérifier si la création de l'objet DateTime a réussi
            if (!$debutVisionnage) {
                echo "Erreur lors de la création de DateTime pour la chaîne : $debutVisionnageString. Erreurs : " . implode(", ", DateTime::getLastErrors()) . "\n";
                continue; // Ignorer cette ligne si la date est invalide
            }

            // Gérer les profils
            if ($insertProfil->execute([':nom_profil' => $nomProfil])) {
                $idProfil = $insertProfil->fetchColumn();
                echo "Profil inséré : $nomProfil avec ID $idProfil\n"; // Débogage
            } else {
                echo "Échec de l'insertion du profil : $nomProfil\n"; // Débogage
            }

            // 3. Déterminer si c'est une série ou un film
            $isSerie = strpos($title, 'Saison') !== false;

            if ($isSerie) {
                // Si c'est une série, extraire les informations
                if (preg_match('/^(.*?):\s*Saison\s*(\d+):.*?\s*\(Épisode\s*(\d+)\)$/u', trim($title), $matches)) {
                    $nomSerie = $matches[1]; // Le nom de la série
                    $numSaison = $matches[2]; // Le numéro de saison
                    $numEpisode = $matches[3]; // Le numéro d'épisode
                    echo "Titre reconnu : $title, Série : $nomSerie, Saison : $numSaison, Épisode : $numEpisode\n"; // Débogage

                    // Insérer dans la table des séries
                    if ($insertSerie->execute([':nom_serie' => $nomSerie])) {
                        $idSerie = $insertSerie->fetchColumn();
                        echo "Série insérée : $nomSerie avec ID $idSerie\n"; // Débogage
                    }

                    // Insérer dans la table des saisons
                    if ($insertSaison->execute([':num_saison' => $numSaison, ':id_serie' => $idSerie])) {
                        $idSaison = $insertSaison->fetchColumn();
                        echo "Saison insérée : Saison $numSaison de $nomSerie avec ID $idSaison\n"; // Débogage
                    }

                    // Insérer dans la table des épisodes
                    if ($insertEpisode->execute([':id_saison' => $idSaison, ':num_episode' => $numEpisode])) {
                        $idEpisode = $insertEpisode->fetchColumn();
                        echo "Épisode inséré : Épisode $numEpisode de Saison $numSaison de $nomSerie avec ID $idEpisode\n"; // Débogage
                    } else {
                        echo "Échec de l'insertion de l'épisode pour la saison $numSaison de $nomSerie. Numéro d'épisode : $numEpisode\n";
                    }

                    // Insérer dans visionnages si idEpisode est défini
                    if (isset($idEpisode)) {
                        // Maintenant, inclure id_saison et id_serie dans l'insertion
                        $insertVisionnage->execute([
                            ':id_profil' => $idProfil,
                            ':id_episode' => $idEpisode,
                            ':id_film' => null,
                            ':id_saison' => $idSaison, // Nouveau paramètre
                            ':id_serie' => $idSerie,   // Nouveau paramètre
                            ':debut_visionnage' => $debutVisionnage->format('Y-m-d H:i:s'),
                            ':duree_visionnage' => $dureeVisionnage
                        ]);
                        echo "Visionnage inséré pour le profil $nomProfil, Épisode ID $idEpisode.\n"; // Débogage
                    } else {
                        echo "Numéro d'épisode non défini pour le titre : $title. Ignorer l'insertion dans visionnages.\n";
                    }

                } else {
                    echo "Format du titre non valide : $title. Ignorer.\n"; // Afficher le titre non valide
                    continue; // Ignorer cette ligne si le format est invalide
                }

            } else {
                // Si c'est un film
                if ($insertFilm->execute([':nom_film' => $title])) {
                    $idFilm = $insertFilm->fetchColumn();
                    echo "Film inséré : $title avec ID $idFilm\n"; // Débogage

                    // Insérer dans visionnages
                    $insertVisionnage->execute([
                        ':id_profil' => $idProfil,
                        ':id_episode' => null,
                        ':id_film' => $idFilm,
                        ':id_saison' => null, // Pas de saison pour les films
                        ':id_serie' => null,   // Pas de série pour les films
                        ':debut_visionnage' => $debutVisionnage->format('Y-m-d H:i:s'),
                        ':duree_visionnage' => $dureeVisionnage
                    ]);
                    echo "Visionnage inséré pour le profil $nomProfil, Film ID $idFilm.\n"; // Débogage
                }

            }

            // Gérer les appareils
            if ($insertAppareil->execute([':type_appareil' => $deviceType])) {
                $idAppareil = $insertAppareil->fetchColumn();
                echo "Appareil inséré : $deviceType avec ID $idAppareil\n"; // Débogage
            } else {
                echo "Échec de l'insertion de l'appareil : $deviceType\n"; // Débogage
            }

            // Insérer dans visionnage_appareil
            $insertVisionnageAppareil->execute([
                ':id_visionnage' => $pdo->lastInsertId(),
                ':id_appareil' => $idAppareil
            ]);
        }

        fclose($handle);
    } else {
        echo "Erreur lors de l'ouverture du fichier.\n";
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
?>
