<?php
class Database
{
    private $user = "root";
    private $host = "localhost";
    private $pass = "";
    private $dbname = "library_system";
    public $conn;

    public function __construct()
    {
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->dbname}", $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //echo "good";
        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }

    public function getPdo()
    {
        return $this->conn;
    }
}
