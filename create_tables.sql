CREATE TABLE IF NOT EXISTS profils (
    id_profil SERIAL PRIMARY KEY,
    nom_profil VARCHAR(255) NOT NULL
);



CREATE TABLE IF NOT EXISTS series (
    id_serie SERIAL PRIMARY KEY,
    nom_serie VARCHAR(255) NOT NULL
);



CREATE TABLE IF NOT EXISTS saisons (
    id_saison SERIAL PRIMARY KEY,
    num_saison INTEGER NOT NULL,
    id_serie INTEGER NOT NULL,
    FOREIGN KEY (id_serie) REFERENCES series(id_serie) ON DELETE CASCADE
);



CREATE TABLE IF NOT EXISTS episodes (
    id_episode SERIAL PRIMARY KEY,
    num_episode INTEGER NOT NULL,
    id_saison INTEGER NOT NULL,
    FOREIGN KEY (id_saison) REFERENCES saisons(id_saison) ON DELETE CASCADE
);



CREATE TABLE IF NOT EXISTS films (
    id_film SERIAL PRIMARY KEY,
    nom_film VARCHAR(255) NOT NULL
);



CREATE TABLE IF NOT EXISTS visionnages (
    id_visionnage SERIAL PRIMARY KEY,
    id_profil INTEGER NOT NULL,
    id_episode INTEGER,
    id_film INTEGER,
    debut_visionnage TIMESTAMP NOT NULL,
    fin_visionnage TIMESTAMP NOT NULL,
    duree_visionnage INTERVAL NOT NULL,
    FOREIGN KEY (id_profil) REFERENCES profils(id_profil) ON DELETE CASCADE,
    FOREIGN KEY (id_episode) REFERENCES episodes(id_episode) ON DELETE CASCADE,
    FOREIGN KEY (id_film) REFERENCES films(id_film) ON DELETE CASCADE
);



CREATE TABLE IF NOT EXISTS appareils (
    id_appareil SERIAL PRIMARY KEY,
    type_appareil VARCHAR(255) NOT NULL
);



CREATE TABLE IF NOT EXISTS visionnage_appareil (
    id_visionnage INTEGER NOT NULL,
    id_appareil INTEGER NOT NULL,
    FOREIGN KEY (id_visionnage) REFERENCES visionnages(id_visionnage) ON DELETE CASCADE,
    FOREIGN KEY (id_appareil) REFERENCES appareils(id_appareil) ON DELETE CASCADE,
    PRIMARY KEY (id_visionnage, id_appareil)  -- Clé primaire composite
);





