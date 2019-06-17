<?php
/**
 * Page type Dto
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

class RhEasyPageDto
{
    //dto has public fields because json_encode()

    public $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function toJson()
    {
        return json_encode($this);
    }
}
