<?php

namespace Framework\Request;

use Framework\Base;
use Framework\Request\Exception;

class Response extends Base
{
    /**
     * @var $_response
     * @readwrite
     */
    protected $_response;

    /**
     * @var $_body
     * @read
     */
    protected $_body = null;

    /**
     * @var array $_headers
     * @read
     */
    protected $_headers = [];

    protected function _getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} not implemented");
    }

    public function __construct($options = [])
    {
        if (!empty($options["response"])) {
            $response = $this->_response = $options["response"];
            unset($options["response"]);
        }

        parent::__construct($options);

        $pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';
        preg_match_all($pattern, $response, $matches);

        $headers = array_pop($matches[0]);
        $headers = explode("\r\n", str_replace("\r\n\r\n", "", $headers));

        $this->_body = str_replace($headers, "", $response);

        $version = array_shift($headers);
        preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $version, $matches);

        $this->_headers["Http-Version"] = $matches[1];
        $this->_headers["Status-Code"]  = $matches[2];
        $this->_headers["Status"]       = $matches[2] . " " . $matches[3];

        foreach ($headers as $header) {
            preg_match('#(.*?)\:\s(.*)#', $header, $matches);
            $this->_headers[$matches[1]] = $matches[2];
        }
    }

    public function __toString()
    {
        return $this->body;
    }
}
