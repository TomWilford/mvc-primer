<?php

namespace Framework;

use Framework\Core\Exception;
use Framework\Inspector;
use Framework\ArrayMethods;
use Framework\StringMethods;

class Base
{
    private $_inspector;

    public function __construct($options = array())
    {
        $this->_inspector = new Inspector($this);

        if (is_array($options) || is_object($options))
        {
            foreach ($options as $key => $value)
            {
                $key = ucfirst($key);
                $method = "set{$key}";
                $this->$method($value);
            }
        }
    }

    public function  __call($name, $arguments)
    {
        if (empty($this->_inspector))
        {
            throw new Exception("Call parent::__construct!");
        }

        $getMatches = StringMethods::match($name, "^get([a-zA-Z0-9]+)$");
        if (sizeof($getMatches) > 0)
        {
            $normalized = lcfirst($getMatches[0]);
            $property = "_{$normalized}";
        }

        if (property_exists($this, $property))
        {
            $meta = $this->_inspector->getPropertyMeta($property);

            if (empty($meta["@readwrite"]) && empty($meta["@read"]))
            {
                throw $this->_getException($normalized, "writeonly");
            }

            if (isset($this->$property)){
                return $this->$property;
            }

            return null;
        }

        $setMatches = StringMethods::match($name, "^set([a-zA-Z0-9]+)$");
        if (sizeof($setMatches) > 0)
        {
            $normalized = lcfirst($setMatches[0]);
            $property = "_{$normalized}";

            if (property_exists($this, $property))
            {
                $meta = $this->_inspector->getPropertyMeta($property);

                if (empty($meta["@readwrite"]) && empty($meta["@write"]))
                {
                    throw $this->_getException($normalized, "readonly");
                }

                $this->$property = $arguments[0];
                return $this;
            }
        }

        throw $this->_getException($name, "method");
    }

    public function __get($name)
    {
        $function = "get".ucfirst($name);
        return $this->$function();
    }

    public function __set($name, $value)
    {
        $function = "set".ucfirst($name);
        return $this->$function($value);
    }

    protected function _getException($request, $type){
        switch ($type)
        {
            case "readonly":
                return new Exception\ReadOnly("{$request} is read-only");
            case "writeonly":
                return new Exception\WriteOnly("{$request} is write-only");
            case "property":
                return new Exception\Property("Invalid property");
            case "method":
                return new Exception\Argument("{$request} method not implemented");
            case "implementation":
                return new Exception\Implementation("{$request} method not implemented");
            case "syntax":
                return new Exception\Syntax("{$request} method not implemented");
            default:
                return new Exception("Not sure what's going on here");
        }
    }

}
