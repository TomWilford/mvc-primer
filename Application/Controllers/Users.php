<?php

namespace Controllers;

use Models\User;
use Shared\Controller;
use Framework\Registry;
use Framework\RequestMethods;

class Users extends Controller
{
    public function register()
    {
        $view  = $this->getActionView();

        if (RequestMethods::post("register"))
        {
            $first    = RequestMethods::post("first");
            $last     = RequestMethods::post("last");
            $email    = RequestMethods::post("email");
            $password = RequestMethods::post("password");
            $honeypot = RequestMethods::post("honeypot");

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
        else
        {
            $view->set("success", false);
        }
        echo $view->render();
    }

    public function login()
    {
        if (RequestMethods::post("login"))
        {
            $email    = RequestMethods::post("email");
            $password = RequestMethods::post("password");

            $view  = $this->getActionView();
            $error = false;

            if (empty($email))
            {
                $view->set("email_error", "Email not provided");
                $error = true;
            }

            if (empty($password))
            {
                $view->set("password_error", "Password not provided");
                $error = true;
            }

            if (!$error)
            {
                $user = User::first([
                    "email = ?"    => $email,
                    "password = ?" => $password,
                    "live = ?"     => true,
                    "deleted = ?"  => false
                ]);

                if (!empty($user))
                {
                    /** @var \Framework\Session $session */
                    $session = Registry::get("session");
                    $session->set("user", serialize($user));

                    header("Location: /users/profile.html");
                    exit();
                }
                else
                {
                    $view->set("password_error", "Email address and/or password are incorrect");
                }
            }
        }
    }

    public function profile()
    {
        $session = Registry::get("session");
        $user    = unserialize($session->get("user", null));

        if (empty($user))
        {
            $user = new StdClass();
            $user->first = "Mx. ";
            $user->last  = "Smyf";
        }

        $this->getActionView()->set("user", $user);
    }
}