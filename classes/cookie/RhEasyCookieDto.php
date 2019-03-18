<?php

require_once(_PS_MODULE_DIR_ . 'roihunter/classes/dtos/RhEasyCartDto.php');

class RhEasyCookieDto {

    //dto has public fields because json_encode()

    public $rhEasyCartDto;

    /**
     * RhEasyCookieDto constructor.
     */
    public function __construct() {
        $this->rhEasyCartDto = new RhEasyCartDto([], 0, null);
    }

    public static function fromArray($arr) {

        $instance = new RhEasyCookieDto();
        $instance->setRhEasyCartDto(RhEasyCartDto::fromArray(
            $arr['rhEasyCartDto']['cartItems'],
            $arr['rhEasyCartDto']['totalPrice'],
            $arr['rhEasyCartDto']['currency']));
        return $instance;
    }

    /**
     * @return RhEasyCartDto
     */
    public function getRhEasyCartDto() {
        return $this->rhEasyCartDto;
    }

    /**
     * @param RhEasyCartDto $rhEasyCartDto
     */
    public function setRhEasyCartDto($rhEasyCartDto) {
        $this->rhEasyCartDto = $rhEasyCartDto;
    }


}