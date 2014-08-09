<?php

function validateDocumentName($string) {
	return preg_match("/\w+/", $string) == 1;
}

function escapeSlashes($string) {
	return str_replace("/", "_", $string);
}

?>
