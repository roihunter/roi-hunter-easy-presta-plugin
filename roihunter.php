<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/cookie/RhEasyCookieManager.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/js/RhTrackingScriptLoader.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/dtos/RhEasyProductDto.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/dtos/RhEasyCategoryDto.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/dtos/RhEasyCartDto.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/dtos/RhEasyCartItemDto.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/dtos/RhEasyOrderDto.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/dtos/RhEasyPageDto.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/enums/EPageType.php');

class Roihunter extends Module {

    const ROI_HUNTER_MODULE_NAME = 'roihunter';

    protected $config_form = false;
    private $roiHunterStorage;
    private $rhEasyCookieManager;

    public function __construct() {
        $this->name = self::ROI_HUNTER_MODULE_NAME;
        $this->tab = 'advertising_marketing';
        $this->version = '0.9.0';
        $this->author = 'ROI Hunter';
        $this->need_instance = 1;
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('ROI Hunter Easy');
        $this->description = $this->l('ROI Hunter Easy addon for PrestaShop');
        $this->confirmUninstall = $this->l('');
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
        $this->roiHunterStorage = ROIHunterStorage::getInstance();
        $this->rhEasyCookieManager = RhEasyCookieManager::getInstance();
    }

    public function install() {
        $retval = parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayFooter') &&
            $this->registerHook('actionCartSave') &&
            $this->registerHook('displayBackOfficeHeader');
        if ($retval) {
            $this->installModuleTab('AdminRoihunter', 'ROI Hunter Easy', 0);
            $this->roiHunterStorage->setClientToken($this->getSecureToken());
        }
        return $retval;
    }

    public function uninstall() {
        $this->roiHunterStorage->setClientToken(null);
        $this->uninstallModuleTab('AdminRoihunter');
        $this->roiHunterStorage->clearStorage();
        return parent::uninstall();
    }

    /************************** hooks start ******************************/

    public function hookDisplayFooter($params) {

        $google_conversion_id = $this->roiHunterStorage->getGoogleConversionId();
        $google_conversion_label = $this->roiHunterStorage->getGoogleConversionLabel();
        $fb_pixel_id = $this->roiHunterStorage->getFbPixelId();

        $output = '';
        if (empty($google_conversion_id) && empty($fb_pixel_id)) {
            return $output;
        }

        $rhTrackingScriptLoader = RhTrackingScriptLoader::getInstance();

        $pageType = EPageType::fromPrestaShopController(Tools::getValue('controller'));
        $rhTrackingScriptLoader->setRhEasyPageDto(new RhEasyPageDto($pageType));

        if ($pageType == EPageType::PRODUCT) {
            $rhTrackingScriptLoader->setRhEasyProductDto($this->createRhEasyProductDto());
        }
        if ($pageType == EPageType::CATEGORY) {
            $rhTrackingScriptLoader->setRhEasyCategoryDto($this->createRhEasyCategoryDto());
        }
        if ($pageType == EPageType::ORDER_CONFIRMATION) {
            $rhTrackingScriptLoader->setRhEasyOrderDto($this->createRhEasyOrderDto());
        }
        if (isset(Context::getContext()->cookie->roihunter)) {  // add to cart event from previous web page
            $rhTrackingScriptLoader->setRhEasyCartDto($this->getRhEasyCartDtoFromCookie());
        }

        $output .= $rhTrackingScriptLoader->generateJsScriptOutput();

        return $output;
    }

    public function hookActionCartSave($params) {

        if ((int)Tools::getValue("id_product") && Tools::getValue('add')) {

            //store new item into storage
            $rhEasyCookieDto = $this->rhEasyCookieManager->loadRhEasyCookieDto();

            $newRhCartItemDto = $this->createRhEasyCartItemDto();   //get cart item from hook
            $cartItems = $rhEasyCookieDto->getRhEasyCartDto()->getCartItems();
            array_push($cartItems, $newRhCartItemDto);
            $rhEasyCookieDto->getRhEasyCartDto()->setCartItems($cartItems);

            $this->rhEasyCookieManager->saveRhEasyCookieDto($rhEasyCookieDto);
        }
    }

