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
						if ($_SERVER['REQUEST_METHOD'] === 'GET') { // get startup script(s)
							$response = array('error' => false);
							if (isset($_GET['name'])) { // only one startup script
								$query = "SELECT name, script FROM scripts WHERE name=:name AND userid=:userid";
								$stmt = $db->prepare($query);
								$nameLowerCase = strtolower($_GET['name']);
								$stmt->bindParam(":name", $nameLowerCase);
								$stmt->bindParam(":userid", $userid);
								$stmt->execute();
								
								if ($stmt->rowCount() == 1) { // check if startup script exists
									$row = $stmt->fetch(PDO::FETCH_ASSOC);
									$response += array("script" => array(
														"name" => $row['name'],
														"script" => $row['script']
												)
											);
								} else {
									$response = array ('error' => true, 'message' => 'Unknown startup script');
									http_response_code(400);
								}		
							} else { // all startup scripts
								$query = "SELECT name, script FROM scripts WHERE userid=:userid";
								$stmt = $db->prepare($query);
								$stmt->bindParam(":userid", $userid);
								$stmt->execute();
								if ($stmt->rowCount() > 0) {
									$scriptArray = array('scripts' => array());
									while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
										$scriptArray['scripts'][] = array(
														"name" => $row['name'],
														"script" => $row['script']
												);
								
									}
								$response += $scriptArray;
								} else {
									$response = array ('error' => true, 'message' => 'No startup script found');
									http_response_code(400);
								}
							}		
							http_response_code(200);
						} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
							if (isset($_GET['name'])) {
								$input = file_get_contents('php://input');
								if ($input) {
									$query = "SELECT NULL FROM scripts WHERE name=:name AND userid=:userid";
									$stmt = $db->prepare($query);
									$nameLowerCase = strtolower($_GET['name']);
									$stmt->bindParam(":name", $nameLowerCase);
									$stmt->bindParam(":userid", $userid);
									$stmt->execute();
								
									if ($stmt->rowCount() == 0) { // check if startup script already exists
										$nameLowerCase = strtolower($_GET['name']);
										$query = "INSERT INTO scripts (userid, name, script) VALUES (:userid, :name, :script)";
										$stmt= $db->prepare($query);
										$stmt->bindParam(":userid", $userid);
										$stmt->bindParam(":name", $nameLowerCase);
										$stmt->bindParam(":script", $input);
												
										if ($stmt->execute()) {
											$response = array ('error' => false, 'message' => 'Startup script successfully saved');
											http_response_code(200);
										} else {
											$response = array ('error' => true, 'message' => 'Unknown error');
											http_response_code(400);
										}	
									} else {
										$response = array ('error' => true, 'message' => 'Startup script already exists');
										http_response_code(400);
									}
								} else {
									$response = array ('error' => true, 'message' => 'Missing startup script');
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
									$query = "SELECT NULL FROM scripts WHERE name=:name AND userid=:userid";
									$stmt = $db->prepare($query);
									$nameLowerCase = strtolower($_GET['name']);
									$stmt->bindParam(":name", $nameLowerCase);
									$stmt->bindParam(":userid", $userid);
									$stmt->execute();
								
									if ($stmt->rowCount() == 1) { // check if startup script already exists
										$nameLowerCase = strtolower($_GET['name']);
										$query = "UPDATE scripts SET script=:script WHERE userid=:userid AND name=:name";
										$stmt= $db->prepare($query);
										$stmt->bindParam(":script", $input);
										$stmt->bindParam(":userid", $userid);
										$stmt->bindParam(":name", $nameLowerCase);
												
										if ($stmt->execute()) {
											$response = array ('error' => false, 'message' => 'Startup script successfully updated');
											http_response_code(200);
										} else {
											$response = array ('error' => true, 'message' => 'Unknown error');
											http_response_code(400);
										}	
									} else {
										$response = array ('error' => true, 'message' => 'Startup script not found');
										http_response_code(400);
									}
								} else {
									$response = array ('error' => true, 'message' => 'Missing startup script');
									http_response_code(400);
								}
							} else {
								$response = array ('error' => true, 'message' => 'Name parameter missing');
								http_response_code(400);
							}	
						} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
								if (isset($_GET['name'])) {
									$nameLowerCase = strtolower($_GET['name']);
									$query = "DELETE FROM scripts WHERE userid=:userid AND name=:name";
									$stmt= $db->prepare($query);
									$stmt->bindParam(":userid", $userid);
									$stmt->bindParam(":name", $nameLowerCase);
									$stmt->execute();
									
									if ($stmt->rowCount() == 1) {
										$response = array ('error' => false, 'message' => 'Startup script successfully deleted');
										http_response_code(200);
									} else {
										$response = array ('error' => true, 'message' => 'No startup script found');
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
