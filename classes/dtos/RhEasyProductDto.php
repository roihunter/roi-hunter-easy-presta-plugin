<?php

class RhEasyProductDto {

    //dto has public fields because json_encode()

    public $productId;
    public $variantId;
    public $name;
    public $price;
    public $currency;

    public function __construct($productId, $variantId, $name = null, $price = null, $currency = null) {
        $this->productId = $productId;
        $this->variantId = $variantId;
        $this->name = $name;
        $this->price = $price;
        $this->currency = $currency;
    }

    public function toJson() {
        return json_encode($this);
    }
}