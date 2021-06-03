<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'config/core.php';
include_once 'config/database.php';

session_start();

if (isset($_GET['apikey'])) {

			// instantiate database and server object
			$database = new Database();
			$db = $database->getConnection();
	
			$query = "SELECT id, enabled FROM users WHERE apikey=:apikey";
			$stmt = $db->prepare($query);
	
			$stmt->bindParam(":apikey", $_GET['apikey']);
			$stmt->execute();
	
			if ($stmt->rowCount() == 1) { // check if API key exists
				$stmt->bindColumn('id', $userid);
				$stmt->bindColumn('enabled', $enabled);
				$stmt->fetch(); 
					if ($enabled == "1") { // check if the account of owner of the API key is enabled
						
						if (isset($_GET['provider'])) { // validate provider
								$query = "SELECT name FROM providers WHERE name LIKE :providerName";
								$stmt = $db->prepare($query);
								$stmt->bindParam(":providerName", $_GET['provider']);
								$stmt->execute();
								
								if ($stmt->rowCount() == 1) { // check if provider is valid
									$providerName = $stmt->fetchColumn();
								} else {
									$response = array ('error' => true, 'message' => 'Invalid/unknown provider');
									http_response_code(400);
									exit(json_encode($response));
								}		
						}
					
						if ($_SERVER['REQUEST_METHOD'] === 'GET') { // get server(s)		
							
							$query = "SELECT * FROM tokens WHERE userid=:userid";
							$stmt = $db->prepare($query);
							$stmt->bindParam(":userid", $userid);
							$stmt->execute();
							
							$response = array('error' => false);
							$tokenArray = array('tokens' => array());
							
							while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
								if ($row['enabled'] == "1") {
									$enabled = true;
								} else {
									$enabled = false;
								}
								if(isset($providerName)){
									if ($providerName == $row['provider']) {
										$response += array("token" => array(
														"provider" => $row['provider'],
														"token" => $row['token'],
														"enabled" => $enabled
												)
											);
										break;
									}
								} else {
									$providerArray['tokens'][] = array(
														"provider" => $row['provider'],
														"token" => $row['token'],
														"enabled" => $enabled
												);
								}
							}
							if (!isset($providerName)) {
								$response += $providerArray;
							}
						} else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
							if (isset($_GET['provider'])) {
									if (isset($_GET['token'])) {
										if (isset($providerName)) { // check provider
											if (isset($_GET['enable'])) {
												if ($_GET['enable'] == "1" OR $_GET['enable'] == "true") { // enable 
													$enable = 1;
												} else if($_GET['enable'] == "0" OR $_GET['enable'] == "false") { // disable
													$enable = 0;
												} else {
													$response = array ('error' => true, 'message' => 'Invalid value for the enable parameter');
													http_response_code(400);
													exit(json_encode($response));
												}
											}
											
											$query = "SELECT NULL FROM tokens WHERE userid=:userid AND provider=:providerName";
											$stmt = $db->prepare($query);
											$stmt->bindParam(":userid", $userid);
											$stmt->bindParam(":providerName", $providerName);
											$stmt->execute();
									
											if ($stmt->rowCount() == 0) { // add token
												if (isset($enable)) {
													$query = "INSERT INTO tokens (userid, provider, token, enabled) VALUES (:userid, :providerName, :token, $enable)";
												} else {
													$query = "INSERT INTO tokens (userid, provider, token) VALUES (:userid, :providerName, :token)";
												}
												$stmt= $db->prepare($query);
												$stmt->bindParam(":userid", $userid);
												$stmt->bindParam(":providerName", $providerName);
												$stmt->bindParam(":token", $_GET['token']);
												
												if ($stmt->execute()) {
													$response = array ('error' => false, 'message' => 'Provider token successfully saved');
													http_response_code(200);
												} else {
													$response = array ('error' => true, 'message' => 'Unknown error');
													http_response_code(400);
												}
											
											} else { // update token
												if (isset($enable)) {
													$query = "UPDATE tokens SET token=:token, enabled=$enable WHERE userid=:userid AND provider=:providerName";
												} else {
													$query = "UPDATE tokens SET token=:token WHERE userid=:userid AND provider=:providerName";
												}
												$stmt= $db->prepare($query);
												$stmt->bindParam(":token", $_GET['token']);
												$stmt->bindParam(":userid", $userid);
												$stmt->bindParam(":providerName", $providerName);
												
												
												if ($stmt->execute()) {
													$response = array ('error' => false, 'message' => 'Provider token successfully updated');
													http_response_code(200);
												} else {
													$response = array ('error' => true, 'message' => 'Unknown error');
													http_response_code(400);
												}
											}
										} else {
											$response = array ('error' => true, 'message' => 'Invalid/unknown provider');
											http_response_code(400);
										}
										
									} else if(isset($_GET['enable'])) {
										if (isset($providerName)) { // check provider
											if ($_GET['enable'] == "1" OR $_GET['enable'] == "true") { // enable 
													$enable = 1;
												} else if($_GET['enable'] == "0" OR $_GET['enable'] == "false") { // disable
													$enable = 0;
												} else {
													$response = array ('error' => true, 'message' => 'Invalid value for the enable parameter');
													http_response_code(400);
													exit(json_encode($response));
												}
												
												$query = "SELECT * FROM tokens WHERE userid=:userid AND provider=:providerName";
												$stmt = $db->prepare($query);
												$stmt->bindParam(":userid", $userid);
												$stmt->bindParam(":providerName", $providerName);
												$stmt->execute();
												
												if ($stmt->rowCount() == 1) {
													$query = "UPDATE tokens SET enabled=:enabled WHERE userid=:userid AND provider=:providerName";
													$stmt= $db->prepare($query);
													$stmt->bindParam(":enabled", $enable);
													$stmt->bindParam(":userid", $userid);
													$stmt->bindParam(":providerName", $providerName);
												
													if ($stmt->execute()) {
														$response = array ('error' => false, 'message' => 'Token status successfully updated');
														http_response_code(200);
													} else {
														$response = array ('error' => true, 'message' => 'Unknown error');
														http_response_code(400);
													}
													
												} else {
													$response = array ('error' => true, 'message' => 'No token found');
													http_response_code(400);
												}
										} else {
											$response = array ('error' => true, 'message' => 'Invalid/unknown provider');
											http_response_code(400);
										}
									} else {
										$response = array ('error' => true, 'message' => 'Missing parameter');
										http_response_code(400);
									}
							} else {
								$response = array ('error' => true, 'message' => 'Missing provider name');
								http_response_code(400);
							}								
						} else if($_SERVER['REQUEST_METHOD'] === 'DELETE') { // delete token
							if (isset($_GET['provider'])) {
								if (isset($providerName)) { // check provider
									$query = "DELETE FROM tokens WHERE userid=:userid AND provider=:providerName";
									$stmt= $db->prepare($query);
									$stmt->bindParam(":userid", $userid);
									$stmt->bindParam(":providerName", $providerName);
									$stmt->execute();
									
									if ($stmt->rowCount() == 1) {
										$response = array ('error' => false, 'message' => 'Token successfully deleted');
										http_response_code(200);
									} else {
										$response = array ('error' => true, 'message' => 'No token found');
										http_response_code(400);
									}
								} else {
									$response = array ('error' => true, 'message' => 'Invalid/unknown provider');
									http_response_code(400);
								}
							} else {
								$response = array ('error' => true, 'message' => 'Missing provider name');
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
				$response = array ('error' => true, 'message' => 'Invalid/unknown API key');
				http_response_code(400);
			}
		
} else {
	$response = array ('error' => true, 'message' => 'No API key given');
	http_response_code(400);
	// TODO: check session
}
echo json_encode($response);

?>
