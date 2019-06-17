<?php
/**
 * Make requests to ROI Hunter API
 *
 * LICENSE: The buyer can free use/edit/modify this software in anyway
 * The buyer is NOT allowed to redistribute this module in anyway or resell it
 * or redistribute it to third party
 *
 * @author    ROI Hunter Easy
 * @copyright 2019 ROI Hunter
 * @license   EULA
 * @version   1.0
 * @link      https://easy.roihunter.com/
 */

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');

class ROIHunterRequestsManager
{
    const GOOSTAV_API_PRODUCTION = 'https://goostav.roihunter.com';
    const GOOSTAV_API_STAGING = 'https://goostav-staging.roihunter.com';

    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new ROIHunterRequestsManager();
        }
        return self::$instance;
    }

    private $accessToken;
    private $baseUrl;

    private function __construct()
    {
        $this->accessToken = ROIHunterStorage::getInstance()->getAccessToken();
        if (ROIHunterStorage::getInstance()->isActiveBeProfileProduction()) {
            $this->baseUrl = self::GOOSTAV_API_PRODUCTION;
        } elseif (ROIHunterStorage::getInstance()->isActiveBeProfileStaging()) {
            $this->baseUrl = self::GOOSTAV_API_STAGING;
        }
    }

    public function onAppUninstall()
    {
        try {
            $this->send('/uninstall');
        } catch (Exception $e) {
            error_log("Some error while sending request was occurred: " . $e->getMessage());
        }
    }

    private function send($path)
    {
        if ($this->baseUrl == null) {
            error_log('Cannot send the request ' . $path . ' because base URL was not specified');
            return ;
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->baseUrl . $path,
            CURLOPT_HTTPHEADER => ['X-Authorization: ' . $this->accessToken]
        ]);

        curl_exec($curl);

        curl_close($curl);
    }
}
