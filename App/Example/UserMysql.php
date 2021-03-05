<?php

namespace App\Example;

use App\Example\Base;

class UserMysql extends Base
{
    protected $data;

    public function getAll(){
        return $this->database->query()->string("SELECT * FROM users");
    }

}