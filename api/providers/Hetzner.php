<?php
include_once("./objects/Provider.php");
include_once("./objects/Request.php");

class Hetzner extends Provider {
	public function locations($id = null, $allProviders = false) {
		$request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			     "Content-type: application/json\r\n";
		$request->httpRequest("GET", "https://api.hetzner.cloud/v1/locations", $header, "");
		$response = $request->getResponse();
		$decoded = json_decode($response);
		$response = array('error' => false);
		if($allProviders == true){
			$locationArray = array('locations' => array('Hetzner' => array()));
		} else {
			$locationArray = array('locations' => array());
		}
		foreach($decoded->locations as $regions){
			if (is_null($id)) {
				if($allProviders == true){
					$locationArray['locations']['Hetzner'][] = array(
						"id" => $regions->name,
						"country" => $regions->country,
						"city" => $regions->city
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
					if($allProviders == false){
						$response += array("locations" => array(
											"id" => $regions->name,
											"country" => $regions->country,
											"city" => $regions->city,
											"provider" => "Hetzner"
										)
								);
					} else {
						$response = array("error" => true, "message" => "Missing provider parameter");
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
		public function plans($id = null, $allProviders = false) {
		$request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			     "Content-type: application/json\r\n";
		$request->httpRequest("GET", "https://api.hetzner.cloud/v1/server_types", $header, "");
		$response = $request->getResponse();
		$decoded = json_decode($response);
		$response = array('error' => false);
		if($allProviders == true){
			$planArray = array('plans' => array('Hetzner' => array()));
		} else {
			$planArray = array('plans' => array());
		}
		foreach($decoded->server_types as $plans){
			if (is_null($id)) {
				if($allProviders == true){
					$planArray['plans']['Hetzner'][] = array(
						"id" => $plans->name,
						"cores" => $plans->cores,
						"memory" => $plans->memory * 1024,
						"disk" => $plans->disk * 1000,
						"bandwidth" => 20 * 1000 * 1024
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
					if($allProviders == false){
						$response += array("plans" => array(
											"id" => $plans->name,
											"cores" => $plans->cores,
											"memory" => $plans->memory * 1024,
											"disk" => $plans->disk * 1000,
											"bandwidth" => 20 * 1000 * 1024
										)
								);
					} else {
						$response = array("error" => true, "message" => "Missing provider parameter");
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
	
	public function os($id = null, $family = null, $allProviders = false) {
		$request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			     "Content-type: application/json\r\n";
		$request->httpRequest("GET", "https://api.hetzner.cloud/v1/images", $header, "");
		$response = $request->getResponse();
		$decoded = json_decode($response);
		$response = array('error' => false);
		
		if($allProviders == true){
			$osArray = array('os' => array('Hetzner' => array()));
		} else {
			$osArray = array('os' => array());
		}
		
		foreach($decoded->images as $os){
			if($os->type == "system"){
				if(!is_null($family)){
					if($family != $os->os_flavor){
						continue;
					}
                }					
			if (is_null($id)) {
				if($allProviders == true){
					$osArray['os']['Hetzner'][] = array(
					   "id" => $os->id,
					   "name" => $os->description,
					   "family" => $os->os_flavor
					);
				} else {
					$osArray['os'][] = array(
					   "id" => $os->id,
					   "name" => $os->description,
					   "family" => $os->os_flavor
					);
				}
			} else {
				if($os->name == $id){
					if($provider == false){
						$response += array("os" => array(
										"id" => $os->id,
										"name" => $os->description,
										"family" => $os->os_flavor
										)
								);
					} else {
						$response = array("error" => true, "message" => "Missing provider parameter");
					}
					break;
				}
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
  public function create($hostname, $location, $plan, $os, $sshkey, $script = null){
	   $request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			     "Content-type: application/json\r\n";
		$postData = '{"name":"'.$hostname.'","location":"'.$location.'","server_type":"'.$plan.'","image":"'.$os.'","ssh_keys":['.$sshkey.'],"user_data":"'.$script.'"}';
		$request->httpRequest("POST", "https://api.hetzner.cloud/v1/servers", $header, $postData);
		$response = $request->getResponse();
		$decoded = json_decode($response);
		if(!isset($decoded->error)){
			$id = $decoded->server->id;
			$osId = $decoded->server->image->id;
			$os = $decoded->server->image->description;
			$location = $decoded->server->datacenter->location->name;
			$plan = $decoded->server->server_type->name;
			$hostname = $decoded->server->name;
			$status = $decoded->server->status;
			
			$server = array('id' => $id, 'hostname' => $hostname, 'status' => $status, 'os' => $os, 'osID' => $osId, 'location' => $location, 'plan' => $plan);
			$response = array('error' => false, 'message' => 'Server successfully created', 'servers' => $server);
		} else {
			$response = array('error' => true, 'message' => 'Server could not be created');
		}
		return $response;
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
		if(!isset($decoded->error)){
			$response = array('error' => false, 'message' => 'Server successfully deleted');
		} else {
			$response = array('error' => true, 'message' => 'Server could not be deleted');
		}
		return $response;
   }
       public function servers($id = null, $allProviders = false){
	  $request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			     "Content-type: application/json\r\n";
		$request->httpRequest("GET", "https://api.hetzner.cloud/v1/servers", $header, "");
		$response = $request->getResponse();
		$decoded = json_decode($response);
		$response = array('error' => false);
		if($allProviders == true){
			$planArray = array('servers' => array('Hetzner' => array()));
		} else {
			$planArray = array('servers' => array());
		}
		foreach($decoded->servers as $instances){
			if (is_null($id)) {
				if($allProviders == true){
					$planArray['servers']['Hetzner'][] = array(
						"id" => $instances->id,
						"hostname" => $instances->name,
						"status" => $instances->status,
						"created" => strtotime($instances->created),
						"ipv4" => $instances->public_net->ipv4->ip,
						"ipv6" => $instances->public_net->ipv6->ip,
						"location" => $instances->datacenter->location->name,
						"os" => $instances->image->description,
						"osID" => $instances->image->id,
						"plan" => $instances->server_type->name,
						"bandwidth" => floor($instances->included_traffic / 1000),
						"cores" => $instances->server_type->cores,
						"memory" => $instances->server_type->memory * 1000,
						"disk" => $instances->server_type->disk * 1000
					);
				} else {
					$planArray['servers'][] = array(
						"id" => $instances->id,
						"hostname" => $instances->name,
						"status" => $instances->status,
						"created" => strtotime($instances->created),
						"ipv4" => $instances->public_net->ipv4->ip,
						"ipv6" => $instances->public_net->ipv6->ip,
						"location" => $instances->datacenter->location->name,
						"os" => $instances->image->description,
						"osID" => $instances->image->id,
						"plan" => $instances->server_type->name,
						"bandwidth" => floor($instances->included_traffic / 1000),
						"cores" => $instances->server_type->cores,
						"memory" => $instances->server_type->memory * 1000,
						"disk" => $instances->server_type->disk * 1000
					);
				}
			} else {
				if($instances->id == $id){
					if($allProviders == false){
						$response += array("servers" => array(
										"id" => $instances->id,
										"hostname" => $instances->name,
										"status" => $instances->status,
										"created" => strtotime($instances->created),
										"ipv4" => $instances->public_net->ipv4->ip,
										"ipv6" => $instances->public_net->ipv6->ip,
										"location" => $instances->datacenter->location->name,
										"os" => $instances->image->description,
										"osID" => $instances->image->id,
										"plan" => $instances->server_type->name,
										"bandwidth" => floor($instances->included_traffic / 1000),
										"cores" => $instances->server_type->cores,
										"memory" => $instances->server_type->memory * 1000,
										"disk" => $instances->server_type->disk * 1000
										)
								);
					} else {
						$response = array("error" => true, "message" => "Missing provider parameter");
					}
					break;
				}
			}
		}
		if (is_null($id)) {
			$response += $planArray;
		} else if (count($response) < 2) {
			$response = array("error" => true, "message" => "Server not found");
		}
		return $response;
	}
   public function control($id, $action){
	   $request = new Request();
		$apikey = $this->token;
		$header = "Accept-language: en\r\n" .
				  "Authorization: Bearer $apikey\r\n" . 
			     "Content-type: application/json\r\n";
	   if($action == "reboot"){
		   $request->httpRequest("POST", "https://api.hetzner.cloud/v1/servers/$id/actions/reboot", $header, "");
		   $response = $request->getResponse();
		   $decoded = json_decode($response);
		    if(!isset($decoded->error->code)){
			    $response = array("error" => false, "message" => "The server has been restarted successfully.");
		   } else {
			   $response = array("error" => true, "message" => "The server could not be restarted.");
		   }
	   } else if ($action == "start" OR $action == "boot") {
		   $request->httpRequest("POST", "https://api.hetzner.cloud/v1/servers/$id/actions/poweron", $header, "");
		   $response = $request->getResponse();
		   $decoded = json_decode($response);
		    if(!isset($decoded->error->code)){
			    $response = array("error" => false, "message" => "The server has been started successfully.");
		   } else {
			   $response = array("error" => true, "message" => "The server could not be started.");
		   }
	   } else if ($action == "stop" OR $action == "shutdown") {
		   $request->httpRequest("POST", "https://api.hetzner.cloud/v1/servers/$id/actions/shutdown", $header, "");
		   $response = $request->getResponse();
		   $decoded = json_decode($response);
		   if(!isset($decoded->error->code)){
			    $response = array("error" => false, "message" => "The server has been stopped successfully.");
		   } else {
			   $response = array("error" => true, "message" => "The server could not be stopped.");
		   }
	   } else {
		   $response = array("error" => true, "message" => "Unknown action");
	   }
	   return $response;  
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
		if(isset($decoded->ssh_key)){
			$id = $decoded->ssh_key->id;
			return $id;
		} else {
			return false;
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
		//	$response = true;
			$response = array('error' => false, 'message' => 'SSH Key successfully deleted');
		} else {
		//	$response = false;
			$response = array('error' => true, 'message' => 'SSH Key could not be deleted');
		}
		return $response;
  }
  
   public function createScript($script){
	   return null;
   }
   
   public function deleteScript($id){
	   return null;
   }
}	
?>
