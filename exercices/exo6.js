module.exports = async function(client){

  const requete = 
  `
  SELECT
    s.nom_serie,
    TO_CHAR(v.debut_visionnage, 'YYYY-MM-DD HH24:MI:SS') AS debut_visionnage
  FROM
    visionnages v
  JOIN
    episodes e ON v.id_episode = e.id_episode
  JOIN
    saisons sa ON e.id_saison = sa.id_saison
  JOIN
    series s ON sa.id_serie = s.id_serie
  JOIN
    profils p ON v.id_profil = p.id_profil
  WHERE
    p.nom_profil = 'Okona'
  ORDER BY
    v.debut_visionnage DESC
  LIMIT 1;

  `;

  const result = await client.query(requete)
  console.log(result.rows)
}