<?php
include_once './config/core.php';
include_once './config/database.php';

include_once './libs/php-jwt-master/src/BeforeValidException.php';
include_once './libs/php-jwt-master/src/ExpiredException.php';
include_once './libs/php-jwt-master/src/SignatureInvalidException.php';
include_once './libs/php-jwt-master/src/JWT.php';
use \Firebase\JWT\JWT;

header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(isset($_GET['method'])){
	$database = new Database();
	$db = $database->getConnection();

	if($_SERVER['REQUEST_METHOD'] === 'POST'){
		if ($_GET['method'] == "register") {
			if(isset($_GET['username']) AND isset($_GET['email']) AND isset($_GET['password'])){
				if (filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
					$query = "SELECT NULL FROM users WHERE username=:username OR email=:email";
					$stmt = $db->prepare($query);
					$stmt->bindParam(":username", $_GET['username']);
					$stmt->bindParam(":email", $_GET['email']);
					$stmt->execute();
								
					if ($stmt->rowCount() == 0) {
						$query = "INSERT INTO users SET username=:username, email=:email, password=:password, registrationDate=:registrationDate, apikey=:apikey";
						$stmt = $db->prepare($query);
						$stmt->bindParam(':username', $_GET['username']);
						$stmt->bindParam(':email', $_GET['email']);
						$password_hash = password_hash($_GET['password'], PASSWORD_BCRYPT);
						$stmt->bindParam(':password', $password_hash);
						$registrationDate = time();
						$stmt->bindParam(':registrationDate', $registrationDate);
						$apikey = "123456789";
						$stmt->bindParam(':apikey', $apikey);

						if ($stmt->execute()) {
							$response = array("error" => false, "message" => "User was successfully registered");
							http_response_code(200);
						} else {
							$response = array("error" => true, "message" => "Unable to register the user");
							http_response_code(400);
						}
					} else {
						$response = array("error" => true, "message" => "Username and/or email address already in use");
						http_response_code(400);
					}
				} else {
					$response = array("error" => true, "message" => "Invalid email address");
					http_response_code(400);
				}
			} else {
				$response = array("error" => true, "message" => "Missing parameter");
				http_response_code(400);
			}
		} else if ($_GET['method'] == "login") {
			if((isset($_GET['username']) OR isset($_GET['email'])) AND isset($_GET['password'])){
				if (isset($_GET['username'])) { // log in with username
					$query = "SELECT * FROM users WHERE username=:username";
					$stmt = $db->prepare($query);
					$stmt->bindParam(":username", $_GET['username']);
				} else { // log in with email address
					$query = "SELECT * FROM users WHERE email=:email";
					$stmt = $db->prepare($query);
					$stmt->bindParam(":email", $_GET['email']);
				}
				
				$stmt->execute();
				if ($stmt->rowCount() == 1) {
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					if (password_verify($_GET['password'], $row['password'])) {
						
						$issuer_claim = "CLOUDCOMPUTING";
						$audience_claim = "USER";
						$issuedat_claim = time(); // issued at
						$notbefore_claim = $issuedat_claim + 10; //not before in seconds
						$expire_claim = $issuedat_claim + 60; // expire time in seconds
						
						$token = array(
							"iss" => $issuer_claim,
							"aud" => $audience_claim,
							"iat" => $issuedat_claim,
							"nbf" => $notbefore_claim,
							"exp" => $expire_claim,
							"data" => array(
								"id" => $row['id'],
								"username" => $row['username'],
								"email" => $row['email'],
								"enabled" => $row['enabled']
						));

						$jwt = JWT::encode($token, $secret_key);
						$response = array("error" => false, "message" => "Successful login", "jwt" => $jwt, "expireAt" => $expire_claim);
						http_response_code(200);
						
					} else {
						$response = array("error" => true, "message" => "Invalid password");
						http_response_code(400);
					}
				} else {
					$response = array("error" => true, "message" => "User not found");
					http_response_code(400);
				}
				
			} else {
				$response = array("error" => true, "message" => "Missing parameter");
				http_response_code(400);
			}
			
		} else {
			$response = array("error" => true, "message" => "Invalid method");
			http_response_code(400);
		}
	} else {
		$response = array("error" => true, "message" => "Invalid request method");
		http_response_code(400);
	}
} else {
		$response = array("error" => true, "message" => "Missing method");
		http_response_code(400);
	}
echo json_encode($response);
?>