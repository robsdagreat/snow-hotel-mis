<?php
require_once 'Database.php';
class Income {
    private $pdo;
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }
    public function addIncome($customer_id, $service_id, $amount, $description) {
        $sql = "INSERT INTO income (customer_id, service_id, amount, description) VALUES (:customer_id, :service_id, :amount, :description)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':customer_id' => $customer_id,
            ':service_id' => $service_id,
            ':amount' => $amount,
            ':description' => $description,
        ]);
        return true;
    }
    public function getIncomeByService($service_id) {
        $sql = "
            SELECT customers.name AS customer_name, income.amount, income.description, income.transaction_date
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
            SELECT services.service AS service_name, income.amount, income.description, income.transaction_date
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
