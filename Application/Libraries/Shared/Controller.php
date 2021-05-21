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

        Events::add("framework.controller.destruct.after",
            function ($name)
            {
                /** @var MysqlPDO $database */
                $database = Registry::get("database");
                $database->disconnect();
            }
        );

        /** @var Server $session */
        $session = \Framework\Registry::get("session");

        /** @var User $user */
        $user    = unserialize($session->get("user", null));
        $this->setUser($user);
    }

    public function render()
    {
        if ($this->getUser())
        {
            if ($this->getActionView())
            {
                $this->getActionView()->set("user", $this->getUser());
            }

            if ($this->getLayoutView())
            {
                $this->getLayoutView()->set("user", $this->getUser());
            }
        }

        parent::render();
    }
}