<?php
require_once("sql.php");
require_once("Password.php");

function validateDocumentName($string) {
	return preg_match("/\w+/", $string) == 1;
}

function escapeSlashes($string) {
	return str_replace("/", "_", $string);
}

function createNewUser($username, $password) {
	$pass = new Password($password);
	$userid = false; // to be returned
	try {
		// open database connection
		$db = newDb();

		// prepare statements to create user, a group for the user (with 
		// the user's name as group name) and add the user to the group
		$createUser = $db -> prepare("INSERT INTO users (username, 
			password, salt, iterations, algorithm) VALUES 
			(:username, :password, :salt, :iterations, :algorithm)");
		$createGroup = $db -> prepare("INSERT INTO groups (groupname) 
			VALUES (:groupname)");
		$addUserToGroup = $db -> prepare("INSERT INTO ingroup (groupid, 
			userid) VALUES (:groupid, :userid)");

		// begin a transaction
		$db -> beginTransaction();

		// add the user and get the userid
		$res = $createUser -> execute(array(
			":username" => $username, 
			":password" => $pass -> getHash(), 
			":salt" => $pass -> getSalt(), 
			":iterations" => $pass -> getNiterations(), 
			":algorithm" => $pass -> getAlgorithm()
		));
		if (!$res) {
			trigger_error("Could not create user: " . $db -> errorInfo()[2]);
			return false;
		}
		$userid = $db -> lastInsertId("users_userid_seq");
		if (!$userid) {
			trigger_error("Could not get user id: " . $db -> errorInfo()[2]);
		}

		// create the group and get the group id
		$res = $createGroup -> execute(array(":groupname" => $username));
		if (!$res) {
			trigger_error("Could not create group: " . $db -> errorInfo()[2]);
			return false;
		}
		$groupid = $db -> lastInsertId("groups_groupid_seq");
		if (!$groupid) {
			trigger_error("Could not get group id: " . $db -> errorInfo()[2]);
		}

		// add the user to the group
		$res = $addUserToGroup -> execute(array(
			":groupid" => $groupid,
			":userid" => $userid
		));
		if (!$res) {
			trigger_error("Could not add user to group: " . $db -> errorInfo()[2]);
			return false;
		}

		// commit and return the user id
		$db -> commit();
		$toreturn = $userid;
	} catch (PDOException $e) {
		return false;
	}

	return $userid;
}

function createNewGroup($groupname) {
	try {
		$db = newDb();
		$q = $db -> prepare("INSERT INTO groups (groupname) VALUES 
			(:groupname)");
		$q -> execute(array(":groupname" => $groupname));
	} catch (PDOException $e) {
		return false;
	} 

	return true;
}

function placeUserInGroup($userid, $groupid) {
	try {
		$db = newDb();
		$q = $db -> prepare("INSERT INTO ingroup (groupid, userid) 
			VALUES (:groupid, :userid)");
		$q -> execute(array(
			":groupid" => $groupid, 
			":userid" => $userid
		));
	} catch (PDOException $e) {
		return false;
	}

	return true;
}

function validateUser($username, $password) {
	$result = false;
	try {
		$db = newDb();

		// get the user data
		$q = $db -> prepare("SELECT userid, username, password, salt, iterations,
			algorithm FROM users WHERE username = :username");
		$q -> execute(array(":username" => $username));
		$data = $q -> fetch();


		// create an instance of Password and match the entered password
		$passwordValidator = new Password(
			$data["password"], $data["salt"], $data["iterations"], 
			$data["algorithm"], true);
		if ($passwordValidator -> validatePassword($password)) {
			$result = $data["userid"];
		} else {
			$result = false;
		}
	} catch (PDOException $e) {
		$result = false;
	}

	return $result;
}

function isUserLoggedIn() {
	if ($_SESSION["loggedin"] == true) {
		return true;
	} else {
		return false;
	}
}

function loginUser($userid, $username) {
	$_SESSION["loggedin"] = true;
	$_SESSION["userid"] = $userid;
	$_SESSION["username"] = $username;
}

function logoutUser() {
	$_SESSION = array();
	setcookie(session_name(), "", time() - 42000,
		$params["path"], $params["domain"],
		$params["secure"], $params["httponly"]
	);
	session_destroy();
}

?>
