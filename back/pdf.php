<?php

require_once("config.php");
require_once("functions.php");


$filename = $_GET["p"];
$group = $_GET["g"];
$doc = explode(".pdf", $filename)[0];
$path = WORKDIR . "/" . getDirectory($group, $doc) . "/" . $filename;

// if the "d" parameter is set, the document shuold be downloaded 
$download = false;
if (isset($_GET["d"])) {
	$download = true;
}

if (!validateDocumentName($doc)) {
	http_response_code(400);
	die("Invalid document name: " . $doc);
}

// check the file exists
if (!file_exists($path)) {
	die("$path does not exist.");
}

if ($download == true) {
	header("Content-type: application/octet-stream");
} else {
	header("Content-type: application/pdf; charset=utf-8");
}

$size = filesize($path);
header("Content-length: $size");
header("Content-Disposition: filename=\"$filename\"");
echo file_get_contents($path);
?>
