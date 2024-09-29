<?php
    namespace Web\Project\Controllers;

    class HomeController extends BaseController
    {
        function index($data = []){
            $this->render("HomeView.twig", ["title" => $data["title"]]);
        }
    }