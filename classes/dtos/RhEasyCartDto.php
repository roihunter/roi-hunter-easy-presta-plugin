<?php

class RhEasyCartDto {

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
    public function __construct($cartItems, $totalPrice, $currency) {
        $this->cartItems = $cartItems;
        $this->totalPrice = $totalPrice;
        $this->currency = $currency;
    }

    public static function fromArray($cartItemsAsArray, $totalPrice, $currency) {

        $cartItems = [];
        if (isset($cartItemsAsArray)) {
            foreach ($cartItemsAsArray as $cartItem) {
                $product = $cartItem['product'];
                array_push($cartItems, new RhEasyCartItemDto(
                    new RhEasyProductDto(
                        $product['productId'],
                        $product['variantId'],
                        $product['name'],
                        $product['price'],
                        $product['currency']),
                    $cartItem['quantity']));
            }
        }

        return new RhEasyCartDto($cartItems, $totalPrice, $currency);
    }

    /**
     * @return array
     */
    public function getCartItems() {
        return $this->cartItems;
    }

    /**
     * @param array $cartItems
     */
    public function setCartItems($cartItems) {
        $this->cartItems = $cartItems;
        $this->computeTotalPrice();
    }

    /**
     * @return float
     */
    public function getTotalPrice() {
        return $this->totalPrice;
    }

    public function toJson() {
        return json_encode($this);
    }

    private function computeTotalPrice() {

        $this->totalPrice = 0;
        foreach ($this->getCartItems() as $cartItem) {  //RhEasyCartItemDto
            $this->totalPrice += $cartItem->getProduct()->getPrice() * $cartItem->getQuantity();
            $this->currency = $cartItem->getProduct()->getCurrency();
        }
    }
}