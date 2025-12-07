<?php
class Database
{
    public $conn;

    public function __construct()
    {
        $host = '127.0.0.1';
        $user = 'root';
        $pass = '';
        $dbname = 'library_system';

        try {
            // First try to connect to the database
            $this->conn = new PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            // If database doesn't exist, try to create it
            if ($e->getCode() == 1049) {
                try {
                    $pdo = new PDO("mysql:host={$host}", $user, $pass);
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                    $this->conn = new PDO(
                        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                        $user,
                        $pass,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                        ]
                    );
                } catch (PDOException $e2) {
                    $this->conn = null;
                    error_log("Database connection failed: " . $e2->getMessage());
                }
            } else {
                $this->conn = null;
                error_log("Database connection failed: " . $e->getMessage());
            }
        }
    }

    public function getPdo()
    {
        return $this->conn;
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
