<?php

// 1. define the default path for includes
define("APP_PATH", dirname(dirname(__FILE__)));

// 2. load he Core class that includes an autoloader
require("../Framework/Core.php");
Framework\Core::initialise();

// 3. load and initialise the Configuration class
$configuration = new Framework\Configuration([
    "type" => "ini"
]);
Framework\Registry::set("configuration", $configuration->initialise());

// 4. load and initialise the Database class
$database = new Framework\Database();
Framework\Registry::set("database", $database->initialise());

// 5. load and initialise the Cache class
$cache = new Framework\Cache();
Framework\Registry::set("cache", $cache->initialise());

// 6. load and initialise the Session class
$session = new Framework\Session();
Framework\Registry::set("session", $session->initialise());

// 7. load the Router class and provide teh url + extension
$router = new Framework\Router([
    "url"       => $_GET["url"] ?? "home/index",
    "extension" => $_GET["url"] ?? "html"
]);
Framework\Registry::set("router", $router);

// 8. dispatch the current request
$router->dispatch();

unset($configuration);
unset($database);
unset($cache);
unset($session);
unset($router);