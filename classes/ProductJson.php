<?php

class ProductJson {
    protected $product;
    protected $current_id_product = 0;
    protected $instance;
    protected $categoryCache = array();
    protected static $_separator = ' | ';

    public function __construct($instance) {
        $this->instance = $instance;
    }

    public function getJson($id_product, $id_product_attribute, $id_lang, $id_shop) {
        if ($id_product != $this->current_id_product) {
            $this->product = $this->getSingleProduct($id_product, $id_lang, $id_shop);
            $this->product['category'] = $this->getProductDefaultCategory($this->product['id_category_default'], $id_lang);
            $this->product['features'] = $this->getProductFeatures($id_product, $id_lang);
            $this->product['images'] = $this->getImages($id_product, $id_lang, $this->product);
            $this->product['url'] = $this->getProductUrl($this->product, $id_product_attribute, $id_lang, $id_shop);
            $this->current_id_product = $id_product;

        }
        $product = $this->product;
        $specific_price = array();
        $product['regular_price'] = Product::getPriceStatic($product['id_product'], true, (!empty($id_product_attribute) ? intval($product['id_product_attribute']) : NULL), 2, null, false, false, 1, false, null, null, null);
        $product['price'] = Product::getPriceStatic($product['id_product'], true, (!empty($id_product_attribute) ? intval($product['id_product_attribute']) : NULL), 2, null, false, true, 1, false, null, null, null, $specific_price);
        $product['currency'] = Context::getContext()->currency->iso_code;
        if ($id_product_attribute) {
            $product['attributes'] = $this->getProductAttributes($id_product, $id_product_attribute, $id_lang, $id_shop);
        }
        return $this->getJsonRow($product, $id_product_attribute);
    }


    public function getJsonRow($product, $id_product_attribute) {
        $retval = array();
        if ($id_product_attribute) {
            $retval['id'] = $product['id_product'] . '-' . $id_product_attribute;
        } else {
            $retval['id'] = $product['id_product'];
        }

        $map = array('date_created' => 'date_add', 'date_upd' => 'date_upd', 'date_modified' => 'date_modified',
            'price' => 'price', 'currency' =>'currency', 'visible' => 'active', 'purchasable' => 'available_for_order', 'virtual' => 'is_virtual',
            'stock_quantity' => 'quantity', 'weight' => 'weight', 'name' => 'name', 'category' => 'category');
        while (list($outkey, $inkey) = each($map)) {
            $retval[$outkey] = $product[$inkey];
        }

        $retval['regular_price'] = $product['regular_price'];
        if ($id_product_attribute && isset($product['attributes'][$id_product_attribute])) {

            //$retval['regular_price'] += $product['attributes'][$id_product_attribute]['price'];
            $retval['attributes'] = array();
            while (list($key, $val) = each($product['attributes'][$id_product_attribute]['attributes'])) {
                $retval['attributes'][] = array('id' => $val[4], 'name' => $val[0], 'option' => $val[1]);
            }
        }
        $keys = array('available_date', 'available_now', 'available_later', 'condition', 'ean13');
        foreach ($keys as $key) {
            $retval[$key] = $product[$key];
        }


        $description = strlen($product['description_short'] > 0) ? $product['description_short'] : $product['description'];
        $retval['description'] = $this->createDescription($description);

        $retval['permalink'] = $this->getProductUrlWithVariants($product, $id_product_attribute);

        $cover = 0;
        $nextbestcover = 0;
        $defaultcover = 0;
        while (list($key, $image) = each($product['images'])) {
            if ($image['cover'] && in_array($id_product_attribute, $image['attributes'])) {
                $cover = $key;
            }
            if (in_array($id_product_attribute, $image['attributes'])) {
                $nextbestcover = $key;
            }
            if ($image['cover']) {
                $defaultcover = $key;
            }
        }

        if ((int)$cover == 0) {
            if ($nextbestcover)
                $cover = $nextbestcover;
            else
                $cover = $defaultcover;
        }

        $retval['image'] = array('id' => $cover, 'src' => $product['images'][$cover]['url']);
        reset($product['images']);
        while (list($key, $image) = each($product['images'])) {
            if ($key != $cover && ($id_product_attribute == 0 || in_array($id_product_attribute, $image['attributes']))) {
                $retval['additional_images'] = array('id' => $key, 'src' => $image['url']);
            }
        }

        return $retval;
    }

