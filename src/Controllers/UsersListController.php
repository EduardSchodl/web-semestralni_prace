<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\RolesModel;
    use Web\Project\Models\UserModel;

    class UsersListController extends BaseController{
        function index($data = []){
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] >= ROLE_ADMIN)
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
