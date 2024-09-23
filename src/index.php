<?php
    require __DIR__ . '/../vendor/autoload.php';

    use Web\Project\Controllers\HomeController;

    $controller = new HomeController();

    $controller->index();