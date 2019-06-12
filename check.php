<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/auth/authentication.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/roihunter.php');

if (!Roihunter::isModuleLoaded()) {
    $content = [
        "prestashop_version" => "",
        "prestashop_mode" => "",
        "roihuntereasy_enabled" => false,
        "roihuntereasy_accounts" => 1,
        "roihuntereasy_version" => 0,
        "php_version" => "",
    ];
    $content = json_encode($content);

    header("HTTP/1.1 200 OK");
    header("Content-Type:application/json");
    echo($content);
    die();
}

ROIHunterAuthenticator::getInstance()->authenticate();

$roihunterModule = Roihunter::getModuleInstance();
$id_shop = $roihunterModule->getShopFromUrl($_SERVER['HTTP_HOST']);
Context::getContext()->shop->id = $id_shop;

// nelze pred contextem eshopu
$enabled = true;
if (!$roihunterModule->active || !Module::isEnabled('roihunter')) {
    $enabled = false;
}

$content = [
    "prestashop_version" => _PS_VERSION_,
    "prestashop_mode" => "",
    "roihuntereasy_enabled" => $enabled,
    "roihuntereasy_accounts" => 1,
    "roihuntereasy_version" => $roihunterModule->getPluginVersion(),
    "php_version" => phpversion(),
];
$content = json_encode($content);


header("HTTP/1.1 200 OK");
header("Content-Type:application/json");
echo($content);
die();
