<?php
namespace Controllers;

use Framework\Core\Exception;
use Framework\Model;
use Framework\Session\Driver\Server;
use Models\File;
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

        if (RequestMethods::post("register") && empty(RequestMethods::post("honeypot"))) {
            $error    = false;
            $first    = RequestMethods::post("first", "", FILTER_SANITIZE_STRING);
            $last     = RequestMethods::post("last", "", FILTER_SANITIZE_STRING);
            $email    = RequestMethods::post("email", "", FILTER_SANITIZE_EMAIL);
            $password = password_hash(RequestMethods::post("password"), PASSWORD_DEFAULT, ["cost" => "12"]);

            if (!$this->isValidEmail($email)) {
                $error = true;
                $view->set("success", false);
                $view->set('errors', ['email' => 'Email address and/or password are incorrect']);
            }

            $user = new User([
                "first"    => $first,
                "last"     => $last,
                "email"    => $email,
                "password" => $password
            ]);

            if ($user->validate() && !$error) {
                $user->save();
                $this->_upload("photo", $user->id);
                $view->set("success", true);
            } else {
                $view->set("errors", $user->getErrors());
                $view->set("success", false);
            }
        } else {
            $view->set("success", false);
        }

        $view->render();
    }

    public function login()
    {
        $view  = $this->getActionView();

        if (RequestMethods::post("login")) {
            $error    = false;
            $email    = RequestMethods::post("email", "", FILTER_SANITIZE_EMAIL);
            $password = RequestMethods::post("password");

            if (empty($email)) {
                $error = true;
                $view->set("email_error", "Email not provided");
            }

            if (!$this->isValidEmail($email)) {
                $error = true;
                $view->set("email_error", "Email address and/or password are incorrect");
            }

            if (empty($password)) {
                $error = true;
                $view->set("password_error", "Password not provided");
            }

            if (!$error) {
                $user = User::first([
                    "email = ?"    => $email,
                    "live = ?"     => true,
                    "deleted = ?"  => false
                ]);

                if (!empty($user) && password_verify($password, $user->password)) {
                    $checkedPassword = $this->checkAndRehashPassword($user->password, $password);
                    if ($user->password != $checkedPassword) {
                        $user->password = $checkedPassword;
                        $user->save();
                    }

                    /** @var Server $session */
                    $session = Registry::get("session");
                    $session->set("user", serialize($user));
                    $this->user = $user;

                    self::redirect("/public/profile");
                } else {
                    $view->set("password_error", "Email address and/or password are incorrect");
                }
            }
        }

        $view->render();
    }

    public function profile()
    {
        $view = $this->getActionView();
        $user = $this->user;

        if (empty($user)) {
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

        $query      = trim(RequestMethods::post("query", "", FILTER_SANITIZE_STRING));
        $query      = "%{$query}%";
        $order      = RequestMethods::post("order", "modified", FILTER_SANITIZE_STRING);
        $direction  = RequestMethods::post("direction", "desc", FILTER_SANITIZE_STRING);
        (int)$page  = RequestMethods::post("page", 1, FILTER_SANITIZE_NUMBER_INT);
        (int)$limit = RequestMethods::post("limit", 10, FILTER_SANITIZE_NUMBER_INT);

        $count = 0;
        $users = false;

        if (RequestMethods::post("search")) {
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
        $userCurrent = $this->user;
        $view->set("user", $userCurrent);

        if (RequestMethods::post("update")) {
            $error    = false;
            $email    = RequestMethods::post("email", $userCurrent->email, FILTER_SANITIZE_EMAIL);
            $password = RequestMethods::post("password");
            $password = password_verify($password, $userCurrent->password)
                ? $this->checkAndRehashPassword($userCurrent->password , $password)
                : password_hash($password, PASSWORD_DEFAULT, ['cost' => 15]);

            if (!$this->isValidEmail($email)) {
                $error = true;
                $view->set('errors', ['email' => 'Email address and/or password are incorrect']);
            }

            $user = new User([
                "id"       => $userCurrent->id,
                "first"    => RequestMethods::post("first", $userCurrent->first, FILTER_SANITIZE_STRING),
                "last"     => RequestMethods::post("last", $userCurrent->last, FILTER_SANITIZE_STRING),
                "email"    => $email,
                "password" => $password,
                "live"     => $userCurrent->live,
                "deleted"  => $userCurrent->deleted,
                "created"  => $userCurrent->created
            ]);

            if ($user->validate() && !$error) {
                $user->save();
                $this->_upload("photo", $userCurrent->id);

                $newUser = User::first(["id = ?" => $userCurrent->id]);
                /** @var Server $session */
                $session = Registry::get("session");
                $session->set("user", serialize($newUser));

                $view->set("success", true);
            } else {
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

    public function logout()
    {
        $this->setUser(false);

        /** @var Server $session */
        $session = Registry::get("session");
        $session->erase("user");

        self::redirect("/public/login");
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

        self::redirect("/public/search");
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

        if ($friend) {
            $friend = new Friend([
                "id" => $friend->id
            ]);
            $friend->delete();
        }

        self::redirect("/public/search");
    }

    protected function _upload($name, $user)
    {
        if (isset($_FILES[$name]))
        {
            $file      = $_FILES[$name];
            $path      = APP_PATH . "/public/uploads/";
            $time      = (new \DateTime())->format("His");
            $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
            $filename  = "{$user}--{$time}.{$extension}";

            if (move_uploaded_file($file["tmp_name"], $path . $filename)) {
                $meta = getimagesize($path . $filename);

                if ($meta) {
                    $width  = $meta[0];
                    $height = $meta[1];

                    $file = new File([
                        "name"   => $filename,
                        "mime"   => $file["type"],
                        "size"   => $file["size"],
                        "width"  => $width,
                        "height" => $height,
                        "user"   => $user
                    ]);

                    $file->save();
                }
            }
        }
    }

    /**
     * @param $id
     * @throws Model\Exception\Validation
     * @before _secure, _admin
     */
    public function edit($id)
    {
        $errors = [];

        $editUser = User::first([
            "id = ?" => $id
        ]);

        if (RequestMethods::post("save"))
        {
            $error = false;
            $email    = RequestMethods::post("email", $editUser->email, FILTER_SANITIZE_EMAIL);
            $password = RequestMethods::post("password");
            $password = password_verify($password, $editUser->password)
                ? $this->checkAndRehashPassword($editUser->password, $password)
                : password_hash($password, PASSWORD_DEFAULT, ['cost' => 15]);

            if (!$this->isValidEmail($email)) {
                $error = true;
                $this->actionView->set("success", false);
            }

            $editUser->first    = RequestMethods::post("first", $editUser->first, FILTER_SANITIZE_STRING);
            $editUser->last     = RequestMethods::post("last", $editUser->last, FILTER_SANITIZE_STRING);
            $editUser->email    = $email;
            $editUser->password = $password;
            $editUser->live     = (boolean) RequestMethods::post("live");
            $editUser->admin    = (boolean) RequestMethods::post("admin");

            if ($editUser->validate() && !$error) {
                $editUser->save();
                $this->actionView->set("success", true);
            }

            $errors = $editUser->errors;
        }

        $this->actionView->set("editUser", $editUser)->set("errors", $errors);
    }

    /**
     * @before _secure, _admin
     */
    public function view()
    {
        $this->actionView->set("users", User::all());
    }

    /**
     * @param $id
     * @before _secure, _admin
     */
    public function delete($id)
    {
        $user = User::first([
            "id = ?" => $id
        ]);

        if ($user) {
            $user->live = false;
            $user->save();
        }

        self::redirect("/public/users/view");
    }

    /**
     * @param $id
     * @before _secure, _admin
     */
    public function undelete($id)
    {
        $user = User::first([
            "id = ?" => $id
        ]);

        if ($user) {
            $user->live = true;
            $user->save();
        }

        self::redirect("/public/users/view");
    }

    private function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    private function checkAndRehashPassword($existingPassword, $inputPassword)
    {
        $currentHashAlgorithm = PASSWORD_DEFAULT;
        $currentHashOptions   = ['cost' => 15];
        $password             = $existingPassword;

        if (password_needs_rehash($existingPassword, $currentHashAlgorithm, $currentHashOptions)) {
            $password = password_hash($inputPassword, $currentHashAlgorithm, $currentHashOptions);
        }

        return $password;
    }
}
