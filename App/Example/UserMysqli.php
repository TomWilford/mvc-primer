<?php

namespace App\Example;

use Framework\Database;

class UserMysqli
{

    /**
     * @var Database\Connector\MysqlPDO
     */
    protected $database;

    protected $data;

    public function __construct()
    {
        $connection = new Database([
            "type" => "mysqli",
            "options" => [
                "host"     => "",
                "username" => "",
                "password" => "",
                "schema"   => "",
                "port"     => ""
            ]
        ]);

        $this->database = $connection->initialise();
    }

    public function getAll(){
        $this->database->query()->string("SELECT * FROM users;");

    }

}