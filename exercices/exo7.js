module.exports = async function(client){

  const requete = 
  `
  SELECT
    f.nom_film,
    v.duree_visionnage
  FROM
    visionnages v
  JOIN
    films f ON v.id_film = f.id_film
  WHERE
    v.id_film IS NOT NULL
  ORDER BY
    v.duree_visionnage DESC
  LIMIT 1;
  `;

  const result = await client.query(requete)
  console.log(result.rows)
}