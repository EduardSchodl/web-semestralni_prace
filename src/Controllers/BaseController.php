<?php
    namespace Web\Project\Controllers;

    use Twig\Environment;
    use Twig\Loader\FilesystemLoader;

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

            $data['current_path'] = $path;
            echo $this->twig->render($view, $data);
        }
    }