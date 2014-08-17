<?php

require_once("functions.php");
require_once("Response.php");
require_once("PadManager.php");
require_once("User.php");
require_once("config.php");

session_start();

if (!isUserLoggedIn()) {
	echo new Response(403, "Not logged in");
} else {
	$userid = $_SESSION["userid"];
	$user = new User($userid);
	$group = $user -> getGroup();
	try {
		$pm = new PadManager(ETHERPADLITEHOST, ETHERPADLITEAPIKEY);
		$groupid = $pm -> createGroupIfNotExistsFor($group);
		$documents = $pm -> listPads($groupid);
		$docs = array();
		foreach ($documents as $key => $val) {
			// split at the first $, in total two strings
			$arr = preg_split("/\\$/", $val, 3);
			$groupid = $arr[0];
			$name = $arr[1];
			array_push($docs, array("groupid" => $groupid, "name" => $name));
		}
		echo new Response(200, array("documentList" => $docs));
	} catch (EtherpadException $e) {
		echo new Response(404, "" . $e);
	}
}

?>
