#!/bin/bash
set -e


USERS=( mathieu nils valentin mathis )

for NAME in "${USERS[@]}"
do
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
CREATE USER $NAME;
CREATE DATABASE exo1_$NAME;

ALTER USER $NAME WITH PASSWORD 'tp1_$NAME';

GRANT ALL PRIVILEGES ON DATABASE exo1_$NAME TO $NAME;

\connect exo1_$NAME;
\i /ex1.sql

EOSQL
done


psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
CREATE DATABASE imdb;
EOSQL

for NAME in "${USERS[@]}"
do
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
  \connect imdb;
  GRANT CONNECT ON DATABASE imdb TO $NAME;
  GRANT USAGE ON SCHEMA public TO $NAME;
  GRANT SELECT ON ALL TABLES IN SCHEMA public TO $NAME;
  ALTER DEFAULT PRIVILEGES IN SCHEMA public  GRANT SELECT ON TABLES TO $NAME;
EOSQL

done
