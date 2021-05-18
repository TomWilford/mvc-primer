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
        $view  = $this->getActionView();

        if (RequestMethods::post("login"))
        {
            $email    = RequestMethods::post("email");
            $password = RequestMethods::post("password");

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
                    /** @var \Framework\Registry $session */
                    $session = Registry::get("session");
                    $session->set("user", serialize($user));

                    header("Location: /public/users/profile.html");
                    exit();
                }
                else
                {
                    $view->set("password_error", "Email address and/or password are incorrect");
                }
            }
        }

        echo $view->render();
    }

    public function profile()
    {
        $view = $this->getActionView();

        $session = Registry::get("session");
        $user    = unserialize($session->get("user", null));

        if (empty($user))
        {
            $user = new StdClass();
            $user->first = "Mx. ";
            $user->last  = "Smyf";
        }

        $view->set("user", $user);

        echo $view->render();
    }

    public function search()
    {
        $view = $this->getActionView();

        $query     = trim(RequestMethods::post("query"));
        $order     = RequestMethods::post("order", "modified");
        $direction = RequestMethods::post("direction", "desc");
        $page      = RequestMethods::post("page", 1);
        $limit     = RequestMethods::post("limit", 10);

        $count = 0;
        $users = false;

        if (RequestMethods::post("search"))
        {
            $where = [
                "SOUNDEX(first) = SOUNDEX(?)" => trim($query),
                "live = ?"    => 1,
                "deleted = ?" => 0
            ];

            $fields = [
                "id", "first", "last"
            ];

            $count = User::count($where);
            $users = User::all($where, $fields, $order, $direction, $limit, $page);

            $view
                ->set("query", trim($query))
                ->set("order", $order)
                ->set("direction", $direction)
                ->set("page", $page)
                ->set("limit", $limit)
                ->set("count", $count)
                ->set("users", $users);
        }

        echo $view->render();
    }
}