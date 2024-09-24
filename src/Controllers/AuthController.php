<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\UserModel;

    class AuthController extends BaseController
    {
        function index(){
            $db = new UserModel();
            $users = $db->getAllUsers();

            echo $users;

            $this->render("AuthView.twig");
        }
    }