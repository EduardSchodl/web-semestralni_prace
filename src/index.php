<?php
    require __DIR__ . '/../vendor/autoload.php';
    require_once("settings.inc.php");

    use Web\Project\Router;

    $router = new Router();

    /*
    $router->add("GET", "/", "HomeController@index");
    $router->add("GET", "/auth", "AuthController@index");
    */
    $httpMethod = $_SERVER["REQUEST_METHOD"];
    $requestURI = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    $router->dispatch($httpMethod, $requestURI);
