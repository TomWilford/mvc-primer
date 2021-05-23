<?php

namespace Framework;

use Framework\Base;
use Framework\Events;
use Framework\Registry;
use Framework\Session\Exception;

class Session extends Base
{
    /**
     * @var $_type
     * @readwrite
     */
    protected $_type;

    /**
     * @var $_options
     * @readwrite
     */
    protected $_options;

    protected function getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} not implemented");
    }

    public function initialise()
    {
        Events::fire("framework.session.initialize.before", [$this->type, $this->options]);

        if (!$this->type) {
            /** @var Configuration $configuration */
            $configuration = Registry::get("configuration");

            if ($configuration) {
                $configuration = $configuration->initialise();
                $parsed = $configuration->parse("../Application/Configuration/_session");

                if (!empty($parsed->session->default) && !empty($parsed->session->default->type)) {
                    $this->type = $parsed->session->default->type;
                    unset($parsed->session->default->type);
                    $this->options = (array)$parsed->session->default;
                }
            }
        }

        if (!$this->type) {
            throw new Exception\Argument("Invalid type");
        }

        Events::fire("framework.session.initialize.after", [$this->type, $this->options]);

        switch ($this->type) {
            case "server":
                return new Session\Driver\Server($this->options);
            default:
                throw new Exception\Argument("Invalid type");
        }
    }
}
