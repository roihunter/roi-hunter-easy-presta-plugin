<?php
/**
 * Order Item class
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
