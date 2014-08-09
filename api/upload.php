<?php
require_once("config.php");
require_once("functions.php");

if (!isset($_GET["d"])) {
	http_response_code(400);
	die("No document");
} else {
	$doc = $_GET["d"]; 
	if (!validateDocumentName($doc)) {
		http_response_code(400);
		die("Invalid document name");
	}

	$dir = WORKDIR . "/" . $doc;
	$file = escapeSlashes(basename($_FILES["file"]["name"]));
	$uploadedfile = $dir . "/" . $file;
	if (move_uploaded_file($_FILES["file"]["tmp_name"], $uploadedfile)) {
		echo "Success";
	} else {
		http_response_code(500);
		die("Could not upload file.");
	}
}

?>
