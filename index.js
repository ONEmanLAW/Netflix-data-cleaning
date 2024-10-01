const {exit} = require('process');
const {Client} = require("pg");
const colors = require('colors');
const fs = require("fs");
require('dotenv').config();

(async () => {
    const client = new Client({connectionString: process.env.PG_URL});

    try {
        await client.connect();
        console.log(`👑 PostgreSQL connected`.green);

        const cmd = process.argv.slice(2)[0];
        // Charger le fichier dans create_tables.sql
        if (cmd === 'newTablesSql') {
            try {
                const newTablesSql = fs.readFileSync("create_tables.sql", { encoding: "utf8" });
                await client.query(newTablesSql);
                console.log("📄 Tables créées avec succès !".green);
            } catch (error) {
                console.log("❌ Erreur lors de l'exécution du fichier SQL :".white.bgRed.bold, error);
            }   
        } else  {
            // Charger le fichier dans 'exercices'
            let exercice = null;
            try {
                exercice = require(`./exercices/${cmd}.js`);
                console.log(`🦊 Exercice ${cmd} found`.green);

            } catch (error) {
                console.error(`😭 Cannot find ${cmd}.js in exercices or ${cmd} contains errors`.white.bgRed.bold);
                console.debug(error);
                exit(100)
            }
            console.log(`🍣 Starting ${cmd}`.green);
            try {
                await exercice(client);
            } catch (error) {
                console.log(`😱 An error occured`.red.bold);
                console.log(error);
            }
        }
    } catch (error) {
        console.error(`😱 Something went wrong`.white.bgRed.bold);
        console.error(error);

    } finally {
        client.end();
        console.log(`👋 Closing PostgreSQL`.gray);
        exit(0);
    }
})();
