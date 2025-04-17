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
require_once '../classes/Income.php';
$customerId = $_GET['id'] ?? null;
if ($customerId) {
    $customers = new Customers();
    $customers->checkoutCustomer($customerId);
    $income = new Income();
    $customer_incomes = $income->getIncomesByCustomerId($customerId);
    header("Location: view_customers.php?success=checkout");
    exit();
} else {
    header("Location: view_customers.php?error=missing_id");
    exit();
}
?>
<!-- After checkout confirmation -->
<h3>Income Summary for this Customer</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Service</th>
            <th>Amount</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($customer_incomes as $inc): ?>
        <tr>
            <td><?= htmlspecialchars($inc['type']) ?></td>
            <td><?= number_format($inc['amount'], 2) ?></td>
            <td><?= htmlspecialchars($inc['transaction_date']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
