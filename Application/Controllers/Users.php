<?php

use Shared\Controller;
use Framework\Registry;
use Framework\RequestMethods;

class Users extends Controller
{
    public function register()
    {
        if (RequestMethods::post("register"))
        {
            $first    = RequestMethods::post("first");
            $last     = RequestMethods::post("last");
            $email    = RequestMethods::post("email");
            $password = RequestMethods::post("password");
            $honeypot = RequestMethods::post("honeypot");

            $view  = $this->getActionView();
            $error = false;

            if (!empty($honeypot))
            {
                $error = true;
            }

            if (empty($first))
            {
                $view->set("first_error", "First name not provided");
                $error = true;
            }

            if (empty($last))
            {
                $view->set("last_error", "Last name not provided");
                $error = true;
            }

            if (empty($email))
            {
                $view->set("email_error", "Email address not provided");
                $error = true;
            }

            if (empty($password))
            {
                $view->set("password_error", "Password not provided");
                $error = true;
            }

            if (!$error)
            {
                $user = new User([
                   "first"    => $first,
                   "last"     => $last,
                   "email"    => $email,
                   "password" => $password
                ]);

                $user->save();
                $view->set("success", true);
            }
        }
    }
}