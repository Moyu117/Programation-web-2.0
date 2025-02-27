<?php
// admin.php
require_once 'config.php';
require_once 'fonction.inc.php';

if (!isset($_SESSION['user'])) {
    die("connecter SVP");
}

// if ($_SESSION['user']['login'] !== 'admin') {
//     die("non droit");
// }

$link = db_connect($db_host, $db_user, $db_pass, $db_name);

// chercher tout les utilisateurs
$sqlUsers = "SELECT * FROM users ORDER BY login";
$resUsers = query($link, $sqlUsers);

echo "<h2>Tous les utilisateurs et leurs collections</h2>";

while ($u = mysqli_fetch_assoc($resUsers)) {
    $login = $u['login'];
    echo "<h3>utilisateur：" . htmlspecialchars($login) . "</h3>";

    $loginEsc = mysqli_real_escape_string($link, $login);
    // Vérifiez les recettes préférées de cet utilisateur
    $sqlFav = "
        SELECT r.titre
        FROM favorites f
        JOIN recettes r ON f.recettes_id = r.id
        WHERE f.user_login='$loginEsc'
        ORDER BY r.titre
    ";
    $resFav = query($link, $sqlFav);

    $favs = [];
    while ($rowFav = mysqli_fetch_assoc($resFav)) {
        $favs[] = $rowFav['titre'];
    }
    $count = count($favs);

    echo "<p>nb de collectée：$count</p>";
    if ($count > 0) {
        echo "<ul>";
        foreach ($favs as $titre) {
            echo "<li>" . htmlspecialchars($titre) . "</li>";
        }
        echo "</ul>";
    }
}
?>