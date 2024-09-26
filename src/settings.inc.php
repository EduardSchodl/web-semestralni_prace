<?php
    //// Pripojeni k databazi ////

    /** Adresa serveru. */
    define("DB_SERVER","localhost");
    /** Nazev databaze. */
    define("DB_NAME","konferencni_system");
    /** Uzivatel databaze. */
    define("DB_USER","root");
    /** Heslo uzivatele databaze */
    define("DB_PASS","");

    //// Tabulky ////
    define("TABLE_USERS", "users");

    const WEB_PAGES = array(
        "/" => array(
            "GET" => array(
                "title" => "Home page",
                "controller_class_name" => Web\Project\Controllers\HomeController::class,
                "function_name" => "index"
            ),
        ),
        "/login" => array(
            "GET" => array(
                "title" => "Sign in page",
                "controller_class_name" => Web\Project\Controllers\AuthController::class,
                "function_name" => "index"
            ),
            "POST" => array(
                "title" => "Sign in page",
                "controller_class_name" => Web\Project\Controllers\AuthController::class,
                "function_name" => "auth"
            )
        ),
        "/register" => array(
            "GET" => array(
                "title" => "Sign up page",
                "controller_class_name" => Web\Project\Controllers\AuthController::class,
                "function_name" => "index"
            ),
            "POST" => array(
                "title" => "Sign up page",
                "controller_class_name" => Web\Project\Controllers\AuthController::class,
                "function_name" => "register"
            )
        )
    );