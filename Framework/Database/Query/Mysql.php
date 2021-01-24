<?php

namespace Framework\Database\Query;

use Framework\ArrayMethods;
use Framework\Database;
//use Framework\Database\Exception;
use Framework\Core\Exception;

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