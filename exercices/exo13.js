module.exports = async function(client){

  const requete = 
  `
   SELECT COUNT(*) AS total_visionnages_community_plus_20min
    FROM visionnages v
    JOIN episodes e ON v.id_episode = e.id_episode
    JOIN saisons sa ON e.id_saison = sa.id_saison
    JOIN series s ON sa.id_serie = s.id_serie
    WHERE s.nom_serie = 'Community'
    AND EXTRACT(EPOCH FROM v.duree_visionnage) / 60 > 20;  
  `;

  const result = await client.query(requete)
  console.log(result.rows)
};
