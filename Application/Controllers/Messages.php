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
                "body"    => RequestMethods::post("body", "", FILTER_SANITIZE_STRING),
                "message" => RequestMethods::post("message", "", FILTER_SANITIZE_STRING),
                "user"    => $user->id
            ]);

            if ($message->validate()) {
                $message->save();

                self::redirect("/public/");
            }
        }
    }
}
