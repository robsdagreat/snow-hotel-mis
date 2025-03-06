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
require_once '../classes/Consumables.php';
$consumables = new Consumables();
$data = $consumables->getAllConsumables();
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Stock</h2>
        <form action="../controllers/stock_controller.php" method="POST">
            <label for="transaction_type">Transaction Type:</label>
            <select name="transaction_type" id="transaction_type" required>
                <option value="">-- Select --</option>
                <option value="purchase">Purchase (++)</option>
                <option value="consumption">Consumption (--)</option>
            </select> 
            
            <label for="consumable_id">Select Consumable:</label>
            <select name="consumable_id" id="consumable_id" required onchange="updateUnitPrice(); updateUnit()">
                <option value="">-- Select --</option>
                <?php foreach ($data as $row): ?>
                    <option 
                    value="<?= $row['id'] ?>" 
                    data-price="<?= htmlspecialchars($row['unit_price']) ?>"
                    data-unit="<?= htmlspecialchars($row['unit']) ?>"
                    >
                    <?= htmlspecialchars($row['item']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="quantity">Quantity (in <span id="item_unit"></span>)</label>
            <input type="number" id="quantity" name="quantity" required min="1" placeholder="Enter quantity">
            <label for="unit_price">Unit Price (per <span id="item_unit"></span>)</label>
            <input type="number" id="unit_price" name="unit_price" required min="1" readOnly="readOnly">
            
            <label>Description:</label>
            <textarea id="custom-textarea" placeholder="Describe why ..." name="description" rows="4"></textarea>
            <button type="submit">Update Stock</button>
        </form>
    </div>
    <script>
        function updateUnitPrice() {
            // Get the selected option
            const consumableSelect = document.getElementById('consumable_id');
            const selectedOption = consumableSelect.options[consumableSelect.selectedIndex];
            
            // Get the data-price attribute value
            const unitPrice = selectedOption.getAttribute('data-price');
            
            // Update the value of the unit price input
            document.getElementById('unit_price').value = unitPrice ? parseFloat(unitPrice).toFixed(2) : '';
        }
        
        function updateUnit() {
            // Get the selected option
            const consumableSelect = document.getElementById('consumable_id');
            const selectedOption = consumableSelect.options[consumableSelect.selectedIndex];
            
            // Get the data-unit attribute value
            const itemUnit = selectedOption.getAttribute('data-unit');
            
            // Update the text content of the item_unit span
            document.getElementById('item_unit').textContent = itemUnit || '';
        }       
    </script>
</body>
</html>
