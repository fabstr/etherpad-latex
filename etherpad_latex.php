<?php

// get the document name and make it shell safe
$doc = $_GET["document"];
$doc = escapeshellcmd($doc);

// get configuration
require_once("config.php");

// get the working directory
$workdir = WORKDIR . "/" . $doc;

// ensure the work directory exists
if (!is_dir($workdir)) {
	if (!mkdir($workdir)) {
		$msg = json_encode(array("result" => "failure", "message" => "Could not create working directory " . $workdir, "errno" => 6));
		die($msg);
	}
}

// latex can only read files in (subdirectories of) $TEXMFOUTPUT
// set $TEXMFOUTPUT to $workdir
putenv("TEXMFOUTPUT=".$workdir);

// get the json text of the pad
$url = sprintf("%s/api/1.2/getText?apikey=%s&padID=%s", ETHERPADLITEHOST, ETHERPADLITEAPIKEY, $doc);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

// decode the json
$data = json_decode($response, true);

// check if there was an error getting the text
if ($data["code"] != 0) {
	$msg = json_encode(array("result" => "failure", "message" => "Could not get pad contents: ", "errno" => 1, "info" => json_encode($data)));
	die($msg);
}

// get the actual text
$text = $data["data"]["text"];


// write the pad to a file
$file = $workdir . "/$doc.tex";
$f = fopen($file, "w+");
if (!$f) {
	$msg = json_encode(array("result" => "failure", "message" => "Could not open file for writing", "errno" => 2, "info" => shell_exec("whoami")));
	die($msg);
}
if (fwrite($f, $text) === FALSE) {
	$msg = json_encode(array("result" => "failure", "message" => "Could not write to file", "errno" => 3, "info" => ""));
	die($msg);
}
fclose($f);

// call latexmk
$cmd = sprintf("%s -pdf -pdflatex=\"%s\" -halt-on-error -output-directory=%s %s/%s", LATEXMKPATH, PDFLATEXPATH, $workdir, $workdir, $doc);
$string = shell_exec($cmd);
if (!$string) {
	$msg = json_encode(array("result" => "failure", "message" => "Could not create pdf", "errno" => 4, "info" => ""));
	die($msg);
}

// check if there was an error with pdflatex
if (preg_match("/Fatal error occurred/", $string)) {
	$msg = json_encode(array("result" => "failure", "message" => str_replace("\n", "<br>\n", $string), "errno" => 5, "info" => ""));
	die($msg);
}

// there was no error
$msg = json_encode(array("result" => "success", "message" => "$doc"));
echo $msg;

?>
