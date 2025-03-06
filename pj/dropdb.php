<?php
// dropdb.php
require_once 'config.php';
require_once 'fonction.inc.php';

// connect
$link = db_connect($db_host, $db_user, $db_pass);

// suprimee
//  $db_name
$dropSql = "DROP DATABASE IF EXISTS `$db_name`";
query($link, $dropSql);

echo "<h3>db '$db_name' suppprime</h3>";
echo "<p><a href='index.php'>retourner</a></p>";
?>