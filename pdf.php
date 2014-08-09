<?php

require_once("config.php");

$filename = escapeshellcmd($_GET["p"]);
$doc = explode(".pdf", $filename)[0];
$path = WORKDIR . "/" . $doc . "/" . $filename;

if (!validateDocumentName($doc)) {
	http_response_code(400);
	die("Invalid document name");
}

// check the file exists
if (!file_exists($path)) {
	header("Content-type: text/plain");
	die("$path does not exist.");
}

$size = filesize($path);
header("Content-type: application/pdf; charset=utf-8");
header("Content-Disposition: filename=\"$filename\"");
header("Content-length: $size");
echo file_get_contents($path);
?>
