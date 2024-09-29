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

            $stmt = $pdo->prepare("SELECT * FROM articles WHERE id_article=:id");
            $stmt->execute(["id" => $id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
    }