    private function roundPrice($price, $currency = null) {
        if (is_null($currency)) {
            $currency = Context::getContext()->currency;
        }
        $decimals = (int)$currency->decimals * _PS_PRICE_DISPLAY_PRECISION_;
        return Tools::ps_round($price, (int)$decimals);
    }

    public function hookBackOfficeHeader() {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    public function hookHeader() {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
        $fb_pixel_id = $this->roiHunterStorage->getFbPixelId();
        if (!empty($fb_pixel_id)) {
            $this->context->controller->addJS('https://storage.googleapis.com/goostav-static-files/rheasy-fbq-wrapper.js');
        }
    }

    public function hookDisplayBackOfficeHeader() {
        return $this->hookBackOfficeHeader();
    }

    private function useTax() {
        return true;
    }

    /************************** hooks end ******************************/

    /************************** controller start ******************************/

    public function getPluginType() {
        return 'rh-easy-prestashop-initial-message';
    }

    public function getStoreUrl($id_shop) {
        return Configuration::get('PS_SSL_ENABLED') ? 'https://' . ShopUrl::getMainShopDomainSSL($id_shop) : 'http://' . ShopUrl::getMainShopDomain($id_shop);
    }

    public function getPreviewUrl($id_shop) {
        return null; // to be implemented
    }

    /**
     * konkretni endpointy jsou
     * check.php
     * products.php
     * state.php
     * ... s paramatrem id_shop
     */
    public function getRhStateApiBaseUrl($id_shop) {
        $base = $this->getStoreUrl($id_shop);
        // $protocol = Configuration::get('PS_SSL_ENABLED')?'https://':'http://';
        return $base . __PS_BASE_URI__ . 'modules/roihunter/';
    }

    public function getStoreName($id_shop) {
        $shop = new Shop($id_shop);
        return $shop->name;
    }

    public function getStoreCurrency($id_shop) {
        $id_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT', null, null, $id_shop);
        $currency = new Currency($id_currency);
        if ($currency->active && !$currency->deleted) {
            return $currency->iso_code;
        }
    }

    public function getStoreLanguage($id_shop) {
        $id_language = (int)Configuration::get('PS_LANG_DEFAULT', null, null, $id_shop);
        $language = new Language($id_language);
        if ($language->active) {
            return $language->iso_code;
        }
    }

    public function getStoreCountry($id_shop) {
        $id_country = (int)Configuration::get('PS_COUNTRY_DEFAULT', null, null, $id_shop);
        $country = new Country($id_country);
        return $country->iso_code;
    }

    public function getPluginVersion() {
        return 'prestashop_' . $this->version;
    }

    public function getIframeUrl() {
        if ($this->roiHunterStorage->isActiveBeProfileProduction()) {
            return 'https://magento.roihunter.com';
        } else {
            return 'https://goostav-fe-staging.roihunter.com';
        }
    }

    /************************** controller end ******************************/

    public function getShopFromUrl($url) {
        $field = Configuration::get("PS_USE_SSL") ? 'domain_ssl' : 'domain';
        $sql = 'SELECT id_shop FROM ' . _DB_PREFIX_ . 'shop_url WHERE ' . $field . '="' . $url . '" AND active = 1';
        $id_shop = (int)Db::getInstance()->getValue($sql);
        if (!$id_shop && $field == 'domain_ssl') {
            $sql = 'SELECT id_shop FROM ' . _DB_PREFIX_ . 'shop_url WHERE ' . $field . '="' . $url . '" AND active = 1';
            $id_shop = (int)Db::getInstance()->getValue($sql);
        }
        if (!$id_shop && !Shop::isFeatureActive()) {
            $sql = 'SELECT MIN(id_shop) FROM    ' . _DB_PREFIX_ . 'shop_url  WHERE active = 1';
            $id_shop = (int)Db::getInstance()->getValue($sql);
        }
        return $id_shop;
    }

    /**
     * Load the configuration form
     */
    public function getContent() {
        $shop_content = self::getAdminShopContext();
        if ($shop_content['multishop'] == true && $shop_content['context'] != 'shop') {
            return '<div class="panel"><h3>' . $this->l('Multishop detected. Please switch to a specific shop!') . '</h3></div>';
        }

        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitRoihunterModule')) == true) {
            $this->saveFormInConfiguration();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        $id_shop = (int)$shop_content['id_shop'];
        $base = $this->getRhStateApiBaseUrl($id_shop);
        $output .= '<table class="table">';
        $output .= '<tr><td>rhStateApiBaseUrl</td><td>' . $base . '</td></tr>';
        $output .= '<tr><td>state endpoint</td><td>' . $base . 'state.php</td></tr>';
        $output .= '<tr><td>check   endpoint</td><td>' . $base . 'check.php</td></tr>';
        $output .= '<tr><td>products endpoint</td><td>' . $base . 'products.php</td></tr>';
        $output .= '<tr><td>google tracking endpoint</td><td>' . $base . 'google-tracking.php</td></tr>';
        $output .= '<tr><td>facebook tracking endpoint</td><td>' . $base . 'facebook-tracking.php</td></tr>';
        $val = "";
        foreach ($this->roiHunterStorage->getStorageWithoutTokens() as $key => $value) {
            $output .= '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
        }
        $output .= '</table>';


        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm() {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitRoihunterModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => [ROIHunterStorage::RH_ACTIVE_BE_PROFILE => $this->roiHunterStorage->isActiveBeProfileProduction()], // form values in configuration
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm() {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Active application profile'),
                        'name' => ROIHunterStorage::RH_ACTIVE_BE_PROFILE,
                        'is_bool' => true,
                        'desc' => $this->l(''),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Production'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Stagging'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    protected function saveFormInConfiguration() {
        if ((int)Tools::getValue(ROIHunterStorage::RH_ACTIVE_BE_PROFILE)) {
            $this->roiHunterStorage->setActiveBeProfile(ROIHunterStorage::RH_ACTIVE_BE_PROFILE_PRODUCTION);
        } else {
            $this->roiHunterStorage->setActiveBeProfile(ROIHunterStorage::RH_ACTIVE_BE_PROFILE_STAGING);
        }
    }

    private function installModuleTab($tabClass, $tabName, $idTabParent) {
        $sql = 'SELECT id_tab FROM ' . _DB_PREFIX_ . ' tab WHERE classname ="' . pSQL($tabClass) . '"';
        $id_tab = Db::getInstance()->getValue($sql);
        if ((int)$id_tab) {
            $tab = new Tab($id_tab);
        } else {
            $tab = new Tab();
        }

        @copy(_PS_MODULE_DIR_ . $this->module->name . '/logo.gif', _PS_IMG_DIR_ . 't/' . $tabClass . '.gif');

        $tabNames = [];
        foreach (Language::getLanguages(false) as $language) {
            $tabNames[$language['id_lang']] = $tabName;
        }
        $tab->name = $tabNames;
        $tab->class_name = $tabClass;
        $tab->module = $this->name;
        $tab->id_parent = $idTabParent;
        if (!$tab->save()) {
            $this->messages[] = 'Failed save Tab ' . implode(',', $tabNames);
            return false;
        }
        if (Tools::version_compare(_PS_VERSION_, '1.7', '<')) {
            if (!Tab::initAccess($tab->id)) {
                $this->messages[] = 'Failed save init access ' . implode(',', $tabNames);
                return false;
            }
        }
        return true;
    }

    public function uninstallModuleTab($tabClass) {
        $idTab = Tab::getIdFromClassName($tabClass);
        if ($idTab != 0) {
            $tab = new Tab($idTab);
            $tab->delete();
            return true;
        }
        return true; // true even on failed
    }

    public function getImageType() {
        return Configuration::get('ROIHUNTER_IMAGE_TYPE') ? Configuration::get('ROIHUNTER_IMAGE_TYPE') : '';
    }

    public static function getAdminShopContext() {
        $retval = [];
        if (Shop::isFeatureActive()) {
            $retval['multishop'] = true;
            switch (Shop::getContext()) {
                case Shop::CONTEXT_GROUP:
                    {
                        $retval['context'] = 'group';
                        break;
                    }
                case Shop::CONTEXT_SHOP:
                    {
                        $retval['context'] = 'shop';
                        break;
                    }
                case Shop::CONTEXT_ALL:
                    {
                        $retval['context'] = 'all';
                        break;
                    }
            }
            $retval['id_shop_group'] = (int)Shop::getContextShopGroupID();
        } else {
            $retval['multishop'] = false;
        }
        $retval['id_shop'] = (int)Context::getContext()->shop->id;

        return $retval;
    }

    private function getSecureToken() {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $token = openssl_random_pseudo_bytes(32);
            return substr(bin2hex($token), 0, 32);
        } else {
            $token = sha1(mt_rand(1, 90000) . _COOKIE_KEY_);
            return base64_encode($token);
        }
    }

    /**
     * Get module instance
     * @return Roihunter
     */
    public static function getModuleInstance() {

        $roihunterModule = parent::getInstanceByName(self::ROI_HUNTER_MODULE_NAME);
        if ($roihunterModule instanceof Roihunter) {
            return $roihunterModule;
        } else {
            throw new Error("Uninitialized prestashop module " . self::ROI_HUNTER_MODULE_NAME);
        }
    }

    /**
     * true if presta returned module instance
     * @return bool
     */
    public static function isModuleLoaded() {

        $roihunterModule = parent::getInstanceByName(self::ROI_HUNTER_MODULE_NAME);
        if ($roihunterModule instanceof Roihunter) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get viewed product from DB based on ID in from $_POST
     * @return RhEasyProductDto
     */
    private function createRhEasyProductDto() {

        $id_product = (int)Tools::getValue('id_product');
        if ($id_product) {

            $sql = 'SELECT name FROM ' . _DB_PREFIX_ . 'product_lang WHERE id_product =' . (int)$id_product . ' AND id_lang =' . Context::getContext()->language->id;
            $name = Db::getInstance()->getValue($sql);

            $price = Product::getPriceStatic($id_product, $this->useTax());

            $currency = Context::getContext()->currency->iso_code;

            return new RhEasyProductDto($id_product, null, $name, $this->roundPrice($price), $currency);
        } else {
            return null;
        }
    }

    private function createRhEasyCategoryDto() {

        $id_category = (int)Tools::getValue('id_category');
        if ($id_category) {
            $ref = new ReflectionObject(Context::getContext()->controller);

            if ($ref->hasProperty('cat_products')) {
                $prop = $ref->getProperty('cat_products');
                $prop->setAccessible(true);
                $products = $prop->getValue(Context::getContext()->controller);

                return RhEasyCategoryDto::fromPrestaShopCategoryProducts($id_category, $products);
            }
        }
        return null;
    }

    private function createRhEasyOrderDto() {

        $orderId = (int)Tools::getValue('id_order');
        if ($orderId) {
            $order = new Order($orderId);
            $currency = new Currency($order->id_currency);

            if ($this->useTax()) {
                $total_price = $order->total_products_wt; // $order->total_paid_tax_incl;
            } else {
                $total_price = $order->total_products;   // $order->total_paid_tax_excl;
            }

            return RhEasyOrderDto::fromPrestaShopOrderProducts($orderId, $currency->iso_code, $order->getProducts(), $this->roundPrice($total_price));

        }
        return null;
    }

    private function createRhEasyCartItemDto() {

        $productId = (int)Tools::getValue("id_product");
        $variantId = (int)Tools::getValue("ipa");
        $quantity = Tools::getValue('qty');
        $price = Product::getPriceStatic($productId, $this->useTax(), $variantId);

        $compute_precision = defined('_PS_PRICE_COMPUTE_PRECISION_') ? _PS_PRICE_COMPUTE_PRECISION_ : 2;
        $roundedPrice = Tools::ps_round($price, $compute_precision);

        $id_currency = Context::getContext()->cart->id_currency;
        $currency = new Currency($id_currency);

        return new RhEasyCartItemDto(
            new RhEasyProductDto($productId, $variantId, null, $roundedPrice, $currency->iso_code),
            $quantity);
    }

    private function getRhEasyCartDtoFromCookie() {

        $rhEasyCookieDto = $this->rhEasyCookieManager->loadRhEasyCookieDto();
        $this->rhEasyCookieManager->clearStorage();

        return $rhEasyCookieDto->getRhEasyCartDto();
    }
}
