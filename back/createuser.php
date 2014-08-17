<?php

require_once("functions.php");
require_once("Response.php");

session_start();

if (isUserLoggedIn()) {
	echo new Response(200);
}

$post = file_get_contents("php://input");
$post = json_decode($post);

$username = $post -> username;
$password = $post -> password;

$userid = createNewUser($username, $password);
if ($userid) {
	loginUser($userid, $username);
	echo new Response(200);
} else {
	echo new Response(500, "Could not create user");
}

?>
