<?php
include_once "./config/core.php";
include_once "./config/database.php";
include_once "./objects/Request.php";

// JWT library
include_once "./libs/php-jwt-master/src/BeforeValidException.php";
include_once "./libs/php-jwt-master/src/ExpiredException.php";
include_once "./libs/php-jwt-master/src/SignatureInvalidException.php";
include_once "./libs/php-jwt-master/src/JWT.php";
use Firebase\JWT\JWT;

// required headers
header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header(
    "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"
);

// Autoload all providers
spl_autoload_register(function ($class_name) {
    include "providers/" . $class_name . ".php";
});

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
                // check if user exists
                $stmt->bindColumn("id", $userid);
                $stmt->bindColumn("enabled", $enabled);
                $stmt->fetch();
                if ($enabled == "1") {
                    // check if the account of owner of the API key is enabled
                    if ($_SERVER["REQUEST_METHOD"] === "GET") {
                        // get server(s)
                        if (!empty($_GET["provider"])) {
                            // only one provider
                            $query =
                                "SELECT name FROM providers WHERE name LIKE :providerName";
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(
                                ":providerName",
                                $_GET["provider"]
                            );
                            $stmt->execute();

                            if ($stmt->rowCount() == 1) {
                                // check if provider is valid
                                $providerName = $stmt->fetchColumn();
                            } else {
                                $response = [
                                    "error" => true,
                                    "message" => "Invalid/unknown provider"
                                ];
                                http_response_code(400);
                                exit(json_encode($response));
                            }
                        }

                        $query = "SELECT * FROM tokens WHERE userid=:userid";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(":userid", $userid);
                        $stmt->execute();
                        $providers = [];

                        if (!empty($_GET["id"])) {
                            $osId = $_GET["id"];
                        } else {
                            $osId = null;
                        }

                        if (!empty($_GET["family"])) {
                            $osFamily = $_GET["family"];
                        } else {
                            $osFamily = null;
                        }

                        //	$response = array("error" => false);
                        $response = [];
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $provider = $row["provider"];
                            $providerToken = $row["token"];
                            $enabled = $row["enabled"];
                            if (isset($providerName)) {
                                if ($provider == $providerName) {
                                    if ($enabled == "1") {
                                        $providerObj = new $provider(
                                            $providerToken
                                        );
                                        $locationResponse = $providerObj->os(
                                            $osId,
                                            $osFamily
                                        );
                                        $response = $locationResponse;
                                    } else {
                                        $response = [
                                            "error" => true,
                                            "message" => "Provider is disabled"
                                        ];
                                        http_response_code(400);
                                        exit(json_encode($response));
                                    }
                                    break;
                                }
                            } else {
                                if ($enabled == "1") {
                                    $providerObj = new $provider(
                                        $providerToken
                                    );
                                    $locationResponse = $providerObj->os(
                                        $osId,
                                        $osFamily,
                                        true
                                    );
                                    if ($locationResponse["error"] == false) {
                                        if (count($response) >= 1) {
                                            $response = array_merge(
                                                $response,
                                                $locationResponse["os"]
                                            );
                                            //$response["os"] = $locationResponse["os"];
                                        } else {
                                            //	$response["os"] = $locationResponse["os"];
                                            $response = array_merge(
                                                $response,
                                                $locationResponse["os"]
                                            );
                                        }
                                    } else {
                                        $response = [
                                            "error" => true,
                                            "message" =>
                                                "Cloud not get requested data"
                                        ];
                                        http_response_code(400);
                                        exit(json_encode($response));
                                    }
                                }
                            }
                        }

                        if (!isset($providerName)) {
                            $response = ["error" => false, "os" => $response];
                        }

                        if (count($response) > 1) {
                            if ($response["error"] == "false") {
                                http_response_code(200);
                            } else {
                                http_response_code(400);
                            }
                        } else {
                            $response = [
                                "error" => true,
                                "message" => "No enabled providers found"
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
                $response = [
                    "error" => true,
                    "message" => "Invalid/unknown user"
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