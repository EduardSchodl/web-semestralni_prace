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

        function getUser($email){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT users.*, roles.name AS role_name FROM users INNER JOIN roles ON users.role_id = roles.id_role WHERE email = :email");
            $stmt->execute(["email" => $email]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        function addUser($postData){
            $pdo = self::getConnection();

            $hash_password = password_hash($postData["password"], PASSWORD_BCRYPT);

            $stmt = $pdo->prepare('
                INSERT INTO users (first_name, last_name, email, password, role_id) 
                VALUES (:firstname, :lastname, :email, :password, :roleid)
            ');

            $stmt->execute([
                "firstname" => $postData["fname"],
                "lastname" => $postData["lname"],
                "email" => $postData["email"],
                "password" => $hash_password,
                "roleid" => ROLE_USER
            ]);

            return $stmt->rowCount();
        }
    }