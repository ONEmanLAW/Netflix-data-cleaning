module.exports = async function(client){

  const requete = 
  `
    SELECT
      TO_CHAR(MIN(debut_visionnage), 'YYYY-MM-DD HH24:MI:SS') AS date_creation_compte
    FROM
      visionnages;
  `;

  const result = await client.query(requete)
  console.log(result.rows)
}

