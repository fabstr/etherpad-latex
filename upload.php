<?php
require_once("config.php");

if (!isset($_GET["d"])) {
	http_response_code(400);
	die("No document");
} else {
	$doc = escapeshellcmd($_GET["d"]); 
	$dir = WORKDIR . "/" . $doc;
	$uploadedfile = $dir . "/" . basename($_FILES["file"]["name"]);
	if (move_uploaded_file($_FILES["file"]["tmp_name"], $uploadedfile)) {
		echo "Success";
	} else {
		http_response_code(500);
		die("Could not upload file.");
	}
}

?>
