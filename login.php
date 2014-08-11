<?php

require_once("api/functions.php");

$userid = validateUser($_POST["username"], $_POST["password"]);
if ($userid) {
	session_start();
	loginUser($userid, $_POST["username"]);
}

header("Location: loggedin.php");

?>
