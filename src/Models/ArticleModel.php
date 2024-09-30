<?php
    namespace Web\Project\Models;

    class ArticleModel extends DatabaseModel{
        function getArticles(){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT * FROM articles");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        function getArticle($id){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT articles.*, users.id_user, users.first_name, users.last_name, users.email FROM articles INNER JOIN users ON articles.author_id = users.id_user WHERE id_article=:id");
            $stmt->execute(["id" => $id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
    }
