<?php

require '../../vendor/autoload.php';

$database = new \Framework\Database([
    "type"    => "mysql_pdo",
    "options" => [
        "host"     => "localhost",
        "username" => "prophpmvc",
        "password" => "prophpmvc",
        "schema"   => "prophpmvc"
    ]
]);
$database = $database->initialise();
$database = $database->connect();

Framework\Registry::set("database", $database);

class Example extends Framework\Model
{
    /**
     * @readwrite
     * @column
     * @type autonumber
     * @primary
     */
    protected $_id;

    /**
     * @readwrite
     * @column
     * @type text
     * @length 32
     */
    protected $_name;

    /**
     * @readwrite
     * @column
     * @type datetime
     */
    protected $_created;
}

Framework\Test::add(
    function () use ($database)
    {
        $example = new Example();
        return ($database->sync($example) instanceof Framework\Database\Connector\MysqlPDO);
    },
    "Model syncs",
    "Model"
);

var_dump(Framework\Test::run());