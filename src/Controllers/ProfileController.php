<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\UserModel;

    if(!isset($_SESSION)){
        session_start();
    }

    class ProfileController extends BaseController {
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
            $db = new UserModel();
            $user = $db->getUser($data["params"][0], False);

            $this->render("UserView.twig", ["title" => $data["title"], "user" => $user]);
        }

        function editProfile($data = []){
            if(!isset($_SESSION["user"])){
                echo "Nejste přihlášen";
                exit;
            }

            if($_SESSION["user"]["role_id"] == SUPERADMIN){
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                exit;
            }

            $this->render("EditView.twig", ["title" => $data["title"]]);
        }
    }