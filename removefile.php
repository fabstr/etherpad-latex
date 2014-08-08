<?php

require_once("config.php");

if (!isset($_GET["d"])) {
	http_response_code(400);
	die("No document.");
} else if (!isset($_POST["file"])) {
	http_response_code(400);
	die("No file.");
}

$dir = WORKDIR . "/" . $_GET["d"] . "/";

$file = $dir . escapeshellcmd($_POST["file"]);

if (!unlink($file)) {
	http_response_code(500);
	echo "Could not delete " . $file . ".";
} else {
	echo "Success";
}

?>
