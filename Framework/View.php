<?php

namespace Framework;

//use Framework\Events;
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

        $this->_template = new Template([
            "implementation" => new Template\Implementation\Standard()
        ]);
    }
}