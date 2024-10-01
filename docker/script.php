<?php

function readTSVFile($path)
{
    $file = fopen($path, "r");
    $header = fgetcsv($file, 0, "\t");
    $rowC = 0;
    $row = [];
    while (!feof($file)) {

        try {
            $row = fgetcsv($file, 0, "\t");
            $rowC++;

            yield array_combine($header, $row);
        } catch (Error|TypeError $error) {
            //do nothing.
            print_r($header);
            print_r($row);
            print_r($rowC);
        }
    }
    fclose($file);


}

function generatePerson()
{
    @unlink("dataset/person.sql");
    $output = fopen("dataset/persons.sql", "w+");
    fputs($output, "DROP TABLE person;
 CREATE TABLE IF NOT EXISTS person(
 id varchar(255) PRIMARY KEY,
 name varchar(255),
 birthyear integer,
 deathyear integer
 ); ");

    fputs($output, "INSERT INTO person(id, name, birthyear, deathyear) VALUES ");
    foreach (readTSVFile("dataset/name.basics.tsv") as $i => $row) {
        if ($i > 0) {
            fputs($output, ",");
        }

        fputs($output, "(" . implode(",", [
                    "'" . $row["nconst"] . "'",
                    "'" . str_replace("'", "''", $row["primaryName"]) . "'",
                    ($row["birthYear"] != "\\N" ? $row["birthYear"] : "NULL"),
                    ($row["deathYear"] != "\\N" ? $row["deathYear"] : "NULL")]
            ) . ")");
    }
}

function generateGenres()
{
    @unlink("dataset/genres.sql");
    $output = fopen("dataset/genres.sql", "w+");
    fputs($output, "DROP TABLE genre;
 CREATE TABLE IF NOT EXISTS genre(
 id varchar(255) PRIMARY KEY,
 genre varchar(255)
 ); ");

    $titleGenres = [];
    foreach (readTSVFile("dataset/title.basics.tsv") as $i => $row) {
        $genres = explode(",", $row["genres"]);
        foreach ($genres as $g) {
            $titleGenres[sha1($g)] = $g;
        }
    }
    fputs($output, "INSERT INTO genre(id, genre) VALUES ");
    foreach ($titleGenres as $shaG => $g) {
        fputs($output, "('" . $shaG . "','" . $g . "'),");

    }
}


function generateGenreLinks()
{
    @unlink("dataset/movie_genre.sql");
    $output = fopen("dataset/movie_genre.sql", "w+");
    fputs($output, "DROP TABLE title_genre;
    CREATE TABLE IF NOT EXISTS title_genre(
        title_id varchar(255),
        genre_id varchar(255),
        PRIMARY KEY(title_id, genre_id)
     ); ");

    fputs($output, "INSERT INTO title_genre(title_id, genre_id) VALUES ");
    foreach (readTSVFile("dataset/title.basics.tsv") as $i => $row) {
        $genres = explode(",", $row["genres"]);
        foreach ($genres as $k => $g) {
            if (!($i == 0 && $k == 0)) {
                fputs($output, ",\n");
            }
            $shaG = sha1($g);
            fputs($output, "('" . $row["tconst"] . "','" . $shaG . "')");
        }
    }
}


function generateFormats()
{

    @unlink("dataset/formats.sql");
    $output = fopen("dataset/formats.sql", "w+");
    fputs($output, "DROP TABLE format;
 CREATE TABLE IF NOT EXISTS format(
 id varchar(255) PRIMARY KEY,
 format varchar(255)
 ); ");

    $formatsDB = [];
    foreach (readTSVFile("dataset/title.basics.tsv") as $i => $row) {
        $formats = explode(",", $row["titleType"]);
        foreach ($formats as $g) {
            $formatsDB[sha1($g)] = $g;
        }
    }
    fputs($output, "INSERT INTO format(id, format) VALUES ");
    $ct = 0;
    foreach ($formatsDB as $shaG => $g) {
        if ($ct > 0) {
            fputs($output, ",\n");
        }
        fputs($output, "('" . $shaG . "','" . $g . "')");
        $ct++;
    }
}


function generateMovies()
{

    @unlink("dataset/movies.sql");
    $output = fopen("dataset/movies.sql", "w+");
    fputs($output, "DROP TABLE title;
 CREATE TABLE IF NOT EXISTS title(
     id varchar(255) PRIMARY KEY,
     format varchar(255),
     primary_title TEXT,
     original_title TEXT,
     isAdult bool,
     startYear integer,
     endYear integer,
     duration integer
 ); ");

    $formatsDB = [];
    fputs($output, "INSERT INTO title(id, format, primary_title, original_title, isAdult, startYear, endYear, duration) VALUES ");

    foreach (readTSVFile("dataset/title.basics.tsv") as $i => $row) {
        if ($i > 0) {
            fputs($output, ",\n");
        }

        fputs($output, "(" . implode(",", [
                    "'" . $row["tconst"] . "'",
                    "'" . sha1($row["titleType"]) . "'",
                    "'" . str_replace("'", "''", $row["primaryTitle"]) . "'",
                    "'" . str_replace("'", "''", $row["originalTitle"]) . "'",
                    $row["isAdult"] == 0 ? 'FALSE' : 'TRUE',
                    ($row["startYear"] != "\\N" ? $row["startYear"] : "NULL"),
                    ($row["endYear"] != "\\N" ? $row["endYear"] : "NULL"),
                    ($row["runtimeMinutes"] != "\\N" ? $row["runtimeMinutes"] : "NULL")]
            ) . ")");
    }
}

//generateMovies();


function generateRoles()
{
    @unlink("dataset/role.sql");
    $output = fopen("dataset/role.sql", "w+");
    fputs($output, "DROP TABLE IF EXISTS role;
 CREATE TABLE IF NOT EXISTS role(
     id varchar(255) PRIMARY KEY,
     role varchar(255)
 ); ");
    $formatsDB = [];
    foreach (readTSVFile("dataset/title.principals.tsv") as $i => $row) {
        $formatsDB[sha1($row["category"])] = $row["category"];
    }
    fputs($output, "INSERT INTO role(id, role) VALUES ");
    $ct = 0;
    foreach ($formatsDB as $shaG => $g) {
        if ($ct > 0) {
            fputs($output, ",\n");
        }
        fputs($output, "('" . $shaG . "','" . $g . "')");
        $ct++;
    }
}

//generateRoles();


function generateCrew()
{

    @unlink("dataset/crew.sql");
    $output = fopen("dataset/crew.sql", "w+");
    fputs($output, "DROP TABLE crew;
 CREATE TABLE IF NOT EXISTS crew(
     title_id varchar(255),
     person_id varchar(255),
     role_id varchar(255),
     job varchar(255),
     PRIMARY KEY (title_id, person_id, role_id)
     
 ); ");

    fputs($output, "INSERT INTO crew(title_id, person_id, role_id, job) VALUES ");
    echo "\n";
    foreach (readTSVFile("dataset/title.principals.tsv") as $i => $row) {
        if ($i > 0) {
            fputs($output, ",\n");
            if ($i % 100000 === 0) {
                echo "\r" . $i;
            }
        }

        fputs($output, "(" . implode(",", [
                    "'" . $row["tconst"] . "'",
                    "'" . $row["nconst"] . "'",
                    "'" . sha1($row["category"]) . "'",
                    ($row["job"] != "\\N" ? "'" . str_replace("'", "''", $row["job"]) . "'" : "NULL"),]
            ) . ")");
    }
    echo "\r" . $i . "\n";
}


generateCrew();