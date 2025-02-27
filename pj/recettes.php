<?php
//recettes
require_once 'header.php'; 

$link = db_connect($db_host, $db_user, $db_pass, $db_name);

$action = isset($_GET['action']) ? $_GET['action'] : 'all';

// 1. Voir toutes les recettes
if ($action === 'all') {
    $sqlAll = "SELECT * FROM recettes ORDER BY titre";
    $resAll = query($link, $sqlAll);

    echo "<h1>Liste de toutes les recettes</h1>";

    while ($recettes = mysqli_fetch_assoc($resAll)) {
        $rid = $recettes['id'];
        $titre = htmlspecialchars($recettes['titre']);

        echo "<div style='margin-bottom: 10px;'>";
        echo "<strong><a href='recettes.php?action=detail&id=$rid'>$titre</a></strong><br>";

        // si connect,afficher botton de favorites
        if (isset($_SESSION['user'])) {
            $loginEsc = mysqli_real_escape_string($link, $_SESSION['user']['login']);
            $ridEsc = mysqli_real_escape_string($link, $rid);
            $sqlCheckFav = "SELECT id FROM favorites WHERE user_login='$loginEsc' AND recettes_id=$ridEsc";
            $resCheckFav = query($link, $sqlCheckFav);
            if (mysqli_fetch_assoc($resCheckFav)) {
                // Collecté-> supprimer
                echo "<a href='favorites.php?action=toggle&rid=$rid'>♥</a>";
            } else {
                // non Collecté-> ajouter
                echo "<a href='favorites.php?action=toggle&rid=$rid'>♡</a>";
            }
        }

        // Liste des ingrédients
        $sqlIng = "
            SELECT i.nom
            FROM recettes_ingredients re
            JOIN ingredients i ON re.ingredient_id = i.id
            WHERE re.recettes_id=$rid
        ";
        $resIng = query($link, $sqlIng);
        echo "<ul>";
        while ($rowIng = mysqli_fetch_assoc($resIng)) {
            $ingName = htmlspecialchars($rowIng['nom']);
            $ingNameUrl = urlencode($rowIng['nom']);
            echo "<li><a href='recettes.php?action=byIngredient&name=$ingNameUrl'>$ingName</a></li>";
        }
        echo "</ul>";

        echo "</div><hr>";
    }

    echo "</body></html>";
    exit;
}

// 2. les détails de la recette
if ($action === 'detail' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sqlR = "SELECT * FROM recettes WHERE id=$id";
    $resR = query($link, $sqlR);
    $recettes = mysqli_fetch_assoc($resR);
    if (!$recettes) {
        echo "<p>Cette recette n'a pas été trouvée</p></body></html>";
        exit;
    }

    echo "<h2>".htmlspecialchars($recettes['titre'])."</h2>";

    // Bouton Favoris/Défavorisés
    if (isset($_SESSION['user'])) {
        $loginEsc = mysqli_real_escape_string($link, $_SESSION['user']['login']);
        $ridEsc = mysqli_real_escape_string($link, $id);
        $sqlFavCheck = "SELECT id FROM favorites WHERE user_login='$loginEsc' AND recettes_id=$ridEsc";
        $resFavCheck = query($link, $sqlFavCheck);
        if (mysqli_fetch_assoc($resFavCheck)) {
            echo "<a href='favorites.php?action=toggle&rid=$id'>♥</a><br><br>";
        } else {
            echo "<a href='favorites.php?action=toggle&rid=$id'>♡</a><br><br>";
        }
    }

    // Liste des ingrédients
    $sqlIng = "
        SELECT i.nom
        FROM recettes_ingredients ri
        JOIN ingredients i ON ri.ingredient_id = i.id
        WHERE ri.recettes_id=$id
    ";
    $resIng = query($link, $sqlIng);
    echo "<h3>Ingrédients:</h3><ul>";
    while ($rowI = mysqli_fetch_assoc($resIng)) {
        $ingName = htmlspecialchars($rowI['nom']);
        $ingNameUrl = urlencode($rowI['nom']);
        echo "<li><a href='recettes.php?action=byIngredient&name=$ingNameUrl'>$ingName</a></li>";
    }
    echo "</ul>";

    echo "<h3>facon:</h3>";
    echo "<p>".nl2br(htmlspecialchars($recettes['preparation']))."</p>";

    echo "</body></html>";
    exit;
}

// 3. chercher par ingrédients
if ($action === 'byIngredient' && isset($_GET['name'])) {
    $ingName = $_GET['name'];
    $ingNameEsc = mysqli_real_escape_string($link, $ingName);

    // trouver ingredient
    $sqlFind = "SELECT id FROM ingredients WHERE nom='$ingNameEsc' LIMIT 1";
    $resFind = query($link, $sqlFind);
    $ingRow = mysqli_fetch_assoc($resFind);
    if (!$ingRow) {
        echo "<p>L'ingrédient n'a pas été trouvé ou il n'existe pas de recette correspondante</p></body></html>";
        exit;
    }
    $ingId = (int)$ingRow['id'];

    // Trouver recette contenant cet ingrédient
    $sqlRec = "
        SELECT r.id, r.titre
        FROM recettes_ingredients re
        JOIN recettes r ON re.recettes_id = r.id
        WHERE re.ingredient_id=$ingId
        ORDER BY r.titre
    ";
    $resRec = query($link, $sqlRec);

    echo "<h2>Contient des ingrédients：".htmlspecialchars($ingName)." recettes</h2>";
    while ($r = mysqli_fetch_assoc($resRec)) {
        $rid = $r['id'];
        $titre = htmlspecialchars($r['titre']);
        echo "<div><a href='recettes.php?action=detail&id=$rid'>$titre</a></div>";
    }
    echo "</body></html>";
    exit;
}

// Revenir à toutes les listes
header("Location: recettes.php?action=all");
exit;
?>