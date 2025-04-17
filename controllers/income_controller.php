<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}
?>
<?php
require_once '../classes/Income.php';
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
$income = new Income();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_income') {
    $customer_id = $_POST['customer_id'] ?? null;
    $service_ids = $_POST['service_id'] ?? [];
    $amount = $_POST['amount'] ?? '';
    $description = $_POST['description'] ?? '';
    $income_date = $_POST['income_date'] ?? date('Y-m-d');
    $added_by = $_SESSION['user_id'] ?? null;
    $errors = [];
    if (!$customer_id) {
        $errors[] = 'Customer is required.';
    }
    if (empty($service_ids)) {
        $errors[] = 'At least one service/privilege must be selected.';
    }
    if ($amount === '' || !is_numeric($amount)) {
        $errors[] = 'Amount is required and must be numeric.';
    }
    if (empty($errors)) {
        foreach ((array)$service_ids as $sid) {
            $serviceDetails = $service->getServiceById($sid);
            $income_type = $serviceDetails ? $serviceDetails['service'] : 'Unknown';
            $income_data = [
                'customer_id' => $customer_id,
                'service_id' => $sid,
                'amount' => $amount,
                'description' => $description,
                'date' => $income_date,
                'type' => $income_type,
                'added_by' => $added_by
            ];
            $income->addIncomeData($income_data);
        }
        header('Location: ../views/view_income.php?success=1');
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>
