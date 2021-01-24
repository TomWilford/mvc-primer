<?php

namespace App\Example;

use App\Example\Base;
use Framework\Core\Exception;

class UserMysql extends Base
{
    protected $data;

    public function getAll(){
        return $this->database->query()->run("SELECT * FROM users");
    }

}