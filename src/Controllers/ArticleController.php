<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\ArticleModel;

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
    }