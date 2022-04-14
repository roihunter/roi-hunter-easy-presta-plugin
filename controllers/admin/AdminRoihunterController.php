<?php
/**
 * Admin ROI Hunter API Controller
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
require_once(_PS_MODULE_DIR_ . 'roihunter/roihunter.php');

class AdminRoihunterController extends AdminController
{
    private $roihunterModule;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->roihunterModule = Roihunter::getModuleInstance();
        parent::__construct();
    }

    public function log($message) {
        PrestaShopLogger::addLog($message, null, null, 'Swift_Message', null, true);
    }

    public function initContent()
    {
        $this->log('Storage: ' . json_encode(ROIHunterStorage::getInstance()));

        $this->display = 'view';

        $shop_context = $this->roihunterModule->getAdminShopContext();
        $id_shop = $shop_context['id_shop'];
        $roiHunterStorage = ROIHunterStorage::getInstance();

        $params = [];
        $params['type'] = pSQL($this->roihunterModule->getPluginType());
        $params['storeUrl'] = pSQL($this->roihunterModule->getStoreUrl($id_shop));
        $params['rhStateApiBaseUrl'] = pSQL($this->roihunterModule->getRhStateApiBaseUrl($id_shop));
        $params['storeName'] = pSQL($this->roihunterModule->getStoreName($id_shop));
        $params['storeCurrency'] = pSQL($this->roihunterModule->getStoreCurrency($id_shop));
        $params['storeLanguage'] = pSQL($this->roihunterModule->getStoreLanguage($id_shop));
        $params['storeCountry'] = pSQL($this->roihunterModule->getStoreCountry($id_shop));
        $params['pluginVersion'] = pSQL($this->roihunterModule->getPluginVersion());
        $params['activeBeProfile'] = pSQL($roiHunterStorage->getActiveBeProfile());
        if ($customer_id = $roiHunterStorage->getSystemUserId()) {
            $params['customerId'] = pSQL($customer_id);
        }
        if ($accessToken = $roiHunterStorage->getAccessToken()) {
            $params['accessToken'] = pSQL($accessToken);
        }
        $clientToken = $roiHunterStorage->getClientToken();
        if (isset($clientToken) && !isset($params['accessToken'])) { //we can't send client token if access token exists
            $params['clientToken'] = pSQL($clientToken);
        } //1) dost toho chybi: google_conversion_id', 'google_conversion_label', 'fb_pixel_id' 2) co poslat bez hodnoty

        Context::getContext()->smarty->assign(
            [
                'params' => $params,
                'iframeBaseUrl' => pSQL($this->roihunterModule->getIframeUrl($id_shop)),
            ]
        );


        parent::initContent();
    }

    public function renderView()
    {
        return parent::renderView();
    }

    public function setHelperDisplay(Helper $helper)
    {
        parent::setHelperDisplay($helper);
        $helper->module = $this->roihunterModule;

        $this->helper = $helper;
    }
}
