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
        $primary = $this->primaryColumn;
        $raw     = $primary["raw"];
        $name    = $primary["name"];

        if (!empty($this->$raw))
        {
            $previous = $this->connector->query()->run(
                "SELECT * FROM {$this->table} 
                 WHERE {$name} = {$this->$raw}
                 LIMIT 1"
            );

            if ($previous == null)
            {
                throw new Exception\Primary("Primary key value invalid");
            }

            foreach ($previous as $key => $item)
            {
                $prop = "_{$key}";
                if (!empty($previous->$key) && !isset($this->$prop))
                {
                    $this->$key = $previous->$key;
                }
            }
        }
    }

    public function save()
    {
        $primary   = $this->primaryColumn;
        $raw       = $primary["raw"];
        $name      = $primary["name"];
        $data      = [];

        foreach ($this->columns as $key => $column)
        {
            if (!$column["read"])
            {
                $prop       = $column["raw"];
                $data[$key] = $this->$prop;
                continue;
            }

            if ($column != $this->primaryColumn && $column)
            {
                $method = "get" . ucfirst($key);
                $data[$key] = $this->$method();
                continue;
            }
        }

        if (!empty($this->$raw))
        {
            $setString = implode(" = ?, ", array_keys($data));

            $result = $this->connector->query()->run(
                "UPDATE {$this->table} SET
                {$setString}
                WHERE {$name} = {$this->$raw}",
                array_values($data)
            );
        }
        else
        {
            $columnNames  = implode(", ", array_keys($data));

            $placeholders = [];
            foreach ($data as $key => $value)
            {
                $placeholders[] = "?";
            }
            $placeholders = implode(", ", $placeholders);

            $result = $this->connector->query()->run(
                "INSERT INTO {$this->table}
                ({$columnNames}) 
                VALUES
                ({$placeholders})",
                array_values($data)
            );
        }

        return $result;
    }

    public function delete()
    {
        $primary = $this->primaryColumn;

        $raw     = $primary["raw"];
        $name    = $primary["name"];

        if (!empty($this->$raw))
        {
            return $this->connector->query()->run(
                "DELETE FROM {$this->table}
                 WHERE {$name} = {$this->$raw}"
            );
        }
    }

    public static function deleteAll($where = [])
    {
        $instance    = new static();
        $whereString = $instance->_getWhereString($where);

        return $instance->connector->query()->run(
            "DELETE FROM {$instance->table}
            {$whereString}"
        );
    }

    public static function all($where = [], $fields = ["*"], $order = null, $direction = null, $limit = null, $page = null)
    {
        $model = new static();

        return $model->_all($where, $fields, $order, $direction, $limit, $page);
    }

    protected function _all($where = [], $fields = ["*"], $order = null, $direction = null, $limit = null, $page = null)
    {
        $whereString  = $this->_getWhereString($where);
        $fieldsString = implode(", ", $fields);
        $orderString  = ($order != null) ? $this->_getOrderString($order, $direction) : "";
        $limitString  = ($limit != null) ? $this->_getLimitString($limit, $page)      : "";

        $rows    = [];
        $class   = get_class($this);

        $results = $this->connector->query()->run(
            "SELECT {$fieldsString} FROM {$this->table}
            {$whereString}
            {$orderString}
            {$limitString}"
        );

        foreach ($results as $row)
        {
            $rows[] = new $class(
                $row
            );;
        }

        return $rows;
    }

    public static function count($where = [])
    {
        $model = new static();

        return $model->_count($where);
    }

    protected function _count($where = [])
    {
        $whereString = $this->_getWhereString($where);

        return $this->connector->query()->run(
            "SELECT count(*) FROM {$this->table}
            {$whereString}"
        );
    }

    protected function _getWhereString($where = [])
    {
        $wheres   = [];

        foreach ($where as $clause => $value)
        {
            $wheres[] = "WHERE {$clause} {$value}";
        }

        return implode(" AND ", $wheres);
    }

    protected function _getOrderString($order, $direction = "ASC")
    {
        return "ORDER BY {$order} {$direction}";
    }

    protected function _getLimitString($limit, $page = 1)
    {
        $offset = $limit * ($page - 1);

        return "LIMIT {$limit} {$offset}, {$page}";
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