#!/bin/bash
set -e

mkdir -p dataset

URLS=("name.basics" "title.akas" "title.basics" "title.crew" "title.episode" "title.principals" "title.ratings")


for URL in "${URLS[@]}"
do
  wget "https://datasets.imdbws.com/${URL}.tsv.gz" -P dataset
  gzip -d "./dataset/${URL}.tsv.gz"
done


