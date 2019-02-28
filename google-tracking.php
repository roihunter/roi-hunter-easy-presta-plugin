<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
$instance = Module::getInstanceByName('roihunter');

$client_token = $_SERVER["HTTP_X_AUTHORIZATION"];
if (empty($client_token)) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}

if ($instance == false) {
    header('HTTP/1.1 403 Forbidden');
    header('HTTP/1.0 403 Forbidden', true, 403);
    die();
}


$id_shop = $instance->getShopFromUrl($_SERVER['HTTP_HOST']);
Context::getContext()->shop->id = $id_shop;

if ($client_token != $instance->getClientToken()) { // token je jen jeden pro multishop
    header('HTTP/1.1 403 Forbidden');
    header('HTTP/1.0 403 Forbidden', true, 403);
    die();
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $keys = ['google_conversion_id', 'google_conversion_label'];
    foreach ($keys as $key) {
        $instance->clearConfigFormValue($key, $id_shop);
    }
}

header("HTTP/1.1 200 OK");

die();

 