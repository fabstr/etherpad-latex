<?php

class EtherpadLiteCommand {
	private $arguments;
	private $command;

	public function __construct($cmd, $argarr = array()) {
		$this -> command = $cmd;
		$this -> arguments = $argarr;
	}

	public function setArgument($arg, $value) {
		$this -> argument[$arg] = $value;
	}

	public function toGetString() {
		$str = $command;
		if (sizeof($arguments) > 0) {
			$str .= "?";
			foreach ($arguments as $key => $val) {
				$str .= $key . "=" . $val . "&"; 
			}
			$str = trim($str, "&");
		}
		return $str;
	}
}

class EtherpadException extends Exception {
	public function __construct ($response) {
		$str = "Code: " . $response["code"];
		$str .= "Message: " . $response["message"];
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

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $urlwithcmd);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($curl);
		curl_close($curl);

		$response = json_decode($response);
		if ($response["code"] != 0) {
			throw new EtherpadException($response);
		} else {
			return $response["data"];
		}
	}

	public function getAuthorId($userid) {
		$result = $this -> createAuthorIfNotExistsFor($userid);
		return $result["authorID"];
	}

	public function createGroup() {
		$cmd = new EtherpadLiteCommand("createGroup");
		return $this -> executeEtherpadQuery($cmd);
	}

	public function createGroupIfNotExistsFor($groupMapper) {
		$cmd = new EtherpadLiteCommand("createAuthorIfNotExistsFor", 
			array("groupMapper" => $groupMapper));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function deleteGroup($groupID) {
		$cmd = new EtherpadLiteCommand("deleteGroup", array(
			"groupID" => $groupID));
		return $this -> executeEtherpadQuery($cmd);
	}


	public function listPads($groupID) {
		$cmd = new EtherpadLiteCommand("listPads", array(
			"groupID" => $groupID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function createGroupPad($groupID, $padName, $text = false) {
		$cmd = new EtherpadLiteCommand("createGroupPad", array(
			"groupID" => $groupID, 
			"padName" => $padName));
		if ($text) {
			$cmd -> setArgument("text", $text);
		}
		return $this -> executeEtherpadQuery($cmd);
	}

	public function listAllGroups() {
		$cmd = new EtherpadLiteCommand("listAllGroups");
		return $this -> executeEtherpadQuery($cmd);
	}

	public function createAuthor($name = false) {
		$cmd = new EtherpadLiteCommand("createAuthor");
		if ($name) {
			$cmd -> setArgument("name", $name);
		}
		return $this -> executeEtherpadQuery($cmd);
	}

	public function createAuthorIfNotExistsFor($authorMapper, 
			$name = false) {
		$cmd = new EtherpadLiteCommand("createAuthorIfNotExistsFor", 
			array("authorMapper" => $authorMapper));
		if ($name) {
			$cmd -> setArgument("name", $name);
		}
		return $this -> executeEtherpadQuery($cmd);
	}

	public function listPadsOfAuthor($authorID) {
		$cmd = new EtherpadLiteCommand("listPadsOfAuthor", array(
			"authorID" => $authorID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function getAuthorName($authorID) {
		$cmd = new EtherpadLiteCommand("getAuthorName", array(
			"authorID" => $authorID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function createSession($groupID, $authorID, $validUntil) {
		$cmd = new EtherpadLiteCommand("createSession", array(
			"groupID" => $groupID, 
			"authorID" => $authorID, 
			"validUntil" => $validUntil));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function deleteSession($sessionID) {
		$cmd = new EtherpadLiteCommand("deleteSession", array(
			"sessionID" => $sessionID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function getSessionInfo($sessionID) {
		$cmd = new EtherpadLiteCommand("getSessionInfo", array(
			"sessionID" => $sessionID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function listSessionsOfGroup($groupID) {
		$cmd = new EtherpadLiteCommand("listSessionsOfGroup", array(
			"groupID" => $groupID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function listSessionsOfAuthor($authorID) {
		$cmd = new EtherpadLiteCommand("listPadsOfAuthor", array(
			"authorID" => $authorID));
		return $this -> executeEtherpadQuery($cmd);
	}


	public function getText($padID, $rev = false) {
		$cmd = new EtherpadLiteCommand("getText", array(
			"padID" => $padID));
		if ($rev) {
			$cmd -> setArgument("rev", $rev);
		}
		return $this -> executeEtherpadQuery($cmd);
	}

	public function setText($padID, $text) {
		$cmd = new EtherpadLiteCommand("setText", array(
			"padID" => $padID));
		if ($text) {
			$cmd -> setArgument("text", $text);
		}
		return $this -> executeEtherpadQuery($cmd);
	}

	public function getHTML($padID, $rev = false) {
		$cmd = new EtherpadLiteCommand("getHTML", array(
			"padID" => $padID));
		if ($rev) {
			$cmd -> setArgument("rev", $rev);
		}
		return $this -> executeEtherpadQuery($cmd);
	}

	public function setHTML($padID, $html) {
		$cmd = new EtherpadLiteCommand("setHTML", array(
			"padID" => $padID));
		if ($html) {
			$cmd -> setArgument("html", $html);
		}
		return $this -> executeEtherpadQuery($cmd);
	}

	public function getAttributePool($padID) {
		$cmd = new EtherpadLiteCommand("getAttributePool", array(
			"padID" => $padID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function getRevisionChangeset($padID, $rev = false) {
		$cmd = new EtherpadLiteCommand("getRevisionChangeset", array(
			"padID" => $padID));
		if ($rev) {
			$cmd -> setArgument("rev", $rev);
		}
		return $this -> executeEtherpadQuery($cmd);
	}

	public function createDiffHTML($padID, $startRev, $endRev) {
		$cmd = new EtherpadLiteCommand("createDiffHTML", array(
			"padID" => $padID));
		if ($startRev) {
			$cmd -> setArgument("startRev", $startRev);
		}

		if ($endRev) {
			$cmd -> setArgument("endRev", $endRev);
		}
		return $this -> executeEtherpadQuery($cmd);
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

		return $this -> executeEtherpadQuery($cmd);
	}

	public function getChatHead($padID) {
		$cmd = new EtherpadLiteCommand("getChatHead", array(
			"padID" => $padID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function createPad($padID, $text = false) {
		$cmd = new EtherpadLiteCommand("createPad", array(
			"padID" => $padID));
		if ($text) {
			$cmd -> setArgument("text", $text);
		}
		return $this -> executeEtherpadQuery($cmd);
	}

	public function getRevisionsCount($padID) {
		$cmd = new EtherpadLiteCommand("getRevisionsCount", array(
			"padID" => $padID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function padUsersCount($padID) {
		$cmd = new EtherpadLiteCommand("padUsersCount", array(
			"padID" => $padID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function padUsers($padID) {
		$cmd = new EtherpadLiteCommand("padUsersCount", array(
			"padID" => $padID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function deletePad($padID) {
		$cmd = new EtherpadLiteCommand("deletePad", array(
			"padID" => $padID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function copyPad($sourceID, $destinationID, $force=false) {
		$cmd = new EtherpadLiteCommand("copyPad", array(
			"sourceID" => $sourceID, 
			"destinationID" => $destinationID));
		if ($force) {
			$cmd -> setArgument("force", $force);
		}
		return $this -> executeEtherpadQuery($cmd);
	}

	public function movePad($sourceID, $destinationID, $force=false) {
		$cmd = new EtherpadLiteCommand("movePad", array(
			"sourceID" => $sourceID, 
			"destinationID" => $destinationID));
		if ($force) {
			$cmd -> setArgument("force", $force);
		}
		return $this -> executeEtherpadQuery($cmd);
	}

	public function getReadOnlyID($padID) {
		$cmd = new EtherpadLiteCommand("getReadOnlyID", array(
			"padID" => $padID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function setPublicStatus($padID, $publicStatus) {
		$cmd = new EtherpadLiteCommand("setPublicStatus", array(
			"padID" => $padID,
		       	"publicStatus" => $publicStatus));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function getPublicStatus($padID) {
		$cmd = new EtherpadLiteCommand("getPublicStatus", array(
			"padID" => $padID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function setPassword($padID, $password) {
		$cmd = new EtherpadLiteCommand("setPassword", array(
			"padID" => $padID,
		       	"password" => $password));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function isPasswordProtected($padID) {
		$cmd = new EtherpadLiteCommand("isPasswordProtected", array(
			"padID" => $padID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function listAuthorsOfPad($padID) {
		$cmd = new EtherpadLiteCommand("listAuthorsOfPad", array(
			"padID" => $padID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function getLastEdited($padID) {
		$cmd = new EtherpadLiteCommand("getLastEdited", array(
			"padID" => $padID));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function sendClientsMessage($padID, $msg) {
		$cmd = new EtherpadLiteCommand("sendClientsMessage", array(
			"padID" => $padID, "msg" => $msg));
		return $this -> executeEtherpadQuery($cmd);
	}

	public function checkToken() {
		$cmd = new EtherpadLiteCommand("checkToken");
		return $this -> executeEtherpadQuery($cmd);
	}

	public function listAllPads() {
		$cmd = new EtherpadLiteCommand("listAllPads");
		return $this -> executeEtherpadQuery($cmd);
	}
}

?>
