<?php
    namespace Web\Project\Models;

    class ReviewModel extends DatabaseModel{
        function getReviewsByArticleId($articleId){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT reviews.*, users.first_name, users.last_name FROM reviews INNER JOIN users ON reviews.id_user = users.id_user WHERE id_article=:articleId");
            $stmt->execute(["articleId" => $articleId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        function getReviewsByArticleSlug($slug){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT reviews.*, users.first_name, users.last_name FROM ((reviews INNER JOIN users ON reviews.id_user = users.id_user) INNER JOIN articles ON reviews.id_article=articles.id_article) WHERE slug=:slug AND reviews.status=:status");
            $stmt->execute(["slug" => $slug, "status" => 1]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

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

        function getReviewsByUserId($id){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT reviews.*, articles.slug, articles.title, articles.abstract, articles.status_id, users.first_name, users.last_name, status.status FROM (((reviews INNER JOIN articles ON reviews.id_article = articles.id_article) INNER JOIN users ON articles.author_id = users.id_user) INNER JOIN status ON articles.status_id=status.id_status) WHERE reviews.id_user=:id");
            $stmt->execute(["id" => $id]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        function submitReview($data){
            $pdo = self::getConnection();

            $date = date("Y-m-d");
            $stmt = $pdo->prepare("UPDATE reviews SET text=:text, content=:content, formality=:formality, up_to_date=:uptodate, language=:language, create_date=:date, status=:status WHERE id_review=:idReview");
            $stmt->execute(["content" => $data["content"], "text" => $data["editorContent"], "formality" => $data["formality"], "uptodate" => $data["up_to_date"], "language" => $data["language"], "idReview" => $data["reviewId"], "date" => $date, "status" => 1]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        function getNumberOfPendingReviews($idUser){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE id_user=:idUser AND status=:status");
            $stmt->execute(["idUser" => $idUser, "status" => 0]);

            return (int)$stmt->fetchColumn();
        }
    }