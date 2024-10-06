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
            $db->submitReview($_POST);
        }

        function reviewUpdate(){
            $db = new ReviewModel();
            $response = $db->addReview($_POST["id_article"], $_POST["id_user"]);

            if ($response[0]) {
                echo json_encode(["status" => "success", "message" => "Review added successfully."]);
                http_response_code(200); // Success
            } else {
                echo json_encode(["status" => "error", "message" => "Error adding review: " . $response[1][2]]);
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