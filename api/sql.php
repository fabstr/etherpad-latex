<?php

define('HOST', "192.168.1.20");
define('USERNAME', "etherpadlatex");
define('PASSWORD', "Do+wuuczHnpKu+2hdHD6TA==");
define('DATABASE', "www");

function newDb() {
	$db = new PDO('pgsql:host='.HOST.';dbname='.DATABASE, USERNAME, PASSWORD);
	return $db;
}

?>
