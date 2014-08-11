<?php

require_once("sql.php");
require_once("Password.php");
require_once("config.php");

class User {
	private $username;
	private $userid;
	private $password;
	private $db;
	private $maingroup;

	public function __construct($userid) {
		try {
			$this -> db = newDb();
			$q = $this -> db -> prepare("SELECT userid, username, password, 
				salt, iterations, algorithm FROM users WHERE 
				userid = :userid");
			$q -> execute(array(":userid" => $userid));
			$result = $q -> fetch();
			$this -> username = $result["username"];
			$this -> userid = $result["userid"];
			$this -> password = new Password($result["password"], 
				$result["salt"], $result["iterations"], 
				$result["algorithm"], true);
		} catch (PDOException $e) {
			return false;
		}
	}

	public function getUsername() {
		return $this -> username;
	}

	public function getUserid() {
		return $this -> userid;
	}

	/*
	 * Get the user's main group (the one with the same name as the user)
	 */
	private function getGroup() {
		$q = $this -> db -> prepare("SELECT groupid FROM groups WHERE 
			groupname = :groupname");
		if (!$q) {
			trigger_error("Could not get group id");
			return false;
		}

		$res = $q -> execute(array(":groupname" => $this -> username));
		if (!$res) {
			trigger_error("Could not execute");
			return false;
		}

		$result = $q -> fetch();
		if (!$result) {
			trigger_error("Could not get result");
			return false;
		}

		return $result["groupid"];
	}
}

?>
