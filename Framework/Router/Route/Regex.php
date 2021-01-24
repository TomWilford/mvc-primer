<?php

namespace Framework\Router\Route;

use Framework\Router;

class Regex extends Router\Route
{
    /**
     * @readwrite
     * @var
     */
    protected $_keys;

    public function matches($url)
    {
        $url     = $this->returnMatchingAlias($url);
        $pattern = $this->pattern;

        preg_match_all("#^{$pattern}$#", $url, $values);

        if (sizeof($values) && sizeof($values[0]) && sizeof($values[1]))
        {
            $derived = array_combine($this->keys, $values[1]);
            $this->parameters = array_merge($this->parameters, $derived);

            return true;
        }

        return false;
    }
}