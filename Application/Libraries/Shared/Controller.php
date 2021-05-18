<?php

namespace Shared;

use Framework\Database\Connector\MysqlPDO;
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
        /** @var MysqlPDO $database */
        /** @var Server $session */
        /** @var User $user */

        parent::__construct($options);

        $database = \Framework\Registry::get("database");
        $database->connect();

        $session = \Framework\Registry::get("session");
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