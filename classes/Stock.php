<?php
require_once 'Database.php';
class Stock {
    private $pdo;
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
        $this->createTables();
    }
    private function createTables() {
        $sqlStock = "
            CREATE TABLE IF NOT EXISTS stock (
                id INT AUTO_INCREMENT PRIMARY KEY,
                consumable_id INT NOT NULL,
                quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
                unit_price DECIMAL(10,2) NOT NULL,
                total_value DECIMAL(10,2) NOT NULL,
                last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (consumable_id) REFERENCES consumables(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $sqlTransactions = "
            CREATE TABLE IF NOT EXISTS stock_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                consumable_id INT NOT NULL,
                transaction_type ENUM('purchase', 'consumption') NOT NULL,
                quantity DECIMAL(10,2) NOT NULL,
                description TEXT NOT NULL,
                unit_price DECIMAL(10,2) NOT NULL,
                total_value DECIMAL(10,2) NOT NULL,                
                transaction_datetime DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (consumable_id) REFERENCES consumables(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        try {
            $this->pdo->exec($sqlStock);
            $this->pdo->exec($sqlTransactions);
        } catch (PDOException $e) {
            die("Error creating tables: " . $e->getMessage());
        }
    }
    
    public function updateStock($consumable_id, $quantity, $description, $unit_price, $transaction_type) {
        try {
            // Determine total price
            $total_value = (float)$quantity * (float)$unit_price;
            
            // Determine adjusted quantity based on transaction type
            $adjusted_quantity = $transaction_type === 'purchase' ? $quantity : -$quantity;
            
            // Check if stock record exists
            $checkSql = "SELECT quantity FROM stock WHERE consumable_id = :consumable_id";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([':consumable_id' => $consumable_id]);
            $existingStock = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingStock) {
                // Update existing stock balance
                $newQuantity = $existingStock['quantity'] + $adjusted_quantity;
                
                $updateSql = "UPDATE stock SET 
                    quantity = :quantity, 
                    unit_price = :unit_price, 
                    total_value = :total_value, 
                    last_updated = NOW() 
                WHERE consumable_id = :consumable_id";
                $updateStmt = $this->pdo->prepare($updateSql);
                $updateStmt->execute([
                    ':quantity' => $newQuantity,
                    ':unit_price' => $unit_price,
                    ':total_value' => $total_value,
                    ':consumable_id' => $consumable_id,
                ]);
            } else {
                // Insert new stock record
                $insertSql = "INSERT INTO 
                stock (
                    consumable_id, 
                    quantity, 
                    unit_price, 
                    total_value,                
                    last_updated) 
                VALUES (
                    :consumable_id, 
                    :quantity, 
                    :unit_price, 
                    :total_value,                     
                    NOW())";
                $insertStmt = $this->pdo->prepare($insertSql);
                $insertStmt->execute([
                    ':consumable_id' => $consumable_id,
                    ':quantity' => $adjusted_quantity,
                    ':unit_price' => $unit_price,
                    ':total_value' => $total_value                    
                ]);
            }
            
            // Log the transaction in stock_transactions
            $logSql = "INSERT INTO 
                stock_transactions (
                consumable_id, 
                transaction_type, 
                quantity, 
                description,
                unit_price, 
                total_value,
                transaction_datetime
            ) 
            VALUES (
                :consumable_id, 
                :transaction_type, 
                :quantity, 
                :description,
                :unit_price, 
                :total_value,
                NOW()
            )";
            $logStmt = $this->pdo->prepare($logSql);
            $logStmt->execute([
                ':consumable_id' => $consumable_id,
                ':transaction_type' => $transaction_type,
                ':quantity' => $quantity,
                ':description' => $description,
                ':unit_price' => $unit_price,
                ':total_value' => $total_value
            ]);
            return true;
        } catch (PDOException $e) {
            die("Error updating stock: " . $e->getMessage());
        }
    }
    
    public function getStock($page = 1, $per_page = 15) {
        // Ensure page is a positive integer
        $page = max(1, (int)$page);
        
        try {
            // Calculate offset
            $offset = ($page - 1) * $per_page;
            
            // First, get total count of stock items
            $count_sql = "
                SELECT COUNT(*) as total_count
                FROM stock s
                JOIN consumables c ON s.consumable_id = c.id
            ";
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute();
            $total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total_count'];
            
            // Debug: Log total count
            error_log("Total stock items: " . $total_count);
            
            // Calculate total pages
            $total_pages = ceil($total_count / $per_page);
            
            // Retrieve paginated stock items
            $sql = "
                SELECT s.id, s.consumable_id, c.item, s.quantity, s.unit_price, s.total_value, s.last_updated
                FROM stock s
                JOIN consumables c ON s.consumable_id = c.id
                LIMIT :per_page OFFSET :offset";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug: Log items count
            error_log("Retrieved stock items: " . count($items));
            
            return [
                'items' => $items,
                'total_pages' => $total_pages,
                'current_page' => $page,
                'total_items' => $total_count
            ];
        } catch (PDOException $e) {
            // Log the full error details
            error_log("Error retrieving stock: " . $e->getMessage());
            error_log("SQL Error Details: " . print_r($e->errorInfo, true));
            
            return [
                'items' => [],
                'total_pages' => 0,
                'current_page' => $page,
                'total_items' => 0
            ];
        }
    }
       
    
    public function getStockHistory($consumable_id){
        $sql = "
            SELECT 
                transaction_type, 
                quantity, 
                description, 
                unit_price, 
                total_value, 
                transaction_datetime
            FROM stock_transactions
            WHERE consumable_id = :consumable_id
            ORDER BY transaction_datetime DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([':consumable_id' => $consumable_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving stock history: " . $e->getMessage());
        }
    }
    
    // New methods for the pages we created earlier
    public function getStockItemById($stock_id) {
        $sql = "
            SELECT s.id, s.consumable_id, c.item, s.quantity, s.unit_price, s.total_value
            FROM stock s
            JOIN consumables c ON s.consumable_id = c.id
            WHERE s.id = :stock_id
        ";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([':stock_id' => $stock_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving stock item: " . $e->getMessage());
        }
    }
    
    public function updateStockItem($stock_id, $quantity, $unit_price) {
        try {
            $total_value = $quantity * $unit_price;
            
            $sql = "UPDATE stock 
                    SET quantity = :quantity, 
                        unit_price = :unit_price, 
                        total_value = :total_value, 
                        last_updated = NOW() 
                    WHERE id = :stock_id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':quantity' => $quantity,
                ':unit_price' => $unit_price,
                ':total_value' => $total_value,
                ':stock_id' => $stock_id
            ]);
            
            return true;
        } catch (PDOException $e) {
            die("Error updating stock item: " . $e->getMessage());
        }
    }
    
    public function getAllStockItems() {
        $sql = "
            SELECT s.id, s.consumable_id, c.item, s.quantity, s.unit_price, s.total_value
            FROM stock s
            JOIN consumables c ON s.consumable_id = c.id
        ";
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving all stock items: " . $e->getMessage());
        }
    }
    
    public function restockItem($stock_id, $quantity, $unit_price) {
        try {
            // First, get the current stock details
            $stockItem = $this->getStockItemById($stock_id);
            
            if (!$stockItem) {
                throw new Exception("Stock item not found");
            }
            
            // Calculate new quantity and total value
            $newQuantity = $stockItem['quantity'] + $quantity;
            $total_value = $newQuantity * $unit_price;
            
            // Update stock
            $sql = "UPDATE stock 
                    SET quantity = :quantity, 
                        unit_price = :unit_price, 
                        total_value = :total_value, 
                        last_updated = NOW() 
                    WHERE id = :stock_id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':quantity' => $newQuantity,
                ':unit_price' => $unit_price,
                ':total_value' => $total_value,
                ':stock_id' => $stock_id
            ]);
            
            // Log the transaction
            $logSql = "INSERT INTO stock_transactions (
                consumable_id, 
                transaction_type, 
                quantity, 
                description,
                unit_price, 
                total_value
            ) VALUES (
                :consumable_id, 
                'purchase', 
                :quantity, 
                'Restocking',
                :unit_price, 
                :total_value
            )";
            
            $logStmt = $this->pdo->prepare($logSql);
            $logStmt->execute([
                ':consumable_id' => $stockItem['consumable_id'],
                ':quantity' => $quantity,
                ':unit_price' => $unit_price,
                ':total_value' => $quantity * $unit_price
            ]);
            
            return true;
        } catch (PDOException $e) {
            die("Error restocking item: " . $e->getMessage());
        }
    }
    
    public function getConsumableDetails($consumable_id) {
        $sql = "
            SELECT c.id, c.item, c.service, c.unit, c.unit_price, s.quantity, s.total_value
            FROM consumables c
            LEFT JOIN stock s ON c.id = s.consumable_id
            WHERE c.id = :consumable_id
        ";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([':consumable_id' => $consumable_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If no stock record exists, create a default one
            if (!$result) {
                // Get consumable details without stock
                $sql = "SELECT id, item, service, unit, unit_price FROM consumables WHERE id = :consumable_id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':consumable_id' => $consumable_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Add default stock values
                $result['quantity'] = 0;
                $result['total_value'] = 0;
            }
            
            return $result;
        } catch (PDOException $e) {
            die("Error retrieving consumable details: " . $e->getMessage());
        }
    }
    
    public function getPurchaseHistory($consumable_id = 'all', $page = 1, $per_page = 15) {
        try {
            // Ensure page is a positive integer
            $page = max(1, (int)$page);
            
            // Base query for counting total records
            $count_sql = "SELECT COUNT(*) as total_count FROM stock_transactions st 
                         JOIN consumables c ON st.consumable_id = c.id 
                         WHERE transaction_type = 'purchase'";
            
            // Base query for fetching records
            $sql = "SELECT 
                    st.id,
                    st.consumable_id,
                    c.item,
                    st.transaction_type,
                    st.quantity,
                    st.description,
                    st.unit_price,
                    st.total_value,
                    DATE_FORMAT(st.transaction_datetime, '%Y-%m-%d %H:%i:%s') as transaction_datetime
                   FROM stock_transactions st 
                   JOIN consumables c ON st.consumable_id = c.id 
                   WHERE transaction_type = 'purchase'";
            
            $params = [];
            
            // Add consumable filter if specified
            if ($consumable_id !== 'all') {
                $count_sql .= " AND st.consumable_id = :consumable_id";
                $sql .= " AND st.consumable_id = :consumable_id";
                $params[':consumable_id'] = $consumable_id;
            }
            
            // Add ordering
            $sql .= " ORDER BY st.transaction_datetime DESC";
            
            // Get total count
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($params);
            $total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total_count'];
            
            // Calculate pagination
            $total_pages = ceil($total_count / $per_page);
            $offset = ($page - 1) * $per_page;
            
            // Add pagination to main query
            $sql .= " LIMIT :per_page OFFSET :offset";
            $params[':per_page'] = $per_page;
            $params[':offset'] = $offset;
            
            // Execute main query
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                if ($key === ':per_page' || $key === ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
            
            return [
                'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total_pages' => $total_pages,
                'total_items' => $total_count
            ];
        } catch (PDOException $e) {
            error_log("Error retrieving purchase history: " . $e->getMessage());
            return [
                'items' => [],
                'total_pages' => 0,
                'total_items' => 0
            ];
        }
    }
    
    public function getConsumptionHistory($consumable_id = 'all', $page = 1, $per_page = 15) {
        try {
            // Ensure page is a positive integer
            $page = max(1, (int)$page);
            
            // Base query for counting total records
            $count_sql = "SELECT COUNT(*) as total_count FROM stock_transactions st 
                         JOIN consumables c ON st.consumable_id = c.id 
                         WHERE transaction_type = 'consumption'";
            
            // Base query for fetching records
            $sql = "SELECT 
                    st.id,
                    st.consumable_id,
                    c.item,
                    st.transaction_type,
                    st.quantity,
                    st.description,
                    st.unit_price,
                    st.total_value,
                    DATE_FORMAT(st.transaction_datetime, '%Y-%m-%d %H:%i:%s') as transaction_datetime
                   FROM stock_transactions st 
                   JOIN consumables c ON st.consumable_id = c.id 
                   WHERE transaction_type = 'consumption'";
            
            $params = [];
            
            // Add consumable filter if specified
            if ($consumable_id !== 'all') {
                $count_sql .= " AND st.consumable_id = :consumable_id";
                $sql .= " AND st.consumable_id = :consumable_id";
                $params[':consumable_id'] = $consumable_id;
            }
            
            // Add ordering
            $sql .= " ORDER BY st.transaction_datetime DESC";
            
            // Get total count
            $count_stmt = $this->pdo->prepare($count_sql);
            $count_stmt->execute($params);
            $total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total_count'];
            
            // Calculate pagination
            $total_pages = ceil($total_count / $per_page);
            $offset = ($page - 1) * $per_page;
            
            // Add pagination to main query
            $sql .= " LIMIT :per_page OFFSET :offset";
            $params[':per_page'] = $per_page;
            $params[':offset'] = $offset;
            
            // Execute main query
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                if ($key === ':per_page' || $key === ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
            
            return [
                'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total_pages' => $total_pages,
                'total_items' => $total_count
            ];
        } catch (PDOException $e) {
            error_log("Error retrieving consumption history: " . $e->getMessage());
            return [
                'items' => [],
                'total_pages' => 0,
                'total_items' => 0
            ];
        }
    }
    
    public function getStockByConsumableId($consumable_id) {
        $sql = "
            SELECT s.id, s.consumable_id, c.item, s.quantity, s.unit_price, s.total_value
            FROM stock s
            JOIN consumables c ON s.consumable_id = c.id
            WHERE s.consumable_id = :consumable_id
        ";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([':consumable_id' => $consumable_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving stock item: " . $e->getMessage());
        }
    }

    public function fixInvalidDates() {
        try {
            $sql = "UPDATE stock_transactions 
                    SET transaction_datetime = last_updated 
                    WHERE transaction_datetime = '0000-00-00 00:00:00' 
                    OR transaction_datetime IS NULL";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error fixing invalid dates: " . $e->getMessage());
            return false;
        }
    }

    public function fixInvalidServiceIds() {
        try {
            // Default service IDs based on description patterns
            $sql = "UPDATE stock_transactions 
                    SET service_id = 
                        CASE 
                            WHEN LOWER(description) LIKE '%breakfast%' THEN 4
                            WHEN LOWER(description) LIKE '%kitchen%' THEN 4
                            WHEN LOWER(description) LIKE '%restaurant%' OR LOWER(description) LIKE '%resto%' THEN 4
                            WHEN LOWER(description) LIKE '%room%' THEN 2
                            ELSE service_id 
                        END 
                    WHERE service_id = 0 
                    AND transaction_type = 'consumption'";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error fixing invalid service IDs: " . $e->getMessage());
            return false;
        }
    }
}
?>