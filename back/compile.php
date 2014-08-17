<?php

require_once("functions.php");
require_once("Response.php");
require_once("LatexCompiler.php");
require_once("PadManager.php");

session_start();
$post = file_get_contents("php://input");

$post = json_decode($post);

if (!isUserLoggedIn()) {
	echo new Response(403, "Not logged in");
} else if (!isset($post -> name)) {
	echo new Response(400, "No document name");
} else if (!isset($post -> group)) {
	echo new Response(400, "No group");
} else {
	// get the user id and create the user object
	$userid = $_SESSION["userid"];
	$user = new User($userid);

	// get the name and the group
	$name = $post -> name;
	$group = $post -> group;

	// check that the user is in the group
	if (!$user -> inEtherpadGroup($group)) {
		echo new Response(403, "Not in group: ". $group);
	} else {
		$dir = getDirectory($group, $name);
		$error = true;
		try {
			$padid = $group . "$" . $name;
			$lc = new LatexCompiler($padid, $name, $dir);
			$lc -> compile();
			$error = false;
		} catch (EtherpadException $e) {
			echo new Response(500, "" . $e);
		} catch (LatexException $e) {
			http_response_code(500);
			echo $e -> getMessage();
			//echo new Response(500, $e -> getLog());
		} catch (InvalidArgumentException $e) {
			echo new Response(500, "Invalid data: " . $e);
		}
		if (!$error) {
			echo new Response(200);
		}
	}
}

?>
