<?php

namespace Framework;

use ArrayIterator;
use Framework\Base;
use Framework\ArrayMethods;
use Framework\StringMethods;
use Framework\Template\Exception;

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
    protected $_header = "if (is_array(\$_data) && sizeof(\$_data)){ extract(\$_data);} \$_text = [];";

    /**
     * @var string
     * @readwrite
     */
    protected $_footer = "return implode(' ', \$_text);";

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
        $args      = $this->_array([$expression]);
        $tags      = $args["tags"];
        $arguments = [];
        $sanitised = StringMethods::sanitise($expression, "()[],.<>*$@");

        foreach ($tags as $i => $tag)
        {
            $sanitised = str_replace($tag, "(.*)", $sanitised);
            $tags[$i]  = str_replace(["{", "}"], "", $tag);
        }

        if (preg_match("#{$sanitised}#", $source, $matches))
        {
            $tags = array_values($tags);
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
        $arguments = [];

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
            return [
                "tag"       => $tag,
                "delimiter" => $delimiter,
                "closer"    => true,
                "source"    => false,
                "arguments" => false,
                "isolated"  => $type["tags"][$tag]["isolated"]
            ];
        }

        if (isset($type["arguments"]))
        {
            $arguments = $this->_arguments($extract, $type["arguments"]);
        }
        else if ($tag && isset($type["tags"][$tag]["arguments"]))
        {
            $arguments = $this->_arguments($extract, $type["tags"][$tag]["arguments"]);
        }

        return [
            "tag"       => $tag,
            "delimiter" => $delimiter,
            "closer"    => false,
            "source"    => $extract,
            "arguments" => $arguments,
            "isolated"  => (!empty($type["tags"]) ? $type["tags"][$tag]["isolated"] : false)
        ];
    }

    protected function _array($sourceArray)
    {
        $parts       = [];
        $tags        = [];
        $all         = [];

        $type        = null;
        $delimiter   = null;

        $source = new ArrayIterator($sourceArray);

        foreach ($source as $item)
        {
            $match     = $this->_implementation->match($item);
            $type      = $match["type"];
            $delimiter = $match["delimiter"];

            $opener    = strpos($item, $type["opener"]);

            if ($opener !== false)
            {
                $closer  = strpos($item, $type["closer"]) + strlen($type["closer"]);
                $parts[] = substr($item, 0, $opener);
                $tags[]  = substr($item, $opener, $closer - $opener);
                $source->append(substr($item, $closer));
            }
            else
            {
                $parts[] = $item;
                unset($item);
            }
        }

        foreach ($parts as $i => $part)
        {
            $all[] = $part;
            if (isset($tags[$i]))
            {
                $all[] = $tags[$i];
            }
        }

        return [
            "text" => ArrayMethods::clean($parts),
            "tags" => ArrayMethods::clean($tags),
            "all"  => ArrayMethods::clean($all)
        ];
    }

    protected function _tree($array)
    {
        $root = [
            "children" => []
        ];
        $current =& $root;

        foreach ($array as $i => $node)
        {
            $result = $this->_tag($node);

            if ($result)
            {
                $tag = $result["tag"] ?? "";
                $arguments = $result["arguments"] ?? "";

                if ($tag)
                {
                    if (!$result["closer"])
                    {
                        $last = ArrayMethods::last($current["children"]);

                        if ($result["isolated"] && is_string($last))
                        {
                            array_pop($current["children"]);
                        }

                        $current["children"][] = [
                            "index"     => $i,
                            "parent"    => &$current,
                            "children"  => [],
                            "raw"       => $result["source"],
                            "tag"       => $tag,
                            "arguments" => $arguments,
                            "delimiter" => $result["delimiter"],
                            "number"    => sizeof($current["children"])
                        ];
                        $current =& $current["children"][sizeof($current["children"]) - 1];
                    }
                    else if (isset($current["tag"]) && $result["tag"] == $current["tag"])
                    {
                        $start             = $current["index"] + 1;
                        $length            = $i - $start;
                        $current["source"] = implode(" ", array_slice($array, $start, $length));
                        $current           =& $current["parent"];
                    }
                }
                else
                {
                    $current["children"][] = [
                        "index"     => $i,
                        "parent"    => &$current,
                        "children"  => [],
                        "raw"       => $result["source"],
                        "tag"       => $tag,
                        "arguments" => $arguments,
                        "delimiter" => $result["delimiter"],
                        "number"    => sizeof($current["children"])
                    ];
                }
            }
            else
            {
                $current["children"][] = $node;
            }
        }

        return $root;
    }

    protected function _script($tree)
    {
        $content = [];

        if (is_string($tree))
        {
            $tree = addslashes($tree);
            return "{$tree}";
        }

        if (sizeof($tree["children"]) > 0)
        {
            foreach ($tree["children"] as $child)
            {
                $content[] = $this->_script($child);
            }
        }

        if (isset($tree["parent"]))
        {
            return $this->_implementation->handle($tree, implode(" ", $content));
        }

        return implode(" ", $content);
    }

    public function parse($template)
    {
        if (!is_a($this->_implementation, "Framework\Template\Implementation"))
        {
            throw new Exception\Implementation();
        }
        $array           = $this->_array($template);
        $tree            = $this->_tree($array["all"]);

        $this->_code     = $this->header.$this->_script($tree).$this->footer;
        $data            = "\$_data";
        $this->_function = function($_data)
        {
            var_dump($this->code);
            // If eval() is the answer, you're almost certainly asking the wrong question. -- Rasmus Lerdorf
            // Sorry! -- Tom Wilford
            return eval($this->code);
        };

        return $this;
    }

    public function process($data = [])
    {
        if ($this->_function == null)
        {
            throw new Exception\Parser();
        }

        try
        {
            $function = $this->_function;

            return $function($data);
        }
        catch (\Exception $e)
        {
            throw new Exception\Parser($e);
        }
    }


}