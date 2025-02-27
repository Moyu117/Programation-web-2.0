<?php
// favorites.php
require_once 'config.php';
require_once 'fonction.inc.php';

if (!isset($_SESSION['user'])) {
    die("Veuillez d'abord vous connecter");
}

$link = db_connect($db_host, $db_user, $db_pass, $db_name);

$action = isset($_GET['action']) ? $_GET['action'] : 'myFav';

// changer etat
if ($action === 'toggle') {
    $rid = isset($_GET['rid']) ? (int) $_GET['rid'] : 0;
    $userLogin = $_SESSION['user']['login'];

    $userLoginEsc = mysqli_real_escape_string($link, $userLogin);
    $ridEsc = mysqli_real_escape_string($link, $rid);

    // tester si favori
    $sqlCheck = "SELECT id FROM favorites WHERE user_login='$userLoginEsc' AND recettes_id=$ridEsc";
    $resCheck = query($link, $sqlCheck);
    if ($row = mysqli_fetch_assoc($resCheck)) {
        // Favoris -> Annuler le favori
        $fid = $row['id'];
        $sqlDel = "DELETE FROM favorites WHERE id=$fid";
        query($link, $sqlDel);
    } else {
        // Pas favori -> Ajouter aux favoris
        $sqlIns = "
            INSERT INTO favorites (user_login, recettes_id)
            VALUES ('$userLoginEsc', $ridEsc)
        ";
        query($link, $sqlIns);
    }

    // Retour à la page source
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Voir ma collection
if ($action === 'myFav') {
    $userLogin = $_SESSION['user']['login'];
    $userLoginEsc = mysqli_real_escape_string($link, $userLogin);

    $sql = "
        SELECT r.id, r.titre
        FROM favorites f
        JOIN recettes r ON f.recettes_id = r.id
        WHERE f.user_login='$userLoginEsc'
        ORDER BY r.titre
    ";
    $res = query($link, $sql);

    echo "<h2>ma collection</h2>";
    while ($r = mysqli_fetch_assoc($res)) {
        $rid = $r['id'];
        $titre = htmlspecialchars($r['titre']);
        echo "<div><a href='recettes.php?action=detail&id=$rid'>$titre</a></div>";
    }
    exit;
}
header("Location: favorites.php?action=myFav");
exit;
?>