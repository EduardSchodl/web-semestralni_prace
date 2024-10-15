<?php
    namespace Web\Project\Models;

    /**
     * Třída UserModel spravuje operace související s uživateli v databázi,
     * včetně získávání, přidávání, aktualizace a mazání uživatelů.
     */
    class UserModel extends DatabaseModel
    {
        /**
         * Získá všechny uživatele včetně jejich rolí.
         *
         * @return array Pole uživatelů.
         */
        function getAllUsers(){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT users.*, roles.name AS role_name FROM users INNER JOIN roles ON users.role_id = roles.id_role ORDER BY users.role_id ASC");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        /**
         * Získá uživatele podle ID nebo uživatelského jména.
         *
         * @param mixed $value ID uživatele nebo uživatelské jméno.
         * @param bool $byUsername Indikátor, zda hledat podle uživatelského jména (výchozí: false).
         * @return array|null Uživatel nebo null, pokud nebyl nalezen.
         */
        function getUser($value, $byUsername = false) {
            $pdo = self::getConnection();

            $column = null;

            // Nastavení sloupce, podle kterého se bude hledat
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

        /**
         * Přidá nového uživatele do databáze.
         *
         * @param array $postData Data o uživateli (jméno, příjmení, uživatelské jméno, email, heslo).
         * @return int ID nově vytvořeného uživatele.
         */
        function addUser($postData){
            $pdo = self::getConnection();

            // Hash hesla pro uložení do databáze
            $hash_password = password_hash($postData["password"], PASSWORD_BCRYPT);

            $stmt = $pdo->prepare('
                INSERT INTO users (first_name, last_name, username, email, password, role_id) 
                VALUES (:firstname, :lastname, :username, :email, :password, :roleid)
            ');

            // Čistění vstupních dat
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

        /**
         * Zkontroluje, zda uživatel s daným uživatelským jménem nebo emailem již existuje.
         *
         * @param string $username Uživatelské jméno.
         * @param string $email Email.
         * @return int 0 - uživatel neexistuje, -1 - uživatel s tímto uživatelským jménem existuje, -2 - uživatel s tímto emailem existuje.
         */
        function checkUserExists($username, $email){
            $pdo = self::getConnection();

            // Kontrola existence uživatele podle uživatelského jména
            $stmt = $pdo->prepare("SELECT id_user FROM users WHERE username=:username");
            $username = trim(strip_tags($username));
            $stmt->execute(["username" => $username]);

            if($stmt->fetchAll(\PDO::FETCH_ASSOC)){
                return -1;
            }

            // Kontrola existence uživatele podle emailu
            $email = trim(strip_tags($email));
            $stmt = $pdo->prepare("SELECT id_user FROM users WHERE email=:email");
            $stmt->execute(["email" => $email]);

            if($stmt->fetchAll(\PDO::FETCH_ASSOC)){
                return -2;
            }

            return 0;
        }

        /**
         * Aktualizuje roli uživatele.
         *
         * @param int $id_user ID uživatele, jehož role se aktualizuje.
         * @param int $id_role Nová ID role.
         * @return array Pole obsahující true, pokud aktualizace proběhla úspěšně, nebo false a chybovou zprávu, pokud došlo k chybě.
         */
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

        /**
         * Aktualizuje stav banu uživatele.
         *
         * @param int $id_user ID uživatele.
         * @param bool $banStatus Nový stav zákazu (0 - ne, 1 - ano).
         * @return array Pole obsahující true, pokud aktualizace proběhla úspěšně, nebo false a chybovou zprávu, pokud došlo k chybě.
         */
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

        /**
         * Smaže uživatele podle ID.
         *
         * @param int $id_user ID uživatele, kterého je třeba smazat.
         * @return array Pole obsahující true, pokud smazání proběhlo úspěšně, nebo false a chybovou zprávu, pokud došlo k chybě.
         */
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

        /**
         * Získá všechny uživatele s rolí recenzenta, kteří nejsou zakázáni.
         *
         * @return array Pole recenzentů.
         */
        function getReviewers(){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT * FROM users WHERE users.role_id=:role_id AND users.banned = :ban");
            $success =$stmt->execute(["role_id" => ROLES["ROLE_REVIEWER"], "ban" => BAN["UNBANNED"]]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
    }