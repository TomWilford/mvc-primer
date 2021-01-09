<?php


namespace Framework\Database\Query;

use Framework\ArrayMethods as ArrayMethods;
use Framework\Database as Database;
//use Framework\Database\Exception as Exception;
use Framework\Core\Exception as Exception;

class Mysql extends Database\Query
{
    public function run($sql, $args = [])
    {
        if (!$args)
        {
            return $this->connector->query($sql);
        }
        $stmt = $this->connector->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }

}