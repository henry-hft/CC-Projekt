<?php
class Request {

	private $response;
	
	function httpRequest($method, $url, $header = "", $content = "") {
		$opts = array(
			'http'=> array(
				'method' => $method,
				'header' => $header,
				'content' => $content
			)
		);

		$context = stream_context_create($opts);
		$this->response = file_get_contents($url, false, $context);
	}
	
	function getResponse(){
		return $this->response;
	}
}
?>
