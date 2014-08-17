<?php

require_once("PadManager.php");
require_once("config.php");

class LatexException extends Exception {
	public function __construct ($str) {
		parent::__construct($str);
	}
}

class LatexCompiler {
	private $pm;
	private $padid;
	private $name;
	private $directory;

	public function __construct($padid, $name, $subdirectory) {
		// the padid should be g.somestring$name
		if (!preg_match("/^g\\.\\w{16}\\$".$name."$/", $padid)) {
			throw new InvalidArgumentException("padid incorrect: " . $padid);
		} 
	       
		// the padname should be letters, digits and underscore
		if (!preg_match("/^\\w+$/", $name)) {
			throw new InvalidArgumentException("name incorrect: " . $name);
		} 
	       
		// the directory should be letters, digits and underscore
		if (!preg_match("/^\\w+$/", $subdirectory)) {
			throw new InvalidArgumentException("name incorrect: " . $subdirectory);
		}

		// the parameters are correct, set the variables
		$this -> padid = $padid;
		$this -> name = $name;
		$this -> directory = WORKDIR . "/" . $subdirectory;
		$this -> pm = new PadManager(ETHERPADLITEHOST, ETHERPADLITEAPIKEY);
	}

	// compile the latex file:
	// - ensure the directory exists
	// - get the text and save it 
	// - call latexmk
	public function compile() {
		// ensure the directory exists
		$this -> ensureDirectory();

		// get the text and save it 
		$this -> writeTextToFile();

		// call latexmk
		$this -> callLatexMk();
	}

	// check if $this->directory is a directory
	// if not, try to create it with mkdir(), if this fails, throw an exception
	private function ensureDirectory() {
		if (!is_dir($this -> directory)) {
			if (!mkdir($this -> directory)) {
				throw new LatexException("Could not create directory " . $this -> directory);
			}
		}
	}

	// download the pad contents from etherpad and save it to a file
	// in $directory/$name.tex
	private function writeTextToFile() {
		$text = $this -> pm -> getText($this -> padid);
		$path = sprintf("%s/%s.tex", $this -> directory, $this -> name);
		file_put_contents($path, $text);
	}

	// call latexmk, output pdf and use pdflatex, halt on errror and 
	// output files in $this -> directory
	//
	// if there is an error compiling, throw an exception
	//
	// if there is no output, read the file $name.log as output
	private function callLatexMk() {
		// execute the command
		$cmd = sprintf("%s -pdf -pdflatex=\"%s\" -halt-on-error -output-directory=%s %s/%s 2>&1", LATEXMKPATH, PDFLATEXPATH, $this -> directory, $this -> directory, $this -> name);
		exec($cmd, $outputArr, $status);

		$output = "";
		if (sizeof($outputArr) == 3) {
			$output = file_get_contents($this -> directory . "/" . $this -> name . ".log");
		} else {
			foreach ($outputArr as $row) {
				$output .= $row . "\n";
			}
		}

		// check for errors and throw an LatexException if there is any
		if ($status != 0) {
			trigger_error("status");
			throw new LatexException($output);
		} else if ($this -> checkForLatexError($output) == true) {
			trigger_error("fatal");
			throw new LatexException($output);
		}
	}

	private function checkForLatexError($output) {
		if (strpos($output, "!  ==> Fatal error occurred") == false) {
			// there is no an error
			return false;
		} 
		return true;
	}
}
