<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/ProductJson.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/auth/authentication.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/roihunter.php');

const RH_FIRST_PRODUCT_PAGE = 1;

ROIHunterAuthenticator::getInstance()->authenticate();

$roihunterModule = Roihunter::getModuleInstance();

Context::getContext()->shop->id = $id_shop;
$id_shop = $roihunterModule->getShopFromUrl($_SERVER['HTTP_HOST']);
Context::getContext()->shop->id = $id_shop;


/*
$stream = file_get_contents('php://input');

$data = json_decode($stream, true);


// TODO ... page from data, id_lang from data


$page = (int)$data['page']?(int)$data['page']:1;

$id_lang = (int)$data['id_lang']?(int)$data['id_lang']:(int)Configuration::get('PS_LANG_DEFAULT', null, null, $id_shop);
*/
$id_lang = (isset($_GET['id_lang']) && (int)$_GET['id_lang']) ? (int)$_GET['id_lang'] : (int)Configuration::get('PS_LANG_DEFAULT', null, null, $id_shop);

$page = (isset($_GET['page']) ? (int) $_GET['page'] : RH_FIRST_PRODUCT_PAGE);
if (!is_numeric($page) || $page < RH_FIRST_PRODUCT_PAGE) {
    header("HTTP/1.1 400 Bad Request");
    echo "Page parameter is not valid.";
    die();
}

$perpage = 10;
$offset = $perpage * ($page - RH_FIRST_PRODUCT_PAGE);

$sql = 'SELECT s.id_product, pa.id_product_attribute FROM
    ' . _DB_PREFIX_ . 'product_shop s LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute pa
    ON s.id_product = pa.id_product AND s.id_shop = ' . (int)$id_shop . ' WHERE
    s.active = 1 
    LIMIT ' . $perpage . ' OFFSET ' . $offset;
$items = Db::getInstance()->executeS($sql);

$json = new ProductJson($roihunterModule);
$jsonData = [];
foreach ($items as $item) {
    $jsonData[] = $json->getJson($item['id_product'], $item['id_product_attribute'], $id_lang, $id_shop);
}

header("HTTP/1.1 200 OK");
header("Content-Type:application/json");
echo json_encode($jsonData);
die();


