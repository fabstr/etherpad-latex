<?php

/*
 * The working directory for latexmk.
 * The http user should have read/write access to thiw directory.
 * The path should not end with a slash.
 * The path should be absolute.
 */
define("WORKDIR", "/usr/local/www/vhosts/tallr.se/latex/compiles");

/*
 * The ip address and port of etherpad-lite.
 * Should be on the form http(s)://127.0.0.1:9001
 */
define("ETHERPADLITEHOST", "http://192.168.1.76:9001");

/*
 * The api key defined in etherpad-lite's settings.json
 */
define("ETHERPADLITEAPIKEY", "a5e9cb64bf449f70d41e706d7a3962a91d54c2881a228215e56dc7af85d4c557");

/*
 * The absolute path to the latexmk binary
 */
define("LATEXMKPATH", "/usr/local/bin/latexmk");

/*
 * The absolute path to the pdflatex binary
 */
define("PDFLATEXPATH", "/usr/local/bin/pdflatex");

?>
