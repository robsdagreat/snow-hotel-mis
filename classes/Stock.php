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
                quantity INT NOT NULL DEFAULT 0,
                unit_price INT NOT NULL,
                tatal_value INT NOT NULL,
                last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (consumable_id) REFERENCES consumables(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $sqlTransactions = "
            CREATE TABLE IF NOT EXISTS stock_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                consumable_id INT NOT NULL,
                transaction_type ENUM('purchase', 'consumption') NOT NULL,
                quantity INT NOT NULL,
                description text NOT NULL,
                unit_price INT NOT NULL,
                tatal_value INT NOT NULL,                
                transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
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
            //determine total price
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
                total_value  
            ) 
            VALUES (
                :consumable_id, 
                :transaction_type, 
                :quantity, 
                :description,
                :unit_price, 
                :total_value                  
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
    public function getStock(){
        $sql = "
            SELECT s.id, s.consumable_id, c.item, s.quantity, s.unit_price, s.total_value, s.last_updated
            FROM stock s
            JOIN consumables c ON s.consumable_id = c.id
        ";
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving stock: " . $e->getMessage());
        }
    }
    public function getStockHistory($consumable_id){
        $sql = "
            SELECT transaction_type, quantity, description, unit_price, total_value, transaction_date
            FROM stock_transactions
            WHERE consumable_id = :consumable_id
            ORDER BY transaction_date DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([':consumable_id' => $consumable_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving stock history: " . $e->getMessage());
        }
    }
}
?>
