<?php

namespace Controllers;

use Models\Friend;
use Models\Message;
use Shared\Controller;

class Home extends Controller
{
    public function index()
    {
        $user = $this->getUser();
        $view = $this->getActionView();

        if ($user)
        {
            $friends = Friend::all([
               "user = ?"    => $user->id,
               "live = ?"    => true,
               "deleted = ?" => false
            ],
            ["friend"]);

            $ids = [];
            foreach ($friends as $friend)
            {
                $ids[] = $friend->friend;
            }
            $ids = implode(", ", $ids);

            $messages = Message::all([
                "user in (?)" => $ids,
                "live = ?"    => true,
                "deleted = ?" => false
            ], ["*"],"created", "asc");

            $view->set("messages", $messages);
        }

        $view->render();
    }
}