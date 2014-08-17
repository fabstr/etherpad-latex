<?php

class EtherpadLiteCommand {
	private $arguments;
	private $command;

	public function __construct($cmd, $argarr = array()) {
		$this -> command = $cmd;
		$this -> arguments = $argarr;
	}

	public function setArgument($arg, $value) {
		$this -> arguments[$arg] = $value;
	}

	public function toGetString() {
		$str = sprintf("%s?", $this -> command);
		if (sizeof($this -> arguments) > 0) {
			foreach ($this -> arguments as $key => $val) {
				$str .= $key . "=" . $val . "&"; 
			}
			$str = trim($str, "&");
		}
		return $str;
	}
}

class EtherpadException extends Exception {
	public function __construct ($response) {
		$str = "Code: " . $response -> code . ", ";
		$str .= "Message: " . $response -> message;
		parent::__construct($str);
	}
}

class PadManager {
	private $url;
	private $apikey;

	public function __construct ($host, $apikey) {
		$this -> url = $host . "/api/1.2.8/";
		$this -> apikey = $apikey;
	}

	private function executeEtherpadQuery($cmd) {
		$cmd -> setArgument("apikey", $this -> apikey);
		$urlwithcmd = $this -> url . $cmd -> toGetString();

		$response = json_decode(file_get_contents($urlwithcmd));
		if ($response -> code != 0) {
			throw new EtherpadException($response);
		} else {
			return $response -> data;
		}
	}

	public function getPadsByUserid($userid) {
		$authorid = $this -> createAuthorIfNotExistsFor($userid);
		$pads = $this -> listPadsOfAuthor($authorid);
		return $pads;
	}

	// here starts standard api calls

	public function createGroup() {
		$cmd = new EtherpadLiteCommand("createGroup");
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> groupID;
	}

	public function createGroupIfNotExistsFor($groupMapper) {
		$cmd = new EtherpadLiteCommand("createGroupIfNotExistsFor", 
			array("groupMapper" => $groupMapper));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> groupID;
	}

	public function deleteGroup($groupID) {
		$cmd = new EtherpadLiteCommand("deleteGroup", array(
			"groupID" => $groupID));
		$data = $this -> executeEtherpadQuery($cmd);
	}


	public function listPads($groupID) {
		$cmd = new EtherpadLiteCommand("listPads", array(
			"groupID" => $groupID));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> padIDs;
	}

	public function createGroupPad($groupID, $padName, $text = false) {
		$cmd = new EtherpadLiteCommand("createGroupPad", array(
			"groupID" => $groupID, 
			"padName" => $padName));
		if ($text) {
			$cmd -> setArgument("text", $text);
		}
		$data = $this -> executeEtherpadQuery($cmd);
	}

	public function listAllGroups() {
		$cmd = new EtherpadLiteCommand("listAllGroups");
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> groupIDs;
	}

	public function createAuthor($name = false) {
		$cmd = new EtherpadLiteCommand("createAuthor");
		if ($name) {
			$cmd -> setArgument("name", $name);
		}
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> authorID;
	}

	public function createAuthorIfNotExistsFor($authorMapper, 
			$name = false) {
		$cmd = new EtherpadLiteCommand("createAuthorIfNotExistsFor", 
			array("authorMapper" => $authorMapper));
		if ($name) {
			$cmd -> setArgument("name", $name);
		}
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> authorID;
	}

	public function listPadsOfAuthor($authorID) {
		$cmd = new EtherpadLiteCommand("listPadsOfAuthor", array(
			"authorID" => $authorID));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> padIDs;
	}

	public function getAuthorName($authorID) {
		$cmd = new EtherpadLiteCommand("getAuthorName", array(
			"authorID" => $authorID));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> authorName;
	}

	public function createSession($groupID, $authorID, $validUntil) {
		$cmd = new EtherpadLiteCommand("createSession", array(
			"groupID" => $groupID, 
			"authorID" => $authorID, 
			"validUntil" => $validUntil));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> sessionID;
	}

	public function deleteSession($sessionID) {
		$cmd = new EtherpadLiteCommand("deleteSession", array(
			"sessionID" => $sessionID));
		$data = $this -> executeEtherpadQuery($cmd);
	}

	public function getSessionInfo($sessionID) {
		$cmd = new EtherpadLiteCommand("getSessionInfo", array(
			"sessionID" => $sessionID));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> authorID;
	}

	public function listSessionsOfGroup($groupID) {
		$cmd = new EtherpadLiteCommand("listSessionsOfGroup", array(
			"groupID" => $groupID));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data;
	}

