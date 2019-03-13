<?php

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/js/ProductViewTracker.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/js/AddToCartTracker.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/js/OrderTracker.php');

class RhTrackingScriptLoader {

    private static $instance;

    private $roiHunterStorage;
    private $productViewTracker;
    private $addToCartTracker;
    private $orderTracker;

    private function __construct() {
        $this->roiHunterStorage = ROIHunterStorage::getInstance();
        $this->productViewTracker = ProductViewTracker::getInstance();
        $this->addToCartTracker = AddToCartTracker::getInstance();
        $this->orderTracker = OrderTracker::getInstance();
    }

    public static function getInstance() {

        if (!isset(self::$instance)) {
            self::$instance = new RhTrackingScriptLoader();
        }
        return self::$instance;
    }

    public function generateJsScriptOutput() {

        $resultJs = $this->generateRhEasyTypeJs();
        $resultJs .= $this->productViewTracker->generateJsScriptOutput();
        $resultJs .= $this->addToCartTracker->generateJsScriptOutput();
        $resultJs .= $this->orderTracker->generateJsScriptOutput();
        $resultJs .= $this->generateRhEasyEventsTrackingJs();
        return $resultJs;
    }

    private function generateRhEasyTypeJs() {

        return '<script>
    if (!window.RhEasy) { 
        window.RhEasy = {
            "platform" : "PRESTA_SHOP",
        };
    }
</script>';
    }

    private function generateRhEasyEventsTrackingJs() {

        if ($this->roiHunterStorage->isActiveBeProfileProduction()) {
            return '<script src="https://storage.googleapis.com/goostav-static-files/rh-easy-events-tracking.umd.js" async></script>';
        } else {
            return '<script src="https://storage.googleapis.com/goostav-static-files/rh-easy-events-tracking-staging.umd.js" async></script>';
        }
    }
}