<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}
?>
<?php
require_once '../classes/Customers.php';
$customerId = $_GET['id'] ?? null;
if ($customerId) {
    $customers = new Customers();
    $customers->checkoutCustomer($customerId);
    header("Location: view_customers.php?success=checkout");
    exit();
} else {
    header("Location: view_customers.php?error=missing_id");
    exit();
}
?>
