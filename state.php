<?php

// set module state from external source

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/auth/authentication.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/storage/storage.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/roihunter.php');

ROIHunterAuthenticator::getInstance()->authenticate();

$roihunterModule = Roihunter::getModuleInstance();

$id_shop = $roihunterModule->getShopFromUrl($_SERVER['HTTP_HOST']);
Context::getContext()->shop->id = $id_shop;

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $stream = Tools::file_get_contents('php://input');
    $data = json_decode($stream, true);

    if ($data[ROIHunterStorage::RH_SYSTEM_USER_ID] != null && !is_int($data[ROIHunterStorage::RH_SYSTEM_USER_ID])) {
        header('HTTP/1.0 400 Bad Request - id is not int.', true, 400);
        die();
    }

    ROIHunterStorage::getInstance()->setStorage($data);

    header("HTTP/1.1 200 OK");
    die();

} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $content = json_encode(ROIHunterStorage::getInstance()->getStorageWithoutTokens());
    header("HTTP/1.1 200 OK");
    header("Content-Type:application/json");
    echo($content);
    die();
} else {
    header('HTTP/1.0 405 Method Not Allowed', true, 405);
    die();
}
 
 
 
