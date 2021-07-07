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
header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT");
header("Access-Control-Max-Age: 3600");
header(
    "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"
);

$data = json_decode(file_get_contents("php://input"));
if (!empty($_SERVER["HTTP_AUTHORIZATION"])) {
    $arr = explode(" ", $_SERVER["HTTP_AUTHORIZATION"]);
    $jwt = $arr[1];
    if (!empty($jwt)) {
        try {
            // instantiate database and server object
            $database = new Database();
            $db = $database->getConnection();

            $decoded = JWT::decode($jwt, $secret_key, ["HS256"]);

            $query = "SELECT id, enabled FROM users WHERE id=:userid";
            $stmt = $db->prepare($query);
            $userid = $decoded->data->id;
            $stmt->bindParam(":userid", $userid);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                // check if API key exists
                $stmt->bindColumn("id", $userid);
                $stmt->bindColumn("enabled", $enabled);
                $stmt->fetch();
                if ($enabled == "1") {
                    // check if the account of owner of the API key is enabled
                    if ($_SERVER["REQUEST_METHOD"] === "GET") {
                        // get sshkey(s)
                        $response = ["error" => false];
                        if (!empty($_GET["name"])) {
                            // only one ssh key
                            $query =
                                "SELECT name, sshkey FROM sshkeys WHERE name=:name AND userid=:userid";
                            $stmt = $db->prepare($query);
                            $nameLowerCase = strtolower($_GET["name"]);
                            $stmt->bindParam(":name", $nameLowerCase);
                            $stmt->bindParam(":userid", $userid);
                            $stmt->execute();

                            if ($stmt->rowCount() == 1) {
                                // check if sshkey exists
                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                $response += [
                                    "sshkey" => [
                                        "name" => $row["name"],
                                        "sshkey" => $row["sshkey"]
                                    ]
                                ];
                            } else {
                                $response = [
                                    "error" => true,
                                    "message" => "Unknown SSH key"
                                ];
                                http_response_code(400);
                            }
                        } else {
                            // all ssh keys
                            $query =
                                "SELECT name, sshkey FROM sshkeys WHERE userid=:userid";
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(":userid", $userid);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                $sshKeyArray = ["sshkeys" => []];
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $sshKeyArray["sshkeys"][] = [
                                        "name" => $row["name"],
                                        "sshkey" => $row["sshkey"]
                                    ];
                                }
                                $response += $sshKeyArray;
                            } else {
                                $response = [
                                    "error" => true,
                                    "message" => "No SSH keys found"
                                ];
                                http_response_code(400);
                            }
                        }
                        http_response_code(200);
                    } elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
                        if (!empty($_GET["name"])) {
                            $input = file_get_contents("php://input");
                            if ($input) {
                                $query =
                                    "SELECT NULL FROM sshkeys WHERE name=:name AND userid=:userid";
                                $stmt = $db->prepare($query);
                                $nameLowerCase = strtolower($_GET["name"]);
                                $stmt->bindParam(":name", $nameLowerCase);
                                $stmt->bindParam(":userid", $userid);
                                $stmt->execute();

                                if ($stmt->rowCount() == 0) {
                                    // check if already sshkey exists
                                    $nameLowerCase = strtolower($_GET["name"]);
                                    $query =
                                        "INSERT INTO sshkeys (userid, name, sshkey) VALUES (:userid, :name, :sshkey)";
                                    $stmt = $db->prepare($query);
                                    $stmt->bindParam(":userid", $userid);
                                    $stmt->bindParam(":name", $nameLowerCase);
                                    $stmt->bindParam(":sshkey", $input);

                                    if ($stmt->execute()) {
                                        $response = [
                                            "error" => false,
                                            "message" =>
                                                "SSH key successfully saved"
                                        ];
                                        http_response_code(200);
                                    } else {
                                        $response = [
                                            "error" => true,
                                            "message" => "Unknown error"
                                        ];
                                        http_response_code(400);
                                    }
                                } else {
                                    $response = [
                                        "error" => true,
                                        "message" => "SSH Key already exists"
                                    ];
                                    http_response_code(400);
                                }
                            } else {
                                $response = [
                                    "error" => true,
                                    "message" => "Missing SSH key"
                                ];
                                http_response_code(400);
                            }
                        } else {
                            $response = [
                                "error" => true,
                                "message" => "Name parameter missing"
                            ];
                            http_response_code(400);
                        }
                    } elseif ($_SERVER["REQUEST_METHOD"] === "PUT") {
                        if (!empty($_GET["name"])) {
                            $input = file_get_contents("php://input");
                            if ($input) {
                                $query =
                                    "SELECT NULL FROM sshkeys WHERE name=:name AND userid=:userid";
                                $stmt = $db->prepare($query);
                                $nameLowerCase = strtolower($_GET["name"]);
                                $stmt->bindParam(":name", $nameLowerCase);
                                $stmt->bindParam(":userid", $userid);
                                $stmt->execute();

                                if ($stmt->rowCount() == 1) {
                                    // check if already sshkey exists
                                    $nameLowerCase = strtolower($_GET["name"]);
                                    $query =
                                        "UPDATE sshkeys SET sshkey=:sshkey WHERE userid=:userid AND name=:name";
                                    $stmt = $db->prepare($query);
                                    $stmt->bindParam(":sshkey", $input);
                                    $stmt->bindParam(":userid", $userid);
                                    $stmt->bindParam(":name", $nameLowerCase);

                                    if ($stmt->execute()) {
                                        $response = [
                                            "error" => false,
                                            "message" =>
                                                "SSH key successfully updated"
                                        ];
                                        http_response_code(200);
                                    } else {
                                        $response = [
                                            "error" => true,
                                            "message" => "Unknown error"
                                        ];
                                        http_response_code(400);
                                    }
                                } else {
                                    $response = [
                                        "error" => true,
                                        "message" => "SSH Key not found"
                                    ];
                                    http_response_code(400);
                                }
                            } else {
                                $response = [
                                    "error" => true,
                                    "message" => "Missing SSH key"
                                ];
                                http_response_code(400);
                            }
                        } else {
                            $response = [
                                "error" => true,
                                "message" => "Name parameter missing"
                            ];
                            http_response_code(400);
                        }
                    } elseif ($_SERVER["REQUEST_METHOD"] === "DELETE") {
                        if (!empty($_GET["name"])) {
                            $nameLowerCase = strtolower($_GET["name"]);
                            $query =
                                "DELETE FROM sshkeys WHERE userid=:userid AND name=:name";
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(":userid", $userid);
                            $stmt->bindParam(":name", $nameLowerCase);
                            $stmt->execute();

                            if ($stmt->rowCount() == 1) {
                                $response = [
                                    "error" => false,
                                    "message" => "SSH key successfully deleted"
                                ];
                                http_response_code(200);
                            } else {
                                $response = [
                                    "error" => true,
                                    "message" => "No SSH key found"
                                ];
                                http_response_code(400);
                            }
                        } else {
                            $response = [
                                "error" => true,
                                "message" => "Name parameter missing"
                            ];
                            http_response_code(400);
                        }
                    } else {
                        $response = [
                            "error" => true,
                            "message" => "Invalid request method"
                        ];
                        http_response_code(400);
                    }
                } else {
                    $response = [
                        "error" => true,
                        "message" => "Authentification failed"
                    ];
                    http_response_code(400);
                }
            } else {
                // Invalid/unknown API Key
                $response = [
                    "error" => true,
                    "message" => "Invalid/unknown API key"
                ];
                http_response_code(400);
            }
        } catch (Exception $e) {
            $response = ["error" => true, "message" => "Authentication failed"];
            http_response_code(401);
        }
    } else {
        $response = ["error" => true, "message" => "Missing access token"];
    }
} else {
    $response = ["error" => true, "message" => "Missing access token"];
}
echo json_encode($response);

?>