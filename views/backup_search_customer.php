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
$term = $_GET['search_term'] ?? '';
$customers = new Customers();
$results = $term ? $customers->searchCustomer($term) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Customer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
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
        form {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 20px;
        }
        input[type="text"] {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            flex: 1;
        }
        button {
            padding: 10px 20px;
            background-color: #5a5af1;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #4949c8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
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
        .button {
            padding: 5px 10px;
            border-radius: 5px;
            background-color: #5a5af1;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #4949c8;
        }
        .no-results {
            text-align: center;
            font-size: 16px;
            color: #555;
            margin-top: 20px;
        }
        /* Add to the existing style block */
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .button {
            padding: 8px 15px;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            color: white;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .view-button {
            background-color: #4CAF50; /* Green for "View" */
        }
        .view-button:hover {
            background-color: #45a049;
        }
        .return-button {
            background-color: #ff9800; /* Orange for "Return Customer" */
        }
        .return-button:hover {
            background-color: #e68900;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Search Customer</h2>
        <form method="GET">
            <input type="text" name="search_term" value="<?= htmlspecialchars($term) ?>" placeholder="Search by name, room number, or ID">
            <button type="submit">Search</button>
        </form>
        <?php if ($term && $results): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Guest Name</th>
                        <th>Room Number</th>
                        <th>Arrival</th>
                        <th>Departure</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['guest_name']) ?></td>
                            <td><?= htmlspecialchars($row['room_number']) ?></td>
                            <td><?= htmlspecialchars($row['arrival_datetime']) ?></td>
                            <td><?= htmlspecialchars($row['departure_datetime']) ?></td>
                            <td class="action-buttons">
                                <a href="view_customer_details.php?id=<?= $row['id'] ?>" class="button view-button">View</a>
                                <a href="add_customer.php?returning_customer=<?= $row['id'] ?>" class="button return-button">Return Customer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($term): ?>
            <p class="no-results">No customers found matching "<?= htmlspecialchars($term) ?>".</p>
        <?php endif; ?>
    </div>
</body>
</html>
