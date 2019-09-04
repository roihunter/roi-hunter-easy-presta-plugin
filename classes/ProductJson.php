<?php
/**
 * Generate products to JSON format
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

class ProductJson
{
    protected $product;
    protected $current_id_product = 0;
    protected $imageType;
    protected $categoryCache = [];

    public function __construct($imageType)
    {
        $this->imageType = $imageType;
    }

    public function getJson($id_product, $id_product_attribute, $id_lang, $id_shop)
    {
        if ($id_product != $this->current_id_product) {
            $this->product = $this->getSingleProduct($id_product, $id_lang, $id_shop);
            if (!$this->product) {
                return null;
            }
            $this->product['category'] = $this->getProductDefaultCategory(
                $this->product['id_category_default'],
                $id_lang
            );
            $this->product['features'] = $this->getProductFeatures($id_product, $id_lang);
            $this->product['images'] = $this->getImages($id_product, $id_lang, $this->product);
            $this->product['url'] = $this->getProductUrl($this->product, $id_product_attribute, $id_lang, $id_shop);
            $this->current_id_product = $id_product;
        }
        $product = $this->product;
        $specific_price = [];
        $product['regular_price'] = Product::getPriceStatic(
            $product['id_product'],
            true,
            $id_product_attribute,
            2,
            null,
            false,
            false,
            1,
            false,
            null,
            null,
            null
        );
        $product['price'] = Product::getPriceStatic(
            $product['id_product'],
            true,
            $id_product_attribute,
            2,
            null,
            false,
            true,
            1,
            false,
            null,
            null,
            null,
            $specific_price
        );
        $product['currency'] = Context::getContext()->currency->iso_code;
        if ($id_product_attribute) {
            $product['attributes'] = $this->getProductAttributes(
                $id_product,
                $id_product_attribute,
                $id_lang,
                $id_shop
            );
        }
        return $this->getJsonRow($product, $id_product_attribute);
    }

    private function getSingleProduct($id_product, $id_lang)
    {
        $sql = 'SELECT p.*, product_shop.*, pl.* , m.`name` AS manufacturer_name, s.`name` AS supplier_name, sa.quantity
            FROM `' . _DB_PREFIX_ . 'product` p
            ' . Shop::addSqlAssociation('product', 'p') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl 
             ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON (s.`id_supplier` = p.`id_supplier`) 
            LEFT JOIN ' . _DB_PREFIX_ . 'stock_available AS sa on (p.id_product=sa.id_product) 
        
            WHERE pl.`id_lang` = ' . (int)$id_lang . ' AND  p.id_product =' . (int)$id_product;

        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if ($rq && is_array($rq)) {
            foreach ($rq as &$row) {
                $row = Product::getTaxesInformations($row);
            }

            return ($rq[0]);
        }
        return null;
    }

    private function getProductDefaultCategory($idCategory, $idLanguage)
    {
        if (!isset($this->categoryCache[$idCategory])) {
            $this->categoryCache[$idCategory] = $this->translateCategory($idCategory, $idLanguage);
        }
        return empty($this->categoryCache[$idCategory]) ? "" : $this->categoryCache[$idCategory];
    }

    private function translateCategory($idCategory, $idLanguage)
    {
        $dbQuery = (new DbQuery())
            ->select('name')
            ->from('category_lang')
            ->where('id_lang = '.$idLanguage)
            ->where('id_category = '.$idCategory);

        $categoryName = Db::getInstance()->getValue($dbQuery);
        return $categoryName;
    }

    private function getProductFeatures($id_product, $id_lang)
    {
        $features = Product::getFrontFeaturesStatic($id_lang, $id_product);
        return $features;
    }

    private function getImages($id_product, $id_lang, $product)
    {
        $retval = [];
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
                $retval[$image['id_image']] = [
                    'cover' => $image['cover'],
                    'url' => $link->getImageLink(
                        $name,
                        $product['id_product'] . '-' . $image['id_image'],
                        $this->imageType
                    ),
                    'attributes' => [(int)$image['id_product_attribute']]];
            } else {
                $retval[$image['id_image']]['attributes'][] = (int)$image['id_product_attribute'];
            }
        }
        return $retval;
    }

    private function toUrl($s)
    {
        if (empty($s)) {
            return '';
        }
        $s = $this->csUtf2ascii($s);
        $s = Tools::strtolower($s);
        $s = preg_replace('~[^-a-z0-9_ ]+~', '', $s);
        return str_replace(" ", "-", $s);
    }

    private function csUtf2ascii($s)
    {
        static $tbl = [
            "\xc3\xa1" => "a",
            "\xc3\xa4" => "a",
            "\xc4\x8d" => "c",
            "\xc4\x8f" => "d",
            "\xc3\xa9" => "e",
            "\xc4\x9b" => "e",
            "\xc3\xad" => "i",
            "\xc4\xbe" => "l",
            "\xc4\xba" => "l",
            "\xc5\x88" => "n",
            "\xc3\xb3" => "o",
            "\xc3\xb6" => "o",
            "\xc5\x91" => "o",
            "\xc3\xb4" => "o",
            "\xc5\x99" => "r",
            "\xc5\x95" => "r",
            "\xc5\xa1" => "s",
            "\xc5\xa5" => "t",
            "\xc3\xba" => "u",
            "\xc5\xaf" => "u",
            "\xc3\xbc" => "u",
            "\xc5\xb1" => "u",
            "\xc3\xbd" => "y",
            "\xc5\xbe" => "z",
            "\xc3\x81" => "A",
            "\xc3\x84" => "A",
            "\xc4\x8c" => "C",
            "\xc4\x8e" => "D",
            "\xc3\x89" => "E",
            "\xc4\x9a" => "E",
            "\xc3\x8d" => "I",
            "\xc4\xbd" => "L",
            "\xc4\xb9" => "L",
            "\xc5\x87" => "N",
            "\xc3\x93" => "O",
            "\xc3\x96" => "O",
            "\xc5\x90" => "O",
            "\xc3\x94" => "O",
            "\xc5\x98" => "R",
            "\xc5\x94" => "R",
            "\xc5\xa0" => "S",
            "\xc5\xa4" => "T",
            "\xc3\x9a" => "U",
            "\xc5\xae" => "U",
            "\xc3\x9c" => "U",
            "\xc5\xb0" => "U",
            "\xc3\x9d" => "Y",
            "\xc5\xbd" => "Z"
        ];
        return strtr($s, $tbl);
    }

    private function getProductUrl($product, $id_product_attribute, $id_lang)
    {
        $placeholder = null;
        if (Tools::version_compare(_PS_VERSION_, '1.7', '>') && (int)$id_product_attribute) {
            $placeholder = '[ipa]';
        }

        $url = Context::getContext()->link->getproductLink(
            $product['id_product'],
            $product['link_rewrite'],
            null,
            null,
            $id_lang,
            null,
            $placeholder
        );
        $url = str_replace('#', '', $url);
        return $url;
    }

    private function getProductAttributes($id_product, $id_product_attribute, $id_lang)
    {
        $id_shop = Context::getContext()->shop->id;
        $id_shop_group = Shop::getGroupFromShop($id_shop);
        $Group = new ShopGroup($id_shop_group);

        $sql = 'SELECT pa.id_product_attribute, pa.id_product,pa.available_date, pa.price,
            pa.reference, pa.ean13,  pa.weight,
            ag.`id_attribute_group`,  agl.`name` AS group_name, al.`name` AS attribute_name,
            agl.`public_name` AS group_pname, 
            a.`id_attribute`,s.quantity, ai.id_image 
            FROM `' . _DB_PREFIX_ . 'product_attribute` pa
            ' . Shop::addSqlAssociation('product_attribute', 'pa') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac
             ON pac.`id_product_attribute` = pa.`id_product_attribute`
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al
             ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int)$id_lang . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl
             ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int)$id_lang . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'stock_available s
             ON (pa.id_product=s.id_product AND pa.id_product_attribute=s.id_product_attribute AND ';

        $sql .= $Group->share_stock == 1 ? 's.id_shop_group=' . $id_shop_group : 's.id_shop=' . $id_shop;
        $sql .= ') LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_image ai
             ON ai.`id_product_attribute` = pa.`id_product_attribute` 
            WHERE pa.`id_product` = ' . (int)$id_product . ' AND pa.id_product_attribute = ' . $id_product_attribute . '
            GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
            ORDER BY pa.`id_product_attribute` LIMIT 100';

        $combinations = Db::getInstance()->executeS($sql);
        $comb_array = [];

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
                $layered = [];
                if (Tools::strlen($in)) {
                    $sql = 'SELECT    id_attribute, url_name, meta_title
                        FROM ' . _DB_PREFIX_ . 'layered_indexable_attribute_lang_value
                        WHERE id_lang =' . (int)$id_lang . ' AND id_attribute IN (' . $in . ')';
                    $res = Db::getInstance()->executeS($sql);
                    if ($res && is_array($res)) {
                        foreach ($res as $atr) {
                            if (!empty($atr['meta_title'])) {
                                $layered[$atr['id_attribute']] = $atr['meta_title'];
                            } elseif ($uselayered == 2) {
                                $layered[$atr['id_attribute']] = $atr['url_name'];
                            }
                        }
                    }
                }

                reset($combinations);
            }

            foreach ($combinations as $combination) {
                $attribute_url = (is_array($layered) && isset($layered[$combination['id_attribute']])
                    && Tools::strlen($layered[$combination['id_attribute']]))
                    ? $layered[$combination['id_attribute']] : self::friendlyAttribute($combination['attribute_name']);

                $comb_array[
                    $combination['id_product_attribute']
                ]['id_product_attribute'] = $combination['id_product_attribute'];
                if (Configuration::get('ZBOZI_ATTR_PUBLIC')) {
                    $comb_array[$combination['id_product_attribute']]['attributes'][] = [
                        $combination['group_pname'],
                        $combination['attribute_name'],
                        self::friendlyAttribute($combination['group_name']),
                        $attribute_url,
                        $combination['id_attribute'],
                        $combination['id_attribute_group']
                    ];
                } else {
                    $comb_array[$combination['id_product_attribute']]['attributes'][] = [
                        $combination['group_name'],
                        $combination['attribute_name'],
                        self::friendlyAttribute($combination['group_name']),
                        $attribute_url,
                        $combination['id_attribute'],
                        $combination['id_attribute_group']
                    ];
                }

                $comb_array[$combination['id_product_attribute']]['price'] = $combination['price'];
                $comb_array[$combination['id_product_attribute']]['reference'] = $combination['reference'];
                $comb_array[$combination['id_product_attribute']]['ean13'] = $combination['ean13'];
                $comb_array[$combination['id_product_attribute']]['weight'] = $combination['weight'];
                $comb_array[$combination['id_product_attribute']]['available_date'] = strftime(
                    $combination['available_date']
                );
                $comb_array[$combination['id_product_attribute']]['quantity'] = $combination['quantity'];
                $comb_array[$combination['id_product_attribute']]['id_product'] = $combination['id_product'];
                $comb_array[$combination['id_product_attribute']]['id_image'] = $combination['id_image'];
            }
        }

        return $comb_array;
    }

    private function friendlyAttribute($val)
    {
        $val = str_replace(
            Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR'),
            '_',
            Tools::link_rewrite(str_replace([',', '.'], '-', $val))
        );
        return $val;
    }

    public function getJsonRow($product, $id_product_attribute)
    {
        $retval = [];
        if ($id_product_attribute) {
            $retval['id'] = $product['id_product'] . '-' . $id_product_attribute;
        } else {
            $retval['id'] = $product['id_product'];
        }

        $map = [
            'date_created' => 'date_add',
            'date_upd' => 'date_upd',
            'price' => 'price',
            'currency' => 'currency',
            'visible' => 'active',
            'purchasable' => 'available_for_order',
            'virtual' => 'is_virtual',
            'weight' => 'weight',
            'name' => 'name',
            'category' => 'category'
        ];
        while (list($outkey, $inkey) = each($map)) {
            $retval[$outkey] = $product[$inkey];
        }

        $retval['regular_price'] = $product['regular_price'];
        if ($id_product_attribute && isset($product['attributes'][$id_product_attribute])) {
            $retval['attributes'] = [];
            while (list($key, $val) = each($product['attributes'][$id_product_attribute]['attributes'])) {
                $retval['attributes'][] = ['id' => $val[4], 'name' => $val[0], 'option' => $val[1]];
            }
        }
        $keys = ['available_date', 'available_now', 'available_later', 'condition', 'ean13'];
        foreach ($keys as $key) {
            $retval[$key] = $product[$key];
        }

        $description = Tools::strlen($product['description_short'] > 0)
            ? $product['description_short']
            : $product['description'];
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
            if ($nextbestcover) {
                $cover = $nextbestcover;
            } else {
                $cover = $defaultcover;
            }
        }

        if (isset($product['images']) && !empty($product['images'])) {
            $retval['image'] = ['id' => $cover, 'src' => $product['images'][$cover]['url']];
        }
        reset($product['images']);
        while (list($key, $image) = each($product['images'])) {
            if ($key != $cover && ($id_product_attribute == 0 ||
                    in_array($id_product_attribute, $image['attributes']))
            ) {
                $retval['additional_images'] = ['id' => $key, 'src' => $image['url']];
            }
        }

        $retval['stock_quantity'] = $this->getProductQuantity($product, $id_product_attribute);

        return $retval;
    }

    private function createDescription($description)
    {
        $description = str_replace([',', '.', '>'], [', ', '. ', '> '], $description);
        $description = strip_tags($description);
        return trim($description);
    }

    private function getProductUrlWithVariants($product, $id_product_attribute)
    {
        $retval = $product['url'];
        if ((int)$id_product_attribute) {
            if (Tools::version_compare(_PS_VERSION_, '1.7', '>')) {
                $retval = str_replace('[ipa]', $id_product_attribute, $retval);
            }
            $retval .= '#';
            $attributes = $product['attributes'][$id_product_attribute]['attributes'];

            foreach ($attributes as $attribute) {
                if ($attribute[4]) {
                    $retval .= '/'
                        . $attribute[4]
                        . Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR')
                        . $attribute[2]
                        . Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR')
                        . $attribute[3];
                } else {
                    $retval .= '/'
                        . $attribute[2]
                        . Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR')
                        . $attribute[3];
                }
            }
        }
        return $retval;
    }

    private function getProductQuantity($product, $id_product_attribute)
    {
        if (isset($id_product_attribute)) { // product combination
            return $product['attributes'][$id_product_attribute]['quantity'];
        } else {
            return $product['quantity'];    // regular product
        }
    }
}
