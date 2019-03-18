<?php

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/dtos/RhEasyDto.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/js/PageTypeTracker.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/js/ProductViewTracker.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/js/CategoryViewTracker.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/js/AddToCartTracker.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/js/OrderTracker.php');

class RhTrackingScriptLoader {

    private static $instance;

    private $roiHunterStorage;
    private $pageTypeTracker;
    private $productViewTracker;
    private $categoryViewTracker;
    private $addToCartTracker;
    private $orderTracker;

    private $rhEasyPageDto;
    private $rhEasyProductDto;
    private $rhEasyCategoryDto;
    private $rhEasyOrderDto;
    private $rhEasyCartDto;

    private function __construct() {
        $this->roiHunterStorage = ROIHunterStorage::getInstance();
        $this->pageTypeTracker = PageTypeTracker::getInstance();
        $this->productViewTracker = ProductViewTracker::getInstance();
        $this->categoryViewTracker = CategoryViewTracker::getInstance();
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

        $resultJs = $this->generateRhEasyObjectJs();
        $resultJs .= $this->pageTypeTracker->generateJsScriptOutput($this->rhEasyPageDto);
        $resultJs .= $this->productViewTracker->generateJsScriptOutput($this->rhEasyProductDto);
        $resultJs .= $this->categoryViewTracker->generateJsScriptOutput($this->rhEasyCategoryDto);
        $resultJs .= $this->addToCartTracker->generateJsScriptOutput($this->rhEasyCartDto);
        $resultJs .= $this->orderTracker->generateJsScriptOutput($this->rhEasyOrderDto);
        $resultJs .= $this->generateRhEasyEventsTrackingJs();
        return $resultJs;
    }

    private function generateRhEasyObjectJs() {

        $rhEasy = new RhEasyDto(
            "PRESTA_SHOP",
            $this->roiHunterStorage->getGoogleConversionId(),
            $this->roiHunterStorage->getGoogleConversionLabel(),
            $this->roiHunterStorage->getFbPixelId());

        return '<script>
    if (!window.RhEasy) { 
        window.RhEasy = ' . $rhEasy->toJson() . ';
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

    public function setRhEasyPageDto($rhEasyPageDto) {
        $this->rhEasyPageDto = $rhEasyPageDto;
    }

    public function setRhEasyProductDto($rhEasyProductDto) {
        $this->rhEasyProductDto = $rhEasyProductDto;
    }

    public function setRhEasyCategoryDto($rhEasyCategoryDto) {
        $this->rhEasyCategoryDto = $rhEasyCategoryDto;
    }

    public function setRhEasyCartDto($rhEasyCartDto) {
        $this->rhEasyCartDto = $rhEasyCartDto;
    }

    public function setRhEasyOrderDto($rhEasyOrderDto) {
        $this->rhEasyOrderDto = $rhEasyOrderDto;
    }
}