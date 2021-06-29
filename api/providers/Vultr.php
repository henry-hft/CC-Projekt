<?php
include_once("./objects/Provider.php");
include_once("./objects/Request.php");

class Vultr extends Provider {
	public function locations($id = null) {
		$request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			     "Content-type: application/json\r\n";
		$request->httpRequest("GET", "https://api.vultr.com/v2/regions", $header, "");
		$response = $request->getResponse();
		$decoded = json_decode($response);
		$response = array('error' => false);
		$locationArray = array('locations' => array());
		foreach($decoded->regions as $regions){
			if (is_null($id)) {
				$locationArray['locations'][] = array(
					"id" => $regions->id,
					"country" => $regions->country,
					"city" => $regions->city
				);
			} else {
				if($regions->id == $id){
					$response += array("locations" => array(
											"id" => $regions->id,
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
		public function plans($id = null) {
		$request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			     "Content-type: application/json\r\n";
		$request->httpRequest("GET", "https://api.vultr.com/v2/plans", $header, "");
		$response = $request->getResponse();
		$decoded = json_decode($response);
		$response = array('error' => false);
		$planArray = array('plans' => array());
		foreach($decoded->plans as $plans){
			if (is_null($id)) {
				$planArray['plans'][] = array(
					"id" => $plans->id,
					"cores" => $plans->vcpu_count,
					"memory" => $plans->ram,
					"disk" => $plans->disk * 1000,
					"bandwidth" => $plans->bandwidth * 1000
				);
			} else {
				if($plans->id == $id){
					$response += array("plans" => array(
									   "id" => $plans->id,
									   "cores" => $plans->vcpu_count,
									   "memory" => $plans->ram,
					                   "disk" => $plans->disk * 1000,
					                   "bandwidth" => $plans->bandwidth * 1000
										)
								);
								break;
				}
			}
		}
		if (is_null($id)) {
			$response += $planArray;
		} else if (count($response) < 2) {
			$response = array("error" => true, "message" => "Unknown server plan");
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
