<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\ArticleModel;
    use Web\Project\Models\ReviewModel;
    use Web\Project\Models\UserModel;

    // Spustí relaci, pokud není nastavena
    if(!isset($_SESSION))
    {
        session_start();
    }

    /**
     * Třída ArticleController spravuje operace související s články.
     */
    class ArticleController extends BaseController{
        /**
         * Zobrazí článek na základě předaných parametrů.
         *
         * @param array $data Parametry pro zobrazení článku, včetně titulu.
         * @return void
         */
        function index($data = []){
            $db = new ArticleModel();
            $article = $db->getArticle($data["params"][0]);

            // Kontrola, zda článek existuje
            if(!$article){
                $_SESSION['flash'] = [
                    'message' => 'Article does not exist!',
                    'type' => 'info'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            // Kontrola stavu článku a relace uživatele
            if($article["status_id"] == STATUS["REVIEW_PROCESS"] && !isset($_SESSION["user"])){
                $_SESSION['flash'] = [
                    'message' => 'Article does not exist!',
                    'type' => 'info'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            $db = new ReviewModel();
            $reviews = $db->getReviewsByArticleSlug($data["params"][0]);

            // Kontrola AJAX požadavku a vykreslení odpovídající šablony
            if($this->isAjaxRequest()){
                $this->render("partials/ArticleDetail.twig", ["title" => $data["title"], "article" => $article, "reviews" => $reviews]);
            }
            else{
                $this->render("ArticleView.twig", ["title" => $data["title"], "article" => $article, "reviews" => $reviews]);
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
         * Aktualizuje článek na základě předaných dat.
         *
         * @return void
         */
        function updateArticle(){
            $db = new ArticleModel();
            $db->updateArticle($_POST);
        }

        /**
         * Zobrazí formulář pro publikaci článku.
         *
         * @param array $data Parametry pro zobrazení formuláře.
         * @return void
         */
        function publishFormShow($data = []){
            // Kontrola, zda je uživatel přihlášen
            if(!isset($_SESSION["user"])){
                $_SESSION['flash'] = [
                    'message' => 'You are not logged in!',
                    'type' => 'info'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            // Kontrola role uživatele
            if($_SESSION["user"]["role_id"] == ROLES["SUPERADMIN"]){
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            $this->render("PublishArticleView.twig", ["title" => $data["title"]]);
        }

        /**
         * Publikuje článek na základě předaných dat.
         *
         * @return void
         */
        function publishArticle(){
            // Kontrola vyplněných polí
            if (empty($_POST["title"]) || empty($_POST["abstract"]) || !isset($_FILES["file"])) {
                $_SESSION['flash'] = [
                    'message' => 'Please fill in all required fields!',
                    'type' => 'warning'
                ];
                header("Location: publish");
                return;
            }

            // Kontrola, zda nastala chyba při nahrávání souboru
            if ($_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
                $_SESSION['flash'] = [
                    'message' => 'Error uploading file!',
                    'type' => 'warning'
                ];
                header("Location: publish");
                return;
            }

            // Zpracování nahrávaného souboru
            $file_name = $_FILES["file"]["name"];

            // Kontrola, zda je soubor PDF
            if(!pathinfo($file_name, PATHINFO_EXTENSION) == "pdf"){
                $_SESSION['flash'] = [
                    'message' => 'File must be PDF!',
                    'type' => 'warning'
                ];
                return;
            }

            $file_info = pathinfo($file_name);
            $file_name = $file_info['filename'];
            $file_tmp = $_FILES["file"]["tmp_name"];
            $file_content = file_get_contents($file_tmp);

            $db = new ArticleModel();
            $slug = $db->insertArticle($_POST["abstract"], $_POST["title"], $file_name, $file_content, $_SESSION["user"]["id_user"]);

            // Kontrola úspěšnosti publikace článku
            if($slug == -1){
                $_SESSION['flash'] = [
                    'message' => 'Something went wrong publishing article!',
                    'type' => 'warning'
                ];
                return;
            }

            header("Location: articles/$slug");
        }

        /**
         * Zobrazí PDF soubor článku.
         *
         * @param array $data Parametry pro zobrazení PDF.
         * @return void
         */
        function showPDF($data = []){
            $db = new ArticleModel();
            $file = $db->getArticle($data["params"][0]);

            header("Content-Type: application/pdf");
            header("Content-Disposition: inline; filename=\"" . $file['filename'] . "\"");
            echo $file['file'];
        }

        /**
         * Získá články uživatele na základě relace.
         *
         * @param array $data Parametry pro zobrazení článků.
         * @return void
         */
        function getProfileArticles($data = []){
            // Kontrola přihlášení uživatele
            if(!isset($_SESSION["user"])){
                $_SESSION['flash'] = [
                    'message' => 'You are not logged in!',
                    'type' => 'info'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            // Kontrola role uživatele
            if($_SESSION["user"]["role_id"] == ROLES["SUPERADMIN"]){
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            $db = new ArticleModel();
            $articles = $db->getArticlesByUser("author_id", $_SESSION["user"]["id_user"]);

            $this->render("ProfileArticlesView.twig", ["title" => $data["title"], "articles" => $articles]);
        }

        /**
         * Získá články uživatele podle uživatelského jména.
         *
         * @param array $data Parametry pro zobrazení článků.
         * @return void
         */
        function getUserArticles($data = []){
            // Kontrola autorizace uživatele
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                $_SESSION['flash'] = [
                    'message' => 'Insufficient authorisation!',
                    'type' => 'warning'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            $db = new ArticleModel();
            $articles = $db->getArticlesByUser("username", $data["params"][0]);

            $this->render("ProfileArticlesView.twig", ["title" => $data["title"], "articles" => $articles]);
        }

        /**
         * Smaže článek a s ním spojené recenze.
         *
         * @return void
         */
        function deleteArticle(){
            $articleId = $_POST["article_id"];

            $db = new ReviewModel();
            $successDeleteReview = $db->removeReviewByArticleId($articleId);

            // Kontrola úspěšnosti odstranění recenzí
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

        /**
         * Zobrazí spávu článků pro adminy.
         * Umožňuje adminům zobrazit články a přidělené recenze, zároveň umožňuje výběr dostupných recenzentů.
         *
         * @param array $data Parametry pro zobrazení správy článků.
         * @return void
         */
        function articlesManagementShow($data = []){
            // Kontrola autorizace uživatele
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                $_SESSION['flash'] = [
                    'message' => 'Insufficient authorisation!',
                    'type' => 'warning'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            $db = new ArticleModel();
            $articles = $db->getArticles();

            $db = new UserModel();
            $reviewers = $db->getReviewers();

            $assignedReviews = [];
            $db = new ReviewModel();
            //Pro každý článek načte recenze podle jeho ID
            foreach ($articles as $article) {
                $assignedReviews[$article['id_article']] = $db->getReviewsByArticleId($article['id_article']);
            }

            $modifiedArticles = [];

            // Pro každý článek vytvoří seznam dostupných recenzentů
            foreach ($articles as $article) {
                // Získá seznam uživatelů, kteří již byli přiděleni k recenzím článku
                $assignedUserIds = array_map(function($review) {
                    return $review['id_user'];
                }, $assignedReviews[$article['id_article']] ?? []);

                // Filtruje dostupné recenzenty (nesmí být autor ani už přidělený recenzent)
                $article['available_reviewers'] = array_filter($reviewers, function($reviewer) use ($article, $assignedUserIds) {
                    return !in_array($reviewer['id_user'], $assignedUserIds) && $reviewer['id_user'] != $article['author_id'];
                });
                $modifiedArticles[] = $article;
            }

            $this->render("ArticlesManagementView.twig", ["title" => $data["title"], "articles" => $modifiedArticles, "assignedReviews" => $assignedReviews]);
        }

        /**
         * Aktualizuje data článku včetně dostupných recenzentů pro konkrétní článek.
         *
         * @param array $data Parametry pro aktualizaci článku.
         * @return void
         */
        function updateArticleCard($data = [])
        {
            $db = new ArticleModel();
            $article = $db->getArticleById($data["queryParams"]["idArticle"]);

            $db = new UserModel();
            $reviewers = $db->getReviewers();

            // Načtení recenzí přiřazených k článku
            $db = new ReviewModel();
            $assignedReviews[$article["id_article"]] = $db->getReviewsByArticleId($data["queryParams"]["idArticle"]);

            // Získá seznam uživatelů, kteří již byli přiděleni k recenzím článku
            $assignedUserIds = array_map(function($review) {
                return $review['id_user'];
            }, $assignedReviews[$article['id_article']] ?? []);

            // Filtruje dostupné recenzenty (nesmí být autor ani už přidělený recenzent)
            $article['available_reviewers'] = array_filter($reviewers, function($reviewer) use ($article, $assignedUserIds) {
                return !in_array($reviewer['id_user'], $assignedUserIds) && $reviewer['id_user'] != $article['author_id'];
            });


            $this->render('partials/ReviewCard.twig', ["title" => $data["title"], "article" => $article, "assignedReviews" => $assignedReviews]);
        }

        /**
         * Kontroluje, zda jsou všechny recenze pro článek dokončeny.
         *  Vrátí úspěch, pokud jsou všechny recenze odevzdány, jinak chybu.
         *
         * @return void
         */
        function checkReviews(){
            // Kontrola, zda je uživatel přihlášen a má dostatečná práva (administrátor).
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                $_SESSION['flash'] = [
                    'message' => 'Insufficient authorisation!',
                    'type' => 'warning'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            // Kontrola, zda všechny recenze pro daný článek byly odevzdány.
            if ($this->checkAllReviewsSubmitted($_POST["values"]["idArticle"])) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Not all reviews have been submitted or not enough reviewers.']);
            }
        }

        /**
         * Kontroluje, zda byly všechny recenze pro daný článek odevzdány.
         *
         * @param int $idArticle ID článku, jehož recenze mají být zkontrolovány.
         * @return bool True, pokud jsou všechny recenze odevzdány, jinak false.
         */
        function checkAllReviewsSubmitted($idArticle) {
            $db = new ReviewModel();
            $reviews = $db->getReviewsByArticleId($idArticle);

            // Pokud je méně než 3 recenze, není možné článek schválit.
            if(sizeof($reviews) < 3){
                return false;
            }

            // Projde všechny recenze a zkontroluje, zda jsou všechny odevzdány.
            foreach ($reviews as $review) {
                if ($review['status'] == 0) {
                    return false;
                }
            }
            return true;
        }

        /**
         * Aktualizuje stav článku podle akce zadané POST metodou.
         *  Akce může být přijmutí článku, odmítnutí nebo vrácení k přepracování.
         *
         * @return void
         */
        function updateArticleStatus(){
            $db = new ArticleModel();

            $response = null;

            // Zpracování akce z POST metody (přijmutí, odmítnutí, vrácení k přepracování).
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

            // Odeslání odpovědi zpět na front-end v závislosti na výsledku operace.
            if ($response[0]) {
                echo json_encode(["status" => "success", "message" => "Review updated successfully."]);
                http_response_code(200);
            } else {
                echo json_encode(["status" => "error", "message" => "Error updating review: " . $response[1][2]]);
                http_response_code(500);
            }
        }
    }