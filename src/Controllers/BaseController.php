<?php
    namespace Web\Project\Controllers;

    use Twig\Environment;
    use Twig\Loader\FilesystemLoader;

    if(!isset($_SESSION))
    {
        session_start();
    }

    class BaseController
    {
        protected $twig;

        public function __construct()
        {
            $loader = new FilesystemLoader(__DIR__."/../Views");
            $this->twig = new Environment($loader);
        }

        protected function render($view, $data = [])
        {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);;
            $scriptName = dirname($_SERVER["SCRIPT_NAME"]);
            $path = str_replace($scriptName, "", $path);

            $user = $_SESSION['user'] ?? null;
            $this->twig->addGlobal('app', ['user' => $user]);
            $this->twig->addGlobal('role', ['role_id' => ROLES]);
            $this->twig->addGlobal('status', ['status_id' => STATUS]);
            $this->twig->addGlobal('ban', ['status' => BAN]);
            $this->twig->addGlobal('min_reviewers', ['min_reviewers' => MINIMAL_REVIEWERS]);

            $data['current_path'] = $path;
            echo $this->twig->render($view, $data);
        }
    }