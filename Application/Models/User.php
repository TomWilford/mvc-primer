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
     *
     * @validate required, alpha, min(3), max(32)
     * @label first name
     */
    protected $_first;

    /**
     * @var $_last
     * @column
     * @readwrite
     * @type text
     * @length 100
     *
     * @validate required, alpha, min(3), max(32)
     * @label last name
     */
    protected $_last;

    /**
     * @var $_email
     * @column
     * @readwrite
     * @type text
     * @length 100
     * @index
     *
     * @validate required, max(100)
     * @label email address
     */
    protected $_email;

    /**
     * @var $_password
     * @column
     * @readwrite
     * @type text
     * @length 500
     * @index
     *
     * @validate required, min(8), max(32)
     * @label password
     */
    protected $_password;

    public function save()
    {
        $this->table = "user";

        parent::save();
    }
}