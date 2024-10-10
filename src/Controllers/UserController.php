<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\ReviewModel;
    use Web\Project\Models\RolesModel;
    use Web\Project\Models\UserModel;

    if(!isset($_SESSION)){
        session_start();
    }

    class UserController extends BaseController {
        function index($data = []){
            if(!isset($_SESSION["user"])){
                $_SESSION['flash'] = [
                    'message' => 'You are not logged in!',
                    'type' => 'info'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                exit;
            }

            if($_SESSION["user"]["role_id"] == ROLES["SUPERADMIN"]){
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                exit;
            }

            $this->render("ProfileView.twig", ["title" => $data["title"]]);
        }

        function showUserProfile($data = []){
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                $_SESSION['flash'] = [
                    'message' => 'Insufficient authorisation!',
                    'type' => 'warning'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                exit;
            }

            $db = new UserModel();
            $user = $db->getUser($data["params"][0], true);

            $this->render("UserView.twig", ["title" => $data["title"], "user" => $user]);
        }

        function showUsersList($data = []){
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                $_SESSION['flash'] = [
                    'message' => 'Insufficient authorisation!',
                    'type' => 'warning'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                exit;
            }

            $db = new UserModel();
            $users = $db->getAllUsers();

            $db = new RolesModel();
            $roles = $db->getRoles();

            if ($this->isAjaxRequest()) {
                $this->render('partials/userTable.twig', ['users' => $users, 'roles' => $roles]);
            } else {
                $this->render('UsersListView.twig', ['users' => $users, 'roles' => $roles]);
            }
        }

        private function isAjaxRequest() {
            return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        }

        function updateUser($data = []){
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                $_SESSION['flash'] = [
                    'message' => 'Insufficient authorisation!',
                    'type' => 'warning'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                exit;
            }

            $db = new UserModel();
            $response = null;

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

            if ($response[0]) {
                echo json_encode(["status" => "success", "message" => "User updated successfully."]);
                http_response_code(200);
            } else {
                echo json_encode(["status" => "error", "message" => "Error updating user: " . $response[1][2]]);
                http_response_code(500);
            }
        }
    }