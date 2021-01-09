<?php


namespace App\Example;

use Framework\Database as Database;

class Base
{
    /**
     * @var Database\Connector\Mysql
     */
    protected $database;

    public function __construct()
    {
        $connection = new Database();
        $this->database = $connection->initialise();
    }
}