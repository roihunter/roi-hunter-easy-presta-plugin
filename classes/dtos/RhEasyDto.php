<?php

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
