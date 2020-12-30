<?php


namespace Framework;

class StringMethods
{
    /**
     * @var string`
     */
    private static $_delimiter = "#";

    /**
     * StringMethods constructor.
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
        // nowt
    }

    /**
     * @param $pattern
     * @return string
     */
    private static function _normalize($pattern)
    {
        return self::$_delimiter . trim($pattern, self::$_delimiter) . self::$_delimiter;
    }

    /**
     * @return string
     */
    public static function getDelimiter()
    {
        return self::$_delimiter;
    }

    /**
     * @param $delimiter
     */
    public static function setDelimiter($delimiter)
    {
        self::$_delimiter = $delimiter;
    }

    /**
     * @param $string
     * @param $pattern
     * @return mixed|null
     */
    public static function match($string, $pattern)
    {
        preg_match_all(self::_normalize($pattern), $string, $matches, PREG_PATTERN_ORDER);

        if (!empty($matches[1]))
        {
            return $matches[1];
        }

        if (!empty($matches[0]))
        {
            return $matches[0];
        }

        return null;
    }

    /**
     * @param $string
     * @param $pattern
     * @param null $limit
     * @return array|false|string[]
     */
    public static function split($string, $pattern, $limit = null){
        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
        return preg_split(self::_normalize($pattern), $string, $limit, $flags);
    }
}