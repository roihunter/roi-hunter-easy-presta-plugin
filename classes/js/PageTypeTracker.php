<?php

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');

class PageTypeTracker {

    private static $instance;

    private $roiHunterStorage;

    private function __construct() {
        $this->roiHunterStorage = ROIHunterStorage::getInstance();
    }

    public static function getInstance() {

        if (!isset(self::$instance)) {
            self::$instance = new PageTypeTracker();
        }
        return self::$instance;
    }

    /**
     * Generate on page loaded script with page type.
     * Script immediately invoke function f passed in window.RhEasy.onPageLoaded.add(f);
     * @param $pageType string EPageType value.
     * @return string Javascript with prepared page type.
     */
    public function generateJsScriptOutput($pageType) {

        return '<script>
    if (window.RhEasy) {
        window.RhEasy.onPageLoaded = { 
            "add" : function (f) { 
                f({"page" : "' . $pageType . '"}); 
                return true;
            }
        };
    }
</script>';
    }
}