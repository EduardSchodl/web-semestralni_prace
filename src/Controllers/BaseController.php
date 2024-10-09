<?php
    namespace Web\Project\Controllers;

    use Twig\Environment;
    use Twig\Loader\FilesystemLoader;
    use Web\Project\Models\ReviewModel;

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

            $numOfReviews = null;
            if($user){
                $db = new ReviewModel();
                $numOfReviews = $db->getNumberOfPendingReviews($user["id_user"]);
            }

            $this->twig->addGlobal('app', ['user' => $user]);
            $this->twig->addGlobal('role', ['role_id' => ROLES]);
            $this->twig->addGlobal('status', ['status_id' => STATUS]);
            $this->twig->addGlobal('ban', ['status' => BAN]);
            $this->twig->addGlobal('min_reviewers', ['min_reviewers' => MINIMAL_REVIEWERS]);
            $this->twig->addGlobal("numOfReviews", ['num' => $numOfReviews]);

            // In your PHP/Twig setup
            $this->twig->addFunction(new \Twig\TwigFunction('render_stars', function ($rating) {
                if($rating === null){
                    return null;
                }

                $fullStars = floor($rating);
                $halfStar = ($rating - $fullStars) >= 0.5;
                $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

                $output = str_repeat('<i class="bi bi-star-fill text-warning"></i>', $fullStars);  // Full stars
                if ($halfStar) {
                    $output .= '<i class="bi bi-star-half text-warning"></i>';  // Half star
                }
                $output .= str_repeat('<i class="bi bi-star text-warning"></i>', $emptyStars);  // Empty stars

                return $output;
            }));

            $flashMessage = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
            $data['current_path'] = $path;
            $data['flash'] = $flashMessage;
            echo $this->twig->render($view, $data);

            unset($_SESSION['flash']);
        }
    }