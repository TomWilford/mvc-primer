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

Framework\Test::add(
    function () use ($database)
    {
        $example = new Example([
            "name"    => "foo",
            "created" => date("Y-m-d H:i:s")
        ]);

        return ($example->save() > 0);
    },
    "Model inserts rows",
    "Model"
);

Framework\Test::add(
    function () use ($database)
    {
        return (Example::count() == 1);
    },
    "Model fetches number of rows",
    "Model"
);

Framework\Test::add(
    function () use ($database)
    {
        $example = new Example([
            "name"    => "bar",
            "created" => date("Y-m-d H:i:s")
        ]);

        $example->save();
        $example->save();
        $example->save();

        return (Example::count() == 4);
    },
    "Model saves single row multiple times",
    "Model"
);

Framework\Test::add(
    function () use ($database) {
        $example = new Example([
            "id"      => 1,
            "name"    => "baz",
            "created" => date("Y-m-d H:i:s")
        ]);
        $example->save();

        return (Example::first()->name == "baz");
    },
    "Model updates rows",
    "Model"
);

Framework\Test::add(
    function () use ($database) {
        $example = new Example([
            "id" => 2
        ]);
        $example->delete();

        return (Example::count() == 3);
    },
    "Model deletes rows",
    "Model"
);

var_dump(Framework\Test::run());