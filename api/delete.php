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
						if ($_SERVER['REQUEST_METHOD'] === 'GET') { // get server(s)
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
										if(!empty($_GET['id'])){
												$providerObj = new $providerName($token);
												$deleteResponse = $providerObj->delete($_GET['id']);
												$response = $deleteResponse;
												if($deleteResponse['error'] == "false"){
													http_response_code(200);
												} else {
													http_response_code(400);
												}
											
											} else {
												$response = array ('error' => true, 'message' => 'Server ID missing.');
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