	public function listSessionsOfAuthor($authorID) {
		$cmd = new EtherpadLiteCommand("listPadsOfAuthor", array(
			"authorID" => $authorID));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data;
	}


	public function getText($padID, $rev = false) {
		$cmd = new EtherpadLiteCommand("getText", array(
			"padID" => $padID));
		if ($rev) {
			$cmd -> setArgument("rev", $rev);
		}
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> text;
	}

	public function setText($padID, $text) {
		$cmd = new EtherpadLiteCommand("setText", array(
			"padID" => $padID));
		if ($text) {
			$cmd -> setArgument("text", $text);
		}
		$data = $this -> executeEtherpadQuery($cmd);
	}

	public function getHTML($padID, $rev = false) {
		$cmd = new EtherpadLiteCommand("getHTML", array(
			"padID" => $padID));
		if ($rev) {
			$cmd -> setArgument("rev", $rev);
		}
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> html;
	}

	public function setHTML($padID, $html) {
		$cmd = new EtherpadLiteCommand("setHTML", array(
			"padID" => $padID));
		if ($html) {
			$cmd -> setArgument("html", $html);
		}
		$data = $this -> executeEtherpadQuery($cmd);
	}

	public function getChatHistory($padID, $start = false, $end = false) {
		$cmd = new EtherpadLiteCommand("getChatHistory", array(
			"padID" => $padID));
		if ($start) {
			$cmd -> setArgument("start", $start);
		}

		if ($end) {
			$cmd -> setArgument("end", $end);
		}

		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> messages;
	}

	public function getChatHead($padID) {
		$cmd = new EtherpadLiteCommand("getChatHead", array(
			"padID" => $padID));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> chatHead;
	}

	public function createPad($padID, $text = false) {
		$cmd = new EtherpadLiteCommand("createPad", array(
			"padID" => $padID));
		if ($text) {
			$cmd -> setArgument("text", $text);
		}
		$data = $this -> executeEtherpadQuery($cmd);
	}

	public function getRevisionsCount($padID) {
		$cmd = new EtherpadLiteCommand("getRevisionsCount", array(
			"padID" => $padID));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> revisions;
	}

	public function padUsersCount($padID) {
		$cmd = new EtherpadLiteCommand("padUsersCount", array(
			"padID" => $padID));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> padUsersCount;
	}

	public function padUsers($padID) {
		$cmd = new EtherpadLiteCommand("padUsersCount", array(
			"padID" => $padID));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> padUsers;
	}

	public function deletePad($padID) {
		$cmd = new EtherpadLiteCommand("deletePad", array(
			"padID" => $padID));
		$data = $this -> executeEtherpadQuery($cmd);
	}

	public function getReadOnlyID($padID) {
		$cmd = new EtherpadLiteCommand("getReadOnlyID", array(
			"padID" => $padID));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> readOnlyID;
	}

	public function setPublicStatus($padID, $publicStatus) {
		$cmd = new EtherpadLiteCommand("setPublicStatus", array(
			"padID" => $padID,
		       	"publicStatus" => $publicStatus));
		$data = $this -> executeEtherpadQuery($cmd);
	}

	public function getPublicStatus($padID) {
		$cmd = new EtherpadLiteCommand("getPublicStatus", array(
			"padID" => $padID));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> publicStatus;
	}

	public function setPassword($padID, $password) {
		$cmd = new EtherpadLiteCommand("setPassword", array(
			"padID" => $padID,
		       	"password" => $password));
		$data = $this -> executeEtherpadQuery($cmd);
	}

	public function isPasswordProtected($padID) {
		$cmd = new EtherpadLiteCommand("isPasswordProtected", array(
			"padID" => $padID));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> isPasswordProtected;
	}

	public function listAuthorsOfPad($padID) {
		$cmd = new EtherpadLiteCommand("listAuthorsOfPad", array(
			"padID" => $padID));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> authorIDs;
	}

	public function getLastEdited($padID) {
		$cmd = new EtherpadLiteCommand("getLastEdited", array(
			"padID" => $padID));
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> lastEdited;
	}

	public function sendClientsMessage($padID, $msg) {
		$cmd = new EtherpadLiteCommand("sendClientsMessage", array(
			"padID" => $padID, "msg" => $msg));
		$data = $this -> executeEtherpadQuery($cmd);
	}

	public function checkToken() {
		$cmd = new EtherpadLiteCommand("checkToken");
		$data = $this -> executeEtherpadQuery($cmd);
	}

	public function listAllPads() {
		$cmd = new EtherpadLiteCommand("listAllPads");
		$data = $this -> executeEtherpadQuery($cmd);
		return $data -> padIDs;
	}
}

?>
