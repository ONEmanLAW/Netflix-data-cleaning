CREATE TABLE profils (
    id_profil SERIAL PRIMARY KEY,
    nom_profil VARCHAR(255) UNIQUE NOT NULL
);



CREATE TABLE series (
    id_serie SERIAL PRIMARY KEY,
    nom_serie VARCHAR(255) UNIQUE NOT NULL
);



CREATE TABLE saisons (
    id_saison SERIAL PRIMARY KEY,
    num_saison INTEGER NOT NULL,
    id_serie INTEGER NOT NULL,
    UNIQUE (num_saison, id_serie),
    FOREIGN KEY (id_serie) REFERENCES series (id_serie) ON DELETE CASCADE
);



CREATE TABLE episodes (
    id_episode SERIAL PRIMARY KEY,
    id_saison INTEGER NOT NULL,
    num_episode INTEGER NOT NULL,
    UNIQUE (id_saison, num_episode),  -- Ajout de la contrainte UNIQUE
    FOREIGN KEY (id_saison) REFERENCES saisons (id_saison) ON DELETE CASCADE
);



CREATE TABLE films (
    id_film SERIAL PRIMARY KEY,
    nom_film VARCHAR(255) UNIQUE NOT NULL
);



CREATE TABLE visionnages (
    id_visionnage SERIAL PRIMARY KEY,
    id_profil INTEGER NOT NULL,
    id_episode INTEGER,
    id_film INTEGER,
    id_serie INTEGER,     
    id_saison INTEGER,        
    debut_visionnage TIMESTAMP NOT NULL,
    duree_visionnage INTERVAL NOT NULL,
    FOREIGN KEY (id_profil) REFERENCES profils (id_profil) ON DELETE CASCADE,
    FOREIGN KEY (id_episode) REFERENCES episodes (id_episode) ON DELETE CASCADE,
    FOREIGN KEY (id_film) REFERENCES films (id_film) ON DELETE CASCADE,
    FOREIGN KEY (id_serie) REFERENCES series (id_serie) ON DELETE CASCADE,
    FOREIGN KEY (id_saison) REFERENCES saisons (id_saison) ON DELETE CASCADE,
    CHECK ( (id_episode IS NOT NULL AND id_film IS NULL) OR (id_film IS NOT NULL AND id_episode IS NULL) )
);



CREATE TABLE appareils (
    id_appareil SERIAL PRIMARY KEY,
    type_appareil VARCHAR(255) UNIQUE NOT NULL
);



CREATE TABLE visionnage_appareil (
    id_visionnage INTEGER NOT NULL,
    id_appareil INTEGER NOT NULL,
    FOREIGN KEY (id_visionnage) REFERENCES visionnages (id_visionnage) ON DELETE CASCADE,
    FOREIGN KEY (id_appareil) REFERENCES appareils (id_appareil) ON DELETE CASCADE,
    PRIMARY KEY (id_visionnage, id_appareil)
);
