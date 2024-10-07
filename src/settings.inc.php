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
    define("MINIMAL_REVIEWERS", 3);

    const ROLES = array(
        "SUPERADMIN" => 1,
        "ROLE_ADMIN" => 2,
        "ROLE_REVIEWER" => 3,
        "ROLE_USER" => 4
    );

    const STATUS = array(
        "REVIEW_PROCESS" => 1,
        "ACCEPTED_REVIEWED" => 2,
        "REJECTED_REVIEWED" => 3
    );

    const BAN = array(
        "BANNED" => 1,
        "UNBANNED" => 0
    );

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
                "controller_class_name" => Web\Project\Controllers\UserController::class,
                "function_name" => "index",
            )
        ),
        "/profile/articles" => array(
            "GET" => array(
                "title" => "My Articles",
                "controller_class_name" => Web\Project\Controllers\ArticleController::class,
                "function_name" => "getProfileArticles",
            )
        ),
        "/users" => array(
            "GET" => array(
                "title" => "Users List",
                "controller_class_name" => Web\Project\Controllers\UserController::class,
                "function_name" => "showUsersList"
            ),
            "POST" => array(
                "title" => "Users List",
                "controller_class_name" => Web\Project\Controllers\UserController::class,
                "function_name" => "updateUser"
            )
        ),
        "/users/{username}" => array(
            "GET" => array(
                "title" => "User Profile",
                "controller_class_name" => Web\Project\Controllers\UserController::class,
                "function_name" => "showUserProfile"
            )
        ),
        "/users/{username}/articles" => array(
            "GET" => array(
                "title" => "User Profile",
                "controller_class_name" => Web\Project\Controllers\ArticleController::class,
                "function_name" => "getUserArticles"
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
        "/articles/delete" => array(
            "POST" => array(
                "title" => "Article Detail",
                "controller_class_name" => Web\Project\Controllers\ArticleController::class,
                "function_name" => "deleteArticle"
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
        ),
        "/articles-management" => array(
            "GET" => array(
                "title" => "Article Management",
                "controller_class_name" => Web\Project\Controllers\ArticleController::class,
                "function_name" => "articlesManagementShow"
            ),
            "POST" => array(
                "title" => "Article Management",
                "controller_class_name" => Web\Project\Controllers\ReviewController::class,
                "function_name" => "reviewUpdate"
            )
        ),
        "/articles-management/article-status-update" => array(
            "POST" => array(
                "title" => "Article Management",
                "controller_class_name" => Web\Project\Controllers\ArticleController::class,
                "function_name" => "checkReviews"
            )
        ),
        "/articles-management/updateArticle" => array(
            "POST" => array(
                "title" => "Update Article",
                "controller_class_name" => Web\Project\Controllers\ArticleController::class,
                "function_name" => "updateArticleStatus"
            )
        ),
        "/profile/reviews" => array(
            "GET" => array(
                "title" => "Reviews",
                "controller_class_name" => Web\Project\Controllers\ReviewController::class,
                "function_name" => "showUserReviews"
            ),
            "POST" => array(
                "title" => "Reviews",
                "controller_class_name" => Web\Project\Controllers\ReviewController::class,
                "function_name" => "submitReview"
            )
        )
    );