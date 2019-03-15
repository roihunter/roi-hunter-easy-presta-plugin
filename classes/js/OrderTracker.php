<?php

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');

class OrderTracker {

    private static $instance;

    private $roiHunterStorage;

    private function __construct() {
        $this->roiHunterStorage = ROIHunterStorage::getInstance();
    }

    public static function getInstance() {

        if (!isset(self::$instance)) {
            self::$instance = new OrderTracker();
        }
        return self::$instance;
    }

    public function generateJsScriptOutput() {

        return '';
    }
}