module.exports = async function(client){

  const requete = 
  `
    WITH series_viewed AS (
      SELECT DISTINCT s.nom_serie AS nom
      FROM visionnages v
      JOIN episodes e ON v.id_episode = e.id_episode
      JOIN saisons sa ON e.id_saison = sa.id_saison
      JOIN series s ON sa.id_serie = s.id_serie
      WHERE EXTRACT(YEAR FROM v.debut_visionnage) = 2020
    ),

    films_viewed AS (
      SELECT DISTINCT f.nom_film AS nom
      FROM visionnages v
      JOIN films f ON v.id_film = f.id_film
      WHERE EXTRACT(YEAR FROM v.debut_visionnage) = 2020
    )

    SELECT 'Série' AS type, nom FROM series_viewed
    UNION ALL
    SELECT 'Film' AS type, nom FROM films_viewed
    UNION ALL
    SELECT 'Total Séries' AS type, COUNT(*)::text AS nom FROM series_viewed
    UNION ALL
    SELECT 'Total Films' AS type, COUNT(*)::text AS nom FROM films_viewed
    UNION ALL
    SELECT 'Total Séries et Films' AS type, (COUNT(*)::text) AS nom
    FROM (
      SELECT nom FROM series_viewed
      UNION ALL
      SELECT nom FROM films_viewed
    ) total_viewed;
  `;

  const result = await client.query(requete)
  console.log(result.rows)
};



