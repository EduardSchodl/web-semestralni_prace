<?php
    namespace Web\Project\Models;

    class ReviewModel extends DatabaseModel{
        function getReviewsByArticleId($articleId){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT reviews.*, users.first_name, users.last_name FROM reviews INNER JOIN users ON reviews.id_user = users.id_user WHERE id_article=:articleId");
            $stmt->execute(["articleId" => $articleId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        function addReview($idArticle, $idUser){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("INSERT INTO reviews (id_user, id_article, content, formality, up_to_date, language) VALUES (:userId, :articleId, NULL, NULL, NULL, NULL)");
            $success = $stmt->execute(["userId" => $idUser, "articleId" => $idArticle]);

            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                return [false, $errorInfo];
            }

            return [true, null];
        }
    }