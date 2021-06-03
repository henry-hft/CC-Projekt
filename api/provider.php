<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/core.php';
include_once '../config/database.php';

session_start();

if (isset($_GET['apikey'])) {

			// instantiate database and server object
			$database = new Database();
			$db = $database->getConnection();
	
			$query = "SELECT userid, enabled FROM users WHERE apikey=?";
			$stmt = $db->getConnection()->prepare($query);
	
			$stmt->bind_param("s", $_GET['apikey']);
			$stmt->execute();
			$stmt->store_result();
	
			if ($stmt->num_rows == 1) { // check if API key exists
				$stmt->bind_result($userid, $enabled);
				$stmt->fetch();
				if ($enabled == "1") { 
					if ($enabled == "1") { // check if the account of owner of the API key is enabled
						if ($_SERVER['REQUEST_METHOD'] === 'GET') { // get server(s)
							$query = "SELECT * FROM providers";
							$stmt = $db->getConnection()->prepare($query);
							$stmt->execute();
							$provider = [];
							if (isset($_GET['provider'])) { // only one provider
								if (in_array($_GET['provider'], $providers)) { // check if provider is valid
									$providerGiven = true;
								} else {
									$providerGiven = false;
									$response = array ('success' => false, 'message' => 'Invalid/unknown provider');
								}		
							} else { // all providers
								$providerGiven = false;
							}		
							
							$response = array('success' => true);
							$providerArray = array('proivders' => array());
							
							while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
								if($providerGiven){
									if ($_GET['provider'] == $row['name']) {
										$response += array("provider" => array(
														"id" => $row['id'],
														"name" => $row['name'],
														"baseurl" => $row['baseurl'],
														"enabled" => $row['enabled']
												)
											);
										break;
									}
								} else {
									$providerArray['proivders'][] = array(
														"id" => $row['id'],
														"name" => $row['name'],
														"baseurl" => $row['baseurl'],
														"enabled" => $row['enabled']
												);
								}
							}
							if (!$providerGiven) {
								$response += $providerArray;
							}
						} else {
							$response = array ('success' => false, 'message' => 'Invalid request method');
						}
					} else { // Invalid/unknown API Key
						$response = array ('success' => false, 'message' => 'Authentification failed');
					}
				}else{
				
				}
			}else{
				$response = array ('success' => false, 'message' => 'Invalid/unknown API key');
			}
		
} else {
	// TODO: check session
}
echo json_encode($response);

?>
