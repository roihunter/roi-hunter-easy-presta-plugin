<?php

class RhEasyOrderDto {

    //dto has public fields because json_encode()

    public $orderId;
    public $currency;
    public $products;
    public $totalValue;

    /**
     * RhEasyOrderDto constructor.
     * @param $orderId
     * @param $currency
     * @param $products array of RhEasyProductDto
     * @param $totalValue
     */
    public function __construct($orderId, $currency, $products, $totalValue) {
        $this->orderId = $orderId;
        $this->currency = $currency;
        $this->products = $products;
        $this->totalValue = $totalValue;
    }

    public static function fromPrestaShopOrderProducts($orderId, $currency, array $products, $totalRoundedPrice) {

        $rhEasyProducts = [];
        foreach ($products as $product) {
            $variantId = null;
            if ($product['product_attribute_id'] > 0) {
                $variantId = $product['product_attribute_id'];
            }
            array_push($rhEasyProducts, new RhEasyProductDto((int)$product['product_id'], $variantId));
        }

        return new RhEasyOrderDto($orderId, $currency, $rhEasyProducts, $totalRoundedPrice);
    }


    public function toJson() {
        return json_encode($this);
    }
}