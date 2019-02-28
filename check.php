<?php
// is module active ?
if ($_SERVER['HTTP_HOST'] == 'localhost:8080') {
    define('DIRECT_DEBUG', false);
}

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
$instance = Module::getInstanceByName('roihunter');

if (defined('DIRECT_DEBUG') && 'DIRECT_DEBUG' == true) {
    $client_token = $instance->getClientToken();
} else {
    $client_token = $_SERVER["HTTP_X_AUTHORIZATION"];

    if (empty($client_token)) {
        header('HTTP/1.0 403 Forbidden', true, 403);
        die();
    }
}

if ($instance == false) {
    $content = [
        "prestashop_version" => _PS_VERSION_,
        "prestashop_mode" => "",
        "roihuntereasy_enabled" => false,
        "roihuntereasy_accounts" => 1,
        "roihuntereasy_version" => 0,
        "php_version" => phpversion(),
    ];
    $content = json_encode($content);

    header("HTTP/1.1 200 OK");
    header("Content-Type:application/json");
    echo($content);
    die();
}


$id_shop = $instance->getShopFromUrl($_SERVER['HTTP_HOST']);
Context::getContext()->shop->id = $id_shop;

if ($client_token != $instance->getClientToken()) { // token je jen jeden pro multishop
    header('HTTP/1.0 403 Forbidden', true, 403);
    die();
}

// nelze pred contextem eshopu   
$enabled = true;
if (!$instance->active || !Module::isEnabled('roihunter')) {
    $enabled = false;
}

$content = [
    "prestashop_version" => _PS_VERSION_,
    "prestashop_mode" => "",
    "roihuntereasy_enabled" => $enabled,
    "roihuntereasy_accounts" => 1,
    "roihuntereasy_version" => $instance->getPluginVersion(),
    "php_version" => phpversion(),
];
$content = json_encode($content);


header("HTTP/1.1 200 OK");
header("Content-Type:application/json");
echo($content);
die();

 