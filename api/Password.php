<?php

define("SALT_LENGTH",  32);
define("N_ITERATIONS",  32768);
define("HASH_ALGORITHM",  "sha256");

class Password {
	private $hash;
	private $salt;
	private $niterations;
	private $algorithm;

	private function hashPassword($password) {
		return hash_pbkdf2($this -> algorithm, $password, $this -> salt, 
			$this -> niterations);
	}

	private function getRandomSalt($length) {
		return bin2hex(openssl_random_pseudo_bytes($length));
	}

	public function __construct($password, $salt = NULL, 
			$niterations = N_ITERATIONS, 
			$algorithm = HASH_ALGORITHM, 
			$passwordIsHashed = false) {
		// these members can be set
		$this -> niterations = $niterations;
		$this -> algorithm = $algorithm;

		// use a random salt if no salt was given ($salt is null)
		if ($salt == NULL) {
			$this -> salt = getRandomSalt(SALT_LENGTH);
		} else {
			$this -> salt = $salt;
		}

		// if $passwordIsHashed is true, $password holds a hashed 
		// password. if not, hash the password
		if ($passwordIsHashed == true) {
			$this -> hash = $password;
		} else {
			$this -> hash = $this -> hashPassword($password);
		}
	}

	public function getHash() {
		return $this -> hash;
	}

	public function getSalt() {
		return $this -> salt;
	}

	public function getNiterations() {
		return $this -> niterations;
	}

	public function getAlgorithm() {
		return $this -> algorithm;
	}

	public function validatePassword($password) {
		return $this -> hash === $this -> hashPassword($password);
	}

}

?>
