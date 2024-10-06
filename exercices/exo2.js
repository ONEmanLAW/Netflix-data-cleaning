module.exports = async function(client){

  const requete = 
  `
    WITH date_par_profil AS (
    SELECT 
      p.nom_profil,
      MIN(v.debut_visionnage) AS date_abonnement
    FROM 
      profils p
    JOIN 
      visionnages v ON p.id_profil = v.id_profil
    GROUP BY 
      p.nom_profil )

    SELECT 
      dp.nom_profil,
      dp.date_abonnement,
      (SELECT MIN(v2.debut_visionnage) FROM visionnages v2) AS date_creation_compte
    FROM 
      date_par_profil dp;
  `;

  const result = await client.query(requete)
  console.log(result.rows)
}


// Requete marche correctement dans la BDD.

