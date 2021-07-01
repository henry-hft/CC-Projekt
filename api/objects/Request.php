<?php
class Request {

	private $response;
	private $header;
	
	function httpRequest($method, $url, $header = "", $content = "") {
		$opts = array(
			'http'=> array(
				'method' => $method,
				'header' => $header,
				'ignore_errors' => true,
				'content' => $content
			)
		);

		$context = stream_context_create($opts);
		$this->response = file_get_contents($url, false, $context);
		$this->header = $http_response_header;
	}
	
	function getResponse(){
		return $this->response;
	}
	
	function getStatusCode(){
		if (is_array($this->header)) {
			$parts = explode(' ', $this->header[0]);
			if (count($parts) > 1) { //HTTP/1.0 <code> <text>
				return intval($parts[1]); //Get code
			}
		}
		return 0;
	}
}
?>
