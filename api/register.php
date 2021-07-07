<?php
include_once "./config/core.php";
include_once "./config/database.php";
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
        !empty($_POST["username"]) and
        !empty($_POST["email"]) and
        !empty($_POST["password"])
    ) {
        if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $database = new Database();
            $db = $database->getConnection();
            $query =
                "SELECT NULL FROM users WHERE username=:username OR email=:email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":username", $_POST["username"]);
            $stmt->bindParam(":email", $_POST["email"]);
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                $query =
                    "INSERT INTO users SET username=:username, email=:email, password=:password, registrationDate=:registrationDate";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":username", $_POST["username"]);
                $stmt->bindParam(":email", $_POST["email"]);
                $password_hash = password_hash(
                    $_POST["password"],
                    PASSWORD_BCRYPT
                );
                $stmt->bindParam(":password", $password_hash);
                $registrationDate = time();
                $stmt->bindParam(":registrationDate", $registrationDate);
                if ($stmt->execute()) {
                    $response = [
                        "error" => false,
                        "message" => "User was successfully registered"
                    ];
                    http_response_code(200);
                } else {
                    $response = [
                        "error" => true,
                        "message" => "Unable to register the user"
                    ];
                    http_response_code(400);
                }
            } else {
                $response = [
                    "error" => true,
                    "message" => "Username and/or email address already in use"
                ];
                http_response_code(400);
            }
        } else {
            $response = ["error" => true, "message" => "Invalid email address"];
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