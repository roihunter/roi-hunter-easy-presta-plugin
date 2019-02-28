<?php

// set module state from external source
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

$id_shop = $instance->getShopFromUrl($_SERVER['HTTP_HOST']);
Context::getContext()->shop->id = $id_shop;

if ($client_token != $instance->getClientToken()) { // token je jen jeden pro multishop
    header('HTTP/1.0 403 Forbidden', true, 403);
    die();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stream = file_get_contents('php://input');
    $data = json_decode($stream, true);
    $keys = $instance->getKeys();

    foreach ($keys as $key) {
        if (isset($data[$key])) {
            if ($key == 'id') {
                if (!(int)$data[$key]) {
                    header('HTTP/1.0 403 Forbidden', true, 403);
                    die();
                }
            }
            $instance->saveConfigFormValue($key, $data[$key], $id_shop);
        }
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
} else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $keys = $instance->getKeys();
    foreach ($keys as $key) {
        $instance->clearConfigFormValue($key, $id_shop);
    }
}
 
 
 
