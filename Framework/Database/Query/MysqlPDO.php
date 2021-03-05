<?php

namespace Framework\Database\Query;

use Framework\Database;
use Framework\Database\Exception;

/**
 * @property Database\Connector\MysqlPDO connector
 */
class MysqlPDO extends Database\Query
{
    /**
     * @var Database\Connector\MysqlPDO $_connector
     * @readwrite
     */
    protected $_connector;

    /**
     * @var mixed $_clause Opening part of the query
     * @read
     */
    protected $_clause;

    /**
     * @var mixed $_table Table to select data from
     * @read
     */
    protected $_table;

    /**
     * @var mixed $_fields Fields to use in query
     * @read
     */
    protected $_fields;

    /**
     * @var mixed $_limit Limit rows returned by query
     * @read
     */
    protected $_limit;

    /**
     * @var mixed $_offset Optional offset for the limit
     * @read
     */
    protected $_offset;

    /**
     * @var mixed $_order Order for rows returned by query
     * @read
     */
    protected $_order;

    /**
     * @var mixed $_direction Optional direction for order
     * @read
     */
    protected $_direction;

    /**
     * @var array $_join Additional tables to join on
     * @read
     */
    protected array $_join = [];

    /**
     * @var array $_where Parameters to filter results returned by query
     * @read
     */
    protected array $_where = [];

    /**
     * @var array $_arguments Arguments for prepared query
     * @read
     */
    protected array $_arguments = [];

    /**
     * @var array $_values Values for prepared query
     * @read
     */
    protected array $_values = [];

    public function string($sql, $arguments = [])
    {
        if (!$arguments)
        {
            return $this->connector->q($sql);
        }
        return $this->connector->prepareAndExecute($sql, $arguments);
    }

    public function join($join, $on, $fields = [])
    {
        if (empty($join))
        {
            throw new Exception\Argument("Invalid argument");
        }

        if (empty($on))
        {
            throw new Exception\Argument("Invalid argument");
        }

        foreach ($fields as $field) {
            $this->_fields[] = $field;
        }
        $this->_join[]  = "JOIN {$join} ON {$on}";

        return $this;
    }

    public function limit($limit, $page = 1)
    {
        if (empty($limit))
        {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_limit = $limit;
        $this->_offset = $limit * ($page - 1);

        return $this;
    }

    public function order($order, $direction = "asc")
    {
        if (empty($order))
        {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_order     = $order;
        $this->_direction = $direction;

        return $this;
    }

    public function where()
    {
        $arguments   = func_get_args();

        if (sizeof($arguments) < 1)
        {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_where[] = $arguments[0];
        $this->_arguments[] = $arguments[1];

        return $this;
    }

    public function select($from, $fields = ["*"])
    {
        if (empty($from))
        {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_table   = $from;
        $this->_clause = "SELECT";
        $this->_fields = $fields;

        return $this;
    }

    public function selectFirst($from, $fields = ["*"])
    {
        $this->select($from, $fields);
        $this->limit(1);

        return $this;
    }

    public function countAll($from)
    {
        if (empty($from))
        {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_table   = $from;
        $this->_clause = "SELECT";
        $this->_fields = ["COUNT(*) AS count"];

        return $this;
    }

    public function insert($into, $arguments)
    {
        if (empty($into) || empty($arguments))
        {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_table  = $into;
        $this->_clause = "INSERT";
        $this->_processArguments($arguments);

        return $this;
    }

    public function delete($from)
    {
        if (empty($from))
        {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_table   = $from;
        $this->_clause = "DELETE";

        return $this;
    }

    public function update($table, $arguments)
    {
        if (empty($table) || empty($arguments))
        {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_table  = $table;
        $this->_clause = "UPDATE";
        $this->_processArguments($arguments);

        return $this;
    }

    private function _processArguments($arguments)
    {
        foreach ($arguments as $field => $value)
        {
            $this->_fields[] = $field;
            $this->_values[] = $value;
        }
    }

    public function run()
    {
        switch ($this->_clause)
        {
            case "SELECT":
                    return $this->string($this->_buildSelect(), $this->_arguments);
                break;
            case "INSERT":
                return $this->string($this->_buildInsert(), $this->_arguments);

        }
    }

    private function _buildSelect()
    {
        $select = $this->_clause;
        $fields = implode(",", $this->_fields);
        $from   = "FROM " . $this->_table;

        $_join  = $this->_join;
        $join   = ($_join) ? implode(" ", $_join) : "";

        $_where = implode(" AND ", $this->_where);
        $where  = ($_where) ? "WHERE {$_where}" : "";

        $_order = $this->_order;
        $order  = ($_order) ? "ORDER BY {$_order} {$this->_direction}" : "";

        $_limit = $this->_limit;
        $limit  = "";
        if ($_limit)
        {
            $_offset = $this->_offset;
            $limit = ($_offset) ? "LIMIT {$_limit}, {$_offset}" : "LIMIT {$_limit}";
        }

        return "{$select} {$fields} {$from}  {$join}  {$where} {$order} {$limit}";
    }

    private function _buildInsert()
    {
        $insertInto = $this->_clause . " INTO";
        $table      = $this->_table;

        $fields  = implode(", ", $this->_fields);

        $_values = [];
        foreach ($this->_values as $value)
        {
            $this->_arguments[] = $value;
            $_values[]          = "?";
        }
        $values = implode(", ", $_values);

        $sql = "{$insertInto} {$table} ({$fields}) VALUES ({$values})";

        return $sql;
    }

}