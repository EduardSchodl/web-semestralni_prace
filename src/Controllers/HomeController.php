<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\ArticleModel;

    /**
     * Třída HomeController zpracovává zobrazení úvodní stránky webu,
     * včetně stránkování článků s přijatými recenzemi.
     */
    class HomeController extends BaseController
    {
        /**
         * Zobrazuje úvodní stránku s články, které mají stav "přijato po recenzi".
         * Implementuje stránkování.
         *
         * @param array $data Data, která mají být předána do šablony.
         * @return void
         */
        function index($data = []){
            $db = new ArticleModel();

            // Získání čísla stránky z parametru URL (výchozí hodnota je 1).
            $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;

            // Počet článků na stránku.
            $limit = 6;

            // Výpočet odsazení (offset) pro SQL dotaz.
            $offset = ($page - 1) * $limit;

            // Načtení celkového počtu článků a konkrétních článků pro aktuální stránku.
            [$totalArticles, $articles] = $db->getArticlesHomePage(STATUS["ACCEPTED_REVIEWED"], $limit, $offset);

            // Výpočet celkového počtu stránek.
            $totalPages = ceil($totalArticles / $limit);

            $this->render("HomeView.twig", ["title" => $data["title"], "articles" => $articles, "totalPages" => $totalPages, "page" => $page]);
        }
    }