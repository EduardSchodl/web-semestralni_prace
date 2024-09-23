<?php
    namespace Web\Project\Controllers;

    class AuthController extends BaseController
    {
        function index(){
            $this->render("AuthView.twig");
        }
    }