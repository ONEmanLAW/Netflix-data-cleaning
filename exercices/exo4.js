module.exports = async function(client){

  const requete = 
  `
    WITH RankedEquipments AS (
      SELECT
        p.nom_profil,
        a.type_appareil,
        COUNT(va.id_appareil) AS utilisation_appareil,
        ROW_NUMBER() OVER (PARTITION BY p.nom_profil ORDER BY COUNT(va.id_appareil) DESC) AS rank_appareil
      FROM
        visionnages v
      JOIN profils p ON v.id_profil = p.id_profil
      JOIN visionnage_appareil va ON va.id_visionnage = v.id_visionnage
      JOIN appareils a ON va.id_appareil = a.id_appareil
      GROUP BY p.nom_profil, a.type_appareil
  )
    SELECT
      nom_profil,
      type_appareil,
      utilisation_appareil,
    CASE
      WHEN rank_appareil = 1 THEN 'Top 1'
      WHEN rank_appareil = 2 THEN 'Top 2'
      WHEN rank_appareil = 3 THEN 'Top 3'
      WHEN rank_appareil = 4 THEN 'Top 4'
    END AS classement
    FROM
      RankedEquipments
    WHERE
      rank_appareil <= 4
    ORDER BY
      nom_profil,
      rank_appareil;
  `;

  const result = await client.query(requete)
  console.log(result.rows)
}