    private function createDescription($description) {
        $description = str_replace(array(',', '.', '>'), array(', ', '. ', '> '), $description);
        $description = strip_tags($description);
        return trim($description);
    }

    private function getProductUrl($product, $id_product_attribute, $id_lang, $id_shop) {
        $placeholder = null;
        if (Tools::version_compare(_PS_VERSION_, '1.7', '>') && (int)$id_product_attribute) {
            $placeholder = '[ipa]';
        }

        $url = Context::getContext()->link->getproductLink($product['id_product'], $product['link_rewrite'], null, null, $id_lang, null, $placeholder);
        $url = str_replace('#', '', $url);
        return $url;
    }

    private function getProductUrlWithVariants($product, $id_product_attribute) {
        $retval = $product['url'];
        if ((int)$id_product_attribute) {
            if (Tools::version_compare(_PS_VERSION_, '1.7', '>')) {
                $url = str_replace('[ipa]', $id_product_attribute, $url);
            }
            $retval .= '#';
            $attributes = $product['attributes'][$id_product_attribute]['attributes'];

            foreach ($attributes as $attribute) {
                if ($attribute[4]) {
                    $retval .= '/' . $attribute[4] . Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR') . $attribute[2] . Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR') . $attribute[3];
                } else {
                    $retval .= '/' . $attribute[2] . Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR') . $attribute[3];
                }

            }
        }
        return $retval;
    }

    private function getImages($id_product, $id_lang, $product) {
        $retval = array();
        $link = Context::getContext()->link;
        $sql = 'SELECT i.id_image, i.id_product, i.cover,il.legend,ai.id_product_attribute
            FROM `' . _DB_PREFIX_ . 'image` i
            LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image`)';
        $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_image` ai ON (i.`id_image` = ai.`id_image`)';

        $sql .= ' WHERE i.`id_product` = ' . (int)$id_product . ' AND il.`id_lang` = ' . (int)$id_lang . '
        ORDER BY i.`position` ASC';
        $images = Db::getInstance()->executeS($sql);

        foreach ($images as $image) {
            $name = $this->toUrl(empty($image['legend']) ? $product['name'] : $image['legend']);
            if (!isset($retval[$image['id_image']])) {
                $retval[$image['id_image']] = array(
                    'cover' => $image['cover'],
                    'url' => $link->getImageLink($name, $product['id_product'] . '-' . $image['id_image'], $this->instance->getImageType()),
                    'attributes' => array((int)$image['id_product_attribute']));
            } else {
                $retval[$image['id_image']]['attributes'][] = (int)$image['id_product_attribute'];
            }
        }
        return $retval;
    }

    private function toUrl($s) {
        if (empty($s))
            return '';
        $s = $this->cs_utf2ascii($s);
        $s = strtolower($s);
        $s = preg_replace('~[^-a-z0-9_ ]+~', '', $s);
        return str_replace(" ", "-", $s);
    }

    private function cs_utf2ascii($s) {
        static $tbl = array("\xc3\xa1" => "a", "\xc3\xa4" => "a", "\xc4\x8d" => "c", "\xc4\x8f" => "d", "\xc3\xa9" => "e", "\xc4\x9b" => "e", "\xc3\xad" => "i", "\xc4\xbe" => "l", "\xc4\xba" => "l", "\xc5\x88" => "n", "\xc3\xb3" => "o", "\xc3\xb6" => "o", "\xc5\x91" => "o", "\xc3\xb4" => "o", "\xc5\x99" => "r", "\xc5\x95" => "r", "\xc5\xa1" => "s", "\xc5\xa5" => "t", "\xc3\xba" => "u", "\xc5\xaf" => "u", "\xc3\xbc" => "u", "\xc5\xb1" => "u", "\xc3\xbd" => "y", "\xc5\xbe" => "z", "\xc3\x81" => "A", "\xc3\x84" => "A", "\xc4\x8c" => "C", "\xc4\x8e" => "D", "\xc3\x89" => "E", "\xc4\x9a" => "E", "\xc3\x8d" => "I", "\xc4\xbd" => "L", "\xc4\xb9" => "L", "\xc5\x87" => "N", "\xc3\x93" => "O", "\xc3\x96" => "O", "\xc5\x90" => "O", "\xc3\x94" => "O", "\xc5\x98" => "R", "\xc5\x94" => "R", "\xc5\xa0" => "S", "\xc5\xa4" => "T", "\xc3\x9a" => "U", "\xc5\xae" => "U", "\xc3\x9c" => "U", "\xc5\xb0" => "U", "\xc3\x9d" => "Y", "\xc5\xbd" => "Z");
        return strtr($s, $tbl);
    }

    private function getSingleProduct($id_product, $id_lang, $id_shop) {
        $sql = 'SELECT p.*, product_shop.*, pl.* , m.`name` AS manufacturer_name, s.`name` AS supplier_name
            FROM `' . _DB_PREFIX_ . 'product` p
            ' . Shop::addSqlAssociation('product', 'p') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON (s.`id_supplier` = p.`id_supplier`) 
    
            WHERE pl.`id_lang` = ' . (int)$id_lang . ' AND  p.id_product =' . (int)$id_product;

        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if ($rq && is_array($rq)) {
            foreach ($rq as &$row)
                $row = Product::getTaxesInformations($row);

            return ($rq[0]);
        }
        return;
    }

    private function getProductAttributes($id_product, $id_product_attribute, $id_lang, $id_shop, $debug = false) {
        $id_shop = Context::getContext()->shop->id;
        $id_shop_group = Shop::getGroupFromShop($id_shop);
        $Group = new ShopGroup($id_shop_group);


        $sql = 'SELECT pa.id_product_attribute, pa.id_product,pa.available_date, pa.price,
            pa.reference, pa.ean13,  pa.weight,
            ag.`id_attribute_group`,  agl.`name` AS group_name,     al.`name` AS attribute_name,   agl.`public_name` AS group_pname, 
            a.`id_attribute`,s.quantity, ai.id_image 
            FROM `' . _DB_PREFIX_ . 'product_attribute` pa
            ' . Shop::addSqlAssociation('product_attribute', 'pa') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int)$id_lang . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int)$id_lang . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'stock_available s on (pa.id_product=s.id_product AND pa.id_product_attribute=s.id_product_attribute AND ';

        $sql .= $Group->share_stock == 1 ? 's.id_shop_group=' . $id_shop_group : 's.id_shop=' . $id_shop;
        $sql .= ') LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_image ai ON ai.`id_product_attribute` = pa.`id_product_attribute` 
            WHERE pa.`id_product` = ' . (int)$id_product . ' AND pa.id_product_attribute = ' . $id_product_attribute . '   
            GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
            ORDER BY pa.`id_product_attribute` LIMIT 100';

        $combinations = Db::getInstance()->executeS($sql);

        $comb_array = array();

        if (is_array($combinations)) {

            $layered = false;
            $uselayered = true;

            if (Module::isInstalled('blocklayered') && Module::isEnabled('blocklayered') && $uselayered) {
                $in = '';
                $carka = '';
                foreach ($combinations as $combination) {
                    if ((int)$combination['id_attribute']) {
                        $in .= $carka . $combination['id_attribute'];
                        $carka = ',';
                    }
                }
                $layered = array();
                if (strlen($in)) {
                    $sql = 'SELECT    id_attribute, url_name, meta_title FROM ' . _DB_PREFIX_ . 'layered_indexable_attribute_lang_value  WHERE 
                        id_lang =' . (int)$id_lang . ' AND id_attribute IN (' . $in . ')';
                    $res = Db::getInstance()->executeS($sql);
                    if ($res && is_array($res))
                        foreach ($res as $atr) {
                            if (!empty($atr['meta_title'])) {
                                $layered[$atr['id_attribute']] = $atr['meta_title'];
                            } elseif ($uselayered == 2) {
                                $layered[$atr['id_attribute']] = $atr['url_name'];
                            }
                        }
                }

                reset($combinations);
            }

            foreach ($combinations as $combination) {

                $attribute_url = (is_array($layered) && isset($layered[$combination['id_attribute']]) && strlen($layered[$combination['id_attribute']])) ? $layered[$combination['id_attribute']] : self::friendlyAttribute($combination['attribute_name']);

                $comb_array[$combination['id_product_attribute']]['id_product_attribute'] = $combination['id_product_attribute'];
                if (Configuration::get('ZBOZI_ATTR_PUBLIC')) {
                    $comb_array[$combination['id_product_attribute']]['attributes'][] = array($combination['group_pname'], $combination['attribute_name'], self::friendlyAttribute($combination['group_name']), $attribute_url, $combination['id_attribute'], $combination['id_attribute_group']);
                } else {
                    $comb_array[$combination['id_product_attribute']]['attributes'][] = array($combination['group_name'], $combination['attribute_name'], self::friendlyAttribute($combination['group_name']), $attribute_url, $combination['id_attribute'], $combination['id_attribute_group']);
                }

                $comb_array[$combination['id_product_attribute']]['price'] = $combination['price'];
                $comb_array[$combination['id_product_attribute']]['reference'] = $combination['reference'];
                $comb_array[$combination['id_product_attribute']]['ean13'] = $combination['ean13'];
                $comb_array[$combination['id_product_attribute']]['weight'] = $combination['weight'];
                //    $comb_array[$combination['id_product_attribute']]['id_image'] = isset($combination_images[$combination['id_product_attribute']][0]['id_image']) ? $combination_images[$combination['id_product_attribute']][0]['id_image'] : 0;
                $comb_array[$combination['id_product_attribute']]['available_date'] = strftime($combination['available_date']);
                $comb_array[$combination['id_product_attribute']]['quantity'] = $combination['quantity'];
                $comb_array[$combination['id_product_attribute']]['id_product'] = $combination['id_product'];
                $comb_array[$combination['id_product_attribute']]['id_image'] = $combination['id_image'];
            }
        }

        return $comb_array;
    }

    private function friendlyAttribute($val) {
        $val = str_replace(Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR'), '_', Tools::link_rewrite(str_replace(array(',', '.'), '-', $val)));
        return $val;
    }

    private function getProductFeatures($id_product, $id_lang) {
        $features = Product::getFrontFeaturesStatic($id_lang, $id_product);
        return $features;
    }

    private function getProductDefaultCategory($id_category, $id_lang) {
        if (!isset($this->categoryCache[$id_category])) {
            $cats = array();
            $cats[] = $id_category;
            $this->getRecursiveCats($id_category, $cats);
            $this->categoryCache[$id_category] = $this->translateCats($cats, $id_lang);
        }
        return empty($this->categoryCache[$id_category]) ? "" : $this->categoryCache[$id_category];
    }

    private function translateCats($cats, $id_lang) {
        $retval = array();
        if (is_array($cats)) {
            $cats = array_reverse($cats);
            foreach ($cats as $cat) {
                $sql = 'SELECT name FROM ' . _DB_PREFIX_ . 'category_lang WHERE id_lang=' . (int)$id_lang . ' AND id_category =' . $cat;
                $name = Db::getInstance()->getValue($sql);
                if ($name && strlen($name)) {
                    $retval[] = $name;
                }
            }

        }
        if (count($retval)) {
            return implode(self::$_separator, $retval);
        }
        return '';
    }

    private function getRecursiveCats($id_category, &$cats) {
        $sql = 'SELECT id_parent, is_root_category FROM ' . _DB_PREFIX_ . 'category WHERE id_category = ' . (int)$id_category;
        $row = Db::getInstance()->getRow($sql);
        if ((int)$row['id_parent'] && $row['is_root_category'] == 0) {
            $cats[] = $row['id_parent'];
            $this->getRecursiveCats($row['id_parent'], $cats);
        }
    }

}
 
