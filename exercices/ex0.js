module.exports = async function(client){

    // duplicate this file to add other exercices.
    const result = await client.query('SELECT NOW() as field1')
    console.log(result.rows[0].field1)
    console.log("🎉 Exercice 0 is a sample on how to create a new file ".black.bgGreen);
    
}