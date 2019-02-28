<?php

class ROIHunterAuthenticator {

    private static $instance;

    private $serverToken;
    private $client_token;

    private function __construct() {

        $rhInstance = Module::getInstanceByName('roihunter');
        if (isset($rhInstance)) {
            $this->serverToken = $rhInstance->getClientToken();
        }
        $this->client_token = $_SERVER["HTTP_X_AUTHORIZATION"];
    }

    public static function getInstance() {

        if (!isset(self::$instance)) {
            self::$instance = new ROIHunterAuthenticator();
        }
        return self::$instance;
    }

    public function authenticate() {

        if (empty($this->serverToken)) {
            header('HTTP/1.1 500 - Internal Server Error - Server authentications is not set. Maybe plugin is not active.', true, 500);
            die();
        }
        if (empty($this->client_token)) {
            header('HTTP/1.1 401 Unauthorized - Token is not set.', true, 401);
            die();
        }
        if ($this->client_token != $this->serverToken) { // token je jen jeden pro multishop
            header('HTTP/1.1 401 Unauthorized - Token is not valid.', true, 401);
            die();
        }
    }
}