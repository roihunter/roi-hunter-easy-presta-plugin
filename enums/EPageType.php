<?php

abstract class EPageType {

    const PRODUCT = 'PRODUCT';
    const CATEGORY = 'CATEGORY';
    const CART = 'CART';
    const ORDER_CONFIRMATION = 'ORDER_CONFIRMATION';
    const HOME_PAGE = 'HOME_PAGE';
    const UNKNOWN = 'UNKNOWN';

    public static function fromPrestaShopController($controllerValue) {
        switch ($controllerValue) {
            case 'product':
                return EPageType::PRODUCT;
            case 'category':
                return EPageType::CATEGORY;
            case 'order':
                return EPageType::CART;
            case 'orderconfirmation':
                return EPageType::ORDER_CONFIRMATION;
            case 'index':
                return EPageType::HOME_PAGE;
            default:
                return Tools::strtoupper($controllerValue);
        }
    }
}