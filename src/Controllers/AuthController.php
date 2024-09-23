<?php
    namespace Web\Project\Controllers;

    class AuthController extends BaseController
    {
        function index(){
            echo $this->twig->render("AuthView.twig", ["name" => "Auth"]);
        }
    }