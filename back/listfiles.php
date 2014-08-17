<?php

require_once("functions.php");
require_once("Response.php");
require_once("PadManager.php");
require_once("User.php");
require_once("config.php");

session_start();

$post = file_get_contents("php://input");
$post = json_decode($post);

if (!isUserLoggedIn()) {
	echo new Response(403, "Not logged in");
} else if (!isset($post -> name)) {
	echo new Response(400, "No name");
} else if (!isset($post -> groupid)) {
	echo new Response(400, "No group");
} else {
	$userid = $_SESSION["userid"];
	$user = new User($userid);
	if (!$user -> inEtherpadGroup($post -> groupid)) {
		echo new Response(403, "Not in group");
	} else {
		$groupid = basename($post -> groupid);
		$name = basename($post -> name);
		$dir = WORKDIR . "/" . getDirectory($groupid, $name);

		$nolisting = array(".", "..", "$name.aux", "$name.fdb_latexmk", 
			"$name.fls", "$name.log", "$name.out", "$name.pdf", 
			"$name.tex", "$name.toc");
		$files = scandir($dir);
		$filestoshow = array_values(array_diff($files, $nolisting));
		echo new Response(200, $filestoshow);
	}
}

?>
