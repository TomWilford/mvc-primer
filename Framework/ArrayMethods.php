<?php


namespace Framework;

class ArrayMethods
{
    /**
     * ArrayMethods constructor.
     */
    private function __construct()
    {
        // do nothing
    }

    /**
     *
     */
    private function __clone()
    {
        // do nothing
    }

    /**
     * @param $array
     * @return array
     */
    public static function clean($array){
        return array_filter($array, function ($item) {
            return !empty($item);
        });
    }

    /**
     * @param $array
     * @return array|string[]
     */
    public static function trim($array){
        return array_map(function ($item){
            return trim($item);
        }, $array);
    }
}
