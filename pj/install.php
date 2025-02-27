<?php
// install.php
require_once 'config.php';
require_once 'fonction.inc.php'; // query, db_connect

// creer
if (isset($_GET['action']) && $_GET['action'] === 'create') {
    //connecter
    $link = db_connect($db_host, $db_user, $db_pass);

    //creer DB
	$createDBSql = "CREATE DATABASE IF NOT EXISTS `$db_name`";
    query($link, $createDBSql);

    //choisir tab
    mysqli_select_db($link, $db_name) or die(mysqli_error($link));

    // evider de redoubler
    $dropTables = [
        "favorites",
        "recettes_ingredients",
        "ingredients",
        "recettes",
        "users"
    ];
    foreach ($dropTables as $t) {
        $sql = "DROP TABLE IF EXISTS `$t`";
        query($link, $sql);
    }

    //cree tab users
    $sqlUsers = "
        CREATE TABLE users (
            login VARCHAR(50) PRIMARY KEY,
            password VARCHAR(255) NOT NULL,
            prenom VARCHAR(50) NOT NULL,
            age INT NOT NULL
        )
    ";
    query($link, $sqlUsers);

    //creer tab recettes
    $sqlrecettes = "
        CREATE TABLE recettes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            ingredients_brut TEXT,
            preparation TEXT,
            photo VARCHAR(255)
        ) 
    ";
    query($link, $sqlrecettes);

    //creer tab ingredients
    $sqlIngredients = "
        CREATE TABLE ingredients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(255) NOT NULL
        ) 
    ";
    query($link, $sqlIngredients);

    //creer tab recettes_ingredients
    $sqlrecettesing = "
        CREATE TABLE recettes_ingredients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            recettes_id INT NOT NULL,
            ingredient_id INT NOT NULL,
            FOREIGN KEY (recettes_id) REFERENCES recettes(id),
            FOREIGN KEY (ingredient_id) REFERENCES ingredients(id)
        ) 
    ";
    query($link, $sqlrecettesing);

    //creer tab favorites
    $sqlFavorites = "
        CREATE TABLE favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_login VARCHAR(50) NOT NULL,
            recettes_id INT NOT NULL,
            FOREIGN KEY (user_login) REFERENCES users(login),
            FOREIGN KEY (recettes_id) REFERENCES recettes(id)
        ) 
    ";
    query($link, $sqlFavorites);

    //Donnees.inc.php =>$Recettes
    include 'Donnees.inc.php';

    foreach ($Recettes as $r) {
        $titre        = mysqli_real_escape_string($link, $r['titre']);
        $ing_brut     = mysqli_real_escape_string($link, $r['ingredients']);
        $preparation  = mysqli_real_escape_string($link, $r['preparation']);

        // dans recettes
        $sqlinsertrecettes = "
            INSERT INTO recettes (titre, ingredients_brut, preparation, photo)
            VALUES ('$titre', '$ing_brut', '$preparation', NULL)
        ";
        query($link, $sqlinsertrecettes);
        $newrecettesId = mysqli_insert_id($link);

        // ingrédients
        if (!empty($r['index'])) {
            foreach ($r['index'] as $ingname) {
                $ingname = trim($ingname);
                if ($ingname === '') continue;

                $ingnameexi = mysqli_real_escape_string($link, $ingname);

                // tester si ingredients exist
                $sqlCheck = "SELECT id FROM ingredients WHERE nom='$ingnameexi' LIMIT 1";
                $resCheck = query($link, $sqlCheck);
                if ($row = mysqli_fetch_assoc($resCheck)) {
                    $ingredientId = $row['id'];
                } else {
                    //non exist
                    $sqlInsertIng = "INSERT INTO ingredients (nom) VALUES ('$ingnameexi')";
                    query($link, $sqlInsertIng);
                    $ingredientId = mysqli_insert_id($link);
                }

                // relation
                $sqlLink = "INSERT INTO recettes_ingredients (recettes_id, ingredient_id) VALUES ($newrecettesId, $ingredientId)";
                query($link, $sqlLink);
            }
        }
    }

    echo "La base de données et les tables ont été créées et les données de la recette ont été importées avec succès!<br>";
    echo "<a href='index.php'>Retour à la page d'accueil</a>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>install base de donnée</title>
</head>
<body>
<h1>creer base de donnée</h1>
<p>
    <a href="install.php?action=create">Cliquez ici pour créer une base de donnée et importer des données</a>
</p>
</body>
</html>
