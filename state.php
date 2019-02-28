<?php

// set module state from external source

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/auth/authentication.php');

ROIHunterAuthenticator::getInstance()->authenticate();

$instance = Module::getInstanceByName('roihunter');

$id_shop = $instance->getShopFromUrl($_SERVER['HTTP_HOST']);
Context::getContext()->shop->id = $id_shop;

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $stream = file_get_contents('php://input');
    $data = json_decode($stream, true);
    $keys = $instance->getKeys();

    foreach ($keys as $key) {
        if ($key == 'id') {
            if ($data[$key] != null && !is_int($data[$key])) {
                header('HTTP/1.0 400 Bad Request - id is not int.', true, 400);
                die();
            }
        }
        $instance->saveConfigFormValue($key, $data[$key], $id_shop);
    }

    header("HTTP/1.1 200 OK");
} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $keys = $instance->getKeys();
    $content = [];
    foreach ($keys as $key) {
        $content[$key] = $instance->getConfigFormValue($key, $id_shop);
    }
    $content = json_encode($content);
    header("HTTP/1.1 200 OK");
    header("Content-Type:application/json");
    echo($content);
    die();
} else {
    header('HTTP/1.0 405 Method Not Allowed', true, 405);
    die();
}
 
 
 
