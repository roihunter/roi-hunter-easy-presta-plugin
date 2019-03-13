<?php

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');

class ProductViewTracker {

    private static $instance;

    private $roiHunterStorage;

    private function __construct() {
        $this->roiHunterStorage = ROIHunterStorage::getInstance();
    }

    public static function getInstance() {

        if (!isset(self::$instance)) {
            self::$instance = new ProductViewTracker();
        }
        return self::$instance;
    }

    /**
     * Generate rh easy product view js
     * @param $rhEasyProductDto RhEasyProductDto
     * @return string javascript with product
     */
    public function generateJsScriptOutput($rhEasyProductDto) {

        if (isset($rhEasyProductDto)) { // generate onProductViewed with product

            return '<script>
    if (window.RhEasy) {
        window.RhEasy.onProductViewed = {
            "add" : function (f) {
                f({"product" : ' . $rhEasyProductDto->toJson() . '}); 
                return true;
            }
        };
    }
</script>';

        } else {                        // generate onProductViewed without product
            return '<script>
    if (window.RhEasy) {
        window.RhEasy.onProductViewed = {
            "add" : function (f) {
                return true;
            }
        };
    }
</script>';
        }
    }
}