<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\RolesModel;
    use Web\Project\Models\UserModel;

    if(!isset($_SESSION)){
        session_start();
    }

    class UserController extends BaseController {
        function index($data = []){
            if(!isset($_SESSION["user"])){
                echo "Nejste přihlášen";
                exit;
            }

            if($_SESSION["user"]["role_id"] == SUPERADMIN){
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                exit;
            }

            $this->render("ProfileView.twig", ["title" => $data["title"]]);
        }

        function showUserProfile($data = []){
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLE_ADMIN)
            {
                echo "Nedostatečné oprávnění";
                exit;
            }

            $db = new UserModel();
            $user = $db->getUser($data["params"][0], true);

            $this->render("UserView.twig", ["title" => $data["title"], "user" => $user]);
        }

        function showUsersList($data = []){
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLE_ADMIN)
            {
                echo "Nedostatečné oprávnění";
                exit;
            }

            $db = new UserModel();
            $users = $db->getAllUsers();

            $db = new RolesModel();
            $roles = $db->getRoles();

            $this->render("UsersListView.twig", ["title" => $data["title"], "users" => $users, "roles" => $roles]);
        }
    }