<?php


namespace Framework;

use Framework\Base as Base;
use Framework\ArrayMethods as ArrayMethods;
use Framework\StringMethods as StringMethods;
//use Framework\Template\Exception as Exception;
use Framework\Core\Exception as Exception;

class Template extends Base
{
    /**
     * @var
     * @readwrite
     */
    protected $_implementation;

    /**
     * @var string
     * @readwrite
     */
    protected $_header = "if (is_array(\$_data) && sizeof(\$_data)) extract(\$_data); \$_text = array();";

    /**
     * @var string
     * @readwrite
     */
    protected $_footer = "return implde(\$_text);";

    /**
     * @var
     * @read
     */
    protected $_code;

    /**
     * @var
     * @read
     */
    protected $_function;

    public function _getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} method not implemented");
    }

    protected function _arguments($source, $expression)
    {
        $args = $this->_array($expression, array(
            $expression => array(
                "opener" => "{",
                "closer" => "}",
            )
        ));

        $tags      = $args["tags"];
        $arguments = array();
        $sanitised = StringMethods::sanitise($expression, "()[],.<>*$@");

        foreach ($tags as $i => $tag)
        {
            $sanitised = str_replace($tag, "(.*)", $sanitised);
            $tags[$i]  = str_replace(array("{", "}"), "", $tag);
        }

        if (preg_match("#{$sanitised}#", $source, $matches))
        {
            foreach ($tags as $i => $tag)
            {
                $arguments[$tag] = $matches[$i + 1];
            }
        }

        return $arguments;
    }

    protected function _tag($source)
    {
        $tag       = null;
        $arguments = array();

        $match = $this->_implementation->match($source);
        if ($match == null)
        {
            return false;
        }

        $delimiter = $match["delimiter"];
        $type      = $match["type"];

        $start     = strlen($type["opener"]);
        $end       = strpos($source, $type["closer"]);
        $extract   = substr($source, $start, $end - $start);

        if (isset($type["tags"]))
        {

            $tags = implode("|", array_keys($type["tags"]));
            $regex = "#^(/){0,1}({$tags})\s*(.*)$#";

            if (!preg_match($regex, $extract, $matches))
            {
                return false;
            }

            $tag     = $matches[2];
            $extract = $matches[3];
            $closer  = !!$matches[1];
        }

        if ($tag && $closer)
        {
            return array(
                "tag"       => $tag,
                "delimiter" => $delimiter,
                "closer"    => true,
                "source"    => false,
                "arguments" => false,
                "isolated"  => $type["tags"][$tag]["isolated"]
            );
        }

        if (isset($type["arguments"]))
        {
            $arguments = $this->_arguments($extract, $type["arguments"]);
        }
        else if ($tag && isset($type["tags"][$tag]["arguements"]))
        {
            $arguments = $this->_arguments($extract, $type["tags"][$tag]["arguements"]);
        }

        return array(
            "tag" => $tag,
            "delimiter" => $delimiter,
            "closer" => false,
            "source" => $extract,
            "arguments" => $arguments,
            "isolated" => (!empty($type["tags"]) ? $type["tags"][$tag]["isolated"] : false)
        );
    }

}