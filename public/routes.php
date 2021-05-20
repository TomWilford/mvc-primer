<?php

$routes = [
    [
        "pattern"    => "register",
        "controller" => "users",
        "action"     => "register"
    ],
    [
        "pattern"    => "login",
        "controller" => "users",
        "action"     => "login"
    ],
    [
        "pattern"    => "logout",
        "controller" => "users",
        "action"     => "logout"
    ],
    [
        "pattern"    => "search",
        "controller" => "users",
        "action"     => "search"
    ],
    [
        "pattern"    => "profile",
        "controller" => "users",
        "action"     => "profile"
    ],
    [
        "pattern"    => "settings",
        "controller" => "users",
        "action"     => "settings"
    ],
    [
        "pattern"    => "unfriend/:id",
        "controller" => "users",
        "action"     => "unfriend"
    ],
    [
        "pattern"    => "friend/:id",
        "controller" => "users",
        "action"     => "friend"
    ],
    [
        "pattern"    => "fonts/:id",
        "controller" => "files",
        "action"     => "fonts"
    ]
];

foreach ($routes as $route)
{
    $router->addRoute(new \Framework\Router\Route\Simple($route));
}

unset($routes);