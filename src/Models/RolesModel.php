<?php
    namespace Web\Project\Models;

    // Spustí relaci, pokud není nastavena
    if(!isset($_SESSION))
    {
        session_start();
    }

    /**
     * Třída RolesModel spravuje operace související s rolemi uživatelů v databázi.
     * Včetně získávání dostupných rolí z databáze.
     */
    class RolesModel extends DatabaseModel
    {
        /**
         * Získá všechny role, které jsou nižší než role aktuálně přihlášeného uživatele.
         *
         * @return array Pole rolí.
         */
        function getRoles(){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT * FROM roles WHERE roles.id_role > :loggedUserId");
            $stmt->execute(["loggedUserId" => $_SESSION["user"]["role_id"]]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
    }