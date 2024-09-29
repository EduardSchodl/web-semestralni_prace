<?php
    namespace Web\Project\Controllers;

    if(!isset($_SESSION)){
        session_start();
    }

    class ProfileController extends BaseController {
        function index($data = []){
            if(!isset($_SESSION["user"])){
                echo "Nejste přihlášen";
                exit;
            }

            $this->render("ProfileView.twig", ["title" => $data["title"]]);
        }
    }