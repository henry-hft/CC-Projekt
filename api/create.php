<?php
include_once './config/core.php';
include_once './config/database.php';
include_once './objects/Request.php';
 
// JWT library
include_once './libs/php-jwt-master/src/BeforeValidException.php';
include_once './libs/php-jwt-master/src/ExpiredException.php';
include_once './libs/php-jwt-master/src/SignatureInvalidException.php';
include_once './libs/php-jwt-master/src/JWT.php';
use \Firebase\JWT\JWT;

// required headers
header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Autoload all providers
spl_autoload_register(function ($class_name) {
    include 'providers/' . $class_name . '.php';
});


$data = json_decode(file_get_contents("php://input"));
if(!empty($_SERVER['HTTP_AUTHORIZATION'])){
	$arr = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
	$jwt = $arr[1];
	if (!empty($jwt)) {
		try {
			// instantiate database and server object
			$database = new Database();
			$db = $database->getConnection();
	
			$decoded = JWT::decode($jwt, $secret_key, array('HS256'));
	
			$query = "SELECT id, enabled FROM users WHERE id=:userid";
			$stmt = $db->prepare($query);
			$userid = $decoded->data->id;
			$stmt->bindParam(":userid", $userid);
			$stmt->execute();
	
			if ($stmt->rowCount() == 1) { // check if user exists
				$stmt->bindColumn('id', $userid);
				$stmt->bindColumn('enabled', $enabled);
				$stmt->fetch(); 
					if ($enabled == "1") { // check if the account of owner of the API key is enabled
						if ($_SERVER['REQUEST_METHOD'] === 'POST') { // get server(s)
							if (isset($_GET['provider'])) { // only one provider
								$query = "SELECT name FROM providers WHERE name LIKE :providerName";
								$stmt = $db->prepare($query);
								$stmt->bindParam(":providerName", $_GET['provider']);
								$stmt->execute();
								
								if ($stmt->rowCount() == 1) { // check if provider is valid
									$providerName = $stmt->fetchColumn();
									// get provider token (api key)
							
								$query = "SELECT token, enabled FROM tokens WHERE userid=:userid AND provider=:providerName";
								$stmt = $db->prepare($query);
								$stmt->bindParam(":providerName", $providerName);
								$stmt->bindParam(":userid", $userid);
								$stmt->execute();
								
								if ($stmt->rowCount() == 1) { // check if token is exists
									$stmt->bindColumn('token', $token);
									$stmt->bindColumn('enabled', $enabled);
									$stmt->fetch(); 
										if($enabled == "1"){
										if(!empty($_GET['hostname'])){
											if(!empty($_GET['location'])){
												if(!empty($_GET['plan'])){
													if(!empty($_GET['os'])){
														if(!empty($_GET['sshkey'])){
														//	if(is_int($_GET['sshkey'])){
																if(!empty($_GET['amount'])){
																	if(is_numeric($_GET['amount'])){
																		$amount = $_GET['amount'];
																	} else {
																		$response = array ('error' => true, 'message' => 'Invalid server amount.');
																		http_response_code(400);
																		exit(json_encode($response));
																	}
																
																} else {
																	$amount = 1;
																}
															
															if(!empty($_GET['script'])){
															//	if(is_int($_GET['script'])){
																	$scriptname = strtolower($_GET['script']);
																	$query = "SELECT script FROM scripts WHERE name=:scriptname AND userid=:userid";
																	$stmt = $db->prepare($query);
																	$stmt->bindParam(":scriptname", $scriptname);
																	$stmt->bindParam(":userid", $userid);
																	$stmt->execute();
								
																	if ($stmt->rowCount() == 1) { // check if provider is valid
																		$script = $stmt->fetchColumn();
																		$script = str_replace(array("\r", "\n"), '', $script);
																	} else {
																		$response = array ('error' => true, 'message' => 'Startup script not found.');
																		http_response_code(400);
																		exit(json_encode($response));
																	}		
																	
																//} else {
																//	$response = array ('error' => true, 'message' => 'Invalid startup script id.');
																//	http_response_code(400);
																//	exit(json_encode($response));
																//}
															} else {
																$script = null;
															}
															
															$SSHKeyName = strtolower($_GET['sshkey']);
															$query = "SELECT sshkey FROM sshkeys WHERE name=:sshkeyname AND userid=:userid";
																	$stmt = $db->prepare($query);
																	$stmt->bindParam(":sshkeyname", $SSHKeyName);
																	$stmt->bindParam(":userid", $userid);
																	$stmt->execute();
								
																	if ($stmt->rowCount() == 1) { // check if provider is valid
																		$sshkey = $stmt->fetchColumn();
																	} else {
																		$response = array ('error' => true, 'message' => 'SSH Key not found.');
																		http_response_code(400);
																		exit(json_encode($response));
																	}		
															
																$providerObj = new $providerName($token);
																$createSSHKey = $providerObj->createSSHKey($sshkey);
																if($createSSHKey != false){
																	if(!is_null($script)){
																		$createScript = $providerObj->createScript($script);
																		if(!is_null($createScript)){
																			if($createScript == false){
																				$response = array ('error' => true, 'message' => 'Could not create startup script');
																				http_response_code(400);
																				exit(json_encode($response));
																			}
																		} else {
																			$createScript = $script;
																		}
						
																	}
																	
																	//if
																$response = array();
																$servers = array();
																for($i = 1; $i <= $amount; $i++){
																	//$providerObj = new $providerName($token);
																	if ($amount == 1) {
																		$createResponse = $providerObj->create($_GET['hostname'], $_GET['location'], $_GET['plan'], $_GET['os'], $createSSHKey, $createScript);
																	} else {
																		$createResponse = $providerObj->create($_GET['hostname'] . '-' . $i, $_GET['location'], $_GET['plan'], $_GET['os'], $createSSHKey, $createScript);
																		$servers[] = $createResponse['servers'];
																	//	$$response[] = $servers;
																	}
																	if($createResponse['error'] == false){
																		$response = $createResponse;
																	} else {
																		$response = array ('error' => true, 'message' => 'Could not create server(s)');
																		http_response_code(400);
																		exit(json_encode($response));
																		break;
																	}
																}
																if($amount > 1){
																	$response['servers'] = $servers;
																}
																$deleteSSHKey = $providerObj->deleteSSHKey($createSSHKey);
																$deleteScript = $providerObj->deleteScript($createScript);
																
																
																if($createResponse['error'] == "false"){
																	http_response_code(200);
																} else {
																	http_response_code(400);
																}
																} else {
																	$response = array ('error' => true, 'message' => 'Could not create ssh key');
																	http_response_code(400);
																}
																
																//} else {
													//	$response = array ('error' => true, 'message' => 'Invalid sshkey id');
													//	http_response_code(400);
													//	}
														} else {
														$response = array ('error' => true, 'message' => 'The sshkey id parameter is missing.');
														http_response_code(400);
														}
														} else {
														$response = array ('error' => true, 'message' => 'The server os parameter is missing.');
														http_response_code(400);
														}
													} else {
														$response = array ('error' => true, 'message' => 'The server plan parameter is missing.');
													http_response_code(400);
													}
												} else {
												$response = array ('error' => true, 'message' => 'The server location parameter is missing.');
												http_response_code(400);
												}
											} else {
												$response = array ('error' => true, 'message' => 'The server hostname parameter is missing.');
												http_response_code(400);
											}
										} else {
											$response = array ('error' => true, 'message' => 'Provider is disabled');
											http_response_code(400);
										}											
								} else {
									$response = array ('error' => true, 'message' => 'No provider API Key found');
									http_response_code(400);
								}

								} else {
									$response = array ('error' => true, 'message' => 'Invalid/unknown provider');
									http_response_code(400);
								}		
							} else {
								$response = array ('error' => true, 'message' => 'Missing provider parameter');
								http_response_code(400);
							}		
							
						} else {
							$response = array ('error' => true, 'message' => 'Invalid request method');
							http_response_code(400);
						}
					} else { // Invalid/unknown API Key
						$response = array ('error' => true, 'message' => 'Authentification failed');
						http_response_code(400);
					}
			}else{
				$response = array ('error' => true, 'message' => 'Invalid/unknown user');
				http_response_code(400);
			}
		
} catch (Exception $e) {
			$response = array ('error' => true, 'message' => 'Authentication failed');
			http_response_code(401);
		}
	} else {
		$response = array ('error' => true, 'message' => 'Missing access token');
	}	
} else {
	$response = array ('error' => true, 'message' => 'Missing access token');
}
echo json_encode($response);

?>
