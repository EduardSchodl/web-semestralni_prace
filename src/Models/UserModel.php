<?php
    namespace Web\Project\Models;

    class UserModel extends DatabaseModel
    {
        function getAllUsers(){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT first_name FROM users WHERE id_user = :id");
            $stmt->execute(["id" => 1]);

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        function loginUser(){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare();

            $stmt->execute();
        }

        function addUser(){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare('
                INSERT INTO users ("first_name, last_name, email, password, role_id") 
                VALUES (:firstname, :lastname, :email, :password, :roleid)users WHERE id_user = :id
            ');
            $stmt->execute(["firstname" => $_POST[""], ""]);
        }
    }