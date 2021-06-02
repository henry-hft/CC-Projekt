<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'config/core.php';
include_once 'config/database.php';

session_start();

if (isset($_GET['apikey'])) {
	if (isset($_GET['provider'])) {
		if (in_array($_GET['provider'], $providers)) { // check if provider is valid
			// instantiate database and server object
			$database = new Database();
			$db = $database->getConnection();
	
			$query = "SELECT userid, enabled FROM users WHERE apikey=?";
			$stmt = $this->conn->prepare($query);
	
			$stmt->bind_param("s", $_GET['apikey']);
			$stmt->execute();
			$stmt->store_result();
	
			if ($stmt->num_rows == 1) { // check if API key exists
				$stmt->bind_result($userid, $enabled);
				$stmt->fetch();
				if ($enabled == "1") { 
					if ($enabled == "1") { // check if the account of owner of the API key is enabled
						if ($_SERVER['REQUEST_METHOD'] === 'GET') { // get server(s)
							if (isset($_GET['id']) { // get only one server
					
							} else { // get all servers
					
							}
						} else if($_SERVER['REQUEST_METHOD'] === 'POST') { // create server
						
						} else if($_SERVER['REQUEST_METHOD'] === 'DELETE') { // delete server
						
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
			$response = array ('success' => false, 'message' => 'Invalid/unknown provider');
		}		
	} else {
		$response = array ('success' => false, 'message' => 'Missing provider parameter');
	}		
} else {
	// TODO: check session
}
echo json_encode($response);

?>
