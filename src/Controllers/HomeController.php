<?php
    namespace Web\Project\Controllers;

    class HomeController extends BaseController
    {
        function index(){
            $this->render("HomeView.twig");
        }
    }