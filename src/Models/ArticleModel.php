<?php
    namespace Web\Project\Models;

    use HTMLPurifier;
    use HTMLPurifier_Config;
    use PDO;

    // Spustí relaci, pokud není nastavena
    if(!isset($_SESSION))
    {
        session_start();
    }

    /**
     * ArticleModel spravuje články v databázi.
     * Obsahuje metody pro práci s články, včetně vkládání, aktualizace, mazání a získávání článků.
     */
    class ArticleModel extends DatabaseModel{
        private $purifier;

        /**
         * Konstruktor třídy ArticleModel.
         * Inicializuje HTMLPurifier, který slouží k vyčištění uživatelských vstupů.
         */
        function __construct(){
            $config = HTMLPurifier_Config::createDefault();
            $this->purifier = new HTMLPurifier($config);
        }

        /**
         * Získá všechny články nebo články podle statusu.
         * @param int|null $status Status článku.
         * @return array Seznam článků.
         */
        function getArticles($status = null){
            $pdo = self::getConnection();

            if ($status !== null) {
                $stmt = $pdo->prepare("SELECT articles.*, users.first_name AS user_first_name, users.last_name AS user_last_name FROM articles INNER JOIN users ON articles.author_id=users.id_user WHERE articles.status_id = :status_id");
                $stmt->execute(["status_id" => $status]);
            } else {
                $stmt = $pdo->prepare("SELECT articles.*, users.first_name AS user_first_name, users.last_name AS user_last_name, status.status FROM ((articles INNER JOIN users ON articles.author_id=users.id_user) INNER JOIN status ON articles.status_id=status.id_status)");
                $stmt->execute();
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Získá omezený počet článků pro zobrazení na hlavní stránce.
         * @param int $status Status článku.
         * @param int $limit Maximální počet článků.
         * @param int $offset Posun v seznamu článků.
         * @return array Seznam článků a jejich počet.
         */
        function getArticlesHomePage($status, $limit, $offset){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("
                SELECT articles.*, users.first_name AS user_first_name, users.last_name AS user_last_name 
                FROM articles 
                INNER JOIN users ON articles.author_id = users.id_user 
                WHERE articles.status_id = :status_id 
                LIMIT :limit OFFSET :offset
            ");

            $stmt->bindParam(':status_id', $status, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

            $stmt->execute();
            $count = $this->countArticlesHomePage($status);

            return [$count ,$stmt->fetchAll(PDO::FETCH_ASSOC)];
        }

        /**
         * Získá počet článků na hlavní stránce podle statusu.
         * @param int $status Status článku.
         * @return int Počet článků.
         */
        function countArticlesHomePage($status){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM articles 
                WHERE status_id = :status_id
            ");

            $stmt->execute(["status_id" => $status]);
            return $stmt->fetchColumn();
        }

        /**
         * Získá články podle uživatele.
         * @param string $column Název sloupce filtru.
         * @param string|int $value Hodnota sloupce filtru.
         * @return array Seznam článků.
         */
        function getArticlesByUser($column, $value){
            $pdo = self::getConnection();

            if($column == "username"){
                $stmt = $pdo->prepare("SELECT id_user FROM users WHERE username = :username");
                $stmt->execute(["username" => $value]);
                $value = $stmt->fetch(PDO::FETCH_ASSOC)["id_user"];
            }

            $stmt = $pdo->prepare("SELECT articles.*, status.status FROM articles INNER JOIN status ON articles.status_id = status.id_status WHERE author_id = :author_id");
            $stmt->execute(["author_id" => $value]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Získá konkrétní článek podle slugu.
         * @param string $slug Slug článku.
         * @return array Článek a informace o autorovi.
         */
        function getArticle($slug){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT articles.*, users.id_user, users.first_name, users.last_name, users.email FROM articles INNER JOIN users ON articles.author_id = users.id_user WHERE slug=:slug");
            $stmt->execute([
                "slug" => $slug
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        /**
         * Aktualizuje článek v databázi.
         * @param array $data Data článku.
         * @return array [status, errorInfo|null] Stav a chybové informace.
         */
        function updateArticle($data){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("UPDATE articles SET title=:title, abstract=:content WHERE id_article=:id");
            $content = $this->purifier->purify($data["content"]);
            $title = $this->purifier->purify($data["title"]);
            $success = $stmt->execute(["title" => $title, "content" => $content,"id" => $data["article_id"]]);

            // Vrátí chybu, pokud nastane chyba
            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                return [false, $errorInfo];
            }

            return [true, null];
        }

        /**
         * Vloží nový článek do databáze.
         * @param string $abstract Abstrakt článku.
         * @param string $title Titulek článku.
         * @param string $fileName Název souboru.
         * @param string $fileContent Obsah souboru.
         * @param int $user ID autora článku.
         * @return string|int Slug článku nebo -1 při chybě.
         */
        function insertArticle($abstract, $title, $fileName, $fileContent, $user){
            $pdo = self::getConnection();

            // Vytvoří datum a status pro nový článek
            $date = date("Y-m-d");
            $statusId = STATUS["REVIEW_PROCESS"];

            // Vygeneruje unikátní slug pro článek
            $slug = str_replace(" ", "_",$fileName)."-".$this->generateUUIDv4()."-".$date;

            $title = $this->purifier->purify($title);
            $abstract = $this->purifier->purify($abstract);

            $stmt = $pdo->prepare("INSERT INTO articles (title, slug, abstract, filename, file, create_time, status_id, author_id) VALUES (:title, :slug, :abstract, :filename, :file, :create_time, :status_id, :author_id)");
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":slug", $slug);
            $stmt->bindParam(":abstract", $abstract);
            $stmt->bindParam(":filename", $fileName);
            $stmt->bindParam(":file", $fileContent, PDO::PARAM_LOB);
            $stmt->bindParam(":create_time", $date);
            $stmt->bindParam(":status_id", $statusId);
            $stmt->bindParam(":author_id", $user);

            // Vrátí slug, pokud projde, jinak -1
            if ($stmt->execute()) {
                echo "File uploaded successfully!";
                return $slug;
            } else {
                echo "Failed to upload the file.";
                return -1;
            }
        }

        /**
         * Získá článek podle jeho ID.
         *
         * @param int $idArticle ID článku, který se má získat.
         * @return array|false Podrobnosti o článku včetně jména a příjmení autora a stavu, nebo false, pokud nebyl nalezen.
         */
        function getArticleById($idArticle){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT articles.*, users.first_name AS user_first_name, users.last_name AS user_last_name, status.status FROM ((articles INNER JOIN users ON articles.author_id=users.id_user) INNER JOIN status ON articles.status_id=status.id_status) WHERE articles.id_article=:idArticle");
            $stmt->execute([
                "idArticle" => $idArticle
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        /**
         * Smaže článek podle jeho ID.
         *
         * @param int $id ID článku, který se má smazat.
         * @return array Pole obsahující true, pokud smazání proběhlo úspěšně, nebo false a chybovou zprávu, pokud došlo k chybě.
         */
        function deleteArticle($id){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("DELETE FROM articles WHERE id_article=:id");
            $success =$stmt->execute(["id" => $id]);

            // Pokud smazání selhalo, vrátí chybovou zprávu
            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                return [false, $errorInfo];
            }

            return [true, null];
        }

        /**
         * Generuje UUID verze 4.
         *
         * @return string Vygenerované UUID ve formátu verze 4.
         */
        function generateUUIDv4()
        {
            // Vytvoření 16 náhodných bajtů
            $data = random_bytes(16);
            // Nastavení verze na 0100 (verze 4)
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            // Nastavení varianty na 10 (RFC 4122)
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            // Formátování UUID ve standardním formátu
            return vsprintf('%s-%s-%s-%s-%s', str_split(bin2hex($data), 4));
        }

        /**
         * Aktualizuje stav článku.
         *
         * @param int $idArticle ID článku, jehož stav se má aktualizovat.
         * @param int $idStatus Nový stav článku.
         * @return array Pole obsahující true, pokud aktualizace proběhla úspěšně, nebo false a chybovou zprávu, pokud došlo k chybě.
         */
        function updateArticleStatus($idArticle, $idStatus){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("UPDATE articles SET status_id=:idStatus WHERE id_article=:idArticle");
            $success = $stmt->execute(["idStatus" => $idStatus, "idArticle" => $idArticle]);

            // Pokud aktualizace selhala, vrátí chybovou zprávu
            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                return [false, $errorInfo];
            }

            return [true, null];
        }
    }
