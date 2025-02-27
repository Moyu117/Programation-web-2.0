<?php

require_once 'config.php';
require_once 'fonction.inc.php';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>recettes de cocktails</title>
    <style>
        .top-right {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        body {
            margin: 50px;
        }
    </style>
</head>
<body>
<div class="top-right">
    <?php
    if (isset($_SESSION['user'])) {
        // deja connecter
        echo "bonjour, " . htmlspecialchars($_SESSION['user']['prenom']) . " | ";
        echo "<a href='login_register.php?action=logout'>deconnecter</a> | ";
        echo "<a href='login_register.php?action=edit'>Modifier les informations</a>";
    } else {
        // non connecter
        ?>
        <form method="POST" action="login_register.php">
            <input type="hidden" name="action" value="login">
            <input type="text" name="login" placeholder="nom d'utilisateur" required>
            <input type="text" name="password" placeholder="mot de passe" required>
            <button type="submit">Se connecter</button>
            <a href="login_register.php?action=registerForm">registre</a>
        </form>
        <?php
    }
    ?>
</div>


<nav>
    <ul>
        <?php
        // Afficher "Créer une base de données" uniquement si la base de données n'a pas encore été installée
        if (!check_db_installed($db_host, $db_user, $db_pass, $db_name)) {
            echo "<li><a href='install.php'>Créer une base de données</a></li>";
        }
        ?>
        <li><a href="index.php">page d'accueil</a></li>
        <li><a href="recettes.php?action=all">Voir toutes les recettes</a></li>
        <?php if (isset($_SESSION['user'])): ?>
            <li><a href="ingredients.php">Afficher par ingrédients</a></li>
            <li><a href="favorites.php?action=myFav">ma collection</a></li>
            <li><a href="admin.php">Afficher tous les favoris des utilisateurs</a></li>
        <?php endif; ?>
    </ul>
</nav>
<hr>