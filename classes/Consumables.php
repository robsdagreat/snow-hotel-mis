<?php
require_once 'Database.php';
class Consumables {
    private $pdo;
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
        $this->createTable(); // Ensure the table is created
    }
    public function createTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS consumables (
                id INT AUTO_INCREMENT PRIMARY KEY,
                item VARCHAR(255) NOT NULL,
                service VARCHAR(255) NOT NULL,
                unit VARCHAR(50) NOT NULL,
                unit_price int(11) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            die("Error creating table: " . $e->getMessage());
        }
    }
    public function addConsumable($item, $service, $unit, $unit_price) {
        $sql = "INSERT INTO consumables 
        (item, service, unit, unit_price) 
        VALUES (:item, :service, :unit, :unit_price)";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute(
                [
                    ':item' => $item, 
                    ':service' => $service,
                    ':unit' => $unit,
                    ':unit_price' => $unit_price
                ]
                );
            return true;
        } catch (PDOException $e) {
            die("Error inserting data: " . $e->getMessage());
        }
    }
    public function getAllConsumables() {
        $sql = "SELECT * FROM consumables";
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving data: " . $e->getMessage());
        }
    }
    public function getConsumableById($id) {
        $sql = "SELECT * FROM consumables WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving consumable: " . $e->getMessage());
        }
    }
    public function updateConsumable($id, $item, $service, $unit, $unit_price) {
        $sql = "UPDATE consumables SET 
        item = :item, 
        service = :service, 
        unit = :unit, 
        unit_price = :unit_price 
        WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([
                ':id' => $id,
                ':item' => $item,
                ':service' => $service,
                ':unit' => $unit,
                ':unit_price' => $unit_price                
            ]);
            return true;
        } catch (PDOException $e) {
            die("Error updating consumable: " . $e->getMessage());
        }
    }
    public function deleteConsumable($id) {
        $sql = "DELETE FROM consumables WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            die("Error deleting consumable: " . $e->getMessage());
        }
    }
    
    public function getSortedConsumables($sort_by, $order) {
        // Validate and sanitize input
        $allowed_columns = ['id', 'item', 'service_name', 'unit', 'unit_price'];
        if (!in_array($sort_by, $allowed_columns)) {
            $sort_by = 'id';
        }
        $allowed_orders = ['asc', 'desc'];
        if (!in_array($order, $allowed_orders)) {
            $order = 'asc';
        }
    
        // Safely escape the column name
        $sort_by = "`$sort_by`";
    
        $sql = "
            SELECT c.id, c.item, c.unit, c.unit_price, s.service AS service_name
            FROM consumables c
            LEFT JOIN services s ON c.service = s.id
            ORDER BY $sort_by $order
        ";
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving sorted data: " . $e->getMessage());
        }
    }
    
    public function getConsumableCount() {
        $sql = "SELECT COUNT(*) AS total FROM consumables";
        try {
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            die("Error counting consumables: " . $e->getMessage());
        }
    }
}
