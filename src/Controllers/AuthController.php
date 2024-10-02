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
            $user = $db->getUser($_POST["username"], true);

            if($user && password_verify($_POST["password"], $user["password"])){
                //alert uspech
                $_SESSION["user"] = $user;
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
            $exists =$db->checkUserExists($_POST["username"], $_POST["email"]);

            if($exists == -1){
                //alert username je jiz zabrany
                header("Location: register");
                exit();
            }
            elseif($exists == -2){
                //alert email je jiz zabrany
                header("Location: register");
                exit();
            }

            $userId = $db->addUser($_POST);

            if($userId) {
                $_SESSION['user'] = $db->getUser($userId);

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