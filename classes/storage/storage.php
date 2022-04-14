<?php
/**
 * Manage (store, load) ROI Hunter data in Database
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

class ROIHunterStorage implements JsonSerializable
{
    const RH_SYSTEM_USER_ID = 'id';
    const RH_ACCESS_TOKEN = 'access_token';
    const RH_GOOGLE_CONVERSION_ID = 'google_conversion_id';
    const RH_GOOGLE_CONVERSION_LABEL = 'google_conversion_label';
    const RH_FB_PIXEL_ID = 'fb_pixel_id';
    const RH_CLIENT_TOKEN = 'client_token';

    const STATE_STORAGE_KEYS = [
        self::RH_SYSTEM_USER_ID,
        self::RH_ACCESS_TOKEN,
        self::RH_GOOGLE_CONVERSION_ID,
        self::RH_GOOGLE_CONVERSION_LABEL,
        self::RH_FB_PIXEL_ID];

    const BE_PROFILE_PRODUCTION_NAME = 'production';
    const BE_PROFILE_STAGING_NAME = 'staging';

    /* change to BE_PROFILE_STAGING_NAME if you want app running in staging mode */
    const RH_ACTIVE_BE_PROFILE = self::BE_PROFILE_PRODUCTION_NAME;

    const RH_FE_PRODUCTION_URL = 'https://goostav-fe.roihunter.com';
    const RH_FE_STAGING_URL = 'https://goostav-fe-staging.roihunter.com';

    private static $instance;

    private $shopId;

    private function __construct()
    {
        $query = (new DbQuery())
            ->select('ms.id_shop')
            ->from('module_shop', 'ms')
            ->innerJoin('module', 'm', 'm.id_module = ms.id_module')
            ->where('m.name = \'roihunter\'');

        $result = Db::getInstance()->executeS($query);
        $this->shopId = $result[0]['id_shop'];
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new ROIHunterStorage();
        }
        return self::$instance;
    }

    public function getStorageWithoutTokens()
    {
        $content = [];
        foreach (ROIHunterStorage::STATE_STORAGE_KEYS as $key) {
            if ($key != ROIHunterStorage::RH_ACCESS_TOKEN) {   //do not send rh access token, we don't need it
                $content[$key] = $this->getConfigFormValue($key);
            }
        }
        return $content;
    }

    public function setStorage($data)
    {
        foreach (ROIHunterStorage::STATE_STORAGE_KEYS as $key) {
            $this->saveConfigFormValue($key, $data[$key]);
        }
    }

    public function clearStorage()
    {
        foreach (self::STATE_STORAGE_KEYS as $key) {
            $key = $this->translateKey($key);
            Configuration::deleteByName($key);
        }
    }

    public function getSystemUserId()
    {
        return $this->getConfigFormValue(self::RH_SYSTEM_USER_ID);
    }

    public function getAccessToken()
    {
        return $this->getConfigFormValue(self::RH_ACCESS_TOKEN);
    }

    public function getGoogleConversionId()
    {
        return $this->getConfigFormValue(self::RH_GOOGLE_CONVERSION_ID);
    }

    public function getGoogleConversionLabel()
    {
        return $this->getConfigFormValue(self::RH_GOOGLE_CONVERSION_LABEL);
    }

    public function getFbPixelId()
    {
        return $this->getConfigFormValue(self::RH_FB_PIXEL_ID);
    }

    public function getClientToken()
    {
        return $this->getConfigFormValue(self::RH_CLIENT_TOKEN);
    }

    public function setClientToken($value)
    {
        $this->saveConfigFormValue(self::RH_CLIENT_TOKEN, $value);
    }

    public function getActiveBeProfile()
    {
        return self::RH_ACTIVE_BE_PROFILE;
    }

    public function isActiveBeProfileProduction()
    {
        return self::RH_ACTIVE_BE_PROFILE == self::BE_PROFILE_PRODUCTION_NAME;
    }

    public function isActiveBeProfileStaging()
    {
        return self::RH_ACTIVE_BE_PROFILE == self::BE_PROFILE_STAGING_NAME;
    }

    public function trackingParamsAreInitialized()
    {
        return $this->googleTrackingParamsAreInitialized() || $this->facebookTrackingParamsAreInitialized();
    }

    private function googleTrackingParamsAreInitialized()
    {
        return !empty($this->getGoogleConversionId()) && !empty($this->getGoogleConversionLabel());
    }

    private function facebookTrackingParamsAreInitialized()
    {
        return !empty($this->getFbPixelId());
    }

    private function saveConfigFormValue($key, $value)
    {
        $key = $this->translateKey($key);
        Configuration::updateValue($key, $value, false, Shop::getGroupFromShop($this->shopId), $this->shopId);
    }

    private function getConfigFormValue($key)
    {
        $key = $this->translateKey($key);
        $result = Configuration::get($key, null, Shop::getGroupFromShop($this->shopId), $this->shopId);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    private function translateKey($key)
    {
        if ($key == ROIHunterStorage::RH_GOOGLE_CONVERSION_LABEL) { // too long for ps 1.5
            return 'ROIHUNTER_GOOGLE_LABEL';
        }
        return 'ROIHUNTER_' . Tools::strtoupper($key);
    }


    public function jsonSerialize()
    {
        $object = array('shop_id' => $this->shopId);
        foreach (self::STATE_STORAGE_KEYS as $key) {
            $object[$key] = $this->getConfigFormValue($key);
        }
        return $object;
    }
}
