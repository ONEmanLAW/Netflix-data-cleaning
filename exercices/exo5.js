module.exports = async function(client){

  const requete = 
  `
   SELECT 
    TO_CHAR(MIN(v.debut_visionnage), 'YYYY-MM-DD HH24:MI:SS') AS debut_visionnage_total,
    TO_CHAR(MAX(v.debut_visionnage + (EXTRACT(EPOCH FROM v.duree_visionnage) * INTERVAL '1 second')), 'YYYY-MM-DD HH24:MI:SS') AS fin_visionnage_total
  FROM 
    visionnages v
    JOIN episodes e ON v.id_episode = e.id_episode
    JOIN saisons sa ON e.id_saison = sa.id_saison
    JOIN series s ON sa.id_serie = s.id_serie
  WHERE 
    s.nom_serie = 'Les nouvelles aventures de Sabrina'
    AND sa.num_saison = 3
    AND e.num_episode = 7;


  `;

  const result = await client.query(requete)
  console.log(result.rows)
}