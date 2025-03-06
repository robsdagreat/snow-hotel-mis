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
$customer = new Customers();
$data = $customer->getAllCustomers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        .button {
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            text-align: center;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
    
        .details-button {
            background-color: #5a5af1;
            color: white;
            margin-right: 10px;
        }
        
        .new-button {
            background-color: orange;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            text-align: center;
            cursor: pointer;
        }        
    
        .checkout-button {
            background-color: orange;
            color: white;
        }
        
        .inactive-button {
            background-color: grey;
            color: white;
            pointer-events: none;
        }
        .button:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            opacity: 0.9;
        }
    
        .details-button:hover {
            background-color: #4949c8;
        }
    
        .checkout-button:hover {
            background-color: #d32f2f;
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
        <h2>All Customers</h2>
        <?php if (empty($data)): ?>
            <p>No customers found.</p>
        <?php else: ?>
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
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['guest_name']) ?></td>
                            <td><?= htmlspecialchars($row['room_number']) ?></td>
                            <td><?= htmlspecialchars($row['arrival_datetime']) ?></td>
                            <td><?= htmlspecialchars($row['departure_datetime']) ?></td>
                            <td>
                                <a href="view_customer_details.php?id=<?= $row['id'] ?>" class="button details-button">Details</a>
                                <?php if ($row['status'] == 1): ?>
                                    <a href="checkout_customer.php?id=<?= htmlspecialchars($row['id']) ?>" class="button checkout-button">Check Out</a>
                                <?php else: ?>
                                    <span class="button inactive-button">Checked Out</span>
                                <?php endif; ?>
                            </td>   
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <div>
            <a href="add_customer.php"><button class="new-button">+ New Customer</button></a>
        </div>
    </div>
</body>
</html>
