<?php
/**
 * Contains DTOs classes needed for orders endpoint
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

class Order implements JsonSerializable {

    private $id;
    private $status;
    private $currency;
    private $price;
    private $created;

    public function __construct($id, $status, $currency, $price, $created)
    {
        $this->id = $id;
        $this->status = $status;
        $this->currency = $currency;
        $this->price = $price;
        $this->created = $created;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'status' => $this->status,
            'currency' => $this->currency,
            'price' => $this->price,
            'created' => $this->created
        );
    }
}

class OrderStatus {
    const CANCELLED = 'cancelled';
    const IN_PROGRESS = 'in_progress';
    const DELIVERED = 'delivered';

    private static $constantsCache = null;

    private $name;

    private function __construct($name)
    {
        if (!self::isValid($name)) {
            throw new InvalidArgumentException("Cannot construct OrderStatus object: invalid name: " . $name);
        }

        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function equals($name) {
        return $this->name === $name;
    }

    public static function getConstants()
    {
        if (self::$constantsCache === null) {
            $constants = (new ReflectionClass(get_called_class()))->getConstants();
            self::$constantsCache = array_map('strtolower', array_keys($constants));
        }

        return self::$constantsCache;
    }

    public static function isValid($name)
    {
        return in_array($name, self::getConstants());
    }

    public static function create($name)
    {
        if (self::isValid($name)) {
            return new OrderStatus($name);
        } else {
            return null;
        }
    }
}

class OrdersResponse implements JsonSerializable {

    private $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function jsonSerialize()
    {
        return array('orders' => $this->orders);
    }

    public function __toString()
    {
        return json_encode($this);
    }
}