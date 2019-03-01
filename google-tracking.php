<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/auth/authentication.php');

ROIHunterAuthenticator::getInstance()->authenticate();

$instance = Module::getInstanceByName('roihunter');

$id_shop = $instance->getShopFromUrl($_SERVER['HTTP_HOST']);
Context::getContext()->shop->id = $id_shop;

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $roiHunterStorage = ROIHunterStorage::getInstance();
    $roiHunterStorage->setGoogleConversionId(null);
    $roiHunterStorage->setGoogleConversionLabel(null);
}

header("HTTP/1.1 200 OK");

die();

 