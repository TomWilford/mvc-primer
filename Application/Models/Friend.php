<?php

namespace Models;

use Shared\Model;

class Friend extends Model
{
    /**
     * @var
     * @readwrite
     */
    protected $_table = "friend";

    /**
     * @var $_user
     * @column
     * @readwrite
     * @type integer
     */
    protected $_user;

    /**
     * @var $_friend
     * @column
     * @readwrite
     * @type integer
     */
    protected $_friend;
}
