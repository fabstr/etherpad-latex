<?php

class Response {
	private $codes = array(
		200 => "OK",
		403 => "Forbidden"
	);

	private $code;
	private $message;

	public function __construct ($code, $msg = "") {
		$this -> code = $code;
		$this -> message = $msg;
	}

	public function __toString() {
		$str = array(
			"code" => $this -> code
		);
		if ($this -> message == "") {
			$str["message"] = $this -> codes[$this -> code];
		} else {
			$str["message"] = $this -> message;
		}

		http_response_code($this -> code);

		return json_encode($str);
	}
}

?>
