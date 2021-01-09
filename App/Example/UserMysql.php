<?php


namespace App\Example;

use App\Example\Base as Base;
use Framework\Core\Exception as Exception;

class UserMysql extends Base
{
    protected $data;

    public function getAll(){
        return $this->database->query()->run("SELECT * FROM users;");
    }

}