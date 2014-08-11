<?php

require_once("api/functions.php");
session_start();
logoutUser();
header("Location: index.php?logout");

?>
