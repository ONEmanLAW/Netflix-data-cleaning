module.exports = async function(client){

  const requete = 
  `
    SELECT
      MIN(debut_visionnage) AS date_creation_compte
    FROM
      visionnages;
  `;

  const result = await client.query(requete)
  console.log(result.rows)
}

