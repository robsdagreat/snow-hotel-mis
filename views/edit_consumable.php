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
$id = $_GET['id'] ?? null;
if (!$id) {
    die('Invalid consumable ID.');
}
$consumable = $consumables->getConsumableById($id);
if (!$consumable) {
    die('Consumable not found.');
}
// Fetch services from the database
try {
    $db = new Database();
    $pdo = $db->getConnection();
    $stmt = $pdo->query("SELECT id, service FROM services ORDER BY service ASC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching services: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Consumable</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }
        .container {
            max-width: 500px;
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
            margin-bottom: 5px;
        }
        input, select, button {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
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
        .note {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
            background: #fffbe6;
            border: 1px solid #ffe08a;
            padding: 10px;
            border-radius: 5px;
        }           
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Consumable</h2>
        <div class="note">
            <strong>Note:</strong> Hotel Management regularly sends a team to the market to get updated about the changes in Units and Unit Prices. Ensure to validate the latest details before submission.
        </div>         
        <form action="../controllers/consumables_controller.php" method="POST">
            <input type="hidden" name="action" value="update_consumable">
            <input type="hidden" name="id" value="<?= htmlspecialchars($consumable['id']) ?>">
            <label for="item">Item:</label>
            <input type="text" id="item" name="item" value="<?= htmlspecialchars($consumable['item']) ?>" required>
            <label for="service_id">Service:</label>
            <select id="service_id" name="service_id" required>
                <option value="">-- Select Service --</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= htmlspecialchars($service['id']) ?>">
                        <?= htmlspecialchars($service['service']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="unit">Unit:</label>
            <select name="unit" id="unit" required>
                <option value="<?= htmlspecialchars($consumable['unit']) ?>"><?= htmlspecialchars($consumable['unit']) ?></option>
                <option value="Kilogram (kg)">Kilogram (kg)</option>
                <option value="Gram (g)">Gram (g)</option>
                <option value="Milligram (mg)">Milligram (mg)</option>
                <option value="Liter (L)">Liter (L)</option>
                <option value="Milliliter (mL)">Milliliter (mL)</option>
                <option value="Cup">Cup</option>
                <option value="Piece">Piece</option>
                <option value="Unit">Unit</option>
                <option value="Pack">Pack</option>
                <option value="Box">Box</option>
                <option value="Carton">Carton</option>
                <option value="Bundle">Bundle</option>
                <option value="Dozen">Dozen</option>
                <option value="Pair">Pair</option>
                <option value="Set">Set</option>
                <option value="Bottle">Bottle</option>
                <option value="Jar">Jar</option>
                <option value="Can">Can</option>
                <option value="Tube">Tube</option>
                <option value="Tin">Tin</option>
                <option value="Bag">Bag</option>
                <option value="Packet">Packet</option>
                <option value="Sachet">Sachet</option>
                <option value="Meter (m)">Meter (m)</option>
                <option value="Centimeter (cm)">Centimeter (cm)</option>
                <option value="Roll">Roll</option>
                <option value="Tray">Tray</option>
                <option value="Slice">Slice</option>
                <option value="Case">Case</option>
                <option value="Sack">Sack</option>
                <option value="Envelope">Envelope</option>
                <option value="Cylinder">Cylinder</option>
            </select>         
            
            <label for="unit_price">Unit Price:</label>
            <input type="text" id="unit_price" name="unit_price" value="<?= htmlspecialchars($consumable['unit_price']) ?>" required>             
            <button type="submit">Update Consumable</button>
        </form>
    </div>
</body>
</html>
