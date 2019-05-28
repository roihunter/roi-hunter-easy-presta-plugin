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
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/requests/ROIHunterRequestsManager.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/enums/EPageType.php');

class Roihunter extends Module {

    const ROI_HUNTER_MODULE_NAME = 'roihunter';
    const ROI_HUNTER_TAB_CLASS_NAME = 'AdminRoihunter';
    const ROI_HUNTER_TAB_NAME = 'ROI Hunter Easy';

    protected $config_form = false;
    private $roiHunterStorage;
    private $rhEasyCookieManager;
    private $rhRequestsManager;

    public function __construct() {
        $this->name = self::ROI_HUNTER_MODULE_NAME;
        $this->tab = 'advertising_marketing';
        $this->version = '1.0.0';
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
        $this->rhRequestsManager = ROIHunterRequestsManager::getInstance();
    }

    public function install() {
        $retval = parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayFooter') &&
            $this->registerHook('actionCartSave') &&
            $this->registerHook('displayBackOfficeHeader');
        if ($retval) {
            $this->installModuleTab();
            $this->roiHunterStorage->setClientToken($this->getSecureToken());
        }
        return $retval;
    }

    public function uninstall() {
        $this->roiHunterStorage->setClientToken(null);
        $this->uninstallModuleTab();
        $this->roiHunterStorage->clearStorage();
        $this->rhRequestsManager->onAppUninstall();
        return parent::uninstall();
    }

    /************************** hooks start ******************************/

    public function hookDisplayFooter() {

        if (!$this->roiHunterStorage->trackingParamsAreInitialized()) {
            return '';
        }

        $smarty = new Smarty();

        $pageType = EPageType::fromPrestaShopController(Tools::getValue('controller'));
        $smarty->assign('rhEasyDto', new RhEasyDto(
            "PRESTA_SHOP",
            $this->roiHunterStorage->getGoogleConversionId(),
            $this->roiHunterStorage->getGoogleConversionLabel(),
            $this->roiHunterStorage->getFbPixelId())
        );

        $smarty->assign('rhEasyPageDto', new RhEasyPageDto($pageType));

        if ($pageType == EPageType::PRODUCT) {
            $smarty->assign('rhEasyProductDto', $this->createRhEasyProductDto());
        }

        if ($pageType == EPageType::CATEGORY) {
            $smarty->assign('rhEasyCategoryDto', $this->createRhEasyCategoryDto());
        }

        if ($pageType == EPageType::ORDER_CONFIRMATION) {
            $smarty->assign('rhEasyOrderDto', $this->createRhEasyOrderDto());
        }

        if (isset(Context::getContext()->cookie->roihunter)) {  // add to cart event from previous web page
            $cartDto =  $this->getRhEasyCartDtoFromCookie();
            if (!empty($cartDto->getCartItems())) {
                $smarty->assign('rhEasyCartDto', $cartDto);
            }
        }

        $smarty->assign('activeProfile', $this->roiHunterStorage->getActiveBeProfile());

        return $smarty->fetch($this->local_path . 'scripts/rheasy_initialize.tpl') .
            $smarty->fetch($this->local_path . 'scripts/rheasy_events_tracking.tpl');
    }

