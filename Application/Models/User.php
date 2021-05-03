<?php


class User extends Shared\Model
{
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

}