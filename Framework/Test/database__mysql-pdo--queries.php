<?php

require '../../vendor/autoload.php';

$options = [
    "type"    => "mysql_pdo",
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

        $database->q("
            DROP TABLE IF EXISTS `tests`;
        ");
        $database->q("
            CREATE TABLE `tests` (
                `id`      int(11)      NOT NULL AUTO_INCREMENT,
                `number`  int(11)      NOT NULL,
                `text`    varchar(255) NOT NULL,
                `boolean` tinyint(4)   NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        return ($database instanceof Framework\Database\Connector\MysqlPDO);
    },
    "Database\Connector\MysqlPDO executes DROP & CREATE TABLE queries",
    "Database\Connector\MysqlPDO"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        for ($i = 0; $i < 4; $i++)
        {
            $database->q("
                INSERT INTO tests (`number`, `text`, `boolean`) VALUES ('42069{$i}', 'text {$i}', 'true');
            ");
        }

        return ($database->getLastInsertId() == 4);
    },
    "Database\Connector\MysqlPDO returns last insert id",
    "Database\Connector\MysqlPDO"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $result = $database->q("
            UPDATE `tests` SET `number` = 42069;
        ");

        return ($database->getAffectedRows($result) == 4);
//        return ($result->rowCount() == 4);
    },
    "Database\Connector\MysqlPDO returns affected rows",
    "Database\Connector\MysqlPDO"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();
        $query    = $database->query();

        return ($query instanceof Framework\Database\Query\MysqlPDO);
    },
    "Database\Connector\MysqlPDO returns instance of Database\Query\MysqlPDO",
    "Database\Query\MysqlPDO"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();
        $query    = $database->query();

        return ($query->connector instanceof Framework\Database\Connector\MysqlPDO);
    },
    "Database\Query\MysqlPDO returns instance of Database\Query\MysqlPDO",
    "Database\Query\MysqlPDO"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $rows = $database->query()->string(
            "SELECT * FROM `tests`"
        );

        return (count($rows->fetchAll()) == 4);
    },
    "Database\Query\MysqlPDO fetches all rows",
    "Database\Query\MysqlPDO"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $result   = $database->query()
                             ->selectFirst("tests")
                             ->run();
        $rows     = $result->fetchAll();
        return (count($rows) == 1);
    },
    "Database\Query\MysqlPDO fetches first row",
    "Database\Query\MysqlPDO"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $result   = $database->query()
                             ->select("tests")
                             ->run();
        $rows     = $result->fetchAll();

        return(count($rows) == 4);

    },
    "Database\Query\MysqlPDO fetches all rows",
    "Database\Query\MysqlPDO"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $result   = $database->query()->countAll("tests")->run();
        $count    = $result->fetch();

        return ($count["count"] == 4);
    },
    "Database\Query\MysqlPDO count all rows",
    "Database\Query\MysqlPDO"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $result   = $database->query()
                             ->select("tests")
                             ->limit(1, 2)
                             ->order("id", "desc")
                             ->run();
        $rows     = $result->fetchAll();

        return(count($rows) == 1 && $rows[0]["id"] == 3);

    },
    "Database\Query\MysqlPDO accepts LIMIT, OFFSET, ORDER and DIRECTION clauses",
    "Database\Query\MysqlPDO"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $result   = $database->query()
                             ->select("tests")
                             ->where("id != ?", 1)
                             ->where("id != ?", 3)
                             ->where("id != ?", 4)
                             ->run();
        $rows     = $result->fetchAll();

        return (count($rows) == 1 && $rows[0]["id"] == 2);
    },
    "Database\Query\MysqlPDO accepts multiple where clauses",
    "Database\Query\MysqlPDO"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $result   = $database->query()
                             ->select("tests",[
                                 "id AS foo"
                             ])
                             ->run();
        $rows    = $result->fetchAll();

        return (count($rows) && isset($rows[0]["foo"]) && $rows[0]["foo"] == 1);
    },
    "Database\Query\MysqlPDO uses alias for column",
    "Database\Query\MysqlPDO"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $result   = $database->query()
                             ->select("tests", [
                                 "tests.id AS foo"
                             ])
                             ->join("tests AS baz", "tests.id = baz.id", [
                                 "baz.id AS bar"
                             ])
                             ->run();
        $rows = $result->fetchAll();

        return (count($rows) && $rows[0]["foo"] == $rows[0]["bar"]);
    },
    "Database\Query\MysqlPDO joins tables and aliases joined fields",
    "Database\Query\MysqlPDO"
);

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $result   = $database->query()
                             ->insert("tests", [
                                 "number"  => 3,
                                 "text"    => "foo",
                                 "boolean" => false
                             ])
                             ->run();
        $id = $database->getLastInsertId($result);

        return ($id == 5);
    },
    "Database\Query\MysqlPDO inserts row",
    "Database\Query\MysqlPDO"
);

/*Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $result   = $database->query()
            ->update("tests", [
                "boolean" => true
            ])
            ->where("id = ?", 5)
            ->run();

        return ($id == 5);
    },
    "Database\Query\MysqlPDO inserts row",
    "Database\Query\MysqlPDO"
);*/

Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $result   = $database->query()->delete("tests")->run();

        $result = $database->query()->countAll("tests")->run();
        $count  = $result->fetch();

        return ($count[0]['count'] == 0);
    },
    "Database\Query\MysqlPDO deletes rows",
    "Database\Query\MysqlPDO"
);

var_dump(Framework\Test::run());