    public function hookActionCartSave($params) {

        if ((int)Tools::getValue("id_product") && Tools::getValue('add')) {

            //store new item into storage
            $rhEasyCookieDto = $this->rhEasyCookieManager->loadRhEasyCookieDto();

            $newRhCartItemDto = $this->createRhEasyCartItemDto();   //get cart item from hook
            $rhEasyCookieDto->getRhEasyCartDto()->addItemToCart($newRhCartItemDto);

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

        $this->context->controller->addJS($this->_path . 'views/js/back.js');
        $this->context->controller->addCSS($this->_path . 'views/css/back.css');
    }

    public function hookHeader() {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
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
        $shopUrl = new ShopUrl($id_shop);
        if (Configuration::get('PS_SSL_ENABLED')) {
            return 'https://'.$shopUrl->domain_ssl.$shopUrl->physical_uri;
        } else {
            return 'http://'.$shopUrl->domain.$shopUrl->physical_uri;
        }
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
        return $this->getStoreUrl($id_shop) . 'modules/roihunter/';
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
        } else if ($this->roiHunterStorage->isActiveBeProfileStaging()) {
            return 'https://goostav-fe-staging.roihunter.com';
        } else {
            throw new PrestaShopException("Cannot get iframe URL because active profile is not staging or production");
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

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        $id_shop = (int)$shop_content['id_shop'];
        $base = $this->getRhStateApiBaseUrl($id_shop);
        $output .= '<table class="table">';
        $output .= '<tr><td>rhStateApiBaseUrl</td><td>' . $base . '</td></tr>';
        $output .= '<tr><td>state endpoint</td><td>' . $base . 'state.php</td></tr>';
        $output .= '<tr><td>check   endpoint</td><td>' . $base . 'check.php</td></tr>';
        $output .= '<tr><td>products endpoint</td><td>' . $base . 'products.php</td></tr>';
        $output .= '<tr><td>active profile</td><td>' . $this->roiHunterStorage->getActiveBeProfile() . '</td></tr>';
        foreach ($this->roiHunterStorage->getStorageWithoutTokens() as $key => $value) {
            $output .= '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
        }
        $output .= '</table>';


        return $output;
    }

    private function installModuleTab() {
        if (Tools::version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->v16InstallModuleTab();
        } else {
            $this->v17InstallModuleTab();
        }
    }

    private function v17InstallModuleTab() {
        $this->tabs = array(
            array(
                'name' => self::ROI_HUNTER_TAB_NAME,
                'class_name' => self::ROI_HUNTER_TAB_CLASS_NAME,
                'visible' => true,
                'parent_class_name' => 'IMPROVE'
            )
        );
    }

    private function v16InstallModuleTab() {
        $sql = 'SELECT id_tab FROM ' . _DB_PREFIX_ . 'tab WHERE class_name ="' . pSQL(self::ROI_HUNTER_TAB_CLASS_NAME) . '"';
        $id_tab = Db::getInstance()->getValue($sql);
        if ((int)$id_tab) {
            $tab = new Tab($id_tab);
        } else {
            $tab = new Tab();
        }

        @copy(_PS_MODULE_DIR_ . $this->module->name . '/logo.gif', _PS_IMG_DIR_ . 't/' . self::ROI_HUNTER_TAB_CLASS_NAME . '.gif');

        $tabNames = [];
        foreach (Language::getLanguages(false) as $language) {
            $tabNames[$language['id_lang']] = self::ROI_HUNTER_TAB_NAME;
        }
        $tab->name = $tabNames;
        $tab->class_name = self::ROI_HUNTER_TAB_CLASS_NAME;
        $tab->module = $this->name;
        $tab->id_parent = 0;
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

    public function uninstallModuleTab() {
        $idTab = Tab::getIdFromClassName(self::ROI_HUNTER_TAB_CLASS_NAME);
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
            $firstVariantId = (int) Db::getInstance()->getValue('select id_product_attribute from '._DB_PREFIX_.'product_attribute where id_product = '.$id_product);

            return new RhEasyProductDto($id_product, $firstVariantId, $name, $this->roundPrice($price), $currency);
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
        $variantId = null;
        if (Tools::getValue("ipa")) {
            $variantId = (int)Tools::getValue("ipa");
        }
        $quantity = (int)Tools::getValue('qty');
        $price = Product::getPriceStatic($productId, $this->useTax(), $variantId);

        $compute_precision = defined('_PS_PRICE_COMPUTE_PRECISION_') ? _PS_PRICE_COMPUTE_PRECISION_ : 2;
        $roundedPrice = Tools::ps_round($price, $compute_precision);

        $id_currency = Context::getContext()->cart->id_currency;
        $currency = new Currency($id_currency);

        return new RhEasyCartItemDto(
            new RhEasyProductDto($productId, $variantId, Product::getProductName($productId), $roundedPrice, $currency->iso_code),
            $quantity);
    }

    private function getRhEasyCartDtoFromCookie() {

        $rhEasyCookieDto = $this->rhEasyCookieManager->loadRhEasyCookieDto();
        $this->rhEasyCookieManager->clearStorage();

        return $rhEasyCookieDto->getRhEasyCartDto();
    }
}
