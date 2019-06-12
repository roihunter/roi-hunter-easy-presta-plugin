<?php

class RhEasyCategoryDto
{

    //dto has public fields because json_encode()

    public $categoryId;
    public $products;

    public function __construct($categoryId, $products)
    {
        $this->categoryId = $categoryId;
        $this->products = $products;
    }

    public static function fromPrestaShopCategoryProducts($categoryId, $products)
    {
        $rhEasyProducts = [];
        foreach ($products as $product) {
            $variantId = null;
            if ($product['id_product_attribute'] > 0) {
                $variantId = $product['id_product_attribute'];
            }
            array_push($rhEasyProducts, new RhEasyProductDto($product['id_product'], $variantId));
        }
        return new RhEasyCategoryDto($categoryId, $rhEasyProducts);
    }

    public function toJson()
    {
        return json_encode($this);
    }
}
