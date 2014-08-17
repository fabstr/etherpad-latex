<?php

require_once("functions.php");
require_once("Response.php");

session_start();

if (isUserLoggedIn()) {
	echo new Response(200);
}

$post = file_get_contents("php://input");
$post = json_decode($post);

$userid = validateUser($post -> username, $post -> password);
if ($userid) {
	loginUser($userid, $post -> username);
	echo new Response(200);
} else {
	echo new Response(403, "Invalid username or password");
}

?>
