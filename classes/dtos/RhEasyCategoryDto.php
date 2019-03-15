<?php

class RhEasyCategoryDto {

    //dto has public fields because json_encode()

    public $categoryId;
    public $products;

    public function __construct($categoryId, $products) {
        $this->categoryId = $categoryId;
        $this->products = $products;
    }

    public function toJson() {
        return json_encode($this);
    }
}