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

            if($user){
                if($user["banned"] == BAN["BANNED"]){
                    $_SESSION['flash'] = [
                        'message' => 'Your account is banned!',
                        'type' => 'danger'
                    ];
                    header("Location: login");
                    exit;
                }

                if(password_verify($_POST["password"], $user["password"])){
                    $_SESSION["user"] = $user;
                    $_SESSION['flash'] = [
                        'message' => 'Login successful!',
                        'type' => 'success'
                    ];
                    header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                }
                else{
                    $_SESSION['flash'] = [
                        'message' => 'Wrong password!',
                        'type' => 'warning'
                    ];
                    header("Location: login");
                }

            }
            else{
                $_SESSION['flash'] = [
                    'message' => 'Account does not exist!',
                    'type' => 'info'
                ];
                header("Location: login");
            }
            exit;
        }

        function register(){
            $db = new UserModel();
            $exists = $db->checkUserExists($_POST["username"], $_POST["email"]);

            if($exists == -1){
                $_SESSION['flash'] = [
                    'message' => 'Username is already taken!',
                    'type' => 'info'
                ];
                header("Location: register");
                exit();
            }
            elseif($exists == -2){
                $_SESSION['flash'] = [
                    'message' => 'Email is already taken!',
                    'type' => 'info'
                ];
                header("Location: register");
                exit();
            }

            $userId = $db->addUser($_POST);

            if($userId) {
                $_SESSION['user'] = $db->getUser($userId);

                $_SESSION['flash'] = [
                    'message' => 'Successfully registered!',
                    'type' => 'success'
                ];

                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                exit();
            } else {
                header("Location: register");
                exit();
            }
        }

        function logout(){
            $_SESSION['flash'] = [
                'message' => 'Logged out successfully!',
                'type' => 'success'
            ];

            unset($_SESSION["user"]);

            header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
            exit();
        }
    }