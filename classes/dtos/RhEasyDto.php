<?php
/**
 * Conversions Id + Label, Pixel Id, platform type class
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

class RhEasyDto
{

    //dto has public fields because json_encode()

    public $platform;
    public $conversionId;
    public $conversionLabel;
    public $fbPixelId;

    /**
     * RhEasyCategoryDto constructor.
     * @param $platform string
     * @param $conversionId string
     * @param $conversionLabel string
     * @param $fbPixelId string
     */
    public function __construct($platform, $conversionId, $conversionLabel, $fbPixelId)
    {
        $this->platform = $platform;
        $this->conversionId = $conversionId;
        $this->conversionLabel = $conversionLabel;
        $this->fbPixelId = $fbPixelId;
    }


    public function toJson()
    {
        return json_encode($this);
    }
}
