<?php

namespace Framework;

use Framework\Events;
use Framework\Template;
use Framework\View\Exception;

class View extends Base
{
    /**
     * @var $_file
     * @readwrite
     */
    protected $_file;

    /**
     * @var $_data
     * @readwrite
     */
    protected $_data;

    /**
     * @var $_template
     * @read
     */
    protected $_template;

    public function __construct($options = [])
    {
        parent::__construct($options);

        Events::fire("framework.view.construct.before", array($this->file));

        $this->_template = new Template([
            "implementation" => new Template\Implementation\Extended()
        ]);

        Events::fire("framework.view.construct.after", array($this->file, $this->template));
    }

    public function _getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} method not implemented");
    }

    public function render()
    {
        Events::fire("framework.view.render.before", array($this->file));

        if (!file_exists($this->file))
        {
            return "";
        }
        $content = [];
        $content[] = file_get_contents($this->file);
        return $this
            ->template
            ->parse($content)
            ->process($this->data);
    }

    public function get($key, $default = "")
    {
        if (isset($this->data[$key]))
        {
            return $this->data[$key];
        }

        return $default;
    }

    protected function _set($key, $value)
    {
        if (!is_string($key) && !is_numeric($key))
        {
            throw new Exception\Data("Key must be a string or a number");
        }

        $data = $this->data;

        if (!$data)
        {
            $data = [];
        }

        $data[$key] = $value;
        $this->data = $data;
    }

    public function set($key, $value = null)
    {
        if (is_array($key))
        {
            foreach ($key as $_key => $value)
            {
                $this->_set($_key, $value);
            }

            return $this;
        }

        $this->_set($key, $value);

        return $this;
    }

    public function erase($key)
    {
        unset($this->data[$key]);

        return $this;
    }
}