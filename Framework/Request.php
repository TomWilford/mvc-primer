<?php

namespace Framework;

use Framework\Base;
//use Framework\Events;
use Framework\StringMethods;
use Framework\RequestMethods;
use Framework\Request\Exception;

class Request extends Base
{
    /**
     * @var $_request
     * @readwrite
     */
    protected $_request;

    /**
     * @var bool $_willFollow
     * @readwrite
     */
    public $_willFollow = true;

    /**
     * @var bool $_willShareSession
     * @readwrite
     */
    protected $_willShareSession = true;

    /**
     * @var array $_headers
     * @readwrite
     */
    protected $_headers = [];

    /**
     * @var array $_options
     * @readwrite
     */
    protected $_options = [];

    /**
     * @var $_referer
     * @readwrite
     */
    protected $_referer;

    /**
     * @var $_agent
     * @readwrite
     */
    protected $_agent;

    protected function _getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} not implemented");
    }

    public function __construct($options = [])
    {
        parent::__construct($options);
        $this->agent = RequestMethods::server("HTTP_USER_AGENT", "Curl/PHP".PHP_VERSION);
    }

    public function delete($url, $parameters = [])
    {
        return $this->request("DELETE", $url, $parameters);
    }

    public function get($url, $parameters = [])
    {
        if (!empty($parameters))
        {
            $url .= StringMethods::indexOf($url, "?") ? "&" : "?";
            $url .= is_string($parameters) ? $parameters : http_build_query($parameters, "", "&");
        }

        return $this->request("GET", $url);
    }

    public function head($url, $parameters = [])
    {
        return $this->request("HEAD", $url, $parameters);
    }

    public function post($url, $parameters = [])
    {
        return $this->request("POST", $url, $parameters);
    }

    public function put($url, $parameters = [])
    {
        return $this->request("PUT", $url, $parameters);
    }

    public function request($method, $url, $parameters = [])
    {
        //Events::fire("framework.request.request.before", [$method, $url, $parameters]);

        $request = $this->_request = curl_init();

        if (is_array($parameters))
        {
            $parameters = http_build_query($parameters, "", "&");
        }

        $this->_setRequestMethod($method)->_setRequestOptions($url, $parameters)->_setRequestHeaders();

        $response = curl_exec($request);

        if ($response)
        {
            $response = new Request\Response([
                "response" => $response
            ]);
        }
        else
        {
            throw new Exception\Response(ucfirst(curl_errno($request) . " - " . curl_error($request)));
        }

        //Events::fire("framework.request.request.after", [$method, $url, $parameters, $response]);

        curl_close($request);

        return $response;
    }

    protected function _setOption($key, $value)
    {
        curl_setopt($this->_request, $key, $value);

        return $this;
    }

    protected function _normalise($key)
    {
        return "CURLOPT_" . str_replace("CURLOPT_", "", strtoupper($key));
    }

    protected function _setRequestMethod($method)
    {
        switch (strtoupper($method))
        {
            case "HEAD":
                $this->_setOption(CURLOPT_NOBODY, true);
                break;
            case "GET":
                $this->_setOption(CURLOPT_HTTPGET, true);
                break;
            case "POST":
                $this->_setOption(CURLOPT_POST, true);
                break;
            default:
                $this->_setOption(CURLOPT_CUSTOMREQUEST, $method);
                break;
        }

        return $this;
    }

    protected function _setRequestOptions($url, $parameters)
    {
        $this
            ->_setOption(CURLOPT_URL, $url)
            ->_setOption(CURLOPT_HEADER, true)
            ->_setOption(CURLOPT_RETURNTRANSFER, true)
            ->_setOption(CURLOPT_USERAGENT, $this->agent);

        if (!empty($parameters))
        {
            $this->_setOption(CURLOPT_POSTFIELDS, $parameters);
        }

        if ($this->willFollow)
        {
            $this->_setOption(CURLOPT_FOLLOWLOCATION, true);
        }

        if ($this->willShareSession)
        {
            $this->_setOption(CURLOPT_COOKIE, session_name() . "=" . session_id());
        }

        if ($this->referer)
        {
            $this->_setOption(CURLOPT_REFERER, $this->referer);
        }

        foreach ($this->_options as $key => $value)
        {
            $this->_setOption(constant($this->_normalise($key)), $value);
        }

        return $this;
    }

    protected function _setRequestHeaders()
    {
        $headers = [];

        foreach ($this->headers as $key => $value)
        {
            $headers[] = $key . ': ' . $value;
        }

        $this->_setOption(CURLOPT_HTTPHEADER, $headers);

        return $this;
    }
}