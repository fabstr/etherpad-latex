<?php

require_once("api/functions.php");
require_once("api/config.php");

session_start();
if (!isUserLoggedIn()) {
	header("Location: index.php?notloggedin");
}

?>
<!DOCTYPE html>
<html lang="sv">
	<head>
	<title>Etherpad-Latex</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
		<script src="js/jquery.js"></script>
		<script src="js/loggedin.js"></script>
		<link rel="stylesheet" type="text/css" href="css/css.css">
	</head>
	<body>
		<div id="box">
			<p>You are logged in. <a href="logout.php">Logout</a></p>
<h2>Your documents</h2>
<ul>
<?php
$userid = $_SESSION["userid"];
try {
$pm = new PadManager(ETHERPADLITEHOST, ETHERPADLITEAPIKEY);
$docs = $pm -> listPadsOfAuthor($pm -> getAuthorId($userid));
} catch (EtherpadException $e) {
	$docs = "An error occurred";
}
?>
</ul>

			<h2>Create a new document</h2>
			<form action="newpad.php" method="post">
			<table>
				<tr>
					<td><label for="padname">Name</label></td>
					<td><input type="text" name="padname" id="padname" value="" />	</td>
				</tr>
				<tr>
					<td colspan="2"><input type="submit" value="Create document" /></td>
				</tr>
			</table>
			</form>
		</div>
	</body>
</html>
