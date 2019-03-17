<?php

class RhEasyCartItemDto {

    //dto has public fields because json_encode()

    public $product;
    public $quantity;

    /**
     * RhEasyCartItemDto constructor.
     * @param $product RhEasyProductDto
     * @param $quantity int
     */
    public function __construct($product, $quantity) {
        $this->product = $product;
        $this->quantity = $quantity;
    }

    /**
     * @return RhEasyProductDto
     */
    public function getProduct() {
        return $this->product;
    }

    /**
     * @return int
     */
    public function getQuantity() {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity) {
        $this->quantity = $quantity;
    }

    public function toJson() {
        return json_encode($this);
    }
}