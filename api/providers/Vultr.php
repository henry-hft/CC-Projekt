<?php
include_once "./objects/Provider.php";
include_once "./objects/Request.php";

class Vultr extends Provider
{
    public function locations($id = null, $allProviders = false)
    {
        $request = new Request();
        $apikey = $this->token;
        $header =
            "Accept-language: en\r\n" .
            "Authorization: Bearer $apikey\r\n" .
            "Content-type: application/json\r\n";
        $request->httpRequest(
            "GET",
            "https://api.vultr.com/v2/regions",
            $header,
            ""
        );
        $response = $request->getResponse();
        $decoded = json_decode($response);
        $response = [
            "error" => false
        ];
        if ($allProviders == true) {
            $locationArray = [
                "locations" => [
                    "Vultr" => []
                ]
            ];
        } else {
            $locationArray = [
                "locations" => []
            ];
        }
        foreach ($decoded->regions as $regions) {
            if (is_null($id)) {
                if ($allProviders == true) {
                    $locationArray["locations"]["Vultr"][] = [
                        "id" => $regions->id,
                        "country" => $regions->country,
                        "city" => $regions->city
                    ];
                } else {
                    $locationArray["locations"][] = [
                        "id" => $regions->id,
                        "country" => $regions->country,
                        "city" => $regions->city
                    ];
                }
            } else {
                if ($regions->id == $id) {
                    if ($allProviders == false) {
                        $response += [
                            "locations" => [
                                "id" => $regions->id,
                                "country" => $regions->country,
                                "city" => $regions->city
                            ]
                        ];
                    } else {
                        $response = [
                            "error" => true,
                            "message" => "Missing provider parameter"
                        ];
                    }
                    break;
                }
            }
        }
        if (is_null($id)) {
            $response += $locationArray;
        } elseif (count($response) < 2) {
            $response = [
                "error" => true,
                "message" => "Unknown location"
            ];
        }
        return $response;
    }
    public function plans($id = null, $allProviders = false)
    {
        $request = new Request();
        $apikey = $this->token;
        $header =
            "Accept-language: en\r\n" .
            "Authorization: Bearer $apikey\r\n" .
            "Content-type: application/json\r\n";
        $request->httpRequest(
            "GET",
            "https://api.vultr.com/v2/plans",
            $header,
            ""
        );
        $response = $request->getResponse();
        $decoded = json_decode($response);
        $response = [
            "error" => false
        ];
        if ($allProviders == true) {
            $planArray = [
                "plans" => [
                    "Vultr" => []
                ]
            ];
        } else {
            $planArray = [
                "plans" => []
            ];
        }
        foreach ($decoded->plans as $plans) {
            if (is_null($id)) {
                if ($allProviders == true) {
                    $planArray["plans"]["Vultr"][] = [
                        "id" => $plans->id,
                        "cores" => $plans->vcpu_count,
                        "memory" => $plans->ram,
                        "disk" => $plans->disk * 1000,
                        "bandwidth" => $plans->bandwidth * 1000
                    ];
                } else {
                    $planArray["plans"][] = [
                        "id" => $plans->id,
                        "cores" => $plans->vcpu_count,
                        "memory" => $plans->ram,
                        "disk" => $plans->disk * 1000,
                        "bandwidth" => $plans->bandwidth * 1000
                    ];
                }
            } else {
                if ($plans->id == $id) {
                    if ($allProviders == false) {
                        $response += [
                            "plans" => [
                                "id" => $plans->id,
                                "cores" => $plans->vcpu_count,
                                "memory" => $plans->ram,
                                "disk" => $plans->disk * 1000,
                                "bandwidth" => $plans->bandwidth * 1000
                            ]
                        ];
                    } else {
                        $response = [
                            "error" => true,
                            "message" => "Missing provider parameter"
                        ];
                    }
                    break;
                }
            }
        }
        if (is_null($id)) {
            $response += $planArray;
        } elseif (count($response) < 2) {
            $response = [
                "error" => true,
                "message" => "Unknown server plan"
            ];
        }
        return $response;
    }

