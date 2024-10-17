module.exports = async function(client){

  const requete = 
  `
    SELECT
      COUNT(*) AS nb_saisons
    FROM
      saisons s
    JOIN
      series ser ON s.id_serie = ser.id_serie
    WHERE
      ser.nom_serie = 'Friends';
  `;

  const result = await client.query(requete)
  console.log(result.rows)
};



