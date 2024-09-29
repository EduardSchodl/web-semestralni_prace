<?php
    namespace Web\Project\Models;

    if(!isset($_SESSION))
    {
        session_start();
    }

    class RolesModel extends DatabaseModel
    {
        function getRoles(){
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("SELECT name FROM roles WHERE roles.id_role > :loggedUserId");
            $stmt->execute(["loggedUserId" => $_SESSION["user"]["role_id"]]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
    }