    public function os($id = null, $family = null, $allProviders = false)
    {
        $request = new Request();
        $apikey = $this->token;
        $header =
            "Accept-language: en\r\n" .
            "Authorization: Bearer $apikey\r\n" .
            "Content-type: application/json\r\n";
        $request->httpRequest(
            "GET",
            "https://api.vultr.com/v2/os",
            $header,
            ""
        );
        $response = $request->getResponse();
        $decoded = json_decode($response);
        $response = [
            "error" => false
        ];
        if ($allProviders == true) {
            $osArray = [
                "os" => [
                    "Vultr" => []
                ]
            ];
        } else {
            $osArray = [
                "os" => []
            ];
        }
        foreach ($decoded->os as $os) {
            if (!is_null($family)) {
                if ($family != $os->family) {
                    continue;
                }
            }
            if (is_null($id)) {
                if ($allProviders == true) {
                    $osArray["os"]["Vultr"][] = [
                        "id" => $os->id,
                        "name" => $os->name,
                        "family" => $os->family
                    ];
                } else {
                    $osArray["os"][] = [
                        "id" => $os->id,
                        "name" => $os->name,
                        "family" => $os->family
                    ];
                }
            } else {
                if ($os->id == $id) {
                    if ($allProviders == false) {
                        $response += [
                            "os" => [
                                "id" => $os->id,
                                "name" => $os->name,
                                "family" => $os->family
                            ]
                        ];
                    } else {
                        $response = [
                            "error" => true,
                            "message" => "Missing provider parameter"
                        ];
                    }
                    break;
                }
            }
        }
        if (is_null($id)) {
            $response += $osArray;
        } elseif (count($response) < 2) {
            $response = [
                "error" => true,
                "message" =>
                    "Unknown operating system or operating system family"
            ];
        }
        return $response;
    }

    public function create(
        $hostname,
        $location,
        $plan,
        $os,
        $sshkey,
        $script = null
    ) {
        $request = new Request();
        $apikey = $this->token;
        $header =
            "Accept-language: en\r\n" .
            "Authorization: Bearer $apikey\r\n" .
            "Content-type: application/json\r\n";
        if (!is_null($script)) {
            $postData =
                '{"hostname":"' .
                $hostname .
                '","label":"' .
                $hostname .
                '","region":"' .
                $location .
                '","plan":"' .
                $plan .
                '","os_id":"' .
                $os .
                '","sshkey_id":["' .
                $sshkey .
                '"],"script_id":"' .
                $script .
                '","enable_ipv6": true}';
        } else {
            $postData =
                '{"hostname":"' .
                $hostname .
                '","label":"' .
                $hostname .
                '","region":"' .
                $location .
                '","plan":"' .
                $plan .
                '","os_id":"' .
                $os .
                '","sshkey_id":["' .
                $sshkey .
                '"],"enable_ipv6": true}';
        }
        $request->httpRequest(
            "POST",
            "https://api.vultr.com/v2/instances",
            $header,
            $postData
        );
        $response = $request->getResponse();
        echo $response;
        $decoded = json_decode($response);
        if (!isset($decoded->error)) {
            $id = $decoded->instance->id;
            $osId = $decoded->instance->os_id;
            $os = $decoded->instance->os;
            $location = $decoded->instance->region;
            $plan = $decoded->instance->plan;
            $hostname = $decoded->instance->label;
            $status = $decoded->instance->status;

            $server = [
                "id" => $id,
                "hostname" => $hostname,
                "status" => $status,
                "os" => $os,
                "osID" => $osId,
                "location" => $location,
                "plan" => $plan
            ];
            $response = [
                "error" => false,
                "message" => "Server successfully created",
                "servers" => $server
            ];
        } else {
            $response = [
                "error" => true,
                "message" => "Server could not be created"
            ];
        }
        return $response;
    }

