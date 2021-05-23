<?php

const DEBUG = true;
define("APP_PATH", dirname(dirname(__FILE__)));

try {
    //core
    require("../Framework/Core.php");
    Framework\Core::initialise();

    //plugins
    $path = APP_PATH . "/Application/plugins";
    $iterator = new DirectoryIterator($path);

    foreach ($iterator as $item)
    {
        if (!$item->isDot() && $item->isDir())
        {
            include ($path . "/" . $item->getFilename() . "/initialise.php");
        }
    }

    // configuration
    $configuration = new Framework\Configuration([
        "type" => "ini"
    ]);
    Framework\Registry::set("configuration", $configuration->initialise());

    // database
    $database = new Framework\Database();
    Framework\Registry::set("database", $database->initialise());

    // cache
    $cache = new Framework\Cache();
    Framework\Registry::set("cache", $cache->initialise());

    // session
    $session = new Framework\Session();
    Framework\Registry::set("session", $session->initialise());

    // router
    $router = new Framework\Router([
        "url" => $_GET["url"] ?? "home/index",
        "extension" => $_GET["url"] ?? "html"
    ]);
    Framework\Registry::set("router", $router);

    include("routes.php");

    // dispatch
    $router->dispatch();

    // unset globals
    unset($configuration);
    unset($database);
    unset($cache);
    unset($session);
    unset($router);
} catch (Exception $e) {
    $exceptions = [
        "500" => [
            "Framework\Cache\Exception",
            "Framework\Cache\Exception\Argument",
            "Framework\Cache\Exception\Implementation",
            "Framework\Cache\Exception\Service",

            "Framework\Configuration\Exception",
            "Framework\Configuration\Exception\Argument",
            "Framework\Configuration\Exception\Implementation",
            "Framework\Configuration\Exception\Syntax",

            "Framework\Controller\Exception",
            "Framework\Controller\Exception\Argument",
            "Framework\Controller\Exception\Implementation",

            "Framework\Core\Exception",
            "Framework\Core\Exception\Argument",
            "Framework\Core\Exception\Implementation",
            "Framework\Core\Exception\Property",
            "Framework\Core\Exception\ReadOnly",
            "Framework\Core\Exception\WriteOnly",

            "Framework\Database\Exception",
            "Framework\Database\Exception\Argument",
            "Framework\Database\Exception\Implementation",
            "Framework\Database\Exception\Service",
            "Framework\Database\Exception\Sql",

            "Framework\Model\Exception",
            "Framework\Model\Exception\Argument",
            "Framework\Model\Exception\Connector",
            "Framework\Model\Exception\Implementation",
            "Framework\Model\Exception\Primary",
            "Framework\Model\Exception\Type",
            "Framework\Model\Exception\Validation",

            "Framework\Request\Exception",
            "Framework\Request\Exception\Argument",
            "Framework\Request\Exception\Implementation",
            "Framework\Request\Exception\Response",

            "Framework\Router\Exception",
            "Framework\Router\Exception\Argument",
            "Framework\Router\Exception\Implementation",

            "Framework\Session\Exception",
            "Framework\Session\Exception\Argument",
            "Framework\Session\Exception\Implementation",

            "Framework\Template\Exception",
            "Framework\Template\Exception\Argument",
            "Framework\Template\Exception\Implementation",
            "Framework\Template\Exception\Parser",

            "Framework\View\Exception",
            "Framework\View\Exception\Argument",
            "Framework\View\Exception\Data",
            "Framework\View\Exception\Implementation",
            "Framework\View\Exception\Renderer",
            "Framework\View\Exception\Syntax"
        ],
        "404" => [
            "Framework\Router\Exception\Action",
            "Framework\Router\Exception\Controller"
        ]
    ];

    $exception = get_class($e);

    error_log(json_encode( $e, JSON_PRETTY_PRINT), 0);

    // attempt to locate appropriate template & render
    foreach ($exceptions as $template => $classes) {
        foreach ($classes as $class) {
            if ($class == $exception) {
                header("Content-type: text/html");
                include(APP_PATH."/Application/Views/errors/{$template}.php");
                exit;
            }
        }
    }

    // render fallback template
    header("Content-type: text/html");
    echo "An error occurred";
    exit;
}
