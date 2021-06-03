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
						if ($_SERVER['REQUEST_METHOD'] === 'GET') { // get sshkey(s)
							$response = array('error' => false);
							if (isset($_GET['name'])) { // only one ssh key
								$query = "SELECT name, sshkey FROM sshkeys WHERE name=:name AND userid=:userid";
								$stmt = $db->prepare($query);
								$nameLowerCase = strtolower($_GET['name']);
								$stmt->bindParam(":name", $nameLowerCase);
								$stmt->bindParam(":userid", $userid);
								$stmt->execute();
								
								if ($stmt->rowCount() == 1) { // check if sshkey exists
									$row = $stmt->fetch(PDO::FETCH_ASSOC);
									$response += array("sshkey" => array(
														"name" => $row['name'],
														"sshkey" => $row['sshkey']
												)
											);
								} else {
									$response = array ('error' => true, 'message' => 'Unknown SSH key');
									http_response_code(400);
								}		
							} else { // all ssh keys
								$query = "SELECT name, sshkey FROM sshkeys WHERE userid=:userid";
								$stmt = $db->prepare($query);
								$stmt->bindParam(":userid", $userid);
								$stmt->execute();
								if ($stmt->rowCount() > 0) {
									$sshKeyArray = array('sshkeys' => array());
									while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
										$sshKeyArray['sshkeys'][] = array(
														"name" => $row['name'],
														"sshkey" => $row['sshkey']
												);
								
									}
								$response += $sshKeyArray;
								} else {
									$response = array ('error' => true, 'message' => 'No SSH keys found');
									http_response_code(400);
								}
							}		
							http_response_code(200);
						} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
							if (isset($_GET['name'])) {
								$input = file_get_contents('php://input');
								if ($input) {
									$query = "SELECT NULL FROM sshkeys WHERE name=:name AND userid=:userid";
									$stmt = $db->prepare($query);
									$nameLowerCase = strtolower($_GET['name']);
									$stmt->bindParam(":name", $nameLowerCase);
									$stmt->bindParam(":userid", $userid);
									$stmt->execute();
								
									if ($stmt->rowCount() == 0) { // check if already sshkey exists
										$nameLowerCase = strtolower($_GET['name']);
										$query = "INSERT INTO sshkeys (userid, name, sshkey) VALUES (:userid, :name, :sshkey)";
										$stmt= $db->prepare($query);
										$stmt->bindParam(":userid", $userid);
										$stmt->bindParam(":name", $nameLowerCase);
										$stmt->bindParam(":sshkey", $input);
												
										if ($stmt->execute()) {
											$response = array ('error' => false, 'message' => 'SSH key successfully saved');
											http_response_code(200);
										} else {
											$response = array ('error' => true, 'message' => 'Unknown error');
											http_response_code(400);
										}	
									} else {
										$response = array ('error' => true, 'message' => 'SSH Key already exists');
										http_response_code(400);
									}
								} else {
									$response = array ('error' => true, 'message' => 'Missing SSH key');
									http_response_code(400);
								}
							} else {
								$response = array ('error' => true, 'message' => 'Name parameter missing');
								http_response_code(400);
							}
						} else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
							if (isset($_GET['name'])) {
								$input = file_get_contents('php://input');
								if ($input) {
									$query = "SELECT NULL FROM sshkeys WHERE name=:name AND userid=:userid";
									$stmt = $db->prepare($query);
									$nameLowerCase = strtolower($_GET['name']);
									$stmt->bindParam(":name", $nameLowerCase);
									$stmt->bindParam(":userid", $userid);
									$stmt->execute();
								
									if ($stmt->rowCount() == 1) { // check if already sshkey exists
										$nameLowerCase = strtolower($_GET['name']);
										$query = "UPDATE sshkeys SET sshkey=:sshkey WHERE userid=:userid AND name=:name";
										$stmt= $db->prepare($query);
										$stmt->bindParam(":sshkey", $input);
										$stmt->bindParam(":userid", $userid);
										$stmt->bindParam(":name", $nameLowerCase);
												
										if ($stmt->execute()) {
											$response = array ('error' => false, 'message' => 'SSH key successfully updated');
											http_response_code(200);
										} else {
											$response = array ('error' => true, 'message' => 'Unknown error');
											http_response_code(400);
										}	
									} else {
										$response = array ('error' => true, 'message' => 'SSH Key not found');
										http_response_code(400);
									}
								} else {
									$response = array ('error' => true, 'message' => 'Missing SSH key');
									http_response_code(400);
								}
							} else {
								$response = array ('error' => true, 'message' => 'Name parameter missing');
								http_response_code(400);
							}	
						} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
								if (isset($_GET['name'])) {
									$nameLowerCase = strtolower($_GET['name']);
									$query = "DELETE FROM sshkeys WHERE userid=:userid AND name=:name";
									$stmt= $db->prepare($query);
									$stmt->bindParam(":userid", $userid);
									$stmt->bindParam(":name", $nameLowerCase);
									$stmt->execute();
									
									if ($stmt->rowCount() == 1) {
										$response = array ('error' => false, 'message' => 'SSH key successfully deleted');
										http_response_code(200);
									} else {
										$response = array ('error' => true, 'message' => 'No SSH key found');
										http_response_code(400);
									}

								} else {
									$response = array ('error' => true, 'message' => 'Name parameter missing');
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
	// TODO: check session
}
echo json_encode($response);

?>
