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
include_once '../includes/navbar.php';
require_once '../classes/Income.php';
$income = new Income();
$service_id = $_GET['service_id'] ?? null;
$data = $income->getIncomeByService($service_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Income Summary</title>
</head>
<body>
    <h2>Income Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $record): ?>
                <tr>
                    <td><?= htmlspecialchars($record['customer_name']) ?></td>
                    <td><?= htmlspecialchars($record['amount']) ?></td>
                    <td><?= htmlspecialchars($record['description']) ?></td>
                    <td><?= htmlspecialchars($record['transaction_date']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
