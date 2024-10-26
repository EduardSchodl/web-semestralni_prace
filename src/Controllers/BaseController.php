<?php
    namespace Web\Project\Controllers;

    use Twig\Environment;
    use Twig\Loader\FilesystemLoader;
    use Web\Project\Models\ReviewModel;

    // Zahájení session, pokud ještě nebyla spuštěna.
    if(!isset($_SESSION))
    {
        session_start();
    }

    /**
     * Třída BaseController poskytuje základní funkce pro všechny kontrolery,
     * jako je například vykreslování šablon Twig a globální proměnné pro aplikaci.
     */
    class BaseController
    {
        /** @var Environment $twig Twig šablonovací engine */
        protected $twig;

        /**
         * Konstruktor BaseController inicializuje Twig s cestou k šablonám.
         */
        public function __construct()
        {
            // Načtení souborů Twig šablon z adresáře Views.
            $loader = new FilesystemLoader(__DIR__."/../Views");
            $this->twig = new Environment($loader);
        }

        /**
         * Metoda render vykresluje Twig šablonu a přidává globální proměnné do šablony.
         *
         * @param string $view Název šablony, která se má vykreslit.
         * @param array $data Data, která mají být předána do šablony.
         * @return void
         */
        protected function render($view, $data = [])
        {
            // Získání aktuální cesty z URL požadavku.
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);;
            $scriptName = dirname($_SERVER["SCRIPT_NAME"]);
            $path = str_replace($scriptName, "", $path);

            // Načtení uživatele ze session (pokud je přihlášen).
            $user = $_SESSION['user'] ?? null;

            // Načtení počtu nevyřízených recenzí pro přihlášeného uživatele.
            $numOfReviews = null;
            if($user){
                $db = new ReviewModel();
                $numOfReviews = $db->getNumberOfPendingReviews($user["id_user"]);
            }

            // Přidání globálních proměnných do Twig šablon.
            $this->twig->addGlobal('app', ['user' => $user]);
            $this->twig->addGlobal('role', ['role_id' => ROLES]);
            $this->twig->addGlobal('status', ['status_id' => STATUS]);
            $this->twig->addGlobal('ban', ['status' => BAN]);
            $this->twig->addGlobal('min_reviewers', ['min_reviewers' => MINIMAL_REVIEWERS]);
            $this->twig->addGlobal("numOfReviews", ['num' => $numOfReviews]);
            $this->twig->addGlobal("currentYear", date("Y"));

            // Přidání Twig funkce pro zobrazení hodnocení pomocí hvězdiček.
            $this->twig->addFunction(new \Twig\TwigFunction('render_stars', function ($rating) {
                if($rating === null){
                    return null;
                }

                // Počet plných, polovičních a prázdných hvězdiček.
                $fullStars = floor($rating);
                $halfStar = ($rating - $fullStars) >= 0.5;
                $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

                // Vytvoření HTML výstupu pro hvězdičky.
                $output = str_repeat('<i class="bi bi-star-fill text-warning"></i>', $fullStars);  // Full stars
                if ($halfStar) {
                    $output .= '<i class="bi bi-star-half text-warning"></i>';  // Half star
                }
                $output .= str_repeat('<i class="bi bi-star text-warning"></i>', $emptyStars);  // Empty stars

                return $output;
            }));

            // Načtení flash zprávy z session (pokud existuje).
            $flashMessage = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;

            // Přidání aktuální cesty a flash zprávy do dat pro šablonu.
            $data['current_path'] = $path;
            $data['flash'] = $flashMessage;

            // Vykreslení požadované šablony s předanými daty.
            echo $this->twig->render($view, $data);

            // Vymazání flash zprávy po jejím zobrazení.
            unset($_SESSION['flash']);
        }
    }