<?php
include_once("./objects/Provider.php");
include_once("./objects/Request.php");

class Vultr extends Provider {
	public function locations($id = null, $provider = false) {
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
				if($provider == true){
					$locationArray['locations'][] = array(
						"id" => $regions->id,
						"country" => $regions->country,
						"city" => $regions->city,
						"provider" => "Vultr"
					);
				} else {
					$locationArray['locations'][] = array(
						"id" => $regions->id,
						"country" => $regions->country,
						"city" => $regions->city
					);
				}
			} else {
				if($regions->id == $id){
					if($provider == true){
						$response += array("locations" => array(
											"id" => $regions->id,
											"country" => $regions->country,
											"city" => $regions->city,
											"provider" => "Vultr"
										)
								);
					} else {
						$response += array("locations" => array(
											"id" => $regions->id,
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
		$request->httpRequest("GET", "https://api.vultr.com/v2/plans", $header, "");
		$response = $request->getResponse();
		$decoded = json_decode($response);
		$response = array('error' => false);
		$planArray = array('plans' => array());
		foreach($decoded->plans as $plans){
			if (is_null($id)) {
				if($provider == true){
					$planArray['plans'][] = array(
						"id" => $plans->id,
						"cores" => $plans->vcpu_count,
						"memory" => $plans->ram,
						"disk" => $plans->disk * 1000,
						"bandwidth" => $plans->bandwidth * 1000,
						"provider" => "Vultr"
					);
				} else {
					$planArray['plans'][] = array(
						"id" => $plans->id,
						"cores" => $plans->vcpu_count,
						"memory" => $plans->ram,
						"disk" => $plans->disk * 1000,
						"bandwidth" => $plans->bandwidth * 1000
					);
				}
			} else {
				if($plans->id == $id){
					if($provider == true){
						$response += array("plans" => array(
									   "id" => $plans->id,
									   "cores" => $plans->vcpu_count,
									   "memory" => $plans->ram,
					                   "disk" => $plans->disk * 1000,
					                   "bandwidth" => $plans->bandwidth * 1000,
									   "provider" => "Vultr"
										)
								);
								
					} else {
						$response += array("plans" => array(
									   "id" => $plans->id,
									   "cores" => $plans->vcpu_count,
									   "memory" => $plans->ram,
					                   "disk" => $plans->disk * 1000,
					                   "bandwidth" => $plans->bandwidth * 1000
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
		$request->httpRequest("GET", "https://api.vultr.com/v2/os", $header, "");
		$response = $request->getResponse();
		$decoded = json_decode($response);
		$response = array('error' => false);
		$osArray = array('os' => array());
		foreach($decoded->os as $os){
			if(!is_null($family)){
					if($family != $os->family){
						continue;
					}
            }	
			if (is_null($id)) {
				if($provider == true){
					$osArray['os'][] = array(
						"id" => $os->id,
						"name" => $os->name,
						"family" => $os->family,
						"provider" => "Vultr"
					);
				} else {
					$osArray['os'][] = array(
						"id" => $os->id,
						"name" => $os->name,
						"family" => $os->family
					);
				}
			} else {
				if($os->id == $id){
					if($provider == true){
						$response += array("os" => array(
									   "id" => $os->id,
									   "name" => $os->name,
					                   "family" => $os->family,
									   "provider" => "Vultr"
									)
								);
					} else {
						$response += array("os" => array(
									   "id" => $os->id,
									   "name" => $os->name,
					                   "family" => $os->family
										)
								);
					}
								break;
				}
			}
		}
		if (is_null($id)) {
			$response += $osArray;
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
		$request->httpRequest("DELETE", "https://api.vultr.com/v2/instances/$id", $header, "");
		$statusCode = $request->getStatusCode();
		if($statusCode == 204){
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
   public function stop($id){
	  
  }
  
    public function createSSHKey($key){
	$name = uniqid();
	$request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			     "Content-type: application/json\r\n";
		$postData = '{"name":"'.$name.'","ssh_key":"'.$key.'"}';
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
  
      public function createScript($script){
	$name = uniqid();
	$encodedScript = base64_encode($script);
	$request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			     "Content-type: application/json\r\n";
		$postData = '{"name":"'.$name.'","script":"'.$encodedScript.'"}';
		$request->httpRequest("POST", "https://api.vultr.com/v2/startup-scripts", $header, $postData);
		$response = $request->getResponse();
		$decoded = json_decode($response);
		if(array_key_exists('startup_script', $decoded)) {
			$id = $decoded->startup_script->id;
			return $id;
		} else {
			return null;
		}
  }
  
    public function deleteScript($id){
	$request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			      "Content-type: application/json\r\n";
		$request->httpRequest("DELETE", "https://api.vultr.com/v2/startup-scripts/$id", $header, "");
		$statusCode = $request->getStatusCode();
		if($statusCode == 204){
			$response = array('error' => false, 'message' => 'Startup script successfully deleted');
		} else {
			$response = array('error' => true, 'message' => 'Startup script could not be deleted');
		}
		return $response;
  }
  
}	
?>
