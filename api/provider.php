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
	
			$query = "SELECT enabled FROM users WHERE apikey=:apikey";
			$stmt = $db->prepare($query);
	
			$stmt->bindParam(":apikey", $_GET['apikey']);
			$stmt->execute();
			//$stmt->store_result();
	
			if ($stmt->rowCount() == 1) { // check if API key exists
				$stmt->bindColumn(1, $enabled);
				$stmt->fetch(); 
					if ($enabled == "1") { // check if the account of owner of the API key is enabled
						if ($_SERVER['REQUEST_METHOD'] === 'GET') { // get server(s)
							$query = "SELECT * FROM providers";
							$stmt = $db->prepare($query);
							$stmt->execute();
							$provider = [];
							if (isset($_GET['name'])) { // only one provider
								if (in_array(strtolower($_GET['name']), $providers)) { // check if provider is valid
									$providerGiven = true;
								} else {
									$providerGiven = false;
									$response = array ('success' => false, 'message' => 'Invalid/unknown provider');
									exit(json_encode($response));
								}		
							} else { // all providers
								$providerGiven = false;
							}		
							
							$response = array('success' => true);
							$providerArray = array('proivders' => array());
							
							while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
								if ($row['enabled'] == "1") {
									$enabled = true;
								} else {
									$enabled = false;
								}
								if($providerGiven){
									if (strtolower($_GET['name']) == strtolower($row['name'])) {
										$response += array("provider" => array(
														"id" => $row['id'],
														"name" => $row['name'],
														"baseurl" => $row['baseurl'],
														"enabled" => $enabled
												)
											);
										break;
									}
								} else {
									$providerArray['proivders'][] = array(
														"id" => $row['id'],
														"name" => $row['name'],
														"baseurl" => $row['baseurl'],
														"enabled" => $enabled
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
				$response = array ('success' => false, 'message' => 'Invalid/unknown API key');
			}
		
} else {
	$response = array ('success' => false, 'message' => 'No API key given');
	// TODO: check session
}
echo json_encode($response);

?>
