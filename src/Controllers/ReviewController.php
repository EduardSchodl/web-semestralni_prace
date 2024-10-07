<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\ReviewModel;

    if (!isset($_SESSION)) {
        session_start();
    }

    class ReviewController extends BaseController
    {
        function submitReview($data = []){
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_REVIEWER"])
            {
                echo "Nedostatečné oprávnění";
                exit;
            }

            $db = new ReviewModel();

            $response = $db->submitReview($_POST);;

            if ($response[0]) {
                http_response_code(200); // Success
            } else {
                http_response_code(500); // Server error
            }
            header("Refresh:0");
        }

        function reviewUpdate(){
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                echo "Nedostatečné oprávnění";
                exit;
            }

            $db = new ReviewModel();
            $response = null;

            switch($_POST["action"]){
                case "addReviewer":
                    $response = $db->addReview($_POST["values"]["idArticle"], $_POST["values"]["idUser"]);
                    break;
                case "removeReview":
                    $response = $db->removeReview($_POST["values"]["idReview"]);
                    break;
            }

            if ($response[0]) {
                echo json_encode(["status" => "success", "message" => "Review updated successfully."]);
                http_response_code(200); // Success
            } else {
                echo json_encode(["status" => "error", "message" => "Error updating review: " . $response[1][2]]);
                http_response_code(500); // Server error
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