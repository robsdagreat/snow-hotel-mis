<?php
require_once 'Database.php';

class Services {
    private $pdo;
    
    public function __construct() {
        try {
            $db = new Database();
            $this->pdo = $db->getConnection();
            $this->createTable();
            $this->ensureIsActiveColumn();
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    public function createTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS services (
                id INT AUTO_INCREMENT PRIMARY KEY,
                service VARCHAR(255) NOT NULL UNIQUE,
                is_active TINYINT(1) DEFAULT 1,
                description TEXT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            die("Error creating table: " . $e->getMessage());
        }
    }
    
    private function ensureIsActiveColumn() {
        try {
            // Check if is_active column exists
            $checkSql = "SHOW COLUMNS FROM services LIKE 'is_active'";
            $result = $this->pdo->query($checkSql);
            
            if ($result->rowCount() == 0) {
                // Column doesn't exist, add it
                $alterSql = "ALTER TABLE services ADD COLUMN is_active TINYINT(1) DEFAULT 1";
                $this->pdo->exec($alterSql);
                
                // Set all existing records to active
                $updateSql = "UPDATE services SET is_active = 1";
                $this->pdo->exec($updateSql);
            }
        } catch (PDOException $e) {
            die("Error adding is_active column: " . $e->getMessage());
        }
    }
    
    public function addService($service, $description = '') {
        $sql = "INSERT INTO services (service, description) VALUES (:service, :description)";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([
                ':service' => $service,
                ':description' => $description
            ]);
            return true;
        } catch (PDOException $e) {
            // Handle duplicate entry gracefully
            if ($e->getCode() == 23000) {
                return false;
            }
            die("Error adding service: " . $e->getMessage());
        }
    }
    
    public function getAllServices() {
        $sql = "SELECT * FROM services WHERE is_active = 1 ORDER BY service ASC";
        
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
    
    public function updateService($id, $service, $description = '', $is_active = 1) {
        $sql = "UPDATE services SET 
                service = :service,
                description = :description,
                is_active = :is_active 
                WHERE id = :id";
                
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([
                ':id' => $id,
                ':service' => $service,
                ':description' => $description,
                ':is_active' => $is_active
            ]);
            return true;
        } catch (PDOException $e) {
            die("Error updating service: " . $e->getMessage());
        }
    }
    
    public function deleteService($id) {
        // Soft delete by setting is_active to 0
        $sql = "UPDATE services SET is_active = 0 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            die("Error deleting service: " . $e->getMessage());
        }
    }
    
    // Hard delete method if needed
    public function permanentlyDeleteService($id) {
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