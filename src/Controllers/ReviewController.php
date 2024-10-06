<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\ReviewModel;

    if (!isset($_SESSION)) {
        session_start();
    }

    class ReviewController extends BaseController
    {
        function submitReview($data = []){
            echo $_POST["content"];
            //$db = new ReviewModel();
            //$db->submitReview($_POST);
        }
    }