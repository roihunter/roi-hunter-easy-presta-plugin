<?php
/**
 * Product in Cart Dto
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

class RhEasyCartItemDto
{

    //dto has public fields because json_encode()

    public $product;
    public $quantity;

    /**
     * RhEasyCartItemDto constructor.
     * @param $product RhEasyProductDto
     * @param $quantity int
     */
    public function __construct($product, $quantity)
    {
        $this->product = $product;
        $this->quantity = $quantity;
    }

    /**
     * @return RhEasyProductDto
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function toJson()
    {
        return json_encode($this);
    }
}
