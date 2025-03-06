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
include '../includes/navbar.php';
require_once '../classes/Customers.php';
require_once '../classes/Income.php';
$customer_id = $_GET['customer_id'] ?? null;
if (!$customer_id) {
    die("Customer ID is required.");
}
$customers = new Customers();
$income = new Income();
$customerDetails = $customers->getCustomerById($customer_id);
$transactions = $income->getTransactionsByCustomer($customer_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Customer History</title>
</head>
<body>
    <h2>Customer: <?= htmlspecialchars($customerDetails['name']) ?></h2>
    <p>Service: <?= htmlspecialchars($customerDetails['service_name']) ?></p>
    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?= htmlspecialchars($transaction['service_name']) ?></td>
                    <td><?= htmlspecialchars($transaction['amount']) ?></td>
                    <td><?= htmlspecialchars($transaction['description']) ?></td>
                    <td><?= htmlspecialchars($transaction['transaction_date']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
