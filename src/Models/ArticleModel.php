<?php
    namespace Web\Project\Models;

    use PDO;

    if(!isset($_SESSION))
    {
        session_start();
    }

    class ArticleModel extends DatabaseModel{
        function getArticles(){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT * FROM articles WHERE articles.status_id = :status_id");
            $stmt->execute(["status_id" => ACCEPTED_REVIEWED]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        function getArticlesByUser($userId){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT articles.*, status.status FROM articles INNER JOIN status ON articles.status_id = status.id_status WHERE author_id = :authorId");
            $stmt->execute(["authorId" => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        function getArticle($slug){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT articles.*, users.id_user, users.first_name, users.last_name, users.email FROM articles INNER JOIN users ON articles.author_id = users.id_user WHERE slug=:slug");
            $stmt->execute([
                "slug" => $slug
            ]);

            $article = $stmt->fetch(PDO::FETCH_ASSOC);

            #$stmt = $pdo->prepare("SELECT articles.*, users.id_user, users.first_name, users.last_name, users.email FROM articles INNER JOIN users ON articles.author_id = users.id_user WHERE slug=:slug AND articles.status_id = :status");
            #$stmt->execute([
            #    "slug" => $slug,
            #    "status" => ACCEPTED_REVIEWED
            #]);
            return $article;
        }

        function updateArticle($data){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("UPDATE articles SET title=:title, abstract=:content WHERE id_article=:id");
            $stmt->execute(["title" => $data["title"], "content" => $data["content"],"id" => $data["article_id"]]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        function insertArticle($abstract, $title, $fileName, $fileContent, $user){
            $pdo = self::getConnection();

            $date = date("Y-m-d");
            $statusId = REVIEW_PROCESS;
            $slug = $fileName."-".$this->generateUUIDv4()."-".$date;

            $stmt = $pdo->prepare("INSERT INTO articles (title, slug, abstract, filename, file, create_time, status_id, author_id) VALUES (:title, :slug, :abstract, :filename, :file, :create_time, :status_id, :author_id)");
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":slug", $slug);
            $stmt->bindParam(":abstract", $abstract);
            $stmt->bindParam(":filename", $fileName);
            $stmt->bindParam(":file", $fileContent, PDO::PARAM_LOB);
            $stmt->bindParam(":create_time", $date);
            $stmt->bindParam(":status_id", $statusId);
            $stmt->bindParam(":author_id", $user);

            if ($stmt->execute()) {
                echo "File uploaded successfully!";
                return $slug;
            } else {
                echo "Failed to upload the file.";
                return -1;
            }
        }

        function deleteArticle($id){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("DELETE FROM articles WHERE id_article=:id");
            $stmt->execute(["id" => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        function generateUUIDv4()
        {
            $data = random_bytes(16);
            // Set the version to 0100 (4)
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            // Set the variant to 10 (RFC 4122)
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            return vsprintf('%s-%s-%s-%s-%s', str_split(bin2hex($data), 4));
        }
    }
