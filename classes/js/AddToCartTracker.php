<?php

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');

class AddToCartTracker {

    private static $instance;

    private $roiHunterStorage;

    private function __construct() {
        $this->roiHunterStorage = ROIHunterStorage::getInstance();
    }

    public static function getInstance() {

        if (!isset(self::$instance)) {
            self::$instance = new AddToCartTracker();
        }
        return self::$instance;
    }

    /**
     * Generate on cart changed script.
     * Script immediately invoke function f passed in window.RhEasy.onCartChanged.add(f);
     * @param $rhEasyCartDto RhEasyCartDto.
     * @return string Javascript with prepared cart.
     */
    public function generateJsScriptOutput($rhEasyCartDto) {

        if (isset($rhEasyCartDto) && !empty($rhEasyCartDto->getCartItems())) {

            return '<script>
    if (window.RhEasy) {
        window.RhEasy.onCartChanged = { 
            "add" : function (f) {
                f(' . $rhEasyCartDto->toJson() . ');
                return true;
            }
        };
    }
</script>';
        } else {

            return '<script>
    if (window.RhEasy) {
        window.RhEasy.onCartChanged = { 
            "add" : function (f) { 
                return true;
            }
        };
    }
</script>';
        }
    }
}