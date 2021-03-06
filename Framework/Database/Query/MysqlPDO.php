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
     * @var array $_whereArguments Arguments for prepared where clause
     * @read
     */
    protected array $_whereArguments = [];

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
        $this->_whereArguments[] = $arguments[1];

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
        $this->_fields = ["COUNT(*) AS total"];

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
                break;
            case "UPDATE":
                return $this->string($this->_buildUpdate(), $this->_arguments);
                break;
            case "DELETE":
                return $this->string($this->_buildDelete(), $this->_arguments);
                break;
            default:
                throw new Exception\Argument("Invalid argument");
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
        foreach ($this->_whereArguments as $whereArgument) {
            $this->_arguments[] = $whereArgument;
        }

        $_order = $this->_order;
        $order  = ($_order) ? "ORDER BY {$_order} {$this->_direction}" : "";

        $limit = $this->_buildLimit($this->_limit, $this->_offset);

        return "{$select} {$fields} {$from} {$join} {$where} {$order} {$limit}";
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

        return "{$insertInto} {$table} ({$fields}) VALUES ({$values})";
    }

    private function _buildUpdate()
    {
        $update = $this->_clause;
        $table  = $this->_table;

        $_fields = [];
        foreach ($this->_fields as $field) {
            $_fields[] = "{$field} = ?";
        }
        $fields = implode(", ", $_fields);

        foreach ($this->_values as $value)
        {
            $this->_arguments[] = $value;
        }

        $where = $this->_buildWhere($this->_where);
        foreach ($this->_whereArguments as $whereArgument) {
            $this->_arguments[] = $whereArgument;
        }

        $limit = $this->_buildLimit($this->_limit, $this->_offset);

        return "{$update} {$table} SET {$fields} {$where} {$limit}";
    }

    private function _buildDelete()
    {
        $delete = $this->_clause;
        $table  = $this->_table;
        $where  = $this->_buildWhere($this->_where);
        foreach ($this->_whereArguments as $whereArgument) {
            $this->_arguments[] = $whereArgument;
        }
        $limit  = $this->_buildLimit($this->_limit, $this->_offset);

        var_dump("{$delete} FROM {$table} {$where} {$limit}");

        return "{$delete} FROM {$table} {$where} {$limit}";
    }

    private function _buildWhere($where)
    {

        $_where = implode(" AND ", $where);
        var_dump($_where);
        return ($_where) ? "WHERE {$_where}" : "";
    }

    private function _buildLimit($limit, $offset)
    {
        if ($limit)
        {
            return ($offset) ? "LIMIT {$limit}, {$offset}" : "LIMIT {$limit}";
        }
        return "";
    }

}