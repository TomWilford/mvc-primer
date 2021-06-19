<?php
namespace Controllers;

use Models\Message;
use Shared\Controller;
use Framework\RequestMethods;

class Messages extends Controller
{
    public function add()
    {
        $user = $this->getUser();

        if (RequestMethods::post("share")) {
            $message = new Message([
                "body"    => RequestMethods::post("body"),
                "message" => RequestMethods::post("message"),
                "user"    => $user->id
            ]);

            if ($message->validate()) {
                $message->save();

                self::redirect("/public/");
            }
        }
    }
}
