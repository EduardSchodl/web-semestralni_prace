<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\ArticleModel;
    use Web\Project\Models\ReviewModel;
    use Web\Project\Models\UserModel;

    if(!isset($_SESSION))
    {
        session_start();
    }

    class ArticleController extends BaseController{
        function index($data = []){
            $db = new ArticleModel();
            $article = $db->getArticle($data["params"][0]);

            if(!$article){
                $_SESSION['flash'] = [
                    'message' => 'Article does not exist!',
                    'type' => 'info'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                exit;
            }

            $db = new ReviewModel();
            $reviews = $db->getReviewsByArticleSlug($data["params"][0]);

            $this->render("ArticleView.twig", ["title" => $data["title"], "article" => $article, "reviews" => $reviews]);
        }

        function updateArticle(){
            $db = new ArticleModel();
            $db->updateArticle($_POST);
        }

        function publishFormShow($data = []){
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

            $this->render("PublishArticleView.twig", ["title" => $data["title"]]);
        }

        function publishArticle(){
            if (!isset($_POST["title"]) || !isset($_POST["abstract"]) || !isset($_FILES["file"])) {
                $_SESSION['flash'] = [
                    'message' => 'Incorrectly filled in form!',
                    'type' => 'warning'
                ];
                exit;
            }

            if ($_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
                $_SESSION['flash'] = [
                    'message' => 'Error uploading file!',
                    'type' => 'warning'
                ];
                exit;
            }

            $file_name = $_FILES["file"]["name"];
            $file_info = pathinfo($file_name);
            $file_name = $file_info['filename'];
            $file_tmp = $_FILES["file"]["tmp_name"];
            $file_content = file_get_contents($file_tmp);

            $db = new ArticleModel();
            $slug = $db->insertArticle($_POST["abstract"], $_POST["title"], $file_name, $file_content, $_SESSION["user"]["id_user"]);

            if($slug == -1){
                $_SESSION['flash'] = [
                    'message' => 'Something went wrong publishing article!',
                    'type' => 'warning'
                ];
                exit;
            }

            header("Location: articles/$slug");
        }

        function showPDF($data = []){
            $db = new ArticleModel();
            $file = $db->getArticle($data["params"][0]);

            header("Content-Type: application/pdf");
            header("Content-Disposition: inline; filename=\"" . $file['filename'] . "\"");
            echo $file['file'];
        }

        function getProfileArticles($data = []){
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

            $db = new ArticleModel();
            $articles = $db->getArticlesByUser("author_id", $_SESSION["user"]["id_user"]);

            $this->render("ProfileArticlesView.twig", ["title" => $data["title"], "articles" => $articles]);
        }

        function getUserArticles($data = []){
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                $_SESSION['flash'] = [
                    'message' => 'Insufficient authorisation!',
                    'type' => 'warning'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                exit;
            }

            $db = new ArticleModel();
            $articles = $db->getArticlesByUser("username", $data["params"][0]);

            $this->render("ProfileArticlesView.twig", ["title" => $data["title"], "articles" => $articles]);
        }

        function deleteArticle(){
            $articleId = $_POST["article_id"];

            $db = new ReviewModel();
            $successDeleteReview = $db->removeReviewByArticleId($articleId);

            if ($successDeleteReview[0]) {
                $db = new ArticleModel();
                $successDeleteArticle = $db->deleteArticle($articleId);

                if ($successDeleteArticle[0]) {
                    echo json_encode(["status" => "success", "message" => "Article and associated reviews deleted successfully."]);
                    http_response_code(200);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error deleting article: " . $successDeleteArticle[1][2]]);
                    http_response_code(500);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Error deleting associated reviews."]);
                http_response_code(500);
            }
        }

        function articlesManagementShow($data = []){
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                $_SESSION['flash'] = [
                    'message' => 'Insufficient authorisation!',
                    'type' => 'warning'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                exit;
            }

            $db = new ArticleModel();
            $articles = $db->getArticles();

            $db = new UserModel();
            $reviewers = $db->getReviewers();

            $assignedReviews = [];
            $db = new ReviewModel();
            foreach ($articles as $article) {
                $assignedReviews[$article['id_article']] = $db->getReviewsByArticleId($article['id_article']);
            }

            foreach ($articles as &$article) {
                $assignedUserIds = array_map(function($review) {
                    return $review['id_user'];
                }, $assignedReviews[$article['id_article']] ?? []);

                $article['available_reviewers'] = array_filter($reviewers, function($reviewer) use ($article, $assignedUserIds) {
                    return !in_array($reviewer['id_user'], $assignedUserIds) && $reviewer['id_user'] != $article['author_id'];
                });
            }

            $this->render("ArticlesManagementView.twig", ["title" => $data["title"], "articles" => $articles, "assignedReviews" => $assignedReviews]);
        }

        function checkReviews(){
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                $_SESSION['flash'] = [
                    'message' => 'Insufficient authorisation!',
                    'type' => 'warning'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                exit;
            }

            if ($this->checkAllReviewsSubmitted($_POST["values"]["idArticle"])) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Not all reviews have been submitted or not enough reviewers.']);
            }
        }

        function checkAllReviewsSubmitted($idArticle) {
            $db = new ReviewModel();
            $reviews = $db->getReviewsByArticleId($idArticle);

            if(sizeof($reviews) < 3){
                return false;
            }

            foreach ($reviews as $review) {
                if ($review['status'] == 0) {
                    return false;
                }
            }
            return true;
        }

        function updateArticleStatus(){
            $db = new ArticleModel();

            $response = null;

            switch($_POST["action"]){
                case "acceptArticle":
                    $response = $db->updateArticleStatus($_POST["idArticle"], STATUS["ACCEPTED_REVIEWED"]);
                    break;
                case "rejectArticle":
                    $response = $db->updateArticleStatus($_POST["idArticle"], STATUS["REJECTED_REVIEWED"]);
                    break;
                case "reconsider":
                    $response = $db->updateArticleStatus($_POST["idArticle"], STATUS["REVIEW_PROCESS"]);
                    break;
            }

            if ($response[0]) {
                echo json_encode(["status" => "success", "message" => "Review updated successfully."]);
                http_response_code(200);
            } else {
                echo json_encode(["status" => "error", "message" => "Error updating review: " . $response[1][2]]);
                http_response_code(500);
            }
        }
    }