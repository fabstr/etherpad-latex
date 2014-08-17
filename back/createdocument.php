<?php

require_once("functions.php");
require_once("Response.php");
require_once("User.php");
require_once("PadManager.php");
require_once("config.php");

session_start();

$padname = $_GET["name"];
$userid = $_SESSION["userid"];
$user = new User($userid);
$usergroup = $user -> getGroup();

if (!isUserLoggedIn()) {
	echo new Response(403, "Not logged in");
} else if (!isset($padname)) {
	echo new Response(400, "No document name");
} else if (!validateDocumentName($padname)) {
	echo new Response(400, "Invalid document name");
} else if ($user == false) {
	echo new Response(500, "Could not get user");
} else if ($usergroup == false) {
	echo new Response(500, "Could not get user group");
} else {
	try {
		$pm = new PadManager(ETHERPADLITEHOST, ETHERPADLITEAPIKEY);
		$groupid = $pm -> createGroupIfNotExistsFor($usergroup);
		$result = $pm -> createGroupPad($groupid, $padname);
		echo new Response(200, json_encode($result));
	} catch (EtherpadException $e) {
		echo new Response(500, "" . $e);
	}
}

?>
