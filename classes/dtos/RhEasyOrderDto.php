<?php
/**
 * Order Dto
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

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/dtos/RhEasyOrderItemDto.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/dtos/RhEasyProductDto.php');

class RhEasyOrderDto
{

    //dto has public fields because json_encode()

    public $orderId;
    public $currency;
    public $orderItems;
    public $totalValue;

    /**
     * RhEasyOrderDto constructor.
     * @param $orderId
     * @param $currency
     * @param $orderItems array of RhEasyOrderItemDto
     * @param $totalValue
     */
    public function __construct($orderId, $currency, $orderItems, $totalValue)
    {
        $this->orderId = $orderId;
        $this->currency = $currency;
        $this->orderItems = $orderItems;
        $this->totalValue = $totalValue;
    }

    public static function fromPrestaShopOrderProducts($orderId, $currency, array $products, $totalRoundedPrice)
    {
        $rhEasyProducts = [];
        foreach ($products as $product) {
            $variantId = null;
            if ($product['product_attribute_id'] > 0) {
                $variantId = $product['product_attribute_id'];
            }
            array_push($rhEasyProducts, new RhEasyOrderItemDto(
                new RhEasyProductDto(
                    (int)$product['product_id'],
                    $variantId,
                    $product['product_name'],
                    $product['unit_price_tax_incl'],
                    $currency
                ),
                $product['product_quantity']
            ));
        }

        return new RhEasyOrderDto($orderId, $currency, $rhEasyProducts, $totalRoundedPrice);
    }

    public function toJson()
    {
        return json_encode($this);
    }
}
