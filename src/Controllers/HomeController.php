<?php
    namespace Web\Project\Controllers;

    use Twig\Environment;
    use Twig\Loader\FilesystemLoader;

    class HomeController extends BaseController
    {
        function index(){
            echo $this->twig->render("HomeView.twig", ["name" => "World"]);
        }
    }