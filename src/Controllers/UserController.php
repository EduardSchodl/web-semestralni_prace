<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\ReviewModel;
    use Web\Project\Models\RolesModel;
    use Web\Project\Models\UserModel;

    // Spustí relaci, pokud není nastavena
    if(!isset($_SESSION)){
        session_start();
    }

    /**
     * Třída UserController zpracovává operace spojené se správou uživatelů,
     * včetně zobrazení profilu, správy uživatelských účtů a aktualizace jejich rolí.
     */
    class UserController extends BaseController{
        /**
         * Zobrazuje profil aktuálně přihlášeného uživatele.
         * Pokud uživatel není přihlášen nebo má roli SUPERADMIN, přesměruje na domovskou stránku.
         *
         * @param array $data Data předávaná pro vykreslení.
         * @return void
         */
        function index($data = []){
            // Kontrola, zda je uživatel přihlášen.
            if(!isset($_SESSION["user"])){
                $_SESSION['flash'] = [
                    'message' => 'You are not logged in!',
                    'type' => 'info'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            if($_SESSION["user"]["role_id"] == ROLES["SUPERADMIN"]){
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            $this->render("ProfileView.twig", ["title" => $data["title"]]);
        }

        /**
         * Zobrazuje profil jiného uživatele.
         *
         * @param array $data Data předávaná pro vykreslení, včetně username uživatele.
         * @return void
         */
        function showUserProfile($data = []){
            // Kontrola autorizace uživatele (administrátor)
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                $_SESSION['flash'] = [
                    'message' => 'Insufficient authorisation!',
                    'type' => 'warning'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            $db = new UserModel();
            $user = $db->getUser($data["params"][0], true);

            $this->render("UserView.twig", ["title" => $data["title"], "user" => $user]);
        }

        /**
         * Zobrazuje seznam všech uživatelů.
         *
         * @param array $data Data předávaná pro vykreslení.
         * @return void
         */
        function showUsersList($data = []){
            // Kontrola autorizace uživatele (administrátor)
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                $_SESSION['flash'] = [
                    'message' => 'Insufficient authorisation!',
                    'type' => 'warning'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            $db = new UserModel();
            $users = $db->getAllUsers();

            $db = new RolesModel();
            $roles = $db->getRoles();

            // Kontrola AJAX požadavku a vykreslení odpovídající šablony
            if ($this->isAjaxRequest()) {
                $this->render('partials/UserTable.twig', ['users' => $users, 'roles' => $roles]);
            } else {
                $this->render('UsersListView.twig', ['users' => $users, 'roles' => $roles]);
            }
        }

        /**
         * Zkontroluje, zda byl požadavek AJAX.
         *
         * @return bool Vrací true, pokud je požadavek AJAX, jinak false.
         */
        public function isAjaxRequest() {
            return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        }

        /**
         * Aktualizuje uživatele podle požadované akce (změna role, zabanování, odbanování, smazání).
         *
         * @param array $data Data pro vykreslení.
         * @return void
         */
        function updateUser($data = []){
            // Kontrola autorizace uživatele (administrátor)
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                $_SESSION['flash'] = [
                    'message' => 'Insufficient authorisation!',
                    'type' => 'warning'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            $db = new UserModel();
            $response = null;

            // Zpracování akce podle POST parametru "action"
            switch($_POST["action"]){
                case "update":
                    $response = $db->updateRole($_POST["id_user"], $_POST["id_role"]);
                    break;
                case "ban":
                    $response = $db->userBanStatusUpdate($_POST["id_user"], BAN["BANNED"]);
                    break;
                case "unban":
                    $response = $db->userBanStatusUpdate($_POST["id_user"], BAN["UNBANNED"]);
                    break;
                case "delete":
                    $response = $db->deleteUser($_POST["id_user"]);
                    break;
            }

            // Nastavení HTTP odpovědi a JSON odpovědi podle výsledku
            if ($response[0]) {
                echo json_encode(["status" => "success", "message" => "User updated successfully."]);
                http_response_code(200);
            } else {
                echo json_encode(["status" => "error", "message" => "Error updating user: " . $response[1][2]]);
                http_response_code(500);
            }
        }
    }