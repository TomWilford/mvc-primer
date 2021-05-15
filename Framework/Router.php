<?php

namespace Framework;

use Framework\Base;
use Framework\Registry;
use Framework\Inspector;
use Framework\Router\Exception;
use Controllers\Users;

class Router extends Base
{
    /**
     * @var
     * @readwrite
     */
    protected $_url;

    /**
     * @var
     * @readwrite
     */
    protected $_extension;

    /**
     * @var
     * @read
     */
    protected $_controller;

    /**
     * @var
     * @read
     */
    protected $_action;

    protected $_routes = [];

    public function _getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} method not implemented");
    }

    public function addRoute($route)
    {
        $this->_routes[] = $route;
        return $this;
    }

    public function removeRoute($route)
    {
        foreach ($this->_routes as $i => $stored)
        {
            if ($stored == $route)
            {
                unset($this->_routes[$i]);
            }
        }
        return $this;
    }

    public function getRoutes()
    {
        $list = [];

        foreach ($this->_routes as $route)
        {
            $list[$route->pattern] = get_class($route);
        }

        return $list;
    }

    public function _pass($controller, $action, $parameters = [])
    {
        $name = ucfirst($controller);
        $name = "Controllers\\{$name}";

        $this->_controller = $controller;
        $this->_action     = $action;

        try
        {
            $instance = (new $name([
                "parameters" => $parameters
            ]));
            Registry::set("controller", $instance);
        }
        catch (\Exception $e)
        {
            throw new Exception\Controller("Controller {$name} not found");
        }

        if (!method_exists($instance, $action))
        {
            $instance->willRenderLayoutView = false;
            $instance->willRenderActionView = false;

            throw new Exception\Action("Action {$action} not found");
        }

        $inspector  = new Inspector($instance);
        $methodMeta = $inspector->getMethodMeta($action);

        if (!empty($methodMeta['@protected']) || !empty($methodMeta['@private']))
        {
            throw new Exception\Action("Action {$action} not found");
        }

        $hooks = function ($meta, $type) use ($inspector, $instance)
        {
            if (isset($meta[$type]))
            {
                $run = [];

                foreach ($meta[$type] as $method)
                {
                    $hookMeta = $inspector->getMethodMeta($method);

                    if (in_array($method, $run) && !empty($hookMeta['@once']))
                    {
                        continue;
                    }

                    $instance->$method();
                    $run = $method;
                }
            }
        };

        $hooks($methodMeta, "@before");

        call_user_func_array([
            $instance,
            $action
        ], is_array($parameters) ? $parameters : []);

        $hooks($methodMeta, "@after");

        Registry::erase("controller");
    }

    public function dispatch()
    {
        $url        = $this->url;
        $parameters = [];
        $controller = 'index';
        $action     = 'index';

        foreach ($this->_routes as $route)
        {
            $matches = $route->matches($url);
            if ($matches)
            {
                $controller = $route->controller;
                $action     = $route->action;
                $parameters = $route->parameters;

                $this->_pass($controller, $action, $parameters);
                return;
            }
        }

        $parts = explode("/", trim($url, "/"));

        if (sizeof($parts) > 0)
        {
            $controller = $parts[0];

            if (sizeof($parts) >= 2)
            {
                $action = $parts[1];
                $parameters = array_slice($parts, 2);
            }
        }

        $this->_pass($controller, $action, $parameters);
    }
}