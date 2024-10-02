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

    //// Konstanty ////
    define("ROLE_ADMIN", 2);
    define("ROLE_USER", 4);
    define("REVIEW_PROCESS", 1);
    define("ACCEPTED_REVIEWED", 2);

    //// Tabulky ////
    define("TABLE_USERS", "users");
    define("TABLE_ROLES", "roles");

    const WEB_PAGES = array(
        "/" => array(
            "GET" => array(
                "title" => "Home page",
                "controller_class_name" => Web\Project\Controllers\HomeController::class,
                "function_name" => "index",
            ),
        ),
        "/login" => array(
            "GET" => array(
                "title" => "Sign in page",
                "controller_class_name" => Web\Project\Controllers\AuthController::class,
                "function_name" => "index",
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
                "function_name" => "index",
            ),
            "POST" => array(
                "title" => "Sign up page",
                "controller_class_name" => Web\Project\Controllers\AuthController::class,
                "function_name" => "register"
            )
        ),
        "/logout" => array(
            "GET" => array(
                "title" => "",
                "controller_class_name" => Web\Project\Controllers\AuthController::class,
                "function_name" => "logout"
            )
        ),
        "/profile" => array(
            "GET" => array(
                "title" => "Profile page",
                "controller_class_name" => Web\Project\Controllers\ProfileController::class,
                "function_name" => "index",
            )
        ),
        "/profile/edit" => array(
            "GET" => array(
                "title" => "Edit Profile",
                "controller_class_name" => Web\Project\Controllers\ProfileController::class,
                "function_name" => "editProfile",
            )
        ),
        "/users" => array(
            "GET" => array(
                "title" => "Users List",
                "controller_class_name" => Web\Project\Controllers\UsersListController::class,
                "function_name" => "index"
            )
        ),
        "/users/{id}" => array(
            "GET" => array(
                "title" => "User Profile",
                "controller_class_name" => Web\Project\Controllers\ProfileController::class,
                "function_name" => "showUserProfile"
            )
        ),
        "/articles/{slug}" => array(
            "GET" => array(
                "title" => "Article Detail",
                "controller_class_name" => Web\Project\Controllers\ArticleController::class,
                "function_name" => "index"
            )
        ),
        "/articles/update" => array(
            "POST" => array(
                "title" => "Article Detail",
                "controller_class_name" => Web\Project\Controllers\ArticleController::class,
                "function_name" => "updateArticle"
            )
        ),
        "/articles/pdf/{slug}" => array(
            "GET" => array(
                "title" => "Article Detail",
                "controller_class_name" => Web\Project\Controllers\ArticleController::class,
                "function_name" => "showPDF"
            )
        ),
        "/publish" => array(
            "GET" => array(
                "title" => "Publish Article",
                "controller_class_name" => Web\Project\Controllers\ArticleController::class,
                "function_name" => "publishFormShow"
            ),
            "POST" => array(
                "title" => "Publish Article",
                "controller_class_name" => Web\Project\Controllers\ArticleController::class,
                "function_name" => "publishArticle"
            )
        )
    );