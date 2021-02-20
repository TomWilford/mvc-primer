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

        $database->execute("
            DROP TABLE IF EXISTS `tests`;
        ");
        $database->execute("
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
            $database->execute("
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

        $result = $database->execute("
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

        $rows = $database->query()->run(
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

        $count = $database->query()->countAllFrom("tests");

        return ($count == 4);
    },
    "Database\Query\MysqlPDO count all rows",
    "Database\Query\MysqlPDO"
);



var_dump(Framework\Test::run());