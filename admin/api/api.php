<?php
namespace admin\api;

use \lib\register as register;

class api{

    public function __construct()
    {

    }

    public function __get($name)
    {
        return register::getInstance($name);
    }

}