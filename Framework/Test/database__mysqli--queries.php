<?php

/**
 * @deprecated
 * */

require '../../vendor/autoload.php';

$options = [
    "type"    => "mysqli",
    "options" => [
        "host"     => "localhost",
        "username" => "prophpmvc",
        "password" => "prophpmvc",
        "schema"   => "prophpmvc"
    ]
];

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();
        $query    = $database->query();

        return ($query instanceof Framework\Database\Query\Mysqli);
    },
    "Database\Connector\Mysqli returns instance of Database\Query\Mysqli",
    "Database\Query\Mysqli"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();
        $query    = $database->query();

        return ($query->connector instanceof Framework\Database\Connector\Mysqli);
    },
    "Database\Connector\Mysqli references connector",
    "Database\Query\Mysqli"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $rows     = $database->query()
            ->from("")
            ->first();

        return ($rows["id"] == 1);
    },
    "Database\Query\Mysqli fetches first row",
    "Database\Query\Mysqli"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $rows     = $database->query()
            ->from("tests")
            ->all();

        return (count($rows) == 4);
    },
    "Database\Query\Mysqli fetches all rows",
    "Database\Query\Mysqli"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $count    = $database->query()
            ->from("tests")
            ->count();

        return ($count == 4);
    },
    "Database\Query\Mysqli fetches number of rows",
    "Database\Query\Mysqli"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $rows     = $database->query()
            ->from("tests")
            ->limit(1, 2)
            ->order("id", "desc")
            ->all();

        return (count($rows) == 1 && $rows[0]["id"] == 3);
    },
    "Database\Query\Mysqli accepts LIMIT, OFFSET, ORDER and DIRECTION clauses",
    "Database\Query\Mysqli"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $rows     = $database->query()
            ->from("tests")
            ->where("id != ?", 1)
            ->where("id != ?", 3)
            ->where("id != ?", 4)
            ->all();

        var_dump($rows);

        return (count($rows) == 1 && $rows[0]["id"] == 2);
    },
    "Database\Query\Mysqli accepts multiple where clauses",
    "Database\Query\Mysqli"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $rows    = $database->query()
            ->from("tests", [
                "id" => "foo"
            ])
            ->all();

        return (count($rows) && isset($rows[0]["foo"]) && $rows[0]["foo"] == 1);
    },
    "Database\Query\Mysqli uses alias for column",
    "Database\Query\Mysqli"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $rows    = $database->query()
            ->from("tests", [
                "tests.id" => "foo"
            ])
            ->join("tests AS baz", "tests.id = baz.id",[
                "baz.id" => "bar"
            ])
            ->all();

        return (count($rows) && $rows[0]->foo == $rows[0]->bar);
    },
    "Database\Query\Mysqli joins tables and aliases joined fields",
    "Database\Query\Mysqli"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $result     = $database->query()
            ->from("tests")
            ->save([
                "number"  => 3,
                "text"    => "foo",
                "boolean" => false
            ]);

        return ($result == 5);
    },
    "Database\Query\Mysqli inserts row",
    "Database\Query\Mysqli"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $database->query()
            ->from("tests")
            ->delete();

        return ($database->query()->from("tests")->count() == 0);
    },
    "Database\Query\Mysqli can delete rows",
    "Database\Query\Mysqli"
);

var_dump(Framework\Test::run());