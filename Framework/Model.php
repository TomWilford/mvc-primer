<?php

namespace Framework;

use Framework\Base;
use Framework\Registry;
use Framework\Inspector;
use Framework\StringMethods;
//use Framework\Model\Exception;
use Framework\Core\Exception;

class Model extends Base
{
    /**
     * @var
     * @readwrite
     */
    protected $_table;

    /**
     * @var
     * @readwrite
     */
    protected $_connector;

    /**
     * @var string[]
     * @read
     */
    protected $_types = [
        "autonumber",
        "text",
        "decimal",
        "boolean",
        "datetime"
    ];

    protected $_columns;
    protected $_primary;

    public function _getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} method not implemented");
    }

    public function __construct($options = [])
    {
        parent::__construct($options);
        $this->load();
    }

    public function load()
    {

    }

    public function getTable()
    {
        if (empty($this->_table))
        {
            $this->_table = strtolower(StringMethods::singular(get_class($this)));
        }

        return $this->_table;
    }

    public function getConnector()
    {
        if (empty($this->_connector))
        {
            $database = Registry::get("database");

            if (!$database)
            {
                throw new Exception\Connector("No connector available");
            }

            $this->_connector = $database->initialise();
        }

        return $this->_connector;
    }

    public function getColumns()
    {
        if (empty($_columns)) {
            $primaries = 0;
            $columns   = [];
            $class     = get_class($this);
            $types     = $this->types;

            $inspector  = new Inspector($this);
            $properties = $inspector->getClassProperties();


            $first = function ($array, $key) {
                if (!empty($array[$key]) && sizeof($array[$key]) == 1) {
                    return $array[$key][0];
                }
                return null;
            };

            foreach ($properties as $property) {
                $propertyMeta = $inspector->getPropertyMeta($property);

                if (!empty($property["@column"]))
                {
                    $name      = preg_replace("#^_#", "", $property);
                    $primary   = !empty($propertyMeta["@primary"]);
                    $type      = $first($propertyMeta, "@type");
                    $length    = $first($propertyMeta, "@length");
                    $index     = !empty($propertyMeta["@index"]);
                    $readwrite = !empty($propertyMeta["@readwrite"]);
                    $read      = !empty($propertyMeta["@read"]) || $readwrite;
                    $write     = !empty($propertyMeta["@write"]) || $readwrite;

                    $validate  = !empty($propertyMeta["@validate"]) ? $propertyMeta["@validate"] : false;
                    $label     = $first($propertyMeta, "@label");

                    if (!in_array($type, $types))
                    {
                        throw new Exception\Type("{$type} is not a valid type");
                    }

                    if ($primary)
                    {
                        $primaries++;
                    }

                    $columns[$name]  = [
                        "raw"      => $property,
                        "name"     => $name,
                        "primary"  => $primary,
                        "type"     => $type,
                        "length"   => $length,
                        "index"    => $index,
                        "read"     => $read,
                        "write"    => $write,
                        "validate" => $validate,
                        "label"    => $label
                    ];
                }
            }

            if ($primaries !== 1)
            {
                throw new Exception\Primary("{$class} must have exactly one @ primary column");
            }

            $this->_columns = $columns;
        }

        return $this->_columns;
    }

    public function getColumn($name)
    {
        if (!empty($this->_columns[$name]))
        {
            return $this->_columns[$name];
        }
        return null;
    }

    public function getPrimaryColumn()
    {
        if (!isset($this->_primary))
        {
            $primary = null;

            foreach ($this->_columns as $column)
            {
                if ($column["primary"])
                {
                    $primary = $column;
                    break;
                }
            }

            $this->_primary = $primary;
        }

        return $this->_primary;
    }
}