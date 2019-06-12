<?php
/**
 * Products Category Dto
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
