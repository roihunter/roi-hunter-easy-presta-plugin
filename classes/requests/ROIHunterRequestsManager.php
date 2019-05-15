<?php

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');


class ROIHunterRequestsManager {

    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new ROIHunterRequestsManager();
        }
        return self::$instance;
    }

    private $accessToken;
    private $baseUrl;

    private function __construct() {
        $this->accessToken = ROIHunterStorage::getInstance()->getAccessToken();
        if (ROIHunterStorage::getInstance()->isActiveBeProfileProduction()) {
            $this->baseUrl = 'https://goostav.roihunter.com';
        } else if (ROIHunterStorage::getInstance()->isActiveBeProfileStaging()) {
            $this->baseUrl = 'https://goostav-staging.roihunter.com';
        }
    }

    public function onAppUninstall() {
        try {
            $this->send('/uninstall');
        } catch (Exception $e) {
            error_log("Some error while sending request was occurred: " . $e->getMessage());
        }
    }

    private function send($path) {
        if ($this->baseUrl == null) {
            error_log('Cannot send the request ' . $path . ' because base URL was not specified');
            return null;
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->baseUrl . $path,
            CURLOPT_HTTPHEADER => ['X-Authorization: ' . $this->accessToken]
        ]);

        $resp = curl_exec($curl);

        curl_close($curl);
        return $resp;
    }
}

