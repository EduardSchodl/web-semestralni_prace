<?php
    namespace Web\Project\Controllers;

    class AuthController extends BaseController
    {
        function index($data = []){
            //$db = new UserModel();
            //$users = $db->getAllUsers();
            $this->render("AuthView.twig", ["title" => $data["title"]]);
        }

        function auth(){
            echo ($_POST["password"]);
        }
    }