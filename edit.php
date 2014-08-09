<?php
if (isset($_GET["d"]) && $_GET["d"] != "") {
	$document = $_GET["d"];
} else {
	header("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="sv">
	<head>
	<title><?php echo $document ?> | Etherpad-Latex</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
		<script src="jquery.js"></script>
		<script src="js/js.js"></script>
		<link rel="stylesheet" type="text/css" href="css.css">
	</head>
	<body>
		<div id="box">
			<div id="menu">
				<form action="#">
					Name of the document: 
					<input type="text" id="pad" value="<?php echo $document; ?>">
					<button id="opendoc">Open document</button>
					<button id="compilepdf">Compile pdf</button>
					<a href="#" id="link">Download</a>
					<a href="#" id="files">Manage files</a>
					<div id="status"></div>
				</form>
			</div>
			<div id="filebox">
				<form enctype="multipart/form-data" id="fileform">
					<label for="file">Upload a file</label>
					<input type="file" id="file" name="file">
					<input type="button" id="fileuploadbtn" value="Upload">
				</form>
				<progress id="fileupload"></progress>
				<table id="filetable"></table>
			</div>

			<div id="content">
				<div id="etherpaddiv">
					<iframe id="etherpad" src=""></iframe>
				</div>
				<div id="viewerdiv">
					<iframe id="pdfview" src="" allowfullscreen webkitallowfullscreen></iframe>
					<div id="log"></div>
				</div>
			</div>
		</div>
	</body>
</html>
