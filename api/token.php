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
header("Access-Control-Allow-Methods: GET, DELETE, PUT");
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

                    if (isset($_GET["provider"])) {
                        // validate provider
                        $query =
                            "SELECT name FROM providers WHERE name LIKE :providerName";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(":providerName", $_GET["provider"]);
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

                    if ($_SERVER["REQUEST_METHOD"] === "GET") {
                        // get server(s)

                        $query = "SELECT * FROM tokens WHERE userid=:userid";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(":userid", $userid);
                        $stmt->execute();

                        $response = ["error" => false];
                        $tokenArray = ["tokens" => []];

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            if ($row["enabled"] == "1") {
                                $enabled = true;
                            } else {
                                $enabled = false;
                            }
                            if (isset($providerName)) {
                                if ($providerName == $row["provider"]) {
                                    $response += [
                                        "token" => [
                                            "provider" => $row["provider"],
                                            "token" => $row["token"],
                                            "enabled" => $enabled
                                        ]
                                    ];
                                    break;
                                }
                            } else {
                                $providerArray["tokens"][] = [
                                    "provider" => $row["provider"],
                                    "token" => $row["token"],
                                    "enabled" => $enabled
                                ];
                            }
                        }
                        if (!isset($providerName)) {
                            $response += $providerArray;
                        }
                    } elseif ($_SERVER["REQUEST_METHOD"] === "PUT") {
                        if (isset($_GET["provider"])) {
                            if (isset($_GET["token"])) {
                                if (isset($providerName)) {
                                    // check provider
                                    if (isset($_GET["enable"])) {
                                        if (
                                            $_GET["enable"] == "1" or
                                            $_GET["enable"] == "true"
                                        ) {
                                            // enable
                                            $enable = 1;
                                        } elseif (
                                            $_GET["enable"] == "0" or
                                            $_GET["enable"] == "false"
                                        ) {
                                            // disable
                                            $enable = 0;
                                        } else {
                                            $response = [
                                                "error" => true,
                                                "message" =>
                                                    "Invalid value for the enable parameter"
                                            ];
                                            http_response_code(400);
                                            exit(json_encode($response));
                                        }
                                    }

                                    $query =
                                        "SELECT NULL FROM tokens WHERE userid=:userid AND provider=:providerName";
                                    $stmt = $db->prepare($query);
                                    $stmt->bindParam(":userid", $userid);
                                    $stmt->bindParam(
                                        ":providerName",
                                        $providerName
                                    );
                                    $stmt->execute();

                                    if ($stmt->rowCount() == 0) {
                                        // add token
                                        if (isset($enable)) {
                                            $query = "INSERT INTO tokens (userid, provider, token, enabled) VALUES (:userid, :providerName, :token, $enable)";
                                        } else {
                                            $query =
                                                "INSERT INTO tokens (userid, provider, token) VALUES (:userid, :providerName, :token)";
                                        }
                                        $stmt = $db->prepare($query);
                                        $stmt->bindParam(":userid", $userid);
                                        $stmt->bindParam(
                                            ":providerName",
                                            $providerName
                                        );
                                        $stmt->bindParam(
                                            ":token",
                                            $_GET["token"]
                                        );

                                        if ($stmt->execute()) {
                                            $response = [
                                                "error" => false,
                                                "message" =>
                                                    "Provider token successfully saved"
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
                                        // update token
                                        if (isset($enable)) {
                                            $query = "UPDATE tokens SET token=:token, enabled=$enable WHERE userid=:userid AND provider=:providerName";
                                        } else {
                                            $query =
                                                "UPDATE tokens SET token=:token WHERE userid=:userid AND provider=:providerName";
                                        }
                                        $stmt = $db->prepare($query);
                                        $stmt->bindParam(
                                            ":token",
                                            $_GET["token"]
                                        );
                                        $stmt->bindParam(":userid", $userid);
                                        $stmt->bindParam(
                                            ":providerName",
                                            $providerName
                                        );

                                        if ($stmt->execute()) {
                                            $response = [
                                                "error" => false,
                                                "message" =>
                                                    "Provider token successfully updated"
                                            ];
                                            http_response_code(200);
                                        } else {
                                            $response = [
                                                "error" => true,
                                                "message" => "Unknown error"
                                            ];
                                            http_response_code(400);
                                        }
                                    }
                                } else {
                                    $response = [
                                        "error" => true,
                                        "message" => "Invalid/unknown provider"
                                    ];
                                    http_response_code(400);
                                }
                            } elseif (isset($_GET["enable"])) {
                                if (isset($providerName)) {
                                    // check provider
                                    if (
                                        $_GET["enable"] == "1" or
                                        $_GET["enable"] == "true"
                                    ) {
                                        // enable
                                        $enable = 1;
                                    } elseif (
                                        $_GET["enable"] == "0" or
                                        $_GET["enable"] == "false"
                                    ) {
                                        // disable
                                        $enable = 0;
                                    } else {
                                        $response = [
                                            "error" => true,
                                            "message" =>
                                                "Invalid value for the enable parameter"
                                        ];
                                        http_response_code(400);
                                        exit(json_encode($response));
                                    }

                                    $query =
                                        "SELECT * FROM tokens WHERE userid=:userid AND provider=:providerName";
                                    $stmt = $db->prepare($query);
                                    $stmt->bindParam(":userid", $userid);
                                    $stmt->bindParam(
                                        ":providerName",
                                        $providerName
                                    );
                                    $stmt->execute();

                                    if ($stmt->rowCount() == 1) {
                                        $query =
                                            "UPDATE tokens SET enabled=:enabled WHERE userid=:userid AND provider=:providerName";
                                        $stmt = $db->prepare($query);
                                        $stmt->bindParam(":enabled", $enable);
                                        $stmt->bindParam(":userid", $userid);
                                        $stmt->bindParam(
                                            ":providerName",
                                            $providerName
                                        );

                                        if ($stmt->execute()) {
                                            $response = [
                                                "error" => false,
                                                "message" =>
                                                    "Token status successfully updated"
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
                                            "message" => "No token found"
                                        ];
                                        http_response_code(400);
                                    }
                                } else {
                                    $response = [
                                        "error" => true,
                                        "message" => "Invalid/unknown provider"
                                    ];
                                    http_response_code(400);
                                }
                            } else {
                                $response = [
                                    "error" => true,
                                    "message" => "Missing parameter"
                                ];
                                http_response_code(400);
                            }
                        } else {
                            $response = [
                                "error" => true,
                                "message" => "Missing provider name"
                            ];
                            http_response_code(400);
                        }
                    } elseif ($_SERVER["REQUEST_METHOD"] === "DELETE") {
                        // delete token
                        if (isset($_GET["provider"])) {
                            if (isset($providerName)) {
                                // check provider
                                $query =
                                    "DELETE FROM tokens WHERE userid=:userid AND provider=:providerName";
                                $stmt = $db->prepare($query);
                                $stmt->bindParam(":userid", $userid);
                                $stmt->bindParam(
                                    ":providerName",
                                    $providerName
                                );
                                $stmt->execute();

                                if ($stmt->rowCount() == 1) {
                                    $response = [
                                        "error" => false,
                                        "message" =>
                                            "Token successfully deleted"
                                    ];
                                    http_response_code(200);
                                } else {
                                    $response = [
                                        "error" => true,
                                        "message" => "No token found"
                                    ];
                                    http_response_code(400);
                                }
                            } else {
                                $response = [
                                    "error" => true,
                                    "message" => "Invalid/unknown provider"
                                ];
                                http_response_code(400);
                            }
                        } else {
                            $response = [
                                "error" => true,
                                "message" => "Missing provider name"
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
                    // Invalid/unknown API Key
                    $response = [
                        "error" => true,
                        "message" => "Authentification failed"
                    ];
                    http_response_code(400);
                }
            } else {
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