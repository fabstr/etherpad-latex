<?php

require_once("config.php");

if (!isset($_GET["d"])) {
	http_response_code(400);
	die("No document.");
} else if (!isset($_POST["file"])) {
	http_response_code(400);
	die("No file.");
} else if (!isset($_POST["newname"])) {
	http_response_code(400);
	die("No new name.");
}

$dir = WORKDIR . "/" . $_GET["d"] . "/";

$file = $dir . escapeshellcmd($_POST["file"]);
$newname = $dir . escapeshellcmd($_POST["newname"]);

if (!rename($file, $newname)) {
	http_response_code(500);
	echo "Could not rename " . $file . " to " . $newname;
} else {
	echo "Success";
}

?>
