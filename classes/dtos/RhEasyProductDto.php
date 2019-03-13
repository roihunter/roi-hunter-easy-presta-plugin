<?php

class RhEasyProductDto {

    private $productId;
    private $name;
    private $price;
    private $currency;

    public function __construct($productId, $name, $price, $currency) {
        $this->productId = $productId;
        $this->name = $name;
        $this->price = $price;
        $this->currency = $currency;
    }

    public function toJson() {
        return json_encode(get_object_vars($this));
    }
}