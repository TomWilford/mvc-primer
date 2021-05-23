<?php

require '../../vendor/autoload.php';

Framework\Test::add(
    function () {
        $cache = new Framework\Cache();

        return ($cache instanceof Framework\Cache);
    },
    "Cache instantiates in uninitialised state",
    "Cache"
);

Framework\Test::add(
    function () {
        $cache = new Framework\Cache([
            "type" => "memcached"
        ]);
        $cache = $cache->initialise();

        return ($cache instanceof Framework\Cache\Driver\Memcached);
    },
    "Cache\Driver\Memcahced initialises",
    "Cache\Driver\Memcached"
);

Framework\Test::add(
    function () {
        $cache = new Framework\Cache([
            "type" => "memcached"
        ]);

        $cache = $cache->initialise();
        return ($cache->connect() instanceof Framework\Cache\Driver\Memcached);
    },
    "Cache\Driver\Memcached connects and returns self",
    "Cache\Driver\Memcached"
);

Framework\Test::add(
    function () {
        $cache = new Framework\Cache([
            "type" => "memcached"
        ]);

        $cache = $cache->initialise();
        $cache = $cache->connect();
        $cache = $cache->disconnect();

        try {
            $cache->get("anything");
        } catch (Framework\Cache\Exception\Service $e) {
            return ($cache instanceof Framework\Cache\Driver\Memcached);
        }
        return false;
    },
    "Cache\Driver\Memcached disconnects and returns self",
    "Cache\Driver\Memcached"
);

Framework\Test::add(
    function () {
        $cache = new Framework\Cache([
           "type" => "memcached"
        ]);

        $cache = $cache->initialise();
        $cache = $cache->connect();

        return ($cache->set("foo", "bar", 1) instanceof Framework\Cache\Driver\Memcached);
    },
    "Cache\Driver\Memcached sets values and returns self",
    "Cache\Driver\Memcached"
);

Framework\Test::add(
    function () {
        $cache = new Framework\Cache([
           "type" => "memcached"
        ]);

        $cache = $cache->initialise();
        $cache = $cache->connect();

        return ($cache->get("foo") == "bar");
    },
    "Cache\Driver\Memcached retrieves values",
    "Cache\Driver\Memcached"
);

Framework\Test::add(
    function () {
        $cache = new Framework\Cache([
            "type" => "memcached"
        ]);

        $cache = $cache->initialise();
        $cache = $cache->connect();

        return ($cache->get("404", "baz") == "baz");
    },
    "Cache\Driver\Memcached returns default values",
    "Cache\Driver\Memcached"
);

Framework\Test::add(
    function () {
        $cache = new Framework\Cache([
            "type" => "memcached"
        ]);

        $cache = $cache->initialise();
        $cache = $cache->connect();

        sleep(1);

        return ($cache->get("foo") == null);
    },
    "Cache\Driver\Memcached expires values",
    "Cache\Driver\Memcached"
);

Framework\Test::add(
    function () {
        $cache = new Framework\Cache([
            "type" => "memcached"
        ]);

        $cache = $cache->initialise();
        $cache = $cache->connect();

        $cache = $cache->set("hello", "world");
        $cache = $cache->erase("hello");
        return ($cache->get("hello") == null && $cache instanceof Framework\Cache\Driver\Memcached);
    },
    "Cache\Driver\Memcached erases value and returns self",
    "Cache\Driver\Memcached"
);

var_dump(Framework\Test::run());
