<?php
require_once 'Database.php';
class Income {
    private $pdo;
    
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }
    
    
    public function addIncomeData($data) {
        try {
            // Create needed columns if they don't exist
            $this->ensureIncomeTableHasRequiredColumns();
            
            // Use the customer ID provided in the data, or use default if not provided
            $customer_id = !empty($data['customer_id']) ? $data['customer_id'] : 1;
            
            // Use the service ID from the data if available, otherwise use default
            $service_id = !empty($data['service_id']) ? $data['service_id'] : 1;
            
            // First check if the default IDs exist
            if (!$this->checkIdExists('customers', $customer_id)) {
                // If using the default customer ID
                if ($customer_id == 1) {
                    // Create a system customer if it doesn't exist
                    $this->createSystemCustomer($customer_id);
                } else {
                    // For non-default customer IDs that don't exist
                    throw new Exception("Customer ID does not exist");
                }
            }
            
            // Rest of the method remains the same...
            
            $sql = "INSERT INTO income (customer_id, service_id, amount, description, transaction_date, income_type, added_by, created_at) 
                    VALUES (:customer_id, :service_id, :amount, :description, :transaction_date, :income_type, :added_by, :created_at)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':customer_id' => $customer_id,
                ':service_id' => $service_id,
                ':amount' => $data['amount'],
                ':description' => $data['description'],
                ':transaction_date' => $data['date'],
                ':income_type' => $data['type'] ?? null,
                ':added_by' => $data['added_by'] ?? null,
                ':created_at' => date('Y-m-d H:i:s')
            ]);
            
            return true;
        } catch (PDOException $e) {
            // Log the error
            error_log("Income addition error: " . $e->getMessage());
            throw $e; // Rethrow to be handled by the caller
        }
    }
    
    // Check if an ID exists in a table
    private function checkIdExists($table, $id) {
        $sql = "SELECT COUNT(*) FROM $table WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }
    
    // Create a system customer for general income records
    private function createSystemCustomer($id) {
        try {
            $sql = "INSERT INTO customers (id, guest_name, nationality, id_passport, email_address, mobile_number) 
                  VALUES (:id, 'System Account', 'System', 'SYSTEM', 'system@hotel.local', '0000000000')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            // Log but continue since this is just a helper method
            error_log("Error creating system customer: " . $e->getMessage());
        }
    }
    
    // Create a general service for miscellaneous income
    private function createGeneralService($id) {
        try {
            $sql = "INSERT INTO services (id, service, description, price) 
                  VALUES (:id, 'General', 'General service for miscellaneous income', 0)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            // Log but continue
            error_log("Error creating general service: " . $e->getMessage());
        }
    }
    
    // Combined method to ensure all required columns exist
    private function ensureIncomeTableHasRequiredColumns() {
        try {
            // Check for income_date column
            $this->addColumnIfNotExists('income', 'income_date', 'DATE');
            
            // Check for income_type column
            $this->addColumnIfNotExists('income', 'income_type', 'VARCHAR(50)');
            
            // Check for added_by column
            $this->addColumnIfNotExists('income', 'added_by', 'INT');
        } catch (PDOException $e) {
            error_log("Error checking table structure: " . $e->getMessage());
        }
    }
    
    // Helper method to add a column if it doesn't exist
    private function addColumnIfNotExists($table, $column, $definition) {
        $sql = "SHOW COLUMNS FROM $table LIKE '$column'";
        $result = $this->pdo->query($sql)->fetch();
        
        if (!$result) {
            $this->pdo->exec("ALTER TABLE $table ADD COLUMN $column $definition");
        }
    }
    
    public function getIncomeByService($service_id) {
        $sql = "
            SELECT customers.guest_name AS customer_name, income.amount, income.description, income.created_at as transaction_date
            FROM income
            JOIN customers ON income.customer_id = customers.id
            WHERE income.service_id = :service_id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':service_id' => $service_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTransactionsByCustomer($customer_id) {
        $sql = "
            SELECT services.service AS service_name, income.amount, income.description, income.created_at as transaction_date
            FROM income
            JOIN services ON income.service_id = services.id
            WHERE income.customer_id = :customer_id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':customer_id' => $customer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>