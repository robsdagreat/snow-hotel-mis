<?php
class Database {
    private $host = 'localhost';
    private $user = 'benax_snowhotel_root';
    private $password = 'Y@p,oBp;Y]$#';
    private $dbname = 'benax_snowhotel';
    private $charset = 'utf8mb4';
    private $pdo;
    public function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            $this->pdo = new PDO($dsn, $this->user, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    public function getConnection() {
        return $this->pdo;
    }
}
?>
