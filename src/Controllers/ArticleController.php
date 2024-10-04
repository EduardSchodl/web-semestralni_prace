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
                echo "Neexistující článek";
                exit;
            }

            $this->render("ArticleView.twig", ["title" => $data["title"], "article" => $article]);
        }

        function updateArticle(){
            $db = new ArticleModel();
            $db->updateArticle($_POST);
        }

        function publishFormShow($data = []){
            if(!isset($_SESSION["user"])){
                echo "Nejste přihlášen";
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
                echo "Chybně vyplněný formulář";
                exit;
            }

            if ($_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
                echo "Error uploading the file.";
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
                echo "Chyba";
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
                echo "Nejste přihlášen";
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
                echo "Nedostatečné oprávnění";
                exit;
            }

            $db = new ArticleModel();
            $articles = $db->getArticlesByUser("username", $data["params"][0]);

            $this->render("ProfileArticlesView.twig", ["title" => $data["title"], "articles" => $articles]);
        }

        function deleteArticle(){
            $db = new ArticleModel();
            $db->deleteArticle($_POST["article_id"]);
        }

        function articlesManagementShow($data = []){
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                echo "Nedostatečné oprávnění";
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

                $article['available_reviewers'] = array_filter($reviewers, function($reviewer) use ($assignedUserIds) {
                    return !in_array($reviewer['id_user'], $assignedUserIds);
                });
            }

            $this->render("ArticlesManagementView.twig", ["title" => $data["title"], "articles" => $articles, "assignedReviews" => $assignedReviews]);
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
    }