<?php
    namespace Web\Project\Controllers;

    class ArticleController extends BaseController{
        function index($data = []){
            $this->render("ArticleView.twig", ["title" => $data["title"]]);
        }
    }