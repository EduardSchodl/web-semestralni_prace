<?php
    namespace Web\Project\Controllers;

    if(!isset($_SESSION)){
        session_start();
    }

    class ProfileController extends BaseController {
        function index($data = []){
            $this->render("ProfileView.twig", ["title" => $data["title"]]);
        }
    }