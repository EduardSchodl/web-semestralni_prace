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

            $column = null;

            if($byUsername){
                $column = "username";
                $value = trim(strip_tags($value));
            }
            else{
                $column = "id_user";
            }

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

            $firstName = trim(strip_tags($postData["fname"]));
            $lastName = trim(strip_tags($postData["lname"]));
            $username = trim(strip_tags($postData["username"]));
            $email = trim(strip_tags($postData["email"]));

            $stmt->execute([
                "firstname" => $firstName,
                "lastname" => $lastName,
                "username" => $username,
                "email" => $email,
                "password" => $hash_password,
                "roleid" => ROLES["ROLE_USER"]
            ]);

            return $pdo->lastInsertId();
        }

        function checkUserExists($username, $email){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT id_user FROM users WHERE username=:username");

            $username = trim(strip_tags($username));

            $stmt->execute(["username" => $username]);

            if($stmt->fetchAll(\PDO::FETCH_ASSOC)){
                return -1;
            }

            $email = trim(strip_tags($email));

            $stmt = $pdo->prepare("SELECT id_user FROM users WHERE email=:email");
            $stmt->execute(["email" => $email]);

            if($stmt->fetchAll(\PDO::FETCH_ASSOC)){
                return -2;
            }

            return 0;
        }

        function updateRole($id_user, $id_role){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("UPDATE users SET role_id=:role_id WHERE id_user=:id");
            $success = $stmt->execute(["role_id" => $id_role , "id" => $id_user]);

            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                return [false, $errorInfo];
            }

            return [true, null];
        }

        function userBanStatusUpdate($id_user, $banStatus){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("UPDATE users SET banned=:banned WHERE id_user=:id");
            $success = $stmt->execute(["banned" => $banStatus, "id" => $id_user]);

            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                return [false, $errorInfo];
            }

            return [true, null];
        }

        function deleteUser($id_user){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("DELETE FROM users WHERE id_user=:id");
            $success = $stmt->execute(["id" => $id_user]);

            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                return [false, $errorInfo];
            }

            return [true, null];
        }

        function getReviewers(){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT * FROM users WHERE users.role_id=:role_id AND users.banned = :ban");
            $success =$stmt->execute(["role_id" => ROLES["ROLE_REVIEWER"], "ban" => BAN["UNBANNED"]]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
    }