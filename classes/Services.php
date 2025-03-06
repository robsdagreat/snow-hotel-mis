<?php
require_once 'Database.php';
class Services {
    private $pdo;
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
        $this->createTable();
    }
    public function createTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS services (
                id INT AUTO_INCREMENT PRIMARY KEY,
                service VARCHAR(255) NOT NULL UNIQUE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            die("Error creating table: " . $e->getMessage());
        }
    }
    public function addService($service) {
        $sql = "INSERT INTO services (service) VALUES (:service)";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([':service' => $service]);
            return true;
        } catch (PDOException $e) {
            die("Error adding service: " . $e->getMessage());
        }
    }
    public function getAllServices() {
        $sql = "SELECT * FROM services";
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving services: " . $e->getMessage());
        }
    }
    public function getServiceById($id) {
        $sql = "SELECT * FROM services WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving service: " . $e->getMessage());
        }
    }
    public function updateService($id, $service) {
        $sql = "UPDATE services SET service = :service WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([':id' => $id, ':service' => $service]);
            return true;
        } catch (PDOException $e) {
            die("Error updating service: " . $e->getMessage());
        }
    }
    public function deleteService($id) {
        $sql = "DELETE FROM services WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            die("Error deleting service: " . $e->getMessage());
        }
    }
}
?>
