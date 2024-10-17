module.exports = async function(client) {

  // Requête pour les films
  const requeteFilms = `
    SELECT f.nom_film
    FROM visionnages v
    JOIN films f ON v.id_film = f.id_film
    JOIN profils p ON v.id_profil = p.id_profil
    WHERE p.id_profil != 9065  -- Exclure le profil 'À voir ensemble'
    GROUP BY f.nom_film
    HAVING COUNT(DISTINCT p.id_profil) = (
      SELECT COUNT(*) 
      FROM profils 
      WHERE id_profil != 9065  --  Sauf À voir ensemble
    );
  `;

  // Requête pour les séries
  const requeteSeries = `
    SELECT s.nom_serie
    FROM visionnages v
    JOIN episodes e ON v.id_episode = e.id_episode
    JOIN saisons sa ON e.id_saison = sa.id_saison
    JOIN series s ON sa.id_serie = s.id_serie
    JOIN profils p ON v.id_profil = p.id_profil
    WHERE p.id_profil != 9065  -- Exclure le profil 'À voir ensemble'
    GROUP BY s.nom_serie
    HAVING COUNT(DISTINCT p.id_profil) = (
      SELECT COUNT(*) 
      FROM profils 
      WHERE id_profil != 9065  --  Sauf À voir ensemble
    );
  `;

  try {
    const resultFilms = await client.query(requeteFilms);
    console.log('Films vus ensemble":');
    console.log(resultFilms.rows);

    const resultSeries = await client.query(requeteSeries);
    console.log('Séries vues ensemble');
    console.log(resultSeries.rows);
  } catch (err) {
    console.error('Erreur', err);
  }
};
