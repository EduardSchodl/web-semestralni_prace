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
                echo "Nejste přihlášen";
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
                echo "Nedostatečné oprávnění";
                exit;
            }

            $db = new UserModel();
            $user = $db->getUser($data["params"][0], true);

            $this->render("UserView.twig", ["title" => $data["title"], "user" => $user]);
        }

        function showUsersList($data = []){
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
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

        function updateUser($data = []){
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                echo "Nedostatečné oprávnění";
                exit;
            }

            $db = new UserModel();

            switch($_POST["action"]){
                case "update":
                    $db->updateRole($_POST["id_user"], $_POST["id_role"]);
                    break;
                case "ban":
                    $db->userBanStatusUpdate($_POST["id_user"], BAN["BANNED"]);
                    break;
                case "unban":
                    $db->userBanStatusUpdate($_POST["id_user"], BAN["UNBANNED"]);
                    break;
                case "delete":
                    $db->deleteUser($_POST["id_user"]);
                    break;
            }
        }

        function showUserReviews($data = []){
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_REVIEWER"])
            {
                echo "Nedostatečné oprávnění";
                exit;
            }

            $db = new ReviewModel();
            $reviews = $db->getReviewsByUserId($_SESSION["user"]["id_user"]);

            $this->render("UserReviewsList.twig", ["title" => $data["title"], "reviews" => $reviews]);
        }
    }