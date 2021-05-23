<?php

namespace Framework\Template\Implementation;

use Framework\Request;
use Framework\Registry;
use Framework\Template;
use Framework\StringMethods;
use Framework\RequestMethods;

class Extended extends Standard
{
    /**
     * @var string $_defaultPath
     * @readwrite
     */
    protected $_defaultPath = "Application/Views";

    /**
     * @var string $_defaultKey
     * @readwrite
     */
    protected $_defaultKey = "_data";

    /**
     * @var int $_index;
     * @readwrite
     */
    protected $_index = 0;

    public function __construct($options = [])
    {
        parent::__construct($options);

        $this->_map = [
            "partial" => [
                "opener"  => "{partial",
                "closer"  => "}",
                "handler" => "_partial"
            ],
            "include" => [
                "opener"  => "{include",
                "closer"  => "}",
                "handler" => "_include"
            ],
            "yield" => [
                "opener"  => "{yield",
                "closer"  => "}",
                "handler" => "_yield"
            ]
        ] + $this->_map;

        $this->_map["statement"]["tags"] = [
            "set" => [
                "isolated"  => false,
                "arguments" => "{key}",
                "handler"   => "set"
            ],
            "append" => [
                "isolated"  => false,
                "arguments" => "{key}",
                "handler"   => "append"
            ],
            "prepend" => [
                "isolated"  => false,
                "arguments" => "{key}",
                "handler"   => "prepend"
            ]
        ] + $this->_map["statement"]["tags"];
    }

    protected function _include($tree, $content)
    {
        $template = new Template([
            "implementation" => new self()
        ]);

        $file       = trim($tree["raw"]);
        $path       = $this->defaultPath;

        $content    = [];
        $content[]  = file_get_contents(APP_PATH."/{$path}/{$file}");
        $template->parse($content);
        $index = $this->_index++;

        return "\$_anon = function(\$_data){
                    " . $template->code . "
                };
                \$_text[] = \$_anon(\$_data);";
    }

    protected function _partial($tree, $content)
    {
        $address = trim($tree['raw'], "/");

        if (StringMethods::indexOf($address, "http") != 0) {
            $host    = RequestMethods::server("HTTP_HOST");
            $address = "http://{$host}/{$address}";
        }

        $request = new Request();
        $response = addslashes(trim($request->get($address)));

        return "\$_text[] = \"{$response}\";";
    }

    protected function _getKey($tree)
    {
        if (empty($tree["arguments"]["key"])) {
            return null;
        }

        return trim($tree["arguments"]["key"]);
    }

    protected function _setValue($key, $value)
    {
        if (!empty($key)) {
            $data = Registry::get($this->defaultKey, []);
            $data[$key] = $value;

            Registry::set($this->defaultKey, $data);
        }
    }

    protected function _getValue($key)
    {
        $data = Registry::get($this->defaultKey);

        if (isset($data[$key])) {
            return $data[$key];
        }

        return "";
    }

    public function set($key, $value)
    {
        if (StringMethods::indexOf($value, "\$_text") > -1) {
            $first = StringMethods::indexOf($value, "\"");
            $last  = StringMethods::lastIndexOf($value, "\"");
            $value = stripslashes(substr($value, $first + 1, ($last - $first) - 1));
        }

        if (is_array($key)) {
            $key = $this->_getKey($key);
        }

        $this->_setValue($key, $value);
    }

    public function append($key, $value)
    {
        if (is_array($key)) {
            $key = $this->_getKey($key);
        }

        $previous = $this->_getValue($key);
        $this->set($key, $previous . $value);
    }

    public function prepend($key, $value)
    {
        if (is_array($key)) {
            $key = $this->_getKey($key);
        }

        $previous = $this->_getValue($key);
        $this->set($key, $value . $previous);
    }

    public function yield($tree, $content)
    {
        $key   = trim($tree["raw"]);
        $value = addslashes($this->_getValue($key));

        return "\$_text[] = \"{$value}\";";
    }
}
