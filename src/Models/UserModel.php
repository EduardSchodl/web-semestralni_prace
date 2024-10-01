<?php
    namespace Web\Project\Models;

    class UserModel extends DatabaseModel
    {
        function getAllUsers(){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT users.*, roles.name AS role_name FROM users INNER JOIN roles ON users.role_id = roles.id_role ORDER BY users.role_id ASC");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        function getUser($value, $byEmail = true) {
            $pdo = self::getConnection();

            $column = $byEmail ? 'email' : 'id_user';
            $stmt = $pdo->prepare("SELECT users.*, roles.name AS role_name FROM users INNER JOIN roles ON users.role_id = roles.id_role WHERE $column = :value");
            $stmt->execute(["value" => $value]);

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