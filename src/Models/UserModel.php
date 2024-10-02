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

        function getUser($value, $byUsername = false) {
            $pdo = self::getConnection();

            $column = $byUsername ? "username" : "id_user";
            $stmt = $pdo->prepare("SELECT users.*, roles.name AS role_name FROM users INNER JOIN roles ON users.role_id = roles.id_role WHERE $column = :value");
            $stmt->execute(["value" => $value]);

            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        function addUser($postData){
            $pdo = self::getConnection();

            $hash_password = password_hash($postData["password"], PASSWORD_BCRYPT);

            $stmt = $pdo->prepare('
                INSERT INTO users (first_name, last_name, username, email, password, role_id) 
                VALUES (:firstname, :lastname, :username, :email, :password, :roleid)
            ');

            $stmt->execute([
                "firstname" => $postData["fname"],
                "lastname" => $postData["lname"],
                "username" => $postData["username"],
                "email" => $postData["email"],
                "password" => $hash_password,
                "roleid" => ROLE_USER
            ]);

            return $pdo->lastInsertId();
        }

        function checkUserExists($username, $email){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT id_user FROM users WHERE username=:username");
            $stmt->execute(["username" => $username]);

            if($stmt->fetchAll(\PDO::FETCH_ASSOC)){
                return -1;
            }

            $stmt = $pdo->prepare("SELECT id_user FROM users WHERE email=:email");
            $stmt->execute(["email" => $email]);

            if($stmt->fetchAll(\PDO::FETCH_ASSOC)){
                return -2;
            }

            return 0;
        }
    }