    public function delete($id)
    {
        $request = new Request();
        $apikey = $this->token;
        $header =
            "Accept-language: en\r\n" .
            "Authorization: Bearer $apikey\r\n" .
            "Content-type: application/json\r\n";
        $request->httpRequest(
            "DELETE",
            "https://api.vultr.com/v2/instances/$id",
            $header,
            ""
        );
        $statusCode = $request->getStatusCode();
        if ($statusCode == 204) {
            $response = [
                "error" => false,
                "message" => "Server successfully deleted"
            ];
        } else {
            $response = [
                "error" => true,
                "message" => "Server could not be deleted"
            ];
        }
        return $response;
    }
    public function servers($id = null, $allProviders = false)
    {
        $request = new Request();
        $apikey = $this->token;
        $header =
            "Accept-language: en\r\n" .
            "Authorization: Bearer $apikey\r\n" .
            "Content-type: application/json\r\n";
        $request->httpRequest(
            "GET",
            "https://api.vultr.com/v2/instances",
            $header,
            ""
        );
        $response = $request->getResponse();
        $decoded = json_decode($response);
        $response = [
            "error" => false
        ];
        if ($allProviders == true) {
            $planArray = [
                "servers" => [
                    "Vultr" => []
                ]
            ];
        } else {
            $planArray = [
                "servers" => []
            ];
        }
        foreach ($decoded->instances as $instances) {
            if (is_null($id)) {
                if ($allProviders == true) {
                    $planArray["servers"]["Vultr"][] = [
                        "id" => $instances->id,
                        "hostname" => $instances->label,
                        "status" => $instances->status,
                        "created" => strtotime($instances->date_created),
                        "ipv4" => $instances->main_ip,
                        "ipv6" =>
                            $instances->v6_network .
                            "/" .
                            $instances->v6_network_size,
                        "location" => $instances->region,
                        "os" => $instances->os,
                        "osID" => $instances->os_id,
                        "plan" => $instances->plan,
                        "bandwidth" => $instances->allowed_bandwidth * 1000,
                        "cores" => $instances->vcpu_count,
                        "memory" => $instances->ram,
                        "disk" => $instances->disk * 1000
                    ];
                } else {
                    $planArray["servers"][] = [
                        "id" => $instances->id,
                        "hostname" => $instances->label,
                        "status" => $instances->status,
                        "created" => strtotime($instances->date_created),
                        "ipv4" => $instances->main_ip,
                        "ipv6" =>
                            $instances->v6_network .
                            "/" .
                            $instances->v6_network_size,
                        "location" => $instances->region,
                        "os" => $instances->os,
                        "osID" => $instances->os_id,
                        "plan" => $instances->plan,
                        "bandwidth" => $instances->allowed_bandwidth * 1000,
                        "cores" => $instances->vcpu_count,
                        "memory" => $instances->ram,
                        "disk" => $instances->disk * 1000
                    ];
                }
            } else {
                if ($instances->id == $id) {
                    if ($allProviders == false) {
                        $response += [
                            "servers" => [
                                "id" => $instances->id,
                                "hostname" => $instances->label,
                                "status" => $instances->status,
                                "created" => strtotime(
                                    $instances->date_created
                                ),
                                "ipv4" => $instances->main_ip,
                                "ipv6" =>
                                    $instances->v6_network .
                                    "/" .
                                    $instances->v6_network_size,
                                "location" => $instances->region,
                                "os" => $instances->os,
                                "osID" => $instances->os_id,
                                "plan" => $instances->plan,
                                "bandwidth" =>
                                    $instances->allowed_bandwidth * 1000,
                                "cores" => $instances->vcpu_count,
                                "memory" => $instances->ram,
                                "disk" => $instances->disk * 1000
                            ]
                        ];
                    } else {
                        $response = [
                            "error" => true,
                            "message" => "Missing provider parameter"
                        ];
                    }
                    break;
                }
            }
        }
        if (is_null($id)) {
            $response += $planArray;
        } elseif (count($response) < 2) {
            $response = [
                "error" => true,
                "message" => "Server not found"
            ];
        }
        return $response;
    }
    public function control($id, $action)
    {
        $request = new Request();
        $apikey = $this->token;
        $header =
            "Accept-language: en\r\n" .
            "Authorization: Bearer $apikey\r\n" .
            "Content-type: application/json\r\n";
        if ($action == "reboot") {
            $request->httpRequest(
                "POST",
                "https://api.vultr.com/v2/instances/$id/reboot",
                $header,
                ""
            );
            $statusCode = $request->getStatusCode();
            if ($statusCode == 204) {
                $response = [
                    "error" => false,
                    "message" => "The server has been restarted successfully."
                ];
            } else {
                $response = [
                    "error" => true,
                    "message" => "The server could not be restarted."
                ];
            }
        } elseif ($action == "start" or $action == "boot") {
            $request->httpRequest(
                "POST",
                "https://api.vultr.com/v2/instances/$id/start",
                $header,
                ""
            );
            $statusCode = $request->getStatusCode();
            if ($statusCode == 204) {
                $response = [
                    "error" => false,
                    "message" => "The server has been started successfully."
                ];
            } else {
                $response = [
                    "error" => true,
                    "message" => "The server could not be started."
                ];
            }
        } elseif ($action == "stop" or $action == "shutdown") {
            $request->httpRequest(
                "POST",
                "https://api.vultr.com/v2/instances/$id/halt",
                $header,
                ""
            );
            $statusCode = $request->getStatusCode();
            if ($statusCode == 204) {
                $response = [
                    "error" => false,
                    "message" => "The server has been stopped successfully."
                ];
            } else {
                $response = [
                    "error" => true,
                    "message" => "The server could not be stopped."
                ];
            }
        } else {
            $response = [
                "error" => true,
                "message" => "Unknown action"
            ];
        }
        return $response;
    }

