<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/ProductJson.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/auth/authentication.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/roihunter.php');

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
$page = (isset($_GET['page']) && (int)$_GET['page']) ? (int)$_GET['page'] : 1;
$id_lang = (isset($_GET['id_lang']) && (int)$_GET['id_lang']) ? (int)$_GET['id_lang'] : (int)Configuration::get('PS_LANG_DEFAULT', null, null, $id_shop);


$perpage = 10;

$sql = 'SELECT s.id_product, pa.id_product_attribute FROM
    ' . _DB_PREFIX_ . 'product_shop s LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute pa
    ON s.id_product = pa.id_product AND s.id_shop = ' . (int)$id_shop . ' WHERE
    s.active = 1';
$hits = Db::getInstance()->executeS($sql);
$counter = 0;
$from = ($page - 1) * $perpage;
$setpage = 1;
$id_previous = 0;

// strankovani po variantach ale zacina vzdy novym produktem
$items = [];
foreach ($hits as $hit) {
    if ($counter >= $perpage) {
        $counter = 0;
        $setpage++;
    }
    if ($setpage == $page) {
        $items[$hit['id_product']][] = $hit;
    }
    if ($setpage > $page) {
        break;
    }
    $id_previous = $hit['id_product'];
    $counter++;
}

$json = new ProductJson($roihunterModule);
$jsonData = [];
while (list($id_product, $data) = each($items)) {
    foreach ($data as $item) {
        $jsonData[] = $json->getJson($id_product, $item['id_product_attribute'], $id_lang, $id_shop);
    }
}
header("HTTP/1.1 200 OK");
header("Content-Type:application/json");
echo json_encode($jsonData);
die();


