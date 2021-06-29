<?php

abstract class Provider {
  public $token;
  public function __construct($token) {
    $this->token = $token;
  }
  abstract public function locations($id = null);
  abstract public function create();
  abstract public function delete();
  abstract public function status();
  abstract public function info();
  abstract public function start();
  abstract public function stop();
}

?>
