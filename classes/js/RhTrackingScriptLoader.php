<?php

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/js/PageTypeTracker.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/js/ProductViewTracker.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/js/AddToCartTracker.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/js/OrderTracker.php');

class RhTrackingScriptLoader {

    private static $instance;

    private $roiHunterStorage;
    private $pageTypeTracker;
    private $productViewTracker;
    private $addToCartTracker;
    private $orderTracker;

    private $pageType;
    private $rhEasyProductDto;

    private function __construct() {
        $this->roiHunterStorage = ROIHunterStorage::getInstance();
        $this->pageTypeTracker = PageTypeTracker::getInstance();
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
        $resultJs .= $this->pageTypeTracker->generateJsScriptOutput($this->pageType);
        $resultJs .= $this->productViewTracker->generateJsScriptOutput($this->rhEasyProductDto);
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

    /* Setters */

    public function setPageType($pageType) {
        $this->pageType = $pageType;
    }

    public function setRhEasyProductDto($rhEasyProductDto) {
        $this->rhEasyProductDto = $rhEasyProductDto;
    }
}