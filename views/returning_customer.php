<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}

require_once '../classes/Customers.php';
require_once '../classes/Rooms.php';

// Initialize classes
$customers = new Customers();
$rooms = new Rooms();

// Fetch the returning customer ID from the URL
$returningCustomerId = $_GET['returning_customer'] ?? null;

if ($returningCustomerId) {
    $returningCustomer = $customers->getCustomerById($returningCustomerId);
    if (!$returningCustomer) {
        die("Customer not found. Please check the customer ID.");
    }
} else {
    die("No customer ID provided. Please select a returning customer.");
}

// Fetch available rooms
$availableRooms = $rooms->getAvailableRooms();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returning Customer</title>
    <link rel="stylesheet" href="../styles/navbar.css">
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
            font-weight: bold;
        }
        input, select, button {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            background-color: #5a5af1;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #4949c8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Returning Customer</h2>
        <form action="../controllers/customers_controller.php" method="POST">
            <input type="hidden" name="action" value="add_customer">

            <label for="guest_name">Guest Name:</label>
            <input type="text" id="guest_name" name="guest_name" value="<?= htmlspecialchars($returningCustomer['guest_name'] ?? '') ?>" required>

            <label for="nationality">Nationality:</label>
            <select id="nationality" name="nationality" required>
                <option value="<?= htmlspecialchars($returningCustomer['nationality'] ?? '') ?>" selected>
                    <?= htmlspecialchars($returningCustomer['nationality'] ?? 'Select nationality') ?>
                </option>
                <!-- Add nationality options -->
            </select>

            <label for="id_passport">ID/Passport:</label>
            <input type="text" id="id_passport" name="id_passport" value="<?= htmlspecialchars($returningCustomer['id_passport'] ?? '') ?>">

            <label for="arrival_datetime">Arrival Date and Time:</label>
            <input type="datetime-local" id="arrival_datetime" name="arrival_datetime" 
                   value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($returningCustomer['arrival_datetime'] ?? ''))) ?>" required>

            <label for="departure_datetime">Departure Date and Time:</label>
            <input type="datetime-local" id="departure_datetime" name="departure_datetime" 
                   value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($returningCustomer['departure_datetime'] ?? ''))) ?>" required>

            <label for="room_number">Room Number:</label>
            <select id="room_number" name="room_number" required onchange="updateRoomRate()">
                <option value="">-- Select Room --</option>
                <?php foreach ($availableRooms as $room): ?>
                    <option 
                        value="<?= htmlspecialchars($room['id']) ?>" 
                        data-room-rate="<?= htmlspecialchars($room['room_rate']) ?>"
                        <?= $room['id'] == $returningCustomer['room_number'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($room['room_number']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="room_rate">Room Rate:</label>
            <input type="text" id="room_rate" name="room_rate" value="<?= htmlspecialchars($returningCustomer['room_rate'] ?? '') ?>" readonly>

            <label for="discount">Discount %:</label>
            <input type="text" id="discount" name="discount" 
                   value="<?= htmlspecialchars($returningCustomer['discount'] ?? '0') ?>" onchange="applyDiscount()">

            <label for="discounted_room_rate">Discounted Room Rate:</label>
            <input type="text" id="discounted_room_rate" name="discounted_room_rate" readonly>

            <label for="num_persons">Number of Persons:</label>
            <input type="number" id="num_persons" name="num_persons" min="1" value="<?= htmlspecialchars($returningCustomer['num_persons'] ?? '1') ?>" required>

            <label for="num_children">Number of Children:</label>
            <input type="number" id="num_children" name="num_children" min="0" value="<?= htmlspecialchars($returningCustomer['num_children'] ?? '0') ?>">

            <label for="total_amount">Total Amount:</label>
            <input type="text" id="total_amount" name="total_amount" readonly>

            <label for="mode_of_payment">Mode of Payment:</label>
            <select id="mode_of_payment" name="mode_of_payment" required>
                <option value="<?= htmlspecialchars($returningCustomer['mode_of_payment'] ?? '') ?>" selected>
                    <?= htmlspecialchars($returningCustomer['mode_of_payment'] ?? 'Select payment mode') ?>
                </option>
                <option value="Cash">Cash</option>
                <option value="Momo">Momo</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>

            <label for="company_agency">Company/Travel Agency:</label>
            <input type="text" id="company_agency" name="company_agency" value="<?= htmlspecialchars($returningCustomer['company_agency'] ?? '') ?>">

            <label for="email_address">Email Address:</label>
            <input type="email" id="email_address" name="email_address" value="<?= htmlspecialchars($returningCustomer['email_address'] ?? '') ?>">

            <label for="mobile_number">Mobile Number:</label>
            <input type="text" id="mobile_number" name="mobile_number" value="<?= htmlspecialchars($returningCustomer['mobile_number'] ?? '') ?>">

            <button type="submit">Register</button>
        </form>
    </div>
     <script>
        function updateRoomRate() {
            const roomSelect = document.getElementById('room_number');
            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
            const roomRate = selectedOption.getAttribute('data-room-rate');
            document.getElementById('room_rate').value = roomRate || '';
            applyDiscount(); // Update discounted rate whenever room changes
            calculateTotalAmount(); // Recalculate total amount
        }
        
        function applyDiscount() {
            const roomRate = parseFloat(document.getElementById('room_rate').value) || 0;
            const discountInput = document.getElementById('discount');
            let discount = parseFloat(discountInput.value) || 0;
        
            // Validate the discount value
            if (discount < 0 || discount > 100) {
                alert("Discount must be between 0 and 100.");
                discountInput.value = 0; // Reset to 0 if invalid
                discount = 0;
            }
        
            // Calculate discounted rate
            const discountedRate = roomRate - (roomRate * discount / 100);
            document.getElementById('discounted_room_rate').value = discountedRate.toFixed(2);
        
            // Recalculate total amount
            calculateTotalAmount();
        }
        
        function calculateTotalAmount() {
            const arrival = document.getElementById('arrival_datetime').value;
            const departure = document.getElementById('departure_datetime').value;
            const discountedRate = parseFloat(document.getElementById('discounted_room_rate').value) || 0;
            if (arrival && departure) {
                const arrivalDate = new Date(arrival);
                const departureDate = new Date(departure);
                const differenceInTime = departureDate - arrivalDate;
                if (differenceInTime > 0) {
                    const differenceInDays = differenceInTime / (1000 * 3600 * 24); // Convert milliseconds to days
                    const totalAmount = differenceInDays * discountedRate;
                    document.getElementById('total_amount').value = totalAmount.toFixed(2);
                } else {
                    document.getElementById('total_amount').value = '0.00';
                }
            } else {
                document.getElementById('total_amount').value = '0.00';
            }
        }
    </script>
</body>
</html>