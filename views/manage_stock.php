<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}
include '../includes/navbar.php';
require_once '../classes/Consumables.php';
$consumables = new Consumables();
$data = $consumables->getAllConsumables(); // Make sure this includes 'stock' field
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stock</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #5a5af1;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            font-size: 14px;
            font-weight: bold;
        }
        select, input {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px;
            background-color: #5a5af1;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #4949c8;
        }
        #stock-warning {
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Stock</h2>
        <form action="../controllers/stock_controller.php" method="POST">
            <label for="transaction_type">Transaction Type:</label>
            <select name="transaction_type" id="transaction_type" required onchange="updateStockWarning();">
                <option value="">-- Select --</option>
                <option value="purchase">Purchase (++)</option>
                <option value="consumption">Consumption (--)</option>
            </select> 
            
            <label for="consumable_id">Select Consumable:</label>
            <select name="consumable_id" id="consumable_id" required onchange="updateUnitPrice(); updateUnit(); updateStockWarning();">
                <option value="">-- Select --</option>
                <?php foreach ($data as $row): ?>
                    <option 
                        value="<?= $row['id'] ?>" 
                        data-price="<?= htmlspecialchars($row['unit_price']) ?>"
                        data-unit="<?= htmlspecialchars($row['unit']) ?>"
                        data-stock="<?= htmlspecialchars($row['stock']) ?>"
                    >
                        <?= htmlspecialchars($row['item']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="quantity">Quantity (in <span id="item_unit"></span>)</label>
            <input type="number" id="quantity" name="quantity" required min="1" placeholder="Enter quantity" onkeyup="updateStockWarning();" onchange="updateStockWarning();">
            <span id="stock-warning"></span> <!-- Warning message -->
            <label for="unit_price">Unit Price (per <span id="item_unit1"></span>)</label>
            <input type="number" id="unit_price" name="unit_price" required min="1" readonly>
            <label>Description:</label>
            <textarea id="custom-textarea" placeholder="Describe why ..." name="description" rows="4"></textarea>
            
            <button type="submit">Update Stock</button>
        </form>
    </div>
    <script>
        function updateUnitPrice() {
            const consumableSelect = document.getElementById('consumable_id');
            const selectedOption = consumableSelect.options[consumableSelect.selectedIndex];
            const unitPrice = selectedOption.getAttribute('data-price');
            document.getElementById('unit_price').value = unitPrice ? parseFloat(unitPrice).toFixed(2) : '';
        }
        function updateUnit() {
            const consumableSelect = document.getElementById('consumable_id');
            const selectedOption = consumableSelect.options[consumableSelect.selectedIndex];
            const itemUnit = selectedOption.getAttribute('data-unit');
            document.getElementById('item_unit').textContent = itemUnit || '';
            document.getElementById('item_unit1').textContent = itemUnit || '';
        }
        function updateStockWarning() {
            const transactionType = document.getElementById('transaction_type').value;
            const consumableSelect = document.getElementById('consumable_id');
            const selectedOption = consumableSelect.options[consumableSelect.selectedIndex];
            const availableStock = parseInt(selectedOption.getAttribute('data-stock'), 10) || 0;
            const quantity = parseInt(document.getElementById('quantity').value, 10) || 0;
            const warningMessage = document.getElementById('stock-warning');
            const submitButton = document.querySelector('button[type="submit"]');
            // Apply stock validation only if transaction type is "consumption"
            if (transactionType === 'consumption' && quantity > availableStock) {
                warningMessage.textContent = `Not enough stock available. Only ${availableStock} left. Please purchase more stock.`;
                submitButton.disabled = true;
            } else {
                warningMessage.textContent = '';
                submitButton.disabled = false;
            }
        }
    </script>
</body>
</html>
