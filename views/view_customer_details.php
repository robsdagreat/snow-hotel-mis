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
$customerId = $_GET['id'] ?? null;
if (!$customerId) {
    die("Customer ID is required.");
}
$customers = new Customers();
$customerDetails = $customers->getCustomerById($customerId);
if (!$customerDetails) {
    die("Customer not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details</title>
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
            font-size: 16px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            color: white;
            transition: background-color 0.3s ease;
        }
        .checkout-button {
            background-color: orange;
        }
        .checkout-button:hover {
            background-color: #d32f2f;
        }
        .inactive-button {
            background-color: grey;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Customer Details</h2>
        <table>
            <tr>
                <th>Field</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Guest Name</td>
                <td><?= htmlspecialchars($customerDetails['guest_name']) ?></td>
            </tr>
            <tr>
                <td>Nationality</td>
                <td><?= htmlspecialchars($customerDetails['nationality']) ?></td>
            </tr>
            <tr>
                <td>ID/Passport</td>
                <td><?= htmlspecialchars($customerDetails['id_passport']) ?></td>
            </tr>
            <tr>
                <td>Arrival Date and Time</td>
                <td><?= htmlspecialchars($customerDetails['arrival_datetime']) ?></td>
            </tr>
            <tr>
                <td>Departure Date and Time</td>
                <td><?= htmlspecialchars($customerDetails['departure_datetime']) ?></td>
            </tr>
            <tr>
                <td>Room Number</td>
                <td><?= htmlspecialchars($customerDetails['room_number']) ?></td>
            </tr>
            <tr>
                <td>Room Rate</td>
                <td><?= htmlspecialchars($customerDetails['room_rate']) ?> RWF</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td><?= htmlspecialchars($customerDetails['discount']) ?>%</td>
            </tr>
            <tr>
                <td>Discounted Room Rate</td>
                <td><?= htmlspecialchars($customerDetails['discounted_room_rate']) ?> RWF</td>
            </tr>
            <tr>
                <td>Total Amount</td>
                <td><?= htmlspecialchars($customerDetails['total_amount']) ?> RWF</td>
            </tr>            
            <tr>
                <td>Number of Persons</td>
                <td><?= htmlspecialchars($customerDetails['num_persons']) ?></td>
            </tr>
            <tr>
                <td>Number of Children</td>
                <td><?= htmlspecialchars($customerDetails['num_children']) ?></td>
            </tr>
            <tr>
                <td>Mode of Payment</td>
                <td><?= htmlspecialchars($customerDetails['mode_of_payment']) ?></td>
            </tr>
            <tr>
                <td>Company/Travel Agency</td>
                <td><?= htmlspecialchars($customerDetails['company_agency']) ?></td>
            </tr>
            <tr>
                <td>Email Address</td>
                <td><?= htmlspecialchars($customerDetails['email_address']) ?></td>
            </tr>
            <tr>
                <td>Mobile Number</td>
                <td><?= htmlspecialchars($customerDetails['mobile_number']) ?></td>
            </tr>
        </table>
        <a href="view_customers.php" class="back-link">Back to Customers</a>
        <?php if ($customerDetails['status'] == 1): ?>
            <a href="checkout_customer.php?id=<?= htmlspecialchars($customerDetails['id']) ?>" class="button checkout-button">Check Out</a>
        <?php else: ?>
            <span class="button inactive-button">Checked Out</span>
        <?php endif; ?>
    </div>
</body>
</html>
