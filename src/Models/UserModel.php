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

        function loginUser($postData){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(["email" => $_POST["email"]]);
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);

            if($data && password_verify($postData["password"], $data["password"])){
                //alert uspech
                $_SESSION["id"] = $data["id_user"];
                echo $_SESSION["id"];
            }
            else{
                //alert spatne heslo nebo mail
                $stmt = null;
                self::closeConnection();

                header("Location: login");
                exit();
            }

            $stmt = null;
            self::closeConnection();
        }

        function addUser($postData){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare('SELECT id_user FROM users WHERE email = :email');

            $stmt->execute(["email" => $postData["email"]]);
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);

            if($data){
                //alert email je jiz zabrany
                $stmt = null;
                self::closeConnection();

                header("Location: register");
                exit();
            }

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
                "roleid" => 4
            ]);
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);

            //check jestli se povedlo
            //hodit do session

            $stmt = null;
            self::closeConnection();

            header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/web-semestralni_prace/src');
            exit();
        }
    }