<?php

require_once("functions.php");
require_once("Response.php");

session_start();

logoutUser();

echo new Response(200);

?>
