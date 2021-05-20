<?php

namespace Models;

use phpDocumentor\Reflection\Types\Context;
use Shared\Model;

class File extends Model
{
    /**
     * @var
     * @readwrite
     */
    protected $_table = "file";

    /**
     * @var $_name
     * @column
     * @readwrite
     * @type text
     * @length 255
     */
    protected $_name;

    /**
     * @var $_mime
     * @column
     * @readwrite
     * @type text
     * @length 32
     */
    protected $_mime;

    /**
     * @var $_size
     * @column
     * @readwrite
     * @type integer
     */
    protected $_size;

    /**
     * @var $_width
     * @column
     * @readwrite
     * @type integer
     */
    protected $_width;

    /**
     * @var $_height
     * @column
     * @readwrite
     * @type integer
     */
    protected $_height;

    /**
     * @var $_user
     * @column
     * @readwrite
     * @type integer
     */
    protected $_user;
}