<?php
    // Načtení autoload souboru pro automatické načítání tříd
    require __DIR__ . '/../vendor/autoload.php';

    // Načtení konfiguračního souboru se specifikacemi
    require_once("settings.inc.php");

    use Web\Project\Router;

    $router = new Router();

    // Získání HTTP metody požadavku (GET, POST, atd.)
    $httpMethod = $_SERVER["REQUEST_METHOD"];
    // Získání cesty požadavku bez dotazovací části
    $requestURI = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// Dispečink požadavku na základě HTTP metody a URI
    $router->dispatch($httpMethod, $requestURI);
