<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\ArticleModel;

    class HomeController extends BaseController
    {
        function index($data = []){
            $db = new ArticleModel();
            $articles = $db->getArticles(STATUS["ACCEPTED_REVIEWED"]);

            $this->render("HomeView.twig", ["title" => $data["title"], "articles" => $articles]);
        }
    }