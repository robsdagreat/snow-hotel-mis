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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'add_income') {
        $customer_id = $_POST['customer_id'] ?? null;
        $service_id = $_POST['service_id'] ?? null;
        $amount = $_POST['amount'] ?? 0;
        $description = $_POST['description'] ?? '';
        if (!empty($customer_id) && !empty($service_id) && $amount > 0) {
            $income->addIncome($customer_id, $service_id, $amount, $description);
            header("Location: ../views/view_income.php?success=1");
            exit();
        } else {
            header("Location: ../views/add_income.php?error=1");
            exit();
        }
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>
