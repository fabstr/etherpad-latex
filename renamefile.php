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

$doc = $_GET["d"];
if (!validateDocumentName($doc)) {
	http_response_code(400);
	die("Invalid document name");
}

$dir = WORKDIR . "/" . $doc . "/";

$file = $dir . escapeSlashes($_POST["file"]);
$newname = $dir . escapeSlashes($_POST["newname"]);

if ($file == "" || $newname = "") {
	http_response_code(400);
	echo "Empty filename";
} else if (rename($file, $newname) == true) {
	echo "Success";
} else {
	http_response_code(500);
	echo "Could not rename " . $file . " to " . $newname;
}

?>
