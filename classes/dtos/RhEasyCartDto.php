<?php
/**
 * All products in Cart Dto
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

class RhEasyCartDto
{

    //dto has public fields because json_encode()

    public $cartItems;
    public $totalPrice;
    public $currency;

    /**
     * RhEasyCartDto constructor.
     * @param $cartItems array of RhEasyCartItemDto
     * @param $totalPrice double
     * @param $currency string
     */
    public function __construct($cartItems, $totalPrice, $currency)
    {
        $this->cartItems = $cartItems;
        $this->totalPrice = $totalPrice;
        $this->currency = $currency;
    }

    public static function fromArray($cartItemsAsArray, $totalPrice, $currency)
    {
        $cartItems = [];
        if (isset($cartItemsAsArray)) {
            foreach ($cartItemsAsArray as $cartItem) {
                $product = $cartItem['product'];
                array_push(
                    $cartItems,
                    new RhEasyCartItemDto(
                        new RhEasyProductDto(
                            $product['productId'],
                            $product['variantId'],
                            $product['name'],
                            $product['price'],
                            $product['currency']
                        ),
                        $cartItem['quantity']
                    )
                );
            }
        }

        return new RhEasyCartDto($cartItems, $totalPrice, $currency);
    }

    /**
     * @return array
     */
    public function getCartItems()
    {
        return $this->cartItems;
    }

    /**
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    public function toJson()
    {
        return json_encode($this);
    }

    private function computeTotalPrice()
    {
        if (!empty($this->cartItems)) {
            $this->currency = $this->cartItems[0]->getProduct()->getCurrency();
        }

        $this->totalPrice = 0;
        foreach ($this->cartItems as $cartItem) {  //RhEasyCartItemDto
            $this->totalPrice += $cartItem->getProduct()->getPrice() * $cartItem->getQuantity();
        }
    }

    public function addItemToCart(RhEasyCartItemDto $newRhCartItemDto)
    {
        $existingItem = $this->findExistingItem(
            $newRhCartItemDto->getProduct()->getProductId(),
            $newRhCartItemDto->getProduct()->getVariantId()
        );
        if ($existingItem != null) {
            $existingItem->setQuantity($existingItem->getQuantity() + $newRhCartItemDto->getQuantity());
        } else {
            array_push($this->cartItems, $newRhCartItemDto);
        }
        $this->computeTotalPrice();
    }

    public function findExistingItem($productId, $variantId)
    {
        foreach ($this->cartItems as $cartItem) {
            if ($cartItem->getProduct()->getProductId() == $productId && $cartItem->getProduct()->getVariantId() == $variantId) {
                return $cartItem;
            }
        }
        return null;
    }
}
