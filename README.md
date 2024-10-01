# TP PostgreSQL
A base code for the [PostgreSQL](https://decima.notion.site/Exercice-PostgreSQL-a7e47b9571974e9c85c64a90354c63f5)

## Requirements
- NodeJS
- Docker with Docker-compose


## Getting started

### Installation
make a copy of `.env.sample` and name it `.env`.
This file is by default configured to run with the docker-compose or local redis installation.

Then run `yarn` or `npm install` depending on your environment.

### Start PostgreSQL with Docker

Start Postgresql server using `docker-compose up -d`. PostgreSQL port is `5432` and mongo express `8081`.


### Usage

Every exercices should be stored in exercices folder.
To run them just run the following command : 

```
npm run start ex0
```

If you have `yarn` you can run
```
yarn start ex0
```
It will automatically use the file `./exercices/ex0.js`.

In the Exercices folder, you can find a `ex0.js`, a sample for you to create new exercices.

All exercices can be found on [course.larget.fr](https://decima.notion.site/Exercice-PostgreSQL-a7e47b9571974e9c85c64a90354c63f5)

---

### Datasets

IMDB :
- https://datasets.imdbws.com/name.basics.tsv.gz
- https://datasets.imdbws.com/title.akas.tsv.gz
- https://datasets.imdbws.com/title.basics.tsv.gz
- https://datasets.imdbws.com/title.crew.tsv.gz
- https://datasets.imdbws.com/title.episode.tsv.gz
- https://datasets.imdbws.com/title.principals.tsv.gz
- href=https://datasets.imdbws.com/title.ratings.tsv.gz