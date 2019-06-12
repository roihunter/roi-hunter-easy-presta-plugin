<?php

class RhEasyOrderItemDto
{
    //dto has public fields because json_encode()

    public $product;
    public $quantity;

    /**
     * RhEasyOrderItemDto constructor.
     * @param $product RhEasyProductDto
     * @param $quantity int
     */
    public function __construct($product, $quantity)
    {
        $this->product = $product;
        $this->quantity = $quantity;
    }
}
