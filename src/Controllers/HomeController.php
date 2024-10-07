<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\ArticleModel;

    class HomeController extends BaseController
    {
        function index($data = []){
            $db = new ArticleModel();

            $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
            $limit = 6;
            $offset = ($page - 1) * $limit;

            [$totalArticles, $articles] = $db->getArticlesHomePage(STATUS["ACCEPTED_REVIEWED"], $limit, $offset);
            $totalPages = ceil($totalArticles / $limit);

            $this->render("HomeView.twig", ["title" => $data["title"], "articles" => $articles, "totalPages" => $totalPages, "page" => $page]);
        }
    }