<?php
    namespace Web\Project\Controllers;

    use Web\Project\Models\ReviewModel;

    // Zahájení session, pokud ještě nebyla spuštěna.
    if (!isset($_SESSION)) {
        session_start();
    }

    /**
     * Třída ReviewController zpracovává akce spojené s recenzemi článků,
     * včetně podání recenze, aktualizace recenzí a zobrazení recenzí uživatele.
     */
    class ReviewController extends BaseController
    {
        /**
         * Zvěřejňuje recenzi uživatele.
         *
         * @param array $data Data předávaná pro šablonu.
         * @return void
         */
        function submitReview($data = []){
            // Kontrola autorizace uživatele (administrátor)
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] != ROLES["ROLE_ADMIN"])
            {
                $_SESSION['flash'] = [
                    'message' => 'Insufficient authorisation!',
                    'type' => 'warning'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            $db = new ReviewModel();

            $response = $db->submitReview($_POST);;

            // Nastavení HTTP odpovědi podle výsledku
            if ($response[0]) {
                http_response_code(200); // Success
            } else {
                http_response_code(500); // Server error
            }

            // Obnoví stránku po odeslání recenze
            header("Refresh:0");
        }

        /**
         * Aktualizuje stav recenze podle požadované akce (přidání nebo odebrání recenzenta).
         *
         * @return void
         */
        function reviewUpdate(){
            // Kontrola autorizace uživatele (administrátor)
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_ADMIN"])
            {
                $_SESSION['flash'] = [
                    'message' => 'Insufficient authorisation!',
                    'type' => 'warning'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            $db = new ReviewModel();
            $response = null;

            // Určuje akci podle POST parametru "action"
            switch($_POST["action"]){
                case "addReviewer":
                    $response = $db->addReview($_POST["values"]["idArticle"], $_POST["values"]["idUser"]);
                    break;
                case "removeReview":
                    $response = $db->removeReview($_POST["values"]["idReview"]);
                    break;
            }

            // Nastavení HTTP odpovědi podle výsledku
            if ($response[0]) {
                echo json_encode(["status" => "success", "message" => "Review updated successfully."]);
                http_response_code(200); // Success
            } else {
                echo json_encode(["status" => "error", "message" => "Error updating review: " . $response[1][2]]);
                http_response_code(500); // Server error
            }
        }

        /**
         * Zobrazuje seznam přidělených recenzí uživateli.
         *
         * @param array $data Data předávaná pro vykreslení.
         * @return void
         */
        function showUserReviews($data = []){
            // Kontrola autorizace uživatele (recenzent)
            if(!isset($_SESSION["user"]) || $_SESSION["user"]["role_id"] > ROLES["ROLE_REVIEWER"])
            {
                $_SESSION['flash'] = [
                    'message' => 'Insufficient authorisation!',
                    'type' => 'warning'
                ];
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
                return;
            }

            // Načtení recenzí podle uživatele
            $db = new ReviewModel();
            $reviews = $db->getReviewsByUserId($_SESSION["user"]["id_user"]);

            $this->render("UserReviewsList.twig", ["title" => $data["title"], "reviews" => $reviews]);
        }
    }