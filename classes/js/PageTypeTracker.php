<?php

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');

class PageTypeTracker {

    private static $instance;

    private function __construct() {
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
     * @param $rhEasyPageDto RhEasyPageDto page.
     * @return string Javascript with prepared page type.
     */
    public function generateJsScriptOutput($rhEasyPageDto) {

        if (isset($rhEasyPageDto)) {

            return '<script>
    if (window.RhEasy) {
        window.RhEasy.onPageLoaded = { 
            "add" : function (f) { 
                f(' . $rhEasyPageDto->toJson() . ');
                return true;
            }
        };
    }
</script>';
        } else {

            return '<script>
    if (window.RhEasy) {
        window.RhEasy.onPageLoaded = { 
            "add" : function (f) { 
                return true;
            }
        };
    }
</script>';
        }
    }
}