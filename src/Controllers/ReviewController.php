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
    }