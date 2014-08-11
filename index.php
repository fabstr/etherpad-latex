<?php
require_once("api/sql.php");
if (isset($_GET["d"])) {
	header("Location: edit.php?d=" . $_GET["d"]);
}

if (isset($_GET["notloggedin"])) {
	$msg = "<p>You are not logged in.</p>";
} else if (isset($_GET["logout"])) {
	$msg = "<p>You are logged out.</p>";
}
?>
<!DOCTYPE html>
<html lang="sv">
	<head>
	<title>Etherpad-Latex</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
		<script src="js/jquery.js"></script>
		<script src="js/index.js"></script>
		<link rel="stylesheet" type="text/css" href="css.css">
	</head>
	<body>
		<div id="box">
			<?php if (isset($msg)) echo $msg; ?>
			<form action="edit.php" method="get">
				Name of the document:
				<input type="text" name="d">
				<input type="submit" value="Open document">
			</form>

			<form action="login.php" method="post">
			<table>
				<tr>
					<td><label for="username">Username</label></td>
					<td><input type="text" id="usernameLogin" name="username"></td>
				</tr>
				<tr>
					<td><label for="password">Password</label></td>
					<td><input type="password" id="passwordLogin" name="password"></td>
				</tr>
				<tr>
					<td colspan="2"><input type="submit" value="Login"></td>
				</tr>
			</table>
			</form>
		
		
			<form action="createuser.php" method="post" onsubmit="return validateForm();">
			<table>
				<tr>
					<td><label for="usernameCreate">Username</label></td>
					<td><input type="text" id="usernameCreate" name="username"></td>
				</tr>
				<tr>
					<td><label for="password1">Password</label></td>
					<td><input type="password" id="password1" name="password"></td>
				</tr>
				<tr>
					<td><label for="password2">Confirm password</label></td>
					<td><input type="password" id="password2"></td>
				</tr>
				<tr>
					<td colspan="2"><input type="submit" value="Create account"></td>
				</tr>
			</table>
			</form>
		</div>
	</body>
</html>
