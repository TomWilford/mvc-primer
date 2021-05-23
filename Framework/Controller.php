<?php

namespace Framework;

use Framework\Base;
use Framework\View;
use Framework\Events;
use Framework\Registry;
use Framework\Template;
use Framework\Controller\Exception;
use Framework\Router\Route;

class Controller extends Base
{
    /**
     * @var
     * @readwrite
     */
    protected $_parameters;

    /**
     * @var
     * @readwrite
     */
    protected $_layoutView;

    /**
     * @var
     * @readwrite
     */
    protected $_actionView;

    /**
     * @var
     * @readwrite
     */
    protected $_willRenderLayoutView = true;

    /**
     * @var
     * @readwrite
     */
    protected $_willRenderActionView = true;

    /**
     * @var
     * @readwrite
     */
    protected $_defaultPath = "Application/Views";

    /**
     * @var
     * @readwrite
     */
    protected $_defaultLayout = "layouts/standard";

    /**
     * @var
     * @readwrite
     */
    protected $_defaultExtension = "html";

    /**
     * @var
     * @readwrite
     */
    protected $_defaultContentType = "text/html";

    /**
     * @var
     * @read
     */
    protected $_name;

    protected function _getExceptionForImplementation($method)
    {
        return new Exception\Implementation("{$method} method not implemented");
    }

    protected function _getExceptionForArgument()
    {
        return new Exception\Argument("Invalid argument");
    }

    public function render()
    {
        Events::fire("framework.controller.render.before", [$this->name]);

        $defaultContentType = $this->getDefaultContentType();
        $results  = null;

        $doAction = $this->getWillRenderActionView() && $this->getActionView();
        $doLayout = $this->getWillRenderLayoutView() && $this->getLayoutView();

        try {
            if ($doAction) {
                $view    = $this->getActionView();
                $results = $view->render();
            }

            if ($doLayout) {
                $view    = $this->getLayoutView();
                $view->set("template", $results);
                $results = $view->render();

                header("Content-type: {$defaultContentType}");
                echo $results;
            } elseif ($doAction) {
                header("Content-type: {$defaultContentType}");
                echo $results;

                $this->setWillRenderLayoutView(false);
                $this->setWillRenderActionView(false);
            }
        } catch (\Exception $e) {
            throw new View\Exception\Renderer("Invalid layout/template syntax");
        }

        Events::fire("framework.controller.render.after", [$this->name]);
    }

    public function __destruct()
    {
        Events::fire("framework.controller.destruct.before", [$this->name]);

        $this->render();

        Events::fire("framework.controller.destruct.after", [$this->name]);
    }

    public function __construct($options = [])
    {
        parent::__construct($options);

        Events::fire("framework.controller.construct.before", [$this->name]);

        if ($this->getWillRenderLayoutView()) {
            $defaultPath      = $this->getDefaultPath();
            $defaultLayout    = $this->getDefaultLayout();
            $defaultExtension = $this->getDefaultExtension();

            $view = new View([
                "file" => APP_PATH."/{$defaultPath}/{$defaultLayout}.{$defaultExtension}"
            ]);

            $this->setLayoutView($view);
        }

        if ($this->getWillRenderActionView()) {
            /** @var Router $router */
            $router = Registry::get("router");
            $controller = $router->getController();
            $action = $router->getAction();

            $view = new View([
               "file" => APP_PATH . "/{$defaultPath}/{$controller}/{$action}.{$defaultExtension}"
            ]);

            $this->setActionView($view);
        }

        Events::fire("framework.controller.construct.after", [$this->name]);
    }

    protected function getName()
    {
        if (empty($this->_name)) {
            $this->_name = get_class($this);
        }

        return $this->_name;
    }
}
