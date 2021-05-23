<?php

namespace Models;

use Shared\Model;

class Message extends Model
{
    /**
     * @var
     * @readwrite
     */
    protected $_table = "message";

    /**
     * @var $_body
     * @column
     * @readwrite
     * @type text
     * @length 256
     *
     * @validate required
     * @label body
     */
    protected $_body;

    /**
     * @var $_message
     * @column
     * @readwrite
     * @type integer
     */
    protected $_message;

    /**
     * @var $_user
     * @column
     * @readwrite
     * @type integer
     */
    protected $_user;

    public function getReplies()
    {
        return self::all([
            "message = ?" => $this->getId(),
            "id != ?" => $this->getId(),
            "live = ?"   => true,
            "deleted = ?" => false
        ],
        ["*"], "created", "asc");
    }

    public static function fetchReplies($id)
    {
        $message = new Message([
            "id" => $id
        ]);

        return $message->getReplies();
    }
}
