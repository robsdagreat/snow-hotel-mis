<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}
?>
<?php
include '../includes/navbar.php';
require_once '../classes/Customers.php';
$customers = new Customers();
$customerList = $customers->getAllCustomers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Income</title>
</head>
<body>
    <h2>Add Income</h2>
    <form action="../controllers/income_controller.php" method="POST">
        <input type="hidden" name="action" value="add_income">
        <label>Customer:</label>
        <select name="customer_id" required>
            <?php foreach ($customerList as $customer): ?>
                <option value="<?= htmlspecialchars($customer['id']) ?>">
                    <?= htmlspecialchars($customer['name']) ?> (<?= htmlspecialchars($customer['service_name']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <label>Amount:</label>
        <input type="number" name="amount" min="0.01" step="0.01" required>
        <label>Description:</label>
        <textarea name="description" rows="4"></textarea>
        <button type="submit">Add Income</button>
    </form>
</body>
</html>
