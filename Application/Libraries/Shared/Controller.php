<?php

namespace Shared;

use Framework\Database\Connector\MysqlPDO;
use Framework\Events;
use Framework\Registry;
use Framework\Session\Driver\Server;
use Models\User;

class Controller extends \Framework\Controller
{
    /**
     * @var $_user
     * @readwrite
     */
    protected $_user;

    /**
     * Controller constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        parent::__construct($options);

        /** @var MysqlPDO $database */
        $database = Registry::get("database");
        $database->connect();

        // schedule: load user from session
        Events::add("framework.router.beforehooks.before",
            function ($name, $parameters) {
                /** @var Server $session */
                $session = Registry::get("session");

                /** @var \Framework\Controller $controller */
                $controller = Registry::get("controller");

                /** @var User $user */
                $user = $session->get("user");

                if ($user) {
                    $controller->user = User::first([
                        "id = ?" => $user
                    ]);
                }
            }
        );

        Events::add("framework.router.beforehooks.after",
            function ($name, $parameters) {
                /** @var Server $session */
                $session = Registry::get("session");

                /** @var \Framework\Controller $controller */
                $controller = Registry::get("controller");

                if ($controller->user) {
                    $session->set("user", $controller->user->id);
                }
            }

        );

        Events::add("framework.controller.destruct.after",
            function ($name) {
                /** @var MysqlPDO $database */
                $database = Registry::get("database");
                $database->disconnect();
            }
        );
    }

    public function render()
    {
        if ($this->getUser())
        {
            if ($this->getActionView()) {
                $this->getActionView()->set("user", $this->getUser());
            }

            if ($this->getLayoutView()) {
                $this->getLayoutView()->set("user", $this->getUser());
            }
        }

        parent::render();
    }

    public function setUser($user)
    {
        /** @var Server $session */
        $session = Registry::get("session");

        if ($user) {
            $session->set("user", $user->id);
        } else {
            $session->erase("user");
        }

        $this->_user = $user;

        return $this;
    }

    public static function redirect($url)
    {
        header("Location: {$url}");
        exit();
    }

    /**
     * @protected
     */
    public function _secure()
    {
        $user = $this->getUser();

        if (!$user) {
            self::redirect("/public/login");
        }
    }

    /**
     * @throws \Framework\Router\Exception\Controller
     * @protected
     */
    public function _admin()
    {
        if (!$this->user->admin) {
            throw new \Framework\Router\Exception\Controller("Not a valid admin user account");
        }
    }
}
