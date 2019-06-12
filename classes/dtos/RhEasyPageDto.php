<?php

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
