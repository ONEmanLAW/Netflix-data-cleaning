module.exports = async function(client) {
  try {
    
    const visionnagesQuery = `
      SELECT COUNT(*) AS total_visionnages_community_plus_20min
      FROM visionnages v
      JOIN episodes e ON v.id_episode = e.id_episode
      JOIN saisons sa ON e.id_saison = sa.id_saison
      JOIN series s ON sa.id_serie = s.id_serie
      WHERE s.nom_serie = 'Community'
      AND EXTRACT(EPOCH FROM v.duree_visionnage) / 60 > 20;
    `;

  
    const visionnagesResult = await client.query(visionnagesQuery);
    const totalVisionnages = visionnagesResult.rows[0].total_visionnages_community_plus_20min;

    
    const completeViewQuery = `
      WITH episodes_community AS (
        SELECT e.id_episode
        FROM episodes e
        JOIN saisons sa ON e.id_saison = sa.id_saison
        JOIN series s ON sa.id_serie = s.id_serie
        WHERE s.nom_serie = 'Community'
      ),
      durations AS (
        SELECT 
            v.id_episode, 
            SUM(EXTRACT(EPOCH FROM v.duree_visionnage)) AS total_duration
        FROM visionnages v
        GROUP BY v.id_episode
      ),
      episodes_vus AS (
        SELECT 
            e.id_episode
        FROM episodes_community e
        JOIN durations d ON e.id_episode = d.id_episode
        WHERE d.total_duration >= 1200  -- Total duration in seconds (20 minutes)
      ),
      series_vues_entierement AS (
        SELECT 
            v.id_profil, 
            COUNT(DISTINCT e.id_episode) AS episodes_vus
        FROM visionnages v
        JOIN episodes_vus e ON v.id_episode = e.id_episode
        GROUP BY v.id_profil
        HAVING COUNT(DISTINCT e.id_episode) = (
            SELECT COUNT(*) 
            FROM episodes_community
        )
      )
      SELECT COUNT(*) AS nb_fois_vu_entierement
      FROM series_vues_entierement;
    `;

   
    const completeViewResult = await client.query(completeViewQuery);
    const nbFoisVuEntierement = completeViewResult.rows[0].nb_fois_vu_entierement;

    
    console.log(`Total visionnages de Community de plus de 20 minutes : ${totalVisionnages}`);
    console.log(`Nombre de fois que Community a été vue entièrement : ${nbFoisVuEntierement}`);

  } catch (error) {
    console.error('Erreur', error);
  }
};
