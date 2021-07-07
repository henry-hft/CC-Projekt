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
                        // get startup script(s)
                        $response = ["error" => false];
                        if (!empty($_GET["name"])) {
                            // only one startup script
                            $query =
                                "SELECT name, script FROM scripts WHERE name=:name AND userid=:userid";
                            $stmt = $db->prepare($query);
                            $nameLowerCase = strtolower($_GET["name"]);
                            $stmt->bindParam(":name", $nameLowerCase);
                            $stmt->bindParam(":userid", $userid);
                            $stmt->execute();

                            if ($stmt->rowCount() == 1) {
                                // check if startup script exists
                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                $response += [
                                    "script" => [
                                        "name" => $row["name"],
                                        "script" => $row["script"]
                                    ]
                                ];
                            } else {
                                $response = [
                                    "error" => true,
                                    "message" => "Unknown startup script"
                                ];
                                http_response_code(400);
                            }
                        } else {
                            // all startup scripts
                            $query =
                                "SELECT name, script FROM scripts WHERE userid=:userid";
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(":userid", $userid);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                $scriptArray = ["scripts" => []];
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $scriptArray["scripts"][] = [
                                        "name" => $row["name"],
                                        "script" => $row["script"]
                                    ];
                                }
                                $response += $scriptArray;
                            } else {
                                $response = [
                                    "error" => true,
                                    "message" => "No startup script found"
                                ];
                                http_response_code(400);
                            }
                        }
                        http_response_code(200);
                    } elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
                        if (!empty($_GET["name"])) {
                            $input = file_get_contents("php://input");
                            $input = nl2br($input);
                            $input = str_replace("<br />", '\n', $input);
                            $input = str_replace('"', '\"', $input);
                            //$input = str_replace('\n', "\n", $input);
                            if ($input) {
                                $query =
                                    "SELECT NULL FROM scripts WHERE name=:name AND userid=:userid";
                                $stmt = $db->prepare($query);
                                $nameLowerCase = strtolower($_GET["name"]);
                                $stmt->bindParam(":name", $nameLowerCase);
                                $stmt->bindParam(":userid", $userid);
                                $stmt->execute();

                                if ($stmt->rowCount() == 0) {
                                    // check if startup script already exists
                                    $nameLowerCase = strtolower($_GET["name"]);
                                    $query =
                                        "INSERT INTO scripts (userid, name, script) VALUES (:userid, :name, :script)";
                                    $stmt = $db->prepare($query);
                                    $stmt->bindParam(":userid", $userid);
                                    $stmt->bindParam(":name", $nameLowerCase);
                                    $stmt->bindParam(":script", $input);

                                    if ($stmt->execute()) {
                                        $response = [
                                            "error" => false,
                                            "message" =>
                                                "Startup script successfully saved"
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
                                        "message" =>
                                            "Startup script already exists"
                                    ];
                                    http_response_code(400);
                                }
                            } else {
                                $response = [
                                    "error" => true,
                                    "message" => "Missing startup script"
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
                            $input = nl2br($input);
                            $input = str_replace("<br />", '\n', $input);
                            $input = str_replace('"', '\"', $input);
                            //	$input = str_replace('\n', "\n", $input);
                            if ($input) {
                                $query =
                                    "SELECT NULL FROM scripts WHERE name=:name AND userid=:userid";
                                $stmt = $db->prepare($query);
                                $nameLowerCase = strtolower($_GET["name"]);
                                $stmt->bindParam(":name", $nameLowerCase);
                                $stmt->bindParam(":userid", $userid);
                                $stmt->execute();

                                if ($stmt->rowCount() == 1) {
                                    // check if startup script already exists
                                    $nameLowerCase = strtolower($_GET["name"]);
                                    $query =
                                        "UPDATE scripts SET script=:script WHERE userid=:userid AND name=:name";
                                    $stmt = $db->prepare($query);
                                    $stmt->bindParam(":script", $input);
                                    $stmt->bindParam(":userid", $userid);
                                    $stmt->bindParam(":name", $nameLowerCase);

                                    if ($stmt->execute()) {
                                        $response = [
                                            "error" => false,
                                            "message" =>
                                                "Startup script successfully updated"
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
                                        "message" => "Startup script not found"
                                    ];
                                    http_response_code(400);
                                }
                            } else {
                                $response = [
                                    "error" => true,
                                    "message" => "Missing startup script"
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
                                "DELETE FROM scripts WHERE userid=:userid AND name=:name";
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(":userid", $userid);
                            $stmt->bindParam(":name", $nameLowerCase);
                            $stmt->execute();

                            if ($stmt->rowCount() == 1) {
                                $response = [
                                    "error" => false,
                                    "message" =>
                                        "Startup script successfully deleted"
                                ];
                                http_response_code(200);
                            } else {
                                $response = [
                                    "error" => true,
                                    "message" => "No startup script found"
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
                        "message" => "Account disabled"
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
        http_response_code(400);
    }
} else {
    $response = ["error" => true, "message" => "Missing access token"];
    http_response_code(400);
}
echo json_encode($response);

?>