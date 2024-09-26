<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\UserModel;

    class AuthController extends BaseController
    {
        function index($data = []){
            //$db = new UserModel();
            //$users = $db->getAllUsers();
            $this->render("AuthView.twig", ["title" => $data["title"]]);
        }

        function auth(){
            $db = new UserModel();
            $db->loginUser($_POST);
        }

        function register(){
            $db = new UserModel();
            $db->addUser($_POST);
        }
    }