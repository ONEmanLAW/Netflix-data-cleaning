<?php
// Connexion à PostgreSQL
$host = 'https://bdd.h91.co'; // Votre hôte
$db = 'exo1_hugo'; // Nom de la base de données
$user = 'hugo'; // Votre utilisateur
$pass = 'tp1_hugo'; // Votre mot de passe

$conn = pg_connect("host=$host dbname=$db user=$user password=$pass");
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

// Lire le fichier CSV
$csvFile = fopen('ViewingActivity.csv', 'r');
if (!$csvFile) {
    die("Impossible d'ouvrir le fichier CSV.");
}

// Ignorer la première ligne (en-tête)
fgetcsv($csvFile);

// Boucle à travers chaque ligne du CSV
while (($record = fgetcsv($csvFile, 1000, ',')) !== FALSE) {
    // Extraction des données
    list($profil, $titre_serie, $saison, $episode, $titre_film, $debut_visionnage, $fin_visionnage, $appareil) = $record;

    // Insertion des profils
    $nom_profil = pg_escape_string($profil);
    $insertProfil = "INSERT INTO profils (nom_profil) VALUES ('$nom_profil') ON CONFLICT (nom_profil) DO NOTHING;";
    pg_query($conn, $insertProfil);

    // Insertion des séries
    $nom_serie = pg_escape_string($titre_serie);
    $insertSerie = "INSERT INTO series (nom_serie) VALUES ('$nom_serie') ON CONFLICT (nom_serie) DO NOTHING;";
    pg_query($conn, $insertSerie);

    // Récupérer l'ID de la série insérée
    $result = pg_query($conn, "SELECT id_serie FROM series WHERE nom_serie = '$nom_serie';");
    $serie_row = pg_fetch_assoc($result);
    $id_serie = $serie_row['id_serie'];

    // Insertion des saisons
    $num_saison = intval($saison);
    $insertSaison = "INSERT INTO saisons (num_saison, id_serie) VALUES ($num_saison, $id_serie) ON CONFLICT (num_saison, id_serie) DO NOTHING;";
    pg_query($conn, $insertSaison);

    // Récupérer l'ID de la saison insérée
    $result = pg_query($conn, "SELECT id_saison FROM saisons WHERE num_saison = $num_saison AND id_serie = $id_serie;");
    $saison_row = pg_fetch_assoc($result);
    $id_saison = $saison_row['id_saison'];

    // Insertion des épisodes
    $num_episode = intval($episode);
    $insertEpisode = "INSERT INTO episodes (num_episode, id_saison) VALUES ($num_episode, $id_saison) ON CONFLICT (num_episode, id_saison) DO NOTHING;";
    pg_query($conn, $insertEpisode);

    // Récupérer l'ID de l'épisode inséré
    $result = pg_query($conn, "SELECT id_episode FROM episodes WHERE num_episode = $num_episode AND id_saison = $id_saison;");
    $episode_row = pg_fetch_assoc($result);
    $id_episode = $episode_row['id_episode'];

    // Insertion des films (si applicable)
    $nom_film = pg_escape_string($titre_film);
    if (!empty($nom_film)) {
        $insertFilm = "INSERT INTO films (nom_film) VALUES ('$nom_film') ON CONFLICT (nom_film) DO NOTHING;";
        pg_query($conn, $insertFilm);
        
        // Récupérer l'ID du film inséré
        $result = pg_query($conn, "SELECT id_film FROM films WHERE nom_film = '$nom_film';");
        $film_row = pg_fetch_assoc($result);
        $id_film = $film_row['id_film'];
    } else {
        $id_film = null;
    }

    // Insertion des visionnages
    $debut_visionnage = pg_escape_string($debut_visionnage);
    $fin_visionnage = pg_escape_string($fin_visionnage);
    $duree_visionnage = "EXTRACT(EPOCH FROM ('$fin_visionnage'::timestamp - '$debut_visionnage'::timestamp)) * interval '1 second'"; 

    $insertVisionnage = "INSERT INTO visionnages (id_profil, id_episode, id_film, debut_visionnage, fin_visionnage, duree_visionnage) VALUES (
        (SELECT id_profil FROM profils WHERE nom_profil = '$nom_profil'),
        $id_episode,
        $id_film,
        '$debut_visionnage',
        '$fin_visionnage',
        $duree_visionnage
    );";
    pg_query($conn, $insertVisionnage);

    // Insertion des appareils
    $type_appareil = pg_escape_string($appareil);
    $insertAppareil = "INSERT INTO appareils (type_appareil) VALUES ('$type_appareil') ON CONFLICT (type_appareil) DO NOTHING;";
    pg_query($conn, $insertAppareil);
    
    // Récupérer l'ID de l'appareil inséré
    $result = pg_query($conn, "SELECT id_appareil FROM appareils WHERE type_appareil = '$type_appareil';");
    $appareil_row = pg_fetch_assoc($result);
    $id_appareil = $appareil_row['id_appareil'];

    // Lier les visionnages aux appareils
    $insertVisionnageAppareil = "INSERT INTO visionnage_appareil (id_visionnage, id_appareil) VALUES (
        (SELECT id_visionnage FROM visionnages WHERE id_profil = (SELECT id_profil FROM profils WHERE nom_profil = '$nom_profil') AND debut_visionnage = '$debut_visionnage'),
        $id_appareil
    );";
    pg_query($conn, $insertVisionnageAppareil);
}

// Fermer la connexion
pg_close($conn);
echo "Données insérées avec succès!";
?>
