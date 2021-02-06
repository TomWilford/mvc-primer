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
            try
            {
                $result = call_user_func($test["callback"]);

                if ($result)
                {
                    $passed[] = [
                        "set"   => $test["set"],
                        "title" => $test["title"]
                    ];
                }
                else
                {
                    $failed[] = [
                        "set"   => $test["test"],
                        "title" => $test["title"],
                    ];
                }
            }
            catch (\Exception $e)
            {
                $exceptions[] = [
                    "set"   => $test["test"],
                    "title" => $test["title"],
                    "type"  => get_class($e)
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