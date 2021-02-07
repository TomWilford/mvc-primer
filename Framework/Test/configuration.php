<?php

require '../../vendor/autoload.php';

Framework\Test::add(
    function ()
    {
        $configuration = new Framework\Configuration();
        return ($configuration instanceof Framework\Configuration);
    },
    "Configuration instantiates inn uninitialised state",
    "Configuration"
);


Framework\Test::add(
    function ()
    {
        $configuration = new Framework\Configuration([
            "type" => "ini"
        ]);

        $configuration = $configuration->initialise();
        return ($configuration instanceof Framework\Configuration\Driver\Ini);
    },
    "Configuration\Driver\Ini initialises",
    "Configuration\Driver\Ini"
);

Framework\Test::add(
    function ()
    {
        $configuration = new Framework\Configuration([
            "type" => "ini"
        ]);

        $configuration = $configuration->initialise();
        $parsed        = $configuration->parse("/etc/.config/_configurationTest");

        return ($parsed->config->first == "hello" && $parsed->config->second == "bar");
    },
    "Configuration\Driver\Ini parses configuration files",
    "Configuration\Driver\Ini"
);

Framework\Test::add(
    function ()
    {
        $configuration = new Framework\Configuration([
            "type" => "json"
        ]);

        $configuration = $configuration->initialise();
        return ($configuration instanceof Framework\Configuration\Driver\Json);
    },
    "Configuration\Driver\Json initialises",
    "Configuration\Driver\Json"
);

Framework\Test::add(
    function ()
    {
        $configuration = new Framework\Configuration([
            "type" => "json"
        ]);

        $configuration = $configuration->initialise();
        $parsed        = $configuration->parse("/etc/.config/_configurationTest");

        return ($parsed->config->first == "hello" && $parsed->config->second == "bar");
    },
    "Configuration\Driver\Json parses configuration files",
    "Configuration\Driver\Json"
);

Framework\Test::add(
    function ()
    {
        $configuration = new Framework\Configuration([
            "type" => "array"
        ]);

        $configuration = $configuration->initialise();
        return ($configuration instanceof Framework\Configuration\Driver\AssociativeArray);
    },
    "Configuration\Driver\AssociativeArray initialises",
    "Configuration\Driver\AssociativeArray"
);

Framework\Test::add(
    function ()
    {
        $configuration = new Framework\Configuration([
            "type" => "array"
        ]);

        $configuration = $configuration->initialise();
        $parsed        = $configuration->parse("/etc/.config/_configurationTest");

        return ($parsed->config->first == "hello" && $parsed->config->second == "bar");
    },
    "Configuration\Driver\AssociativeArray parses configuration files",
    "Configuration\Driver\AssociativeArray"
);

var_dump(Framework\Test::run());