    public function createSSHKey($key)
    {
        $name = uniqid();
        $request = new Request();
        $apikey = $this->token;
        $header =
            "Accept-language: en\r\n" .
            "Authorization: Bearer $apikey\r\n" .
            "Content-type: application/json\r\n";
        $postData = '{"name":"' . $name . '","ssh_key":"' . $key . '"}';
        $request->httpRequest(
            "POST",
            "https://api.vultr.com/v2/ssh-keys",
            $header,
            $postData
        );
        $response = $request->getResponse();
        $decoded = json_decode($response);
        if (isset($decoded->ssh_key)) {
            $id = $decoded->ssh_key->id;
            return $id;
        } else {
            return null;
        }
    }

    public function deleteSSHKey($id)
    {
        $request = new Request();
        $apikey = $this->token;
        $header =
            "Accept-language: en\r\n" .
            "Authorization: Bearer $apikey\r\n" .
            "Content-type: application/json\r\n";
        $request->httpRequest(
            "DELETE",
            "https://api.vultr.com/v2/ssh-keys/$id",
            $header,
            ""
        );
        $statusCode = $request->getStatusCode();
        if ($statusCode == 204) {
            $response = [
                "error" => false,
                "message" => "SSH Key successfully deleted"
            ];
        } else {
            $response = [
                "error" => true,
                "message" => "SSH Key could not be deleted"
            ];
        }
        return $response;
    }

    public function createScript($script)
    {
        $name = uniqid();
        $encodedScript = base64_encode($script);
        $request = new Request();
        $apikey = $this->token;
        $header =
            "Accept-language: en\r\n" .
            "Authorization: Bearer $apikey\r\n" .
            "Content-type: application/json\r\n";
        $postData =
            '{"name":"' . $name . '","script":"' . $encodedScript . '"}';
        $request->httpRequest(
            "POST",
            "https://api.vultr.com/v2/startup-scripts",
            $header,
            $postData
        );
        $response = $request->getResponse();
        $decoded = json_decode($response);
        if (isset($decoded->startup_script)) {
            $id = $decoded->startup_script->id;
            return $id;
        } else {
            return false;
        }
    }

    public function deleteScript($id)
    {
        $request = new Request();
        $apikey = $this->token;
        $header =
            "Accept-language: en\r\n" .
            "Authorization: Bearer $apikey\r\n" .
            "Content-type: application/json\r\n";
        $request->httpRequest(
            "DELETE",
            "https://api.vultr.com/v2/startup-scripts/$id",
            $header,
            ""
        );
        $statusCode = $request->getStatusCode();
        if ($statusCode == 204) {
            $response = [
                "error" => false,
                "message" => "Startup script successfully deleted"
            ];
        } else {
            $response = [
                "error" => true,
                "message" => "Startup script could not be deleted"
            ];
        }
        return $response;
    }
}
?>