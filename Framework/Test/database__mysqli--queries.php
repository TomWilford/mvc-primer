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

        $database->execute("
            THIS IS NOT SQL
        ");

        return (bool) $database->lastError;
    },
    "Database\Connector\Mysqli returns last error",
    "Database\Connector\Mysqli"
);

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

        return !$database->lastError;
    },
    "Database\Connector\Mysqli executes DROP & CREATE TABLE queries",
    "Database\Connector\Mysqli"
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
                INSERT INTO tests (`number`, `text`, `boolean`) VALUES ('42069{$i}', 'text {$i}', '0');
            ");
        }

        return ($database->lastInsertId == 4);
    },
    "Database\Connector\Mysqli returns last insert id",
    "Database\Connector\Mysqli"
);


Framework\Test::add(
    function () use ($options)
    {
        $database = new Framework\Database($options);
        $database = $database->initialise();
        $database = $database->connect();

        $database->execute("
            UPDATE `tests` SET `number` = 42069;
        ");

        return ($database->affectedRows == 4);
    },
    "Database\Connector\Mysqli returns affected rows",
    "Database\Connector\Mysqli"
);

var_dump(Framework\Test::run());