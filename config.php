<?php

/*
 * The working directory for latexmk.
 * The http user should have read/write access to thiw directory.
 * The path should not end with a slash.
 */
define("WORKDIR", "");

/*
 * The ip address and port of etherpad-lite.
 * Should be on the form http(s)://127.0.0.1:9001
 */
define("ETHERPADLITEHOST", "");

/*
 * The api key defined in etherpad-lite's settings.json
 */
define("ETHERPADLITEAPIKEY", "");

/*
 * The absolute path to the latexmk binary
 */
define("LATEXMKPATH", "");

/*
 * The absolute path to the pdflatex binary
 */
define("PDFLATEXPATH", "");

?>
