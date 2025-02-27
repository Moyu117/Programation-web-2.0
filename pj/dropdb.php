<?php
// dropdb.php
require_once 'config.php';
require_once 'fonction.inc.php';

// 连接MySQL(不指定数据库，因为要删除数据库本身)
$link = db_connect($db_host, $db_user, $db_pass);

// 删除整个数据库(测试用)
// 注意：这会彻底删掉 $db_name
$dropSql = "DROP DATABASE IF EXISTS `$db_name`";
query($link, $dropSql);

echo "<h3>数据库 '$db_name' 已删除(如原本存在)。</h3>";
echo "<p><a href='index.php'>返回首页</a></p>";
?>