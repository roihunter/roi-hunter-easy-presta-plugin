<?php
/**
 * Page type Enums
 *
 * LICENSE: The buyer can free use/edit/modify this software in anyway
 * The buyer is NOT allowed to redistribute this module in anyway or resell it
 * or redistribute it to third party
 *
 * @author    ROI Hunter Easy
 * @copyright 2019 ROI Hunter
 * @license   EULA
 * @version   1.0
 * @link      https://easy.roihunter.com/
 */

abstract class EPageType
{
    const PRODUCT = 'PRODUCT';
    const CATEGORY = 'CATEGORY';
    const CART = 'CART';
    const ORDER_CONFIRMATION = 'ORDER_CONFIRMATION';
    const HOME_PAGE = 'HOME_PAGE';
    const UNKNOWN = 'UNKNOWN';

    public static function fromPrestaShopController($controllerValue)
    {
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
