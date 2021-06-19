<?php
namespace Shared;

class Markup
{
    public function __construct()
    {
        // nowt
    }

    public function __clone()
    {
        // nowt
    }

    public static function errors($array, $key, $separator = "<br>", $before = "", $after = "")
    {
        if (isset($array[$key])) {
            return $before . join($separator, $array[$key]) . $after;
        }

        return "";
    }
}
