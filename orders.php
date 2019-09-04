<?php
/**
 * Orders endpoint
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

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/auth/authentication.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/roihunter.php');
require_once(_PS_MODULE_DIR_ . 'roihunter/classes/requests/OrdersDtos.php');

const DEFAULT_PAGE = 1;
function getPageParameter() {
    $page = Tools::getValue('page', DEFAULT_PAGE);
    if (!is_numeric($page)) {
        header("HTTP/1.1 400 Bad Request");
        echo "Page parameter is not valid.";
        die();
    } else if ((int) $page < 1) {
        header("HTTP/1.1 400 Bad Request");
        echo "Page parameter cannot be less than 1.";
        die();
    } else {
        return (int) $page;
    }
}

function getCreatedFromTimeParameter() {
    $createdFrom = Tools::getValue('created_from', null);
    if (is_null($createdFrom) || empty($createdFrom)) {
        return null;
    } else {
        $datetime = DateTime::createFromFormat('Y-m-d\TH:i:s+', $createdFrom);
        if ($datetime instanceof DateTime) {
            return $datetime;
        } else {
            header("HTTP/1.1 400 Bad Request");
            echo "created_from parameter must be in format Y-m-d'T'H:i:s+ for example (2019-08-29T12:01:45.163669)";
            die();
        }
    }
}

/**
 * List of available order statuses values separated by comma
 * see available values at OrderStatus
 */
function getStatusParameter() {
    $orderStatuses = Tools::getValue('status', array());
    $statuses = array();
    if (is_null($orderStatuses) || empty($orderStatuses)) {
        return $statuses;
    } else {
        foreach (explode(",", $orderStatuses) as $statusName) {
            $statusObject = OrderStatus::create($statusName);
            if ($statusObject === null) {
                header("HTTP/1.1 400 Bad Request");
                echo "Order status name must be one of the following: " . join(", ", OrderStatus::getConstants());
                die();
            } else {
                $statuses[] = $statusObject;
            }
        }
        return array_unique($statuses);
    }
}

const DEFAULT_LIMIT = 100;

/**
 * @param $page
 * @param DateTime|null $createdFromTime
 * @param array $statuses
 * @return array of Orders
 * @throws PrestaShopDatabaseException
 */
function getOrders($page, DateTime $createdFromTime = null, $statuses = array()) {
    $query = (new DbQuery())
        ->select('o.id_order, c.iso_code, o.total_products, s.delivery, s.shipped, s.paid, o.date_add')
        ->from('orders', 'o')
        ->innerJoin('order_state', 's', 'o.current_state = s.id_order_state')
        ->innerJoin('currency', 'c', 'o.id_currency = c.id_currency');

    $whereRestriction = 's.deleted = false and o.id_shop = ' . Context::getContext()->shop->id;
    if (!is_null($createdFromTime)) {
        $whereRestriction .= ' and o.date_add >= ' . $createdFromTime->format('\'Y-m-d H:i:s\'');
    }

    if (!empty($statuses)) {
        $statusesRestrictions = mapOrderStatusesToSqlCondition($statuses);
        $whereRestriction .= ' and (' . join(' or ', $statusesRestrictions) . ')';
    }

    $query
        ->where($whereRestriction)
        ->limit(DEFAULT_LIMIT, ($page - 1)*DEFAULT_LIMIT)
        ->orderBy('o.date_add ASC');

    return mapSqlGetOrdersResultToOrdersDtos(Db::getInstance()->executeS($query));
}

function mapSqlGetOrdersResultToOrdersDtos($result) {
    return array_map(function ($resultEntry) {
        if ($resultEntry['delivery'] && $resultEntry['shipped'] && $resultEntry['paid']) {
            $status = OrderStatus::DELIVERED;
        } else if (!$resultEntry['delivery'] && !$resultEntry['shipped'] && !$resultEntry['paid']) {
            $status = OrderStatus::CANCELLED;
        } else if (!$resultEntry['shipped'] && $resultEntry['paid']) {
            $status = OrderStatus::IN_PROGRESS;
        } else {
            $status = 'unknown: (delivery, shipped, paid) = '
                . '(' . join(', ', array($resultEntry['delivery'], $resultEntry['shipped'], $resultEntry['paid'])) . ')';
        }

        return new Order($resultEntry['id_order'], $status, $resultEntry['iso_code'], $resultEntry['total_products'], $resultEntry['date_add']);
    }, $result);
}

function mapOrderStatusesToSqlCondition(array $statuses) {
    return array_map(function ($status) {
        if ($status->equals(OrderStatus::DELIVERED)) {
            return '(s.delivery = true and s.shipped = true and s.paid = true)';
        } else if ($status->equals(OrderStatus::IN_PROGRESS)) {
            return '(s.shipped = false and s.paid = true)';
        } else if ($status->equals(OrderStatus::CANCELLED)) {
            return '(s.delivery = false and s.shipped = false and s.paid = false)';
        } else {
            return '';
        }
    }, $statuses);
}

// Server Logic

ROIHunterAuthenticator::getInstance()->authenticate();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        $page = getPageParameter();
        $createdFrom = getCreatedFromTimeParameter();
        $status = getStatusParameter();

        $orders = getOrders($page, $createdFrom, $status);
    } catch (Exception $e) {
        header("HTTP/1.1 500 Internal Server Error");
        echo("Unknown server exception: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
        die();
    }

    header("HTTP/1.1 200 OK");
    header("Content-Type:application/json");
    echo(new OrdersResponse($orders));
    die();
} else {
    header('HTTP/1.0 405 Method Not Allowed', true, 405);
    die();
}