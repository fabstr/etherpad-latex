<?php
if (isset($_GET["d"])) {
	$str = "Location: edit.php?d=" . $_GET["d"];
	header($str);
}
?>
<!DOCTYPE html>
<html lang="sv">
	<head>
	<!-- <title><?php echo $document ?> | Etherpad-Latex</title> -->
	<title>Etherpad-Latex</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
		<script src="jquery.js"></script>
		<script src="js.js"></script>
		<link rel="stylesheet" type="text/css" href="css.css">
	</head>
	<body>
		<div id="box">
			<form action="edit.php" method="get">
				Name of the document:
				<input type="text" name="d">
				<input type="submit" value="Open document">
			</form>
		</div>
	</body>
</html>
