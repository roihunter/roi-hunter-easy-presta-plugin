<?php

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');

class AdminRoihunterController extends AdminController {
    protected $instance;

    public function __construct() {
        $this->bootstrap = true;
        $this->instance = Module::getInstanceByName('roihunter');
        parent::__construct();
    }

    public function initContent() {
        $this->display = 'view';

        $shop_context = $this->instance->getAdminShopContext();
        $id_shop = $shop_context['id_shop'];
        $roiHunterStorage = ROIHunterStorage::getInstance();

        $params = [];
        $params['type'] = pSQL($this->instance->getPluginType());
        $params['storeUrl'] = pSQL($this->instance->getStoreUrl($id_shop));
        $params['rhStateApiBaseUrl'] = pSQL($this->instance->getRhStateApiBaseUrl($id_shop));
        $params['storeName'] = pSQL($this->instance->getStoreName($id_shop));
        $params['storeCurrency'] = pSQL($this->instance->getStoreCurrency($id_shop));
        $params['storeLanguage'] = pSQL($this->instance->getStoreLanguage($id_shop));
        $params['storeCountry'] = pSQL($this->instance->getStoreCountry($id_shop));
        $params['pluginVersion'] = pSQL($this->instance->getPluginVersion());
        $params['activeBeProfile'] = pSQL($this->instance->getActiveBeProfile($id_shop));
        if ($customer_id = $roiHunterStorage->getSystemUserId()) { // potrebuji id_shop??
            $params['customerId'] = pSQL($customer_id);   // int ??
        }
        if ($accessToken = $roiHunterStorage->getAccessToken()) {
            $params['accessToken'] = pSQL($accessToken);
        }
        $clientToken = $roiHunterStorage->getClientToken();
        if (isset($clientToken) && !isset($params['accessToken'])) {   //we can't send client token if access token exists
            $params['clientToken'] = pSQL($clientToken);
        }
        /* 
        1) dost toho  chybi: google_conversion_id', 'google_conversion_label', 'fb_pixel_id'
        2) co poslat kdyz jeste neni hodnota ulozena
        */

        Context::getContext()->smarty->assign(
            [
                'params' => $params,
                'iframeBaseUrl' => pSQL($this->instance->getIframeUrl($id_shop)),
            ]
        );


        parent::initContent();
    }

    public function renderView() {
        return parent::renderView();
    }

    public function setHelperDisplay(Helper $helper) {
        parent::setHelperDisplay($helper);
        $helper->module = $this->instance;

        $this->helper = $helper;
    }
}
