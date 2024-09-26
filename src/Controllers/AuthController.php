<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\UserModel;

    if(!isset($_SESSION))
    {
        session_start();
    }

    class AuthController extends BaseController
    {
        function index($data = []){
            $this->render("AuthView.twig", ["title" => $data["title"]]);
        }

        function auth(){
            $db = new UserModel();
            $user = $db->getUser($_POST["email"]);

            if($user && password_verify($_POST["password"], $user["password"])){
                //alert uspech
                $_SESSION["user"] = $user;
                echo "asdjas";
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                exit;
            }
            else{
                //alert spatne heslo nebo mail
                echo "invalid";
                header("Location: login");
                exit();
            }
        }

        function register(){
            $db = new UserModel();
            $user = $db->getUser($_POST["email"]);

            if($user){
                //alert email je jiz zabrany
                header("Location: register");
                exit();
            }

            if ($db->addUser($_POST) > 0) {
                $_SESSION['user'] = $db->getUser($_POST["email"]);

                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                exit();
            } else {
                header("Location: register");
                exit();
            }
        }

        function logout(){
            unset($_SESSION["user"]);
            session_destroy();

            header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
            exit();
        }
    }