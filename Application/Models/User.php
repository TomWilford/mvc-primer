<?php

namespace Models;

class User extends \Shared\Model
{
    /**
     * @var
     * @readwrite
     */
    protected $_table = "user";

    /**
     * @var $_first
     * @column
     * @readwrite
     * @type text
     * @length 100
     */
    protected $_first;

    /**
     * @var $_last
     * @column
     * @readwrite
     * @type text
     * @length 100
     */
    protected $_last;

    /**
     * @var $_email
     * @column
     * @readwrite
     * @type text
     * @length 100
     * @index
     */
    protected $_email;

    /**
     * @var $_password
     * @column
     * @readwrite
     * @type text
     * @length 500
     * @index
     */
    protected $_password;

    public function save()
    {
        $this->table = "user";

        parent::save();
    }
}