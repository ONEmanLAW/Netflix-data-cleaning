module.exports = async function(client){

  const query = `
    SELECT COUNT(*) AS nombre_de_profils, ARRAY_AGG(nom_profil) AS profils_disponibles
    FROM profils;
  `;

  const result = await client.query(query)
  console.log(result.rows)
}