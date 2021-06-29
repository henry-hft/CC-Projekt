<?php
abstract class Provider {
  public $apiKey;
  public function __construct($apiKey) {
    $this->apiKey = $apiKey;
  }
  abstract public function locations($id = null) : array;
  abstract public function create() : array;
  abstract public function delete() : array;
  abstract public function status() : array;
  abstract public function info() : array;
  abstract public function start() : array;
  abstract public function stop() : array;
}
?>
