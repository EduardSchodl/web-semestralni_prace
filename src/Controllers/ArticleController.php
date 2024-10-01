<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\ArticleModel;

    if(!isset($_SESSION))
    {
        session_start();
    }

    class ArticleController extends BaseController{
        function index($data = []){
            $db = new ArticleModel();
            $article = $db->getArticle($data["params"][0]);

            $this->render("ArticleView.twig", ["title" => $data["title"], "article" => $article]);
        }

        function updateArticle(){
            $db = new ArticleModel();
            $db->updateArticle($_POST);
        }

        function publishFormShow($data = []){
            $this->render("PublishArticleView.twig", ["title" => $data["title"]]);
        }

        function publishArticle(){
            if (!isset($_POST["abstract"]) || !isset($_FILES["file"])) {
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
            $state = $db->insertArticle($_POST["abstract"], $file_name, $file_content, $_SESSION["user"]["id_user"]);

            if($state == -1){
                echo "Chyba";
                exit;
            }

            header("Location: articles/$state");
        }
    }