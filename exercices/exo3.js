module.exports = async function(client){

  const requete = 
  `
    SELECT 
    p.nom_profil,
    CONCAT(
      FLOOR(SUM(EXTRACT(EPOCH FROM v.duree_visionnage)) / 3600), 'H  ', -- heures
      FLOOR(MOD(SUM(EXTRACT(EPOCH FROM v.duree_visionnage)) / 60, 60)), 'M  ', -- minutes
      FLOOR(MOD(SUM(EXTRACT(EPOCH FROM v.duree_visionnage)), 60)), 'S  ' -- secondes
    ) AS total_temps_visionnage
    FROM 
      profils p
    JOIN 
      visionnages v ON p.id_profil = v.id_profil
    GROUP BY 
      p.nom_profil;
  `;

  const result = await client.query(requete)
  console.log(result.rows)
}



