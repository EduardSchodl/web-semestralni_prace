<?php
    namespace Web\Project\Models;

    use HTMLPurifier;
    use HTMLPurifier_Config;

    /**
     * Třída ReviewModel spravuje operace s recenzemi v databázi.
     * Včetně získávání, přidávání, mazání a aktualizace recenzí.
     */
    class ReviewModel extends DatabaseModel{
        private $purifier;

        /**
         * Konstruktor inicializuje instanci HTMLPurifier s výchozími konfiguracemi.
         */
        function __construct(){
            $config = HTMLPurifier_Config::createDefault();
            $this->purifier = new HTMLPurifier($config);
        }

        /**
         * Získá recenze podle ID článku.
         *
         * @param int $articleId ID článku, jehož recenze se mají získat.
         * @return array Pole recenzí.
         */
        function getReviewsByArticleId($articleId){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT reviews.*, users.first_name, users.last_name FROM reviews INNER JOIN users ON reviews.id_user = users.id_user WHERE id_article=:articleId");
            $stmt->execute(["articleId" => $articleId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        /**
         * Získá recenze podle slugu článku.
         *
         * @param string $slug Slug článku, jehož recenze se mají získat.
         * @return array Pole recenzí.
         */
        function getReviewsByArticleSlug($slug){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT reviews.*, users.first_name, users.last_name FROM ((reviews INNER JOIN users ON reviews.id_user = users.id_user) INNER JOIN articles ON reviews.id_article=articles.id_article) WHERE slug=:slug AND reviews.status=:status");
            $stmt->execute(["slug" => $slug, "status" => 1]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        /**
         * Přidá recenzi k článku.
         *
         * @param int $idArticle ID článku, ke kterému se má přidat recenze.
         * @param int $idUser ID uživatele, který přidává recenzi.
         * @return array Pole obsahující true, pokud přidání proběhlo úspěšně, nebo false a chybovou zprávu, pokud došlo k chybě.
         */
        function addReview($idArticle, $idUser){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("INSERT INTO reviews (id_user, id_article, content, formality, up_to_date, language, status) VALUES (:userId, :articleId, NULL, NULL, NULL, NULL, 0)");
            $success = $stmt->execute(["userId" => $idUser, "articleId" => $idArticle]);

            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                return [false, $errorInfo];
            }

            return [true, null];
        }

        /**
         * Odstraní recenzi podle jejího ID.
         *
         * @param int $idReview ID recenze, která se má odstranit.
         * @return array Pole obsahující true, pokud odstranění proběhlo úspěšně, nebo false a chybovou zprávu, pokud došlo k chybě.
         */
        function removeReview($idReview){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id_review=:idReview");
            $success = $stmt->execute(["idReview" => $idReview]);

            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                return [false, $errorInfo];
            }

            return [true, null];
        }

        /**
         * Odstraní všechny recenze podle ID článku.
         *
         * @param int $idArticle ID článku, jehož recenze se mají odstranit.
         * @return array Pole obsahující true, pokud odstranění proběhlo úspěšně, nebo false a chybovou zprávu, pokud došlo k chybě.
         */
        function removeReviewByArticleId($idArticle){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id_article=:idArticle");
            $success = $stmt->execute(["idArticle" => $idArticle]);

            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                return [false, $errorInfo];
            }

            return [true, null];
        }

        /**
         * Získá recenze přidělené uživateli podle jeho ID.
         *
         * @param int $id ID uživatele, jehož recenze se mají získat.
         * @return array Pole recenzí jako asociativní pole.
         */
        function getReviewsByUserId($id){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT reviews.*, articles.slug, articles.title, articles.abstract, articles.status_id, users.first_name, users.last_name, status.status FROM (((reviews INNER JOIN articles ON reviews.id_article = articles.id_article) INNER JOIN users ON articles.author_id = users.id_user) INNER JOIN status ON articles.status_id=status.id_status) WHERE reviews.id_user=:id");
            $stmt->execute(["id" => $id]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        /**
         * Odesílá recenzi a aktualizuje její obsah.
         *
         * @param array $data Pole obsahující údaje o recenzi.
         * @return array Pole obsahující true, pokud aktualizace proběhla úspěšně, nebo false a chybovou zprávu, pokud došlo k chybě.
         */
        function submitReview($data){
            $pdo = self::getConnection();

            $date = date("Y-m-d");
            $stmt = $pdo->prepare("UPDATE reviews SET text=:text, content=:content, formality=:formality, up_to_date=:uptodate, language=:language, create_date=:date, status=:status WHERE id_review=:idReview");

            $editorContent = $this->purifier->purify($data["editorContent"]);

            $success = $stmt->execute(["content" => $data["content"], "text" => $editorContent, "formality" => $data["formality"], "uptodate" => $data["up_to_date"], "language" => $data["language"], "idReview" => $data["reviewId"], "date" => $date, "status" => 1]);

            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                return [false, $errorInfo];
            }

            return [true, null];
        }

        /**
         * Získá počet čekajících recenzí přidělených uživateli.
         *
         * @param int $idUser ID uživatele, pro kterého se má získat počet čekajících recenzí.
         * @return int Počet čekajících recenzí.
         */
        function getNumberOfPendingReviews($idUser){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE id_user=:idUser AND status=:status");
            $stmt->execute(["idUser" => $idUser, "status" => 0]);

            return (int)$stmt->fetchColumn();
        }

        /**
         * Smaže všechny zveřejněné recenze uživatele.
         *
         * @param int $idUser ID uživatele, jehož recenze se mají smazat.
         * @return array Pole obsahující true, pokud smazání proběhlo úspěšně, nebo false a chybovou zprávu, pokud došlo k chybě.
         */
        function removeAllUserReviews($idUser){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id_user=:idUser");
            $success = $stmt->execute(["idUser" => $idUser]);

            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                return [false, $errorInfo];
            }

            return [true, null];
        }
    }