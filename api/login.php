<?php
include_once "./config/core.php";
include_once "./config/database.php";
// JWT library
include_once "./libs/php-jwt-master/src/BeforeValidException.php";
include_once "./libs/php-jwt-master/src/ExpiredException.php";
include_once "./libs/php-jwt-master/src/SignatureInvalidException.php";
include_once "./libs/php-jwt-master/src/JWT.php";
use Firebase\JWT\JWT;
// required headers
header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header(
    "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"
);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        (!empty($_POST["username"]) or !empty($_POST["email"])) and
        !empty($_POST["password"])
    ) {
        $database = new Database();
        $db = $database->getConnection();
        if (!empty($_POST["username"])) {
            // log in with username
            $query = "SELECT * FROM users WHERE username=:username";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":username", $_POST["username"]);
        } else {
            // log in with email address
            $query = "SELECT * FROM users WHERE email=:email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":email", $_POST["email"]);
        }
        $stmt->execute();
        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($_POST["password"], $row["password"])) {
                $issuer_claim = "CLOUDCOMPUTING";
                $audience_claim = "USER";
                $issuedat_claim = time(); // issued at
                $notbefore_claim = $issuedat_claim + 0; //not before in seconds
                $expire_claim = $issuedat_claim + 3600; // expire time in seconds
                $token = [
                    "iss" => $issuer_claim,
                    "aud" => $audience_claim,
                    "iat" => $issuedat_claim,
                    "nbf" => $notbefore_claim,
                    "exp" => $expire_claim,
                    "data" => ["id" => $row["id"]]
                ];
                $jwt = JWT::encode($token, $secret_key);
                $response = [
                    "error" => false,
                    "message" => "Successful login",
                    "jwt" => $jwt,
                    "expireAt" => $expire_claim
                ];
                http_response_code(200);
            } else {
                $response = ["error" => true, "message" => "Invalid password"];
                http_response_code(400);
            }
        } else {
            $response = ["error" => true, "message" => "User not found"];
            http_response_code(400);
        }
    } else {
        $response = ["error" => true, "message" => "Missing parameter"];
        http_response_code(400);
    }
} else {
    $response = ["error" => true, "message" => "Invalid request method"];
    http_response_code(400);
}
echo json_encode($response);
?>