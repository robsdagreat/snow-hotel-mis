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
require_once '../classes/Stock.php';
require_once '../classes/Consumables.php';
$stock = new Stock();
$consumables = new Consumables();
$consumable_id = $_GET['consumable_id'] ?? null;
if (!$consumable_id) {
    die("Consumable ID is required.");
}
// Fetch the consumable name
$consumable = $consumables->getConsumableById($consumable_id);
if (!$consumable) {
    die("Invalid Consumable ID.");
}
// Fetch stock history
$history = $stock->getStockHistory($consumable_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock History - <?= htmlspecialchars($consumable['item']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h2, h3 {
            text-align: center;
            color: #5a5af1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #5a5af1;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #5a5af1;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Stock History</h2>
        <h3>Consumable: <?= htmlspecialchars($consumable['item']) ?></h3>
        <?php if (empty($history)): ?>
            <p>No history found for this consumable.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Transaction Type</th>
                        <th>Quantity</th>
                        <th>Description</th>
                        <th>Transaction Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars(ucfirst($row['transaction_type'])) ?></td>
                            <td><?= htmlspecialchars($row['quantity']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= htmlspecialchars($row['transaction_date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="view_stock.php" class="back-link">Back to Stock</a>
    </div>
</body>
</html>
