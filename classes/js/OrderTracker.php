<?php

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');

class OrderTracker {

    private static $instance;


    private function __construct() {
    }

    public static function getInstance() {

        if (!isset(self::$instance)) {
            self::$instance = new OrderTracker();
        }
        return self::$instance;
    }

    /**
     * Generate rh easy placed order js
     * @param $rhEasyOrderDto RhEasyOrderDto
     * @return string javascript with placed order
     */
    public function generateJsScriptOutput($rhEasyOrderDto) {

        if (isset($rhEasyOrderDto)) { // generate onOrderPlaced with product

            return '<script>
    if (window.RhEasy) {
        window.RhEasy.onOrderPlaced = {
            "add" : function (f) {
                f(' . $rhEasyOrderDto->toJson() . ');
                return true;
            }
        };
    }
</script>';

        } else {                        // generate onOrderPlaced without product
            return '<script>
    if (window.RhEasy) {
        window.RhEasy.onOrderPlaced = {
            "add" : function (f) {
                return true;
            }
        };
    }
</script>';
        }
    }
}