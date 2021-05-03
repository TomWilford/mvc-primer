<?php

namespace Shared;

class Model extends \Framework\Model
{
    /**
     * @var $_id
     * @column
     * @readwrite
     * @primary
     * @type autonumber
     */
    protected $_id;

    /**
     * @var $_live
     * @column
     * @readwrite
     * @type boolean
     * @index
     */
    protected $_live;

    /**
     * @var $_deleted
     * @column
     * @readwrite
     * @type boolean
     * @index
     */
    protected $_deleted;

    /**
     * @var $_created
     * @column
     * @readwrite
     * @type datetime
     */
    protected $_created;

    /**
     * @var $_modified
     * @column
     * @readwrite
     * @type datetime
     */
    protected $_modified;

    public function save()
    {
        $primary = $this->getPrimaryColumn();
        $raw = $primary["raw"];
        $now = new \DateTime();

        if (empty($this->$raw))
        {
            $this->setCreated($now->format("Y-m-d H:i:s"));
            $this->setDeleted(false);
            $this->setLive(true);
        }
        $this->setModified($now->format("Y-m-d H:i:s"));

        parent::save();
    }
}