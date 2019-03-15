<?php

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');

class CategoryViewTracker {

    private static $instance;

    private $roiHunterStorage;

    private function __construct() {
        $this->roiHunterStorage = ROIHunterStorage::getInstance();
    }

    public static function getInstance() {

        if (!isset(self::$instance)) {
            self::$instance = new CategoryViewTracker();
        }
        return self::$instance;
    }

    /**
     * Generate rh easy category view js
     * @param $rhEasyCategoryDto RhEasyCategoryDto
     * @return string javascript with category
     */
    public function generateJsScriptOutput($rhEasyCategoryDto) {

        if (isset($rhEasyCategoryDto)) {

            return '<script>
    if (window.RhEasy) {
        window.RhEasy.onCategoryViewed = {
            "add" : function (f) {
                f({"category" : ' . $rhEasyCategoryDto->toJson() . '}); 
                return true;
            }
        };
    }
</script>';

        } else {

            return '<script>
    if (window.RhEasy) {
        window.RhEasy.onCategoryViewed = {
            "add" : function (f) {
                return true;
            }
        };
    }
</script>';

        }
    }
}