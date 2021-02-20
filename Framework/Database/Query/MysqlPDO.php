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

    public function run($sql, $arguments = [])
    {
        return $this->connector->q($sql, $arguments);
    }

    public function countAllFrom($table)
    {
        $result = $this->connector->execute("
            SELECT COUNT(*) FROM {$table}
        ");

        return $result->fetchColumn();
    }
}