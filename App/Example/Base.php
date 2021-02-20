<?php

namespace App\Example;

use Framework\Database;

class Base
{
    /**
     * @var Database\Connector\MysqlPDO $database
     */
    protected $database;

    public function __construct()
    {
        $connection = new Database();
        $this->database = $connection->initialise();
    }
}