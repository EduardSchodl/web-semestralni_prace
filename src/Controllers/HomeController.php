<?php
    namespace Web\Project\Controllers;

    class HomeController extends BaseController
    {
        function index(){
            echo $this->twig->render("HomeView.twig", ["name" => "World"]);
        }
    }