<?php

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
	private $lockfile;

	public function __construct($padid) {
		// check that padname is one or more digits
		if (!preg_match('/^\\d+$/', $padid)) {
			throw new LatexException('padname is invalid: ' . $padid);
		}

		$document = Document::find($padid);

		// the parameters are correct, set the variables
		$this -> padid = $document -> ethergroup() . '$' . $padid;
		$this -> name = $document -> id;
		$this -> directory = $document -> absdir();
		$this -> lockfile = $this -> directory . '/' . $this -> name . '.lockfile';
		$this -> pm = new PadManager();
	}

	// compile the latex file:
	// - ensure the directory exists
	// - get the text and save it 
	// - call latexmk
	//
	// If the document is locket (ie the lockfile is present), do nothing 
	// and return false. 
	public function compile() {
		// ensure the directory exists
		$this -> ensureDirectory();

		// create the lockfile (or return)
		if (!$this -> lock()) {
			return false;
		}

		try {
			// clean
			$this -> clean();

			// get the text and save it 
			$this -> writeTextToFile();

			// call latexmk
			$this -> callLatexMk();
		} catch (Exception $e) {
			throw $e;
		} finally {
			$this -> unlock();
		}

		return true;
	}

	// check if the lockfile exists, if it does, return false.
	// else, create the lockfile and return true
	private function lock() {
		if (file_exists($this -> lockfile)) {
			return false;
		}

		return file_put_contents($this -> lockfile, 'I am locked!') !== false;
	}

	// remove the lockfile (if it exists)
	private function unlock() {
		if (file_exists($this -> lockfile)) {
			unlink($this -> lockfile);
		}
	}

	// check if $this->directory is a directory
	// if not, try to create it with mkdir(), if this fails, throw an exception
	private function ensureDirectory() {
		if (!is_dir($this -> directory)) {
			if (!mkdir($this -> directory)) {
				throw new LatexException('Could not create directory ' . $this -> directory);
			}
		}
	}

	// delete all  .aux .fdb_latexmk .fls .log .out .pdf .tex .toc files
	private function clean() {
		$types = ['.aux', '.fdb_latexmk', '.fls', '.log', '.out', '.pdf', '.tex', '.toc'];
		$s = '';
		foreach (scandir($this -> directory) as $file) {
			if ($file === '.' || $file === '..') continue;
			foreach ($types as $t) {
				$extension = substr($file, 0-strlen($t));
				if (in_array($extension, $types) && file_exists($this -> directory . '/' . $file)) {
					unlink($this -> directory . '/' . basename($file));
				}
			}
		}
	}

	// download the pad contents from etherpad and save it to a file
	// in $directory/$name.tex
	private function writeTextToFile() {
		$text = $this -> pm -> getText($this -> padid);
		$path = sprintf('%s/%s.tex', $this -> directory, $this -> name);
		file_put_contents($path, $text);
	}

	// call latexmk, output pdf and use pdflatex, halt on errror and 
	// output files in $this -> directory
	//
	// if there is an error compiling, throw an exception
	//
	// if there is no output, read the file $name.log as output
	private function callLatexMk() {
		// set the env variable
		putenv('TEXMFOUTPUT='.$this -> directory);

		// execute the command
		$cmd = sprintf('%s -pdf -pdflatex=\'%s\' -halt-on-error -output-directory=%s %s/%s 2>&1', 
			$_ENV['LATEXMK_PATH'], 
			$_ENV['PDFLATEX_PATH'], 
			$this -> directory, 
			$this -> directory, 
			$this -> name);
		exec($cmd, $outputArr, $status);

		$output = '';
		if (sizeof($outputArr) == 3) {
			$output = file_get_contents($this -> directory . '/' . $this -> name . '.log');
		} else {
			foreach ($outputArr as $row) {
				$output .= $row . '\n';
			}
		}

		// check for errors and throw an LatexException if there is any
		if ($status != 0) {
			throw new LatexException($output);
		} else if ($this -> checkForLatexError($output) == true) {
			throw new LatexException($output);
		}
	}

	private function checkForLatexError($output) {
		if (strpos($output, '!  ==> Fatal error occurred') == false) {
			// there is no an error
			return false;
		} 
		return true;
	}
}
