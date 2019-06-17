<?php
/**
 * Authentication to ROI Hunter Backend
 *
 * LICENSE: The buyer can free use/edit/modify this software in anyway
 * The buyer is NOT allowed to redistribute this module in anyway or resell it
 * or redistribute it to third party
 *
 * @author    ROI Hunter Easy
 * @copyright 2019 ROI Hunter
 * @license   EULA
 * @version   1.0
 * @link      https://easy.roihunter.com/
 */

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');

class ROIHunterAuthenticator
{
    private static $instance;

    private $serverToken;
    private $client_token;

    private function __construct()
    {
        $this->serverToken = ROIHunterStorage::getInstance()->getClientToken();
        $this->client_token = $_SERVER["HTTP_X_AUTHORIZATION"];
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new ROIHunterAuthenticator();
        }
        return self::$instance;
    }

    public function authenticate()
    {
        if (empty($this->serverToken)) {
            header('HTTP/1.1 500 - Internal Server Error - Server authentications is not set.
             Maybe plugin is not active.', true, 500);
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
