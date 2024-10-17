module.exports = async function(client){

  const requete = 
  `
    SELECT s.nom_serie, COUNT(DISTINCT e.id_episode) AS nombre_episodes
    FROM episodes e
    JOIN saisons sa ON e.id_saison = sa.id_saison
    JOIN series s ON sa.id_serie = s.id_serie
    GROUP BY s.nom_serie
    ORDER BY nombre_episodes DESC
    LIMIT 1;
  `;

  const result = await client.query(requete)
  console.log(result.rows)
}
