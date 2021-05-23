<?php

namespace Framework;

class Test
{
    private static $_tests = [];

    public static function add($callback, $title = "Unnamed Test", $set = "general")
    {
        self::$_tests[] = [
            "set"      => $set,
            "title"    => $title,
            "callback" => $callback
        ];
    }

    public static function run($before = null, $after = null)
    {
        if ($before)
        {
            $before(self::$_tests);
        }

        $failed     = [];
        $passed     = [];
        $exceptions = [];

        foreach (self::$_tests as $test)
        {
            $executionTime = null;
            try
            {
                $start         = microtime(true);
                $result        = call_user_func($test["callback"]);
                $executionTime = microtime(true) - $start;

                if ($result)
                {
                    $passed[] = [
                        "set"   => $test["set"],
                        "title" => $test["title"],
                        "execution_time"  => $executionTime
                    ];
                }
                else
                {
                    $failed[] = [
                        "set"   => $test["set"],
                        "title" => $test["title"],
                        "execution_time"  => $executionTime
                    ];
                }
            }
            catch (\Exception $e)
            {
                $errorCode = $e->getCode() ? " - " . $e->getCode() : "";
                $exceptions[] = [
                    "set"     => $test["set"],
                    "title"   => $test["title"],
                    "execution_time"  => $executionTime,
                    "type"    => get_class($e),
                    "message" => $e->getMessage() . $errorCode . " on " . $e->getFile() .":". $e->getLine()
                ];
            }
        }

        if ($after)
        {
            $after(self::$_tests);
        }

        return [
            "passed"     => $passed,
            "failed"     => $failed,
            "exceptions" => $exceptions
        ];
    }
}
