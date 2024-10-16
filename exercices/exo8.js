module.exports = async function(client) {

  const querySeries = `
    SELECT s.nom_serie
    FROM visionnages v
    JOIN profils p ON v.id_profil = p.id_profil
    JOIN episodes e ON v.id_episode = e.id_episode
    JOIN saisons sa ON e.id_saison = sa.id_saison
    JOIN series s ON sa.id_serie = s.id_serie
    WHERE p.nom_profil = 'Henri'
    GROUP BY s.nom_serie
    LIMIT 10;
  `;

  const queryCount = `
    SELECT COUNT(DISTINCT s.nom_serie) AS nombre_series
    FROM visionnages v
    JOIN profils p ON v.id_profil = p.id_profil
    JOIN episodes e ON v.id_episode = e.id_episode
    JOIN saisons sa ON e.id_saison = sa.id_saison
    JOIN series s ON sa.id_serie = s.id_serie
    WHERE p.nom_profil = 'Henri';
  `;

  try {
    const resultSeries = await client.query(querySeries);
    console.log('Séries regardées:', resultSeries.rows);

    const resultCount = await client.query(queryCount);
    console.log('Total de séries:', resultCount.rows[0].nombre_series);
  } catch (err) {
    console.error('Erreur', err);
  }
};
