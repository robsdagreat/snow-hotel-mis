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
// Setup for sorting
$consumables = new Consumables();
$sort_by = $_GET['sort_by'] ?? 'id';
$order = $_GET['order'] ?? 'asc';
// Allowed columns and orders for sorting
$allowed_columns = ['id', 'item', 'service_name', 'unit', 'unit_price'];
if (!in_array($sort_by, $allowed_columns)) $sort_by = 'id';
$allowed_orders = ['asc', 'desc'];
if (!in_array($order, $allowed_orders)) $order = 'asc';
// Fetch all sorted data
$data = $consumables->getSortedConsumables($sort_by, $order);
// Sort icons for headers
$id_sort_icon = $sort_by === 'id' ? ($order === 'asc' ? '▲' : '▼') : '';
$item_sort_icon = $sort_by === 'item' ? ($order === 'asc' ? '▲' : '▼') : '';
$service_sort_icon = $sort_by === 'service_name' ? ($order === 'asc' ? '▲' : '▼') : '';
$toggle_order = $order === 'asc' ? 'desc' : 'asc';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Consumables</title>
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
        h2 {
            text-align: center;
            color: #5a5af1;
            margin-bottom: 20px;
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
        th a {
            color: white;
            text-decoration: none;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .actions a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            color: white;
        }
        .edit {
            background-color: #4CAF50;
        }
        .delete {
            background-color: #f44336;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            text-decoration: none;
            color: white;
            background-color: #5a5af1;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .links a:hover {
            background-color: #4949c8;
        }
        @media screen and (max-width: 600px) {
            table {
                display: block;
                overflow-x: auto;
            }
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
    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this consumable?")) {
                window.location.href = '../controllers/consumables_controller.php?action=delete&id=' + id;
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>List of Consumables used at Snow Hotel</h2>
        <div class="note">
            <strong>Note:</strong> Hotel Management regularly sends a team to the market to get updated about the changes in Units and Unit Prices. Ensure to validate the latest details before submission.
        </div>        
        <?php if (empty($data)): ?>
            <p>No consumables found. <a href="add_consumable.php">Add one now</a>.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th><a href="?sort_by=item&order=<?= $toggle_order ?>">Item <?= $item_sort_icon ?></a></th>
                        <th><a href="?sort_by=service_name&order=<?= $toggle_order ?>">Service <?= $service_sort_icon ?></a></th>
                        <th>Unit</th>
                        <th>Unit Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $k=1;
                    foreach ($data as $row): 
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($k) ?></td>
                            <td><?= htmlspecialchars($row['item']) ?></td>
                            <td><?= htmlspecialchars($row['service_name'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($row['unit']) ?></td>
                            <td><?= htmlspecialchars($row['unit_price']) ?></td>
                            <td class="actions">
                                <a href="edit_consumable.php?id=<?= $row['id'] ?>" class="edit">Edit</a>
                                <a href="javascript:void(0)" onclick="confirmDelete(<?= $row['id'] ?>)" class="delete">Delete</a>
                            </td>
                        </tr>
                    <?php 
                    $k++;
                    endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <div class="links">
            <a href="add_consumable.php">Add New Consumable</a>
        </div>
    </div>
</body>
</html>
