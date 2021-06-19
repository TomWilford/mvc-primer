<?php
namespace Framework\Database\Query;

use Framework\Database;
use Framework\Database\Exception;
use PDOStatement;

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

    /**
     * Prepares &&|| runs query from string and array of arguments
     * @param $sql
     * @param array $arguments
     * @return array|false|PDOStatement
     * @throws Exception\Service
     */
    public function string($sql, $arguments = [])
    {
        if (!$arguments) {
            return $this->connector->q($sql);
        }

        return $this->connector->prepareAndExecute($sql, $arguments);
    }

    /**
     * Simple Query Generator:
     * - Sets generator variables for select query
     * - Defaults to select all
     * @param $from
     * @param string[] $fields
     * @return $this
     * @throws Exception\Argument
     */
    public function select($from, $fields = ["*"])
    {
        if (empty($from)) {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_table   = $from;
        $this->_clause = "SELECT";
        $this->_fields = $fields;

        return $this;
    }

    /**
     * Simple Query Generator:
     * - Sets generator variables for select query with a limit of 1
     * - Defaults to select all
     * @param $from
     * @param string[] $fields
     * @return $this
     * @throws Exception\Argument
     */
    public function selectFirst($from, $fields = ["*"])
    {
        if (empty($from)) {
            throw new Exception\Argument("Invalid argument");
        }

        $this->select($from, $fields);
        $this->limit(1);

        return $this;
    }

    /**
     * Simple Query Generator:
     * - Sets generator variables for select count query
     * @param $from
     * @return $this
     * @throws Exception\Argument
     */
    public function countAll($from)
    {
        if (empty($from)) {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_table   = $from;
        $this->_clause = "SELECT";
        $this->_fields = ["COUNT(*) AS total"];

        return $this;
    }

    /**
     * Simple Query Generator:
     * - Sets generator variables for insert query
     * - Sorts arguments into fields & values
     * @param $into
     * @param $arguments
     * @return $this
     * @throws Exception\Argument
     */
    public function insert($into, $arguments)
    {
        if (empty($into) || empty($arguments)) {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_table  = $into;
        $this->_clause = "INSERT";
        $this->_processArguments($arguments);

        return $this;
    }

    /**
     * Simple Query Generator:
     * - Sets generator variables for delete query
     * @param $from
     * @return $this
     * @throws Exception\Argument
     */
    public function delete($from)
    {
        if (empty($from)) {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_table   = $from;
        $this->_clause = "DELETE";

        return $this;
    }

    /**
     * Simple Query Generator:
     * - Sets generator variables for update query
     * - Sorts arguments into fields & values
     * @param $table
     * @param $arguments
     * @return $this
     * @throws Exception\Argument
     */
    public function update($table, $arguments)
    {
        if (empty($table) || empty($arguments)) {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_table  = $table;
        $this->_clause = "UPDATE";
        $this->_processArguments($arguments);

        return $this;
    }

    /**
     * Simple Query Generator:
     * - Sets generator variables for ambiguous insert/update queries
     * - Sorts arguments into fields & values
     * @param $table
     * @param $arguments
     * @return $this
     * @throws Exception\Argument
     */
    public function save($table, $arguments)
    {
        if (empty($table)) {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_table  = $table;
        $this->_clause = "SAVE";
        $this->_processArguments($arguments);

        return $this;
    }

    /**
     * Simple Query Generator:
     * - Sets generator variables for joins in query
     * @param $join
     * @param $on
     * @param array $fields
     * @return $this
     * @throws Exception\Argument
     */
    public function join($join, $on, $fields = [])
    {
        if (empty($join)) {
            throw new Exception\Argument("Invalid argument");
        }

        if (empty($on)) {
            throw new Exception\Argument("Invalid argument");
        }

        foreach ($fields as $field) {
            $this->_fields[] = $field;
        }
        $this->_join[]  = "JOIN {$join} ON {$on}";

        return $this;
    }

    /**
     * Simple Query Generator:
     * - Sets generator variables to limit query
     * @param $limit
     * @param int $page
     * @return $this
     * @throws Exception\Argument
     */
    public function limit($limit, $page = 1)
    {
        if (empty($limit)) {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_limit = $limit;
        $this->_offset = $limit * ($page - 1);

        return $this;
    }

    /**
     * Simple Query Generator:
     * - Sets generator variables for ordering in query
     * @param $order
     * @param string $direction
     * @return $this
     * @throws Exception\Argument
     */
    public function order($order, $direction = "asc")
    {
        if (empty($order)) {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_order     = $order;
        $this->_direction = $direction;

        return $this;
    }

    /**
     * Simple Query Generator:
     * - Create array of where clauses for query
     * @return $this
     * @throws Exception\Argument
     */
    public function where()
    {
        $arguments   = func_get_args();

        if (sizeof($arguments) < 1) {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_where[] = $arguments[0];
        $this->_whereArguments[] = $arguments[1];

        return $this;
    }

    /**
     * Simple Query Generator:
     * - Processes arguments for update/insert queries
     * @param $arguments
     */
    private function _processArguments($arguments)
    {
        foreach ($arguments as $field => $value) {
            $this->_fields[] = $field;
            $this->_values[] = $value;
        }
    }

    /**
     * Simple Query Generator:
     * - Finds the right sql builder by the clause
     * - Prepares &&|| runs query, returning statement
     * @return array|false|PDOStatement
     * @throws Exception\Argument
     * @throws Exception\Service
     */
    public function run()
    {
        switch ($this->_clause) {
            case "SELECT":
                return $this->string($this->_buildSelect(), $this->_arguments);
            case "INSERT":
                return $this->string($this->_buildInsert(), $this->_arguments);
            case "UPDATE":
                return $this->string($this->_buildUpdate(), $this->_arguments);
            case "DELETE":
                return $this->string($this->_buildDelete(), $this->_arguments);
            case "SAVE":
                if ($this->_where) {
                    return $this->string($this->_buildUpdate(), $this->_arguments);
                }
                return $this->string($this->_buildInsert(), $this->_arguments);
            default:
                throw new Exception\Argument("Invalid argument");
        }
    }

    /**
     * Simple Query Generator:
     * - Builds select statement from relevant variables
     * @return string
     */
    private function _buildSelect()
    {
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

        return "SELECT {$fields} {$from} {$join} {$where} {$order} {$limit}";
    }

    /**
     * Simple Query Generator:
     * - Builds insert statement from relevant variables
     * @return string
     */
    private function _buildInsert()
    {
        $table   = $this->_table;
        $fields  = implode(", ", $this->_fields);

        $_values = [];
        foreach ($this->_values as $value) {
            $this->_arguments[] = $value;
            $_values[]          = "?";
        }
        $values = implode(", ", $_values);

        return "INSERT INTO {$table} ({$fields}) VALUES ({$values})";
    }

    /**
     * Simple Query Generator:
     * - Builds update statement from relevant variables
     * @return string
     */
    private function _buildUpdate()
    {
        $table   = $this->_table;

        $_fields = [];
        foreach ($this->_fields as $field) {
            $_fields[] = "{$field} = ?";
        }
        $fields = implode(", ", $_fields);

        foreach ($this->_values as $value) {
            $this->_arguments[] = $value;
        }

        $where = $this->_buildWhere($this->_where);
        foreach ($this->_whereArguments as $whereArgument) {
            $this->_arguments[] = $whereArgument;
        }

        $limit = $this->_buildLimit($this->_limit, $this->_offset);

        return "UPDATE {$table} SET {$fields} {$where} {$limit}";
    }

    /**
     * Simple Query Generator:
     * - Builds delete statement from relevant variables
     * @return string
     */
    private function _buildDelete()
    {
        $table  = $this->_table;
        $where  = $this->_buildWhere($this->_where);
        foreach ($this->_whereArguments as $whereArgument) {
            $this->_arguments[] = $whereArgument;
        }
        $limit  = $this->_buildLimit($this->_limit, $this->_offset);

        return "DELETE FROM {$table} {$where} {$limit}";
    }

    /**
     * Simple Query Generator:
     * - Creates sql for WHERE from all where arguments
     * @param $where
     * @return string
     */
    private function _buildWhere($where)
    {
        $_where = implode(" AND ", $where);

        return ($_where) ? "WHERE {$_where}" : "";
    }

    /**
     * Simple Query Generator:
     * - Creates sql for LIMIT
     * @param $limit
     * @param $offset
     * @return string
     */
    private function _buildLimit($limit, $offset)
    {
        if ($limit) {
            return ($offset) ? "LIMIT {$limit}, {$offset}" : "LIMIT {$limit}";
        }
        return "";
    }
}
