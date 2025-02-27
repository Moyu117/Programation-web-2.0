<?php

function query($link, $query)
{
    $result = mysqli_query($link, $query) or die("$query : " . mysqli_error($link));
    return $result;
}


function array_escape($link, $array)
{
    return array_map(function($value) use ($link) {
        return mysqli_real_escape_string($link, $value);
    }, $array);
}


function db_connect($host, $user, $pass, $dbName = null)
{
    $link = mysqli_connect($host, $user, $pass);
    if (!$link) {
        die("La connexion à la base de données a échoué: " . mysqli_connect_error());
    }
    if (!empty($dbName)) {
        mysqli_select_db($link, $dbName) or die("La connexion à la base de données a échoué: " . mysqli_error($link));
    }
    
    //mysqli_set_charset($link, "utf8mb4");
    return $link;
}

function check_db_installed($db_host, $db_user, $db_pass, $db_name)
{
    
    $link = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    if (!$link) {
        
        return false;
    }
    $sql = "SHOW TABLES LIKE 'recettes'";
    $res = mysqli_query($link, $sql);
    if ($res && mysqli_fetch_assoc($res)) {
        return true;
    }
    return false;
}
?>

