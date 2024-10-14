<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\UserModel;

    // Zahájení session, pokud ještě nebyla spuštěna.
    if(!isset($_SESSION))
    {
        session_start();
    }

    /**
     * Třída AuthController zodpovídá za autentizační funkce (přihlášení, registrace, odhlášení).
     */
    class AuthController extends BaseController
    {
        /**
         * Zobrazuje přihlašovací stránku.
         *
         * @param array $data Data pro zobrazení stránky.
         * @return void
         */
        function index($data = []){
            $this->render("AuthView.twig", ["title" => $data["title"]]);
        }

        /**
         * Zpracovává autentizaci uživatele podle zadaných údajů.
         *
         * @return void
         */
        function auth(){
            $db = new UserModel();
            $user = $db->getUser($_POST["username"], true);

            // Kontrola, zda uživatel existuje.
            if($user){
                // Kontrola, zda není účet zabanován.
                if($user["banned"] == BAN["BANNED"]){
                    $_SESSION['flash'] = [
                        'message' => 'Your account is banned!',
                        'type' => 'danger'
                    ];
                    header("Location: login");
                    return;
                }

                // Ověření hesla pomocí password_verify.
                if(password_verify($_POST["password"], $user["password"])){
                    // Uložení uživatele do session.
                    $_SESSION["user"] = $user;
                    $_SESSION['flash'] = [
                        'message' => 'Login successful!',
                        'type' => 'success'
                    ];
                    header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                }
                else{
                    // Zpráva o neúspěšném přihlášení kvůli špatnému heslu.
                    $_SESSION['flash'] = [
                        'message' => 'Wrong password!',
                        'type' => 'warning'
                    ];
                    header("Location: login");
                }

            }
            else{
                // Zpráva o neexistujícím účtu.
                $_SESSION['flash'] = [
                    'message' => 'Account does not exist!',
                    'type' => 'info'
                ];
                header("Location: login");
            }
            return;
        }

        /**
         * Zpracovává registraci nového uživatele.
         *
         * @return void
         */
        function register(){
            $db = new UserModel();
            // Kontrola, zda uživatelské jméno nebo email již neexistují.
            $exists = $db->checkUserExists($_POST["username"], $_POST["email"]);

            // Pokud je uživatelské jméno již obsazeno.
            if($exists == -1){
                $_SESSION['flash'] = [
                    'message' => 'Username is already taken!',
                    'type' => 'info'
                ];
                header("Location: register");
                return;
            }
            // Pokud je email již obsazen.
            elseif($exists == -2){
                $_SESSION['flash'] = [
                    'message' => 'Email is already taken!',
                    'type' => 'info'
                ];
                header("Location: register");
                return;
            }

            $userId = $db->addUser($_POST);

            // Pokud byla registrace úspěšná, načti uživatele do session.
            if($userId) {
                $_SESSION['user'] = $db->getUser($userId);

                $_SESSION['flash'] = [
                    'message' => 'Successfully registered!',
                    'type' => 'success'
                ];

                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            } else {
                header("Location: register");
                return;
            }
        }

        /**
         * Odhlásí uživatele.
         *
         * @return void
         */
        function logout(){
            $_SESSION['flash'] = [
                'message' => 'Logged out successfully!',
                'type' => 'success'
            ];

            // Smazání uživatele ze session.
            unset($_SESSION["user"]);

            header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
            return;
        }
    }