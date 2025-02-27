<?php
// ingredients.php
require_once 'config.php';
require_once 'fonction.inc.php';

if (!isset($_SESSION['user'])) {
    die("vous n'avez pas connect");
}

$link = db_connect($db_host, $db_user, $db_pass, $db_name);

// Obtenez tous les ingrédients + durées d'utilisation
$sql = "
    SELECT i.id, i.nom, COUNT(ri.recettes_id) AS cnt
    FROM ingredients i
    JOIN recettes_ingredients ri ON i.id = ri.ingredient_id
    GROUP BY i.id
";
$res = query($link, $sql);

// mettre dans tableau
$ingredients = [];
while ($row = mysqli_fetch_assoc($res)) {
    $ingredients[] = [
        'id'  => $row['id'],
        'nom' => $row['nom'],
        'cnt' => $row['cnt']
    ];
}
// défaut:par nom d'ingrédient
// tir
$orderField = isset($_GET['orderField']) ? $_GET['orderField'] : 'nom';
$orderDir = isset($_GET['orderDir']) ? $_GET['orderDir'] : 'ASC';


// validee
$validFields = ['nom','cnt'];
if (!in_array($orderField, $validFields)) {
    $orderField = 'nom';
}
if ($orderDir !== 'ASC' && $orderDir !== 'DESC') {
    $orderDir = 'ASC';
}

// tri
usort($ingredients, function($a, $b) use ($orderField, $orderDir) {  //fonction de tri
    if ($a[$orderField] == $b[$orderField]) return 0;

    if ($orderDir === 'ASC') {
    // 按数量排序（升序）或按字母排序（升序）
    if ($orderField === 'cnt') {
        if ($a['cnt'] < $b['cnt']) {
            return -1;
        } elseif ($a['cnt'] > $b['cnt']) {
            return 1;
        } else {
            return 0;
        }
    } else {
        return strcmp($a['nom'], $b['nom']); // 字母顺序
    }
} else {
    // 按数量排序（降序）或按字母排序（降序）
    if ($orderField === 'cnt') {
        if ($b['cnt'] < $a['cnt']) {
            return -1;
        } elseif ($b['cnt'] > $a['cnt']) {
            return 1;
        } else {
            return 0;
        }
    } else {
        return strcmp($b['nom'], $a['nom']); // 逆字母顺序
    }
}

});

//nextDir = (ASC->DESC / DESC->ASC)，sinon defaut
function nextOrderDir($current) {
    return $current === 'ASC' ? 'DESC' : 'ASC';
}

$nextDirForNom = ($orderField === 'nom') ? nextOrderDir($orderDir) : 'ASC';
$nextDirForCnt = ($orderField === 'cnt') ? nextOrderDir($orderDir) : 'DESC';

echo "<h1>Liste des ingrédients</h1>";
echo "<table border='1'>";
echo "<tr>";
echo "<th><a href='?orderField=cnt&orderDir=$nextDirForCnt'>Nombre de recettes</a></th>";
echo "<th><a href='?orderField=nom&orderDir=$nextDirForNom'>Aliment</a></th>";
echo "</tr>";

foreach ($ingredients as $ing) {
    $nom = htmlspecialchars($ing['nom']);
    $cnt = $ing['cnt'];
    $nomUrl = urlencode($ing['nom']);
    echo "<tr>";
    echo "<td>$cnt</td>";
    echo "<td><a href='recettes.php?action=byIngredient&name=$nomUrl'>$nom</a></td>";
    echo "</tr>";
}
echo "</table>";
?>