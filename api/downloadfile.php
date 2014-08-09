<?php

require_once("config.php");
require_once("functions.php");

if (!isset($_GET["d"])) {
	http_response_code(400);
	die("No document");
} else if (!isset($_GET["file"])) {
	http_response_code(400);
	die("No file");
}

$doc = $_GET["d"];
$file = $_GET["file"];
$file = escapeSlashes($file);
$path = WORKDIR . "/" . $doc . "/" . $file;

if (!validateDocumentName($doc)) {
	http_response_code(400);
	die("Invalid document name");
}

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$file\"");
header("Content-Length: " . filesize($path));
readfile($path);

?>
