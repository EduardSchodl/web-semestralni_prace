<?php
    namespace Web\Project\Models;

    /**
     * Třída DatabaseModel zajišťuje připojení k databázi pomocí PDO a implementuje Singleton pattern.
     * Zajišťující existenci pouze jednoho připojení.
     */
    class DatabaseModel{
        // Uložení instance PDO pro připojení k databázi
        protected static $pdo;

        /**
         * Získá připojení k databázi.
         *
         * @return \PDO|null Vrací instanci PDO, pokud je připojení úspěšné, jinak vrací null.
         */
        protected static function getConnection()
        {
            // Singleton pattern: Vytvoření jediného připojení k databázi
            if (!self::$pdo) {
                try {
                    self::$pdo = new \PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME, DB_USER, DB_PASS);
                    self::$pdo->exec("set names utf8");
                } catch (\PDOException $exception) {
                    die("Database connection failed: " . $exception->getMessage());
                }
            }

            return self::$pdo;
        }
    }