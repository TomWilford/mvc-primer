<?php

namespace Controllers;

use Framework\Model;
use Framework\Session;
use Models\Friend;
use Models\User;
use Shared\Controller;
use Framework\Registry;
use Framework\RequestMethods;
use stdClass;

class Users extends Controller
{
    public function register()
    {
        $view  = $this->getActionView();

        if (RequestMethods::post("register") && empty(RequestMethods::post("honeypot")))
        {
            $user = new User([
                "first"    => RequestMethods::post("first"),
                "last"     => RequestMethods::post("last"),
                "email"    => RequestMethods::post("email"),
                "password" => RequestMethods::post("password")
            ]);

            if ($user->validate())
            {
                $user->save();
                $view->set("success", true);
            }
            else
            {
                $view->set("errors", $user->getErrors());
                $view->set("success", false);
            }
        }
        else
        {
            $view->set("success", false);
        }

        $view->render();
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
                    /** @var Session\Driver\Server $session */
                    $session = Registry::get("session");
                    $session->set("user", serialize($user));

                    header("Location: /public/profile");
                    exit();
                }
                else
                {
                    $view->set("password_error", "Email address and/or password are incorrect");
                }
            }
        }

        $view->render();
    }

    public function profile()
    {
        $view = $this->getActionView();

        /** @var Session\Driver\Server $session */
        /** @var User $user */
        $session = Registry::get("session");
        $user    = unserialize($session->get("user", null));

        if (empty($user))
        {
            $user = new StdClass();
            $user->first = "Mx. ";
            $user->last  = "Smyf";
        }

        $view->set("user", $user);

         $view->render();
    }

    public function search()
    {
        $view = $this->getActionView();

        $query     = trim(RequestMethods::post("query"));
        $query     = "%{$query}%";
        $order     = RequestMethods::post("order", "modified");
        $direction = RequestMethods::post("direction", "desc");
        (int)$page      = RequestMethods::post("page", 1);
        (int)$limit     = RequestMethods::post("limit", 10);

        $count = 0;
        $users = false;

        if (RequestMethods::post("search"))
        {
            $where = [
                "CONCAT(first, ' ', last) LIKE ?" => $query,
                "live = ?"    => true,
                "deleted = ?" => false
            ];

            $fields = [
                "id", "first", "last"
            ];

            $count = User::count($where);
            $users = User::all($where, $fields, $order, $direction, $limit, $page);

            $view
                ->set("query", trim(str_replace("%", "", $query)))
                ->set("order", $order)
                ->set("direction", $direction)
                ->set("page", $page)
                ->set("limit", $limit)
                ->set("count", $count)
                ->set("users", $users);
        }

        $view->render();
    }

    public function settings()
    {
        $view = $this->getActionView();
        $user = $this->getUser();

        if (RequestMethods::post("update"))
        {
            $user = new User([
                "first"    => RequestMethods::post("first", $user->first),
                "last"     => RequestMethods::post("last", $user->last),
                "email"    => RequestMethods::post("email", $user->email),
                "password" => RequestMethods::post("password", $user->password)
            ]);

            if ($user->validate())
            {
                $user->save();
                $view->set("success", true);
            }
            else
            {
                $view->set("errors", $user->getErrors());
                $view->set("success", false);
            }
        }
        else
        {
            $view->set("user", $user);
            $view->set("success", false);
        }

         $view->render();
    }

    public function logout()
    {
        $this->setUser(false);

        /** @var Session\Driver\Server $session */
        $session = Registry::get("session");
        $session->erase("user");

        header("Location: /public/login");
        exit();
    }

    /**
     * @before _secure
     */
    public function friend($id)
    {
        $user = $this->getUser();

        $friend = new Friend([
            "user"   => $user->id,
            "friend" => $id
        ]);

        $friend->save();

        header("Location: /public/search");
        exit();
    }

    /**
     * @before _secure
     */
    public function unfriend($id)
    {
        $user = $this->getUser();

        $friend = Friend::first([
            "user = ?"   => $user->id,
            "friend = ?" => $id
        ]);

        if ($friend)
        {
            $friend = new Friend([
                "id" => $friend->id
            ]);
            $friend->delete();
        }

        header("Location: /public/search");
        exit();
    }

    /**
     * @protected
     */
    public function _secure()
    {
        $user = $this->getUser();

        if (!$user)
        {
            header("Location: /public/login");
            exit();
        }
    }
}