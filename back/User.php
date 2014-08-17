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
	public function getGroup() {
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

	public function getAllGroups() {
		try {
			$q = $this -> db -> prepare("SELECT groupid FROM ingroup WHERE userid = :userid");
			if (!$q) {
				throw new SQLException("Could not get groups");
			}

			$res = $q -> execute(array(":userid" => $this -> userid));
			if (!$res) {
				throw new SQLException("Could not get groups");
			}

			$groups = array();
			$result = $q -> fetchAll();
			foreach($result as $r) {
				array_push($groups, $r["groupid"]);
			}

			return $groups;
		} catch (PDOException $e) {
			throw new SQLException("PDO Exception", $e -> getMessage());
		}
	}

	// get the groups of the user and the corresponding etherpadlite groups
	public function getAllEtherpadGroups() {
		$groups = $this -> getAllGroups();
		$pm = new PadManager(ETHERPADLITEHOST, ETHERPADLITEAPIKEY);

		$callback = function($e) use ($pm) {
			$groupid = $pm -> createGroupIfNotExistsFor($e);
			return $groupid;
		};
		$ethergroups = array_map($callback, $groups);
		return $ethergroups;
	}

	// get the groups of the user and the corresponding etherpadlite groups
	// then check if groupid is in the list of groups
	public function inEtherpadGroup($groupid) {
		$groups = $this -> getAllEtherpadGroups();
		return in_array($groupid, $groups);
	}
}

?>
