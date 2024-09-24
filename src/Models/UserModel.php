<?php
    namespace Web\Project\Models;

    class UserModel extends DatabaseModel
    {
        function getAllUsers(){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT first_name FROM users WHERE id = :id");
            $stmt->execute(["id" => 1]);

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
    }