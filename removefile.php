<?php

require_once("config.php");
require_once("functions.php");

if (!isset($_GET["d"])) {
	http_response_code(400);
	die("No document.");
} else if (!isset($_POST["file"])) {
	http_response_code(400);
	die("No file.");
}

$doc = $_GET["d"];
$file = escapeSlashes($_POST["file"]);

if (!validateDocumentName($doc)) {
	http_response_code(400);
	die("Invalid document name");
}

$path = WORKDIR . "/" . $doc . "/" . $file;

if (!unlink($path)) {
	http_response_code(500);
	echo "Could not delete " . $path . ".";
} else {
	echo "Success";
}

?>
