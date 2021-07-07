<?php
abstract class Provider
{
    public $token;
    public function __construct($token)
    {
        $this->token = $token;
    }
    abstract public function locations($id = null, $allProviders = false);
    abstract public function plans($id = null, $allProviders = false);
    abstract public function os($id = null, $allProviders = false);
    abstract public function create(
        $hostname,
        $location,
        $plan,
        $os,
        $sshkey,
        $script = null
    );
    abstract public function delete($id);
    abstract public function servers($id = null, $allProviders = false);
    abstract public function control($id, $action);
    abstract public function createSSHKey($key);
    abstract public function deleteSSHKey($id);
    abstract public function createScript($script);
    abstract public function deleteScript($id);
}
?>