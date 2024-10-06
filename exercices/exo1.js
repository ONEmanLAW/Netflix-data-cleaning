module.exports = async function(client){

  const requete = 
  `
    SELECT COUNT(*) AS nombre_de_profils, ARRAY_AGG(nom_profil) AS profils_disponibles
    FROM profils;
  `;

  const result = await client.query(requete)
  console.log(result.rows)
}