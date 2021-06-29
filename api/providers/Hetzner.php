<?php
include_once("./objects/Provider.php");
include_once("./objects/Request.php");

class Hetzner extends Provider {
	public function locations($id = null) {
		$request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			     "Content-type: application/json\r\n";
		$request->httpRequest("GET", "https://api.hetzner.cloud/v1/locations", $header, "");
		$response = $request->getResponse();
		$decoded = json_decode($response);
		$response = array('error' => false);
		$locationArray = array('locations' => array());
		foreach($decoded->locations as $regions){
			if (is_null($id)) {
				$locationArray['locations'][] = array(
					"id" => $regions->name,
					"country" => $regions->country,
					"city" => $regions->city
				);
			} else {
				if($regions->name == $id){
					$response += array("locations" => array(
											"id" => $regions->name,
											"country" => $regions->country,
											"city" => $regions->city
										)
								);
								break;
				}
			}
		}
		if (is_null($id)) {
			$response += $locationArray;
		} else if (count($response) < 2) {
			$response = array("error" => true, "message" => "Unknown location");
		}
		return $response;
	}
  public function create(){
	  
  }
   public function delete(){
	  
  }
   public function status(){
	  
  }
   public function info(){
	  
  }
   public function start(){
	  
  }
   public function stop(){
	  
  }
}	
?>
