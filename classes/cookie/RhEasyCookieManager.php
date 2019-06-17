<?php
/**
 * Manage cookies
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

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/cookie/RhEasyCookieDto.php');

class RhEasyCookieManager
{
    private static $instance;

    private function __construct()
    {
        if (!isset(Context::getContext()->cookie->roihunter)) {
            Context::getContext()->cookie->roihunter = json_encode(new RhEasyCookieDto());
        }
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new RhEasyCookieManager();
        }
        return self::$instance;
    }

    /**
     * @return RhEasyCookieDto
     */
    public function loadRhEasyCookieDto()
    {
        return RhEasyCookieDto::fromArray(json_decode(Context::getContext()->cookie->roihunter, true));
    }

    public function saveRhEasyCookieDto($rhEasyCookieDto)
    {
        Context::getContext()->cookie->roihunter = json_encode($rhEasyCookieDto);
    }

    public function clearStorage()
    {
        Context::getContext()->cookie->roihunter = json_encode(new RhEasyCookieDto());
    }
}
