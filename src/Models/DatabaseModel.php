<?php
    namespace Web\Project\Models;

    class DatabaseModel{
        protected static $pdo;

        protected static function getConnection()
        {
            // Singleton pattern: Create a single database connection
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

        protected static function closeConnection(){
            self::$pdo = null;
        }
    }