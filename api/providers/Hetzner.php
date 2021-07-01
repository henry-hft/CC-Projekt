<?php
include_once("./objects/Provider.php");
include_once("./objects/Request.php");

class Hetzner extends Provider {
	public function locations($id = null, $provider = false) {
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
				if($provider == true){
					$locationArray['locations'][] = array(
						"id" => $regions->name,
						"country" => $regions->country,
						"city" => $regions->city,
						"provider" => "Hetzner"
					);
				} else {
					$locationArray['locations'][] = array(
						"id" => $regions->name,
						"country" => $regions->country,
						"city" => $regions->city
					);
				}
			} else {
				if($regions->name == $id){
					if($provider == true){
						$response += array("locations" => array(
											"id" => $regions->name,
											"country" => $regions->country,
											"city" => $regions->city,
											"provider" => "Hetzner"
										)
								);
					} else {
						$response += array("locations" => array(
											"id" => $regions->name,
											"country" => $regions->country,
											"city" => $regions->city
										)
								);
					}
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
		public function plans($id = null, $provider = false) {
		$request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			     "Content-type: application/json\r\n";
		$request->httpRequest("GET", "https://api.hetzner.cloud/v1/server_types", $header, "");
		$response = $request->getResponse();
		$decoded = json_decode($response);
		$response = array('error' => false);
		$planArray = array('plans' => array());
		foreach($decoded->server_types as $plans){
			if (is_null($id)) {
				if($provider == true){
					$planArray['plans'][] = array(
						"id" => $plans->name,
						"cores" => $plans->cores,
						"memory" => $plans->memory * 1024,
						"disk" => $plans->disk * 1000,
						"bandwidth" => 20 * 1000 * 1024,
						"provider" => "Hetzner"
					);
				} else {
					$planArray['plans'][] = array(
						"id" => $plans->name,
						"cores" => $plans->cores,
						"memory" => $plans->memory * 1024,
						"disk" => $plans->disk * 1000,
						"bandwidth" => 20 * 1000 * 1024
					);
				}
			} else {
				if($plans->name == $id){
					if($provider == true){
						$response += array("plans" => array(
											"id" => $plans->name,
											"cores" => $plans->cores,
											"memory" => $plans->memory * 1024,
											"disk" => $plans->disk * 1000,
											"bandwidth" => 20 * 1000 * 1024,
											"provider" => "Hetzner"
										)
								);
					} else {
						$response += array("plans" => array(
											"id" => $plans->name,
											"cores" => $plans->cores,
											"memory" => $plans->memory * 1024,
											"disk" => $plans->disk * 1000,
											"bandwidth" => 20 * 1000 * 1024
										)
								);
					}
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
	
	public function os($id = null, $family = null, $provider = false) {
		$request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			     "Content-type: application/json\r\n";
		$request->httpRequest("GET", "https://api.hetzner.cloud/v1/images", $header, "");
		$response = $request->getResponse();
		$decoded = json_decode($response);
		$response = array('error' => false);
		$osArray = array('os' => array());
		foreach($decoded->images as $os){
			if($os->type == "system"){
				if(!is_null($family)){
					if($family != $os->os_flavor){
						continue;
					}
                }					
			if (is_null($id)) {
				if($provider == true){
					$planArray['os'][] = array(
					   "id" => $os->id,
					   "name" => $os->description,
					   "family" => $os->os_flavor,
					   "provider" => "Hetzner"
					);
				} else {
					$planArray['os'][] = array(
					   "id" => $os->id,
					   "name" => $os->description,
					   "family" => $os->os_flavor
					);
				}
			} else {
				if($plans->name == $id){
					if($provider == true){
						$response += array("plans" => array(
										"id" => $os->id,
										"name" => $os->description,
										"family" => $os->os_flavor,
										"provider" => "Hetzner"
										)
								);
					} else {
						$response += array("plans" => array(
										"id" => $os->id,
										"name" => $os->description,
										"family" => $os->os_flavor
										)
								);
					}
								break;
				}
			}
		}
		}
		if (is_null($id)) {
			$response += $planArray;
		} else if (count($response) < 2) {
			$response = array("error" => true, "message" => "Unknown operating system or operating system family");
		}
		return $response;
	}
  public function create($hostname, $location, $plan, $os, $sshkey, $script){
	  
  }
   public function delete($id){
	  $request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			     "Content-type: application/json\r\n";
		$request->httpRequest("DELETE", "https://api.hetzner.cloud/v1/servers/$id", $header, "");
		$response = $request->getResponse();
		$decoded = json_decode($response);
		if($decoded->action->error == null){
			$response = array('error' => false, 'message' => 'Server successfully deleted');
		} else {
			$response = array('error' => true, 'message' => 'Server could not be deleted');
		}
		return $response;
   }
   public function status(){
	  
  }
   public function info(){
	  
  }
   public function start(){
	  
  }
  public function createSSHKey($key){
	$name = uniqid();
	$request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			     "Content-type: application/json\r\n";
		$postData = '{"name":"'.$name.'","public_key":"'.$key.'"}';
		$request->httpRequest("POST", "https://api.hetzner.cloud/v1/ssh_keys", $header, $postData);
		$response = $request->getResponse();
		$decoded = json_decode($response);
		if(array_key_exists('ssh_key', $decoded)) {
			$id = $decoded->ssh_key->id;
			return $id;
		} else {
			return null;
		}
  }
  
    public function deleteSSHKey($id){
	$request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			      "Content-type: application/json\r\n";
		$request->httpRequest("DELETE", "https://api.hetzner.cloud/v1/ssh_keys/$id", $header, "");
		$statusCode = $request->getStatusCode();
		if($statusCode == 204){
			$response = array('error' => false, 'message' => 'SSH Key successfully deleted');
		} else {
			$response = array('error' => true, 'message' => 'SSH Key could not be deleted');
		}
		return $response;
  }
  
  public function stop($id){
	  
  }
}	
?>
