<?php

require_once("config.php");

if (!isset($_GET["d"])) {
	http_response_code(400);
	die("No document");
}

$doc = $_GET["d"];
$dir = WORKDIR . "/" . $doc;

if (!validateDocumentName($doc)) {
	http_response_code(400);
	die("Invalid document name");
}

$files = scandir($dir);

$nolisting = array(".", "..", "$doc.aux", "$doc.fdb_latexmk", "$doc.fls", "$doc.log", "$doc.out", "$doc.pdf", "$doc.tex", "$doc.toc");
$result = array();
foreach ($files as $file) {
	if (!in_array($file, $nolisting)) {
		array_push($result, array(
			"name" => $file
		));
	}
}

echo json_encode($result);

?>
