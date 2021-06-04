<?php
include_once './config/core.php';
include_once './config/database.php';

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
	
			if ($stmt->rowCount() == 1) { // check if API key exists
				$stmt->bindColumn('id', $userid);
				$stmt->bindColumn('enabled', $enabled);
				$stmt->fetch(); 
					if ($enabled == "1") { // check if the account of owner of the API key is enabled
						if ($_SERVER['REQUEST_METHOD'] === 'GET') { // get server(s)
							if (isset($_GET['name'])) { // only one provider
								$query = "SELECT name FROM providers WHERE name LIKE :providerName";
								$stmt = $db->prepare($query);
								$stmt->bindParam(":providerName", $_GET['name']);
								$stmt->execute();
								
								if ($stmt->rowCount() == 1) { // check if provider is valid
									$providerName = $stmt->fetchColumn();
								} else {
									$response = array ('error' => true, 'message' => 'Invalid/unknown provider');
									http_response_code(400);
									exit(json_encode($response));
								}		
							} else { // all providers
							
							}		
							
							$query = "SELECT * FROM providers";
							$stmt = $db->prepare($query);
							$stmt->execute();
							
							$response = array('error' => false);
							$providerArray = array('proivders' => array());
							
							while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
								if ($row['enabled'] == "1") {
									$enabled = true;
								} else {
									$enabled = false;
								}
								if(isset($providerName)){
									if ($providerName == $row['name']) {
										$response += array("provider" => array(
														"name" => $row['name'],
														"baseurl" => $row['baseurl'],
														"enabled" => $enabled
												)
											);
										break;
									}
								} else {
									$providerArray['proivders'][] = array(
														"name" => $row['name'],
														"baseurl" => $row['baseurl'],
														"enabled" => $enabled
												);
								}
							}
							if (!isset($providerName)) {
								$response += $providerArray;
							}
							http_response_code(200);
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
