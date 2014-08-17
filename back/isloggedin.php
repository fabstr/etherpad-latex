<?php

require_once("functions.php");
require_once("Response.php");

session_start();

if (isUserLoggedIn()) {
	echo new Response(200);
} else {
	echo new Response(403, "Not logged in");
}

?>
