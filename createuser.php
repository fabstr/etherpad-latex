<?php

require_once("api/functions.php");

$username = $_POST["username"];
$password = $_POST["password"];

$userid = createNewUser($username, $password);
if ($userid) {
	session_start();
	loginUser($userid, $username);
	header("Location: loggedin.php");
} else {
	echo "Could not create user.";
}


?>
