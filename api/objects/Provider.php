<?php

abstract class Provider {
  public $token;
  public function __construct($token) {
    $this->token = $token;
  }
  abstract public function locations($id = null, $allProviders = false);
  abstract public function plans($id = null, $allProviders = false);
  abstract public function os($id = null, $allProviders = false);
  abstract public function create($hostname, $location, $plan, $os, $sshkey, $script);
  abstract public function delete($id);
  abstract public function status();
  abstract public function info();
  abstract public function start();
  abstract public function stop($id);
  abstract public function createSSHKey($key);
  abstract public function deleteSSHKey($id);
}

?>
