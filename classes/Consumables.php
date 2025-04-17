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
    
    public function addConsumable($item, $service, $unit, $unit_price, $cost_price = 0) {
        $sql = "INSERT INTO consumables 
        (item, service, unit, unit_price, cost_price) 
        VALUES (:item, :service, :unit, :unit_price, :cost_price)";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([
                ':item' => $item, 
                ':service' => $service,
                ':unit' => $unit,
                ':unit_price' => $unit_price,
                ':cost_price' => $cost_price
            ]);
            return $this->pdo->lastInsertId();
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

   // Get paginated consumables with sorting
    public function getPaginatedConsumables($sort_by = 'id', $order = 'asc', $limit = 15, $offset = 0) {
        // Validate sort_by to prevent SQL injection
        $allowed_columns = ['id', 'item', 'service_name', 'unit', 'unit_price'];
        if (!in_array($sort_by, $allowed_columns)) {
            $sort_by = 'id';
        }
        
        // Validate order to prevent SQL injection
        $allowed_orders = ['asc', 'desc'];
        if (!in_array(strtolower($order), $allowed_orders)) {
            $order = 'asc';
        }
        
        // Handle specific case for service_name which comes from services table
        $sort_column = ($sort_by === 'service_name') ? 's.service' : "c.{$sort_by}";
        
        $sql = "SELECT c.*, s.service AS service_name
                FROM consumables c
                LEFT JOIN services s ON c.service = s.id
                ORDER BY {$sort_column} {$order}
                LIMIT :limit OFFSET :offset";
                
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving paginated consumables: " . $e->getMessage());
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
    
    public function updateConsumable($id, $item, $service, $unit, $unit_price, $cost_price = 0) {
        $sql = "UPDATE consumables SET 
        item = :item, 
        service = :service, 
        unit = :unit, 
        unit_price = :unit_price, 
        cost_price = :cost_price
        WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([
                ':id' => $id,
                ':item' => $item,
                ':service' => $service,
                ':unit' => $unit,
                ':unit_price' => $unit_price,
                ':cost_price' => $cost_price
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
    
    // Original getSortedConsumables method (kept for backward compatibility)
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
    
    // Method for counting total consumables
    public function countConsumables() {
        $sql = "SELECT COUNT(*) AS total FROM consumables";
        try {
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            die("Error counting consumables: " . $e->getMessage());
        }
    }

    public function searchConsumables($params = []) {
        $sql = "SELECT c.*, s.service AS service_name
                FROM consumables c
                LEFT JOIN services s ON c.service = s.id
                WHERE 1=1";
        
        $conditions = [];
        $parameters = [];
        
        // Search by item name or service
        if (!empty($params['search_term'])) {
            $conditions[] = "(c.item LIKE :search_term OR s.service LIKE :search_term)";
            $parameters[':search_term'] = "%" . $params['search_term'] . "%";
        }
        
        // Filter by service
        if (!empty($params['service_id'])) {
            $conditions[] = "c.service = :service_id";
            $parameters[':service_id'] = $params['service_id'];
        }
        
        // Filter by price range
        if (!empty($params['price_min'])) {
            $conditions[] = "c.unit_price >= :price_min";
            $parameters[':price_min'] = $params['price_min'];
        }
        if (!empty($params['price_max'])) {
            $conditions[] = "c.unit_price <= :price_max";
            $parameters[':price_max'] = $params['price_max'];
        }
        
        // Add conditions to SQL
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
        
        // Add sorting
        $sort_by = $params['sort_by'] ?? 'id';
        $order = $params['order'] ?? 'asc';
        
        // Validate sort_by to prevent SQL injection
        $allowed_columns = ['id', 'item', 'service_name', 'unit', 'unit_price'];
        if (!in_array($sort_by, $allowed_columns)) {
            $sort_by = 'id';
        }
        
        // Validate order to prevent SQL injection
        $allowed_orders = ['asc', 'desc'];
        if (!in_array(strtolower($order), $allowed_orders)) {
            $order = 'asc';
        }
        
        $sort_column = ($sort_by === 'service_name') ? 's.service' : "c.{$sort_by}";
        $sql .= " ORDER BY {$sort_column} {$order}";
        
        // Add pagination
        if (isset($params['limit']) && isset($params['offset'])) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $parameters[':limit'] = (int)$params['limit'];
            $parameters[':offset'] = (int)$params['offset'];
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($parameters as $key => $value) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching consumables: " . $e->getMessage());
            return [];
        }
    }

    public function getSearchCount($params = []) {
        $sql = "SELECT COUNT(*) as total 
                FROM consumables c
                LEFT JOIN services s ON c.service = s.id
                WHERE 1=1";
        
        $conditions = [];
        $parameters = [];
        
        // Search by item name or service
        if (!empty($params['search_term'])) {
            $conditions[] = "(c.item LIKE :search_term OR s.service LIKE :search_term)";
            $parameters[':search_term'] = "%" . $params['search_term'] . "%";
        }
        
        // Filter by service
        if (!empty($params['service_id'])) {
            $conditions[] = "c.service = :service_id";
            $parameters[':service_id'] = $params['service_id'];
        }
        
        // Filter by price range
        if (!empty($params['price_min'])) {
            $conditions[] = "c.unit_price >= :price_min";
            $parameters[':price_min'] = $params['price_min'];
        }
        if (!empty($params['price_max'])) {
            $conditions[] = "c.unit_price <= :price_max";
            $parameters[':price_max'] = $params['price_max'];
        }
        
        // Add conditions to SQL
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($parameters as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Error counting search results: " . $e->getMessage());
            return 0;
        }
    }

    public function getAllServices() {
        $sql = "SELECT id, service FROM services ORDER BY service ASC";
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error retrieving services: " . $e->getMessage());
            return [];
        }
    }
}