<?php

define('HOST', "mysql.lan.tallr.se");
define('USERNAME', "etherpadlatex");
define('PASSWORD', "AxN6G8NHt7SqWZbu");
define('DATABASE', "etherpadlatex");

function newDb() {
	$db = new PDO('mysql:host='.HOST.';dbname='.DATABASE, USERNAME, PASSWORD);
	return $db;
}

class SQLException extends Exception {
	public function __construct($message, $error) {
		$str = "Message: " . $message . ", ";
		$str .= "Error: " . $error;
		parent::__construct($str);
	}
}

?>
