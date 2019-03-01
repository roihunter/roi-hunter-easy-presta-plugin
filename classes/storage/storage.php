<?php

class ROIHunterStorage {

    const RH_SYSTEM_USER_ID = 'id';
    const RH_ACCESS_TOKEN = 'access_token';
    const RH_GOOGLE_CONVERSION_ID = 'google_conversion_id';
    const RH_GOOGLE_CONVERSION_LABEL = 'google_conversion_label';
    const RH_FB_PIXEL_ID = 'fb_pixel_id';
    const RH_ACTIVE_BE_PROFILE = 'ROIHUNTER_ACTIVEBEPROFILE';
    const RH_CLIENT_TOKEN = 'ROIHUNTER_CLIENT_TOKEN';

    const STATE_STORAGE_KEYS = [
        self::RH_SYSTEM_USER_ID,
        self::RH_ACCESS_TOKEN,
        self::RH_GOOGLE_CONVERSION_ID,
        self::RH_GOOGLE_CONVERSION_LABEL,
        self::RH_FB_PIXEL_ID];

    private static $instance;

    private $shopId;

    private function __construct() {
        $this->shopId = Context::getContext()->shop->id;
    }

    public static function getInstance() {

        if (!isset(self::$instance)) {
            self::$instance = new ROIHunterStorage();
        }
        return self::$instance;
    }

    public function getStorageWithoutTokens() {
        $content = [];
        foreach (ROIHunterStorage::STATE_STORAGE_KEYS as $key) {
            if ($key != ROIHunterStorage::RH_ACCESS_TOKEN) {   //do not send rh access token, we don't need it
                $content[$key] = $this->getConfigFormValue($key);
            }
        }
        return $content;
    }

    public function setStorage($data) {

        foreach (ROIHunterStorage::STATE_STORAGE_KEYS as $key) {
            $this->saveConfigFormValue($key, $data[$key]);
        }
    }

    public function clearStorage() {
        foreach (self::STATE_STORAGE_KEYS as $key) {
            $key = $this->translateKey($key);
            Configuration::deleteByName($key);
        }
    }

    public function getSystemUserId() {
        return $this->getConfigFormValue(self::RH_SYSTEM_USER_ID);
    }

    public function getAccessToken() {

        if (Configuration::get(self::RH_ACTIVE_BE_PROFILE, null, null, $this->shopId) != 1) {
            return 'demoAccessToken';
        }

        return $this->getConfigFormValue(self::RH_ACCESS_TOKEN);
    }


    public function getGoogleConversionId() {
        return $this->getConfigFormValue(self::RH_GOOGLE_CONVERSION_ID);
    }

    public function setGoogleConversionId($value) {
        $this->saveConfigFormValue(self::RH_GOOGLE_CONVERSION_ID, $value);
    }

    public function getGoogleConversionLabel() {
        return $this->getConfigFormValue(self::RH_GOOGLE_CONVERSION_LABEL);
    }

    public function setGoogleConversionLabel($value) {
        $this->saveConfigFormValue(self::RH_GOOGLE_CONVERSION_LABEL, $value);
    }

    public function getFbPixelId() {
        return $this->getConfigFormValue(self::RH_FB_PIXEL_ID);
    }

    public function setFbPixelId($value) {
        $this->saveConfigFormValue(self::RH_FB_PIXEL_ID, $value);
    }

    public function getClientToken() {
        return $this->getConfigFormValue(self::RH_CLIENT_TOKEN);
    }

    public function setClientToken($value) {
        $this->saveConfigFormValue(self::RH_CLIENT_TOKEN, $value);
    }

    private function saveConfigFormValue($key, $value) {
        $key = $this->translateKey($key);
        Configuration::updateValue($key, $value, false, Shop::getGroupFromShop($this->shopId), $this->shopId);
    }

    private function getConfigFormValue($key) {
        $key = $this->translateKey($key);
        $result = Configuration::get($key, null, Shop::getGroupFromShop($this->shopId), $this->shopId);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    private function translateKey($key) {
        if ($key == ROIHunterStorage::RH_GOOGLE_CONVERSION_LABEL) { // too long for ps 1.5
            return 'ROIHUNTER_GOOGLE_LABEL';
        }
        return 'ROIHUNTER_' . strtoupper($key);
    }
}