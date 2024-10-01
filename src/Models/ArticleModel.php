<?php
    namespace Web\Project\Models;

    class ArticleModel extends DatabaseModel{
        function getArticles(){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT * FROM articles");
            $stmt->execute();
            //$stmt = $pdo->prepare("SELECT * FROM articles WHERE articles.status_id = :status_id");
            //$stmt->execute(["status_id" => ACCEPTED_REVIEWED]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        function getArticle($id){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT articles.*, users.id_user, users.first_name, users.last_name, users.email FROM articles INNER JOIN users ON articles.author_id = users.id_user WHERE id_article=:id");
            $stmt->execute(["id" => $id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        function updateArticle($data){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("UPDATE articles SET title=:title, article_content=:content WHERE id_article=:id");
            $stmt->execute(["title" => $data["title"], "content" => $data["content"],"id" => $data["article_id"]]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        function insertArticle($abstract, $fileName, $fileContent, $user){
            $pdo = self::getConnection();

            $date = date("Y-m-d");
            $statusId = REVIEW_PROCESS;

            $stmt = $pdo->prepare("INSERT INTO articles (title, abstract, file, create_time, status_id, author_id) VALUES (:title, :abstract, :file, :create_time, :status_id, :author_id)");
            $stmt->bindParam(":title", $fileName);
            $stmt->bindParam(":abstract", $abstract);
            $stmt->bindParam(":file", $fileContent, \PDO::PARAM_LOB);
            $stmt->bindParam(":create_time", $date);
            $stmt->bindParam(":status_id", $statusId);
            $stmt->bindParam(":author_id", $user);

            if ($stmt->execute()) {
                echo "File uploaded successfully!";
                return $pdo->lastInsertId();
            } else {
                echo "Failed to upload the file.";
                return -1;
            }
        }
    }
