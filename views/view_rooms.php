<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../classes/Rooms.php';
$rooms = new Rooms();
$all_rooms = $rooms->getAllRoomsWithStatus();

$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['room_id'], $_POST['room_rate'])) {
    $room_id = intval($_POST['room_id']);
    $room_rate = floatval($_POST['room_rate']);
    if ($rooms->updateRoomRate($room_id, $room_rate)) {
        $success = true;
        $all_rooms = $rooms->getAllRoomsWithStatus();
    } else {
        $error = 'Failed to update room rate.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - Snow Hotel Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #5a5af1;
            --primary-dark: #4747c2;
            --primary-light: #8080ff;
            --accent: #ff6b6b;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
            --dark: #333;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --radius: 8px;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6fc;
            color: var(--dark);
            /* line-height: 1.6; */
        }
        .layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }
        .sidebar {
            background: var(--primary);
            color: white;
            padding: 0.1rem;
            position: fixed;
            height: 100vh;
            width: 260px;
            z-index: 100;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            top: 0;
            left: 0;
            margin: 0;
        }
        .sidebar-header {
            display: flex;
            align-items: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: 1rem;
            margin-top: 2rem;
        }
        .logo i {
            font-size: 1.8rem;
        }
        .nav-section {
            margin-bottom: 1.5rem;
        }
        .nav-section-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.75rem;
            margin-left: 2.5rem;
            opacity: 0.8;
        }
        .nav-links {
            list-style: none;
        }
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.7rem 1rem;
            border-radius: var(--radius);
            margin-bottom: 0.25rem;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .nav-link i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
            width: 1.5rem;
            text-align: center;
        }
        .main-content {
            grid-column: 2;
            padding: 1.5rem 2rem;
            margin-left: 0;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .page-title h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }
        .breadcrumb {
            display: flex;
            align-items: center;
            color: var(--gray);
            font-size: 0.875rem;
        }
        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.2s;
        }
        .breadcrumb a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        .breadcrumb span {
            margin: 0 0.5rem;
        }
        .breadcrumb .time-display {
            margin-left: auto;
        }
        .room-table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
        .room-table th, .room-table td { border: 1px solid #eee; padding: 0.75rem; text-align: left; }
        .room-table th { background: #f8f9fa; }
        .room-status-available { color: #4caf50; font-weight: bold; }
        .room-status-occupied { color: #f44336; font-weight: bold; }
        .edit-form { display: flex; gap: 0.5rem; align-items: center; }
        .edit-form input[type='number'] { width: 100px; }
        .btn { padding: 0.5rem 1rem; border-radius: 6px; border: none; background: #5a5af1; color: #fff; cursor: pointer; }
        .btn:hover { background: #4747c2; }
        .alert-success { background: #d4edda; color: #155724; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
    </style>
</head>
<body>
<div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-snowflake"></i>
                <span>Snow Hotel</span>
            </div>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <ul class="nav-links">
                <li><a href="../index.php" class="nav-link"><i class="fas fa-th-large"></i>Dashboard</a></li>
                <li><a href="view_customers.php" class="nav-link"><i class="fas fa-users"></i>Customers</a></li>
                <li><a href="view_services.php" class="nav-link"><i class="fas fa-concierge-bell"></i>Services</a></li>
                <li><a href="view_consumables.php" class="nav-link"><i class="fas fa-shopping-basket"></i>Consumables</a></li>
            </ul>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <ul class="nav-links">
                <li><a href="view_stock.php" class="nav-link"><i class="fas fa-boxes"></i>Inventory</a></li>
                <li><a href="add_income.php" class="nav-link"><i class="fas fa-money-bill-wave"></i>Revenue</a></li>
                <li><a href="view_customer_history.php" class="nav-link"><i class="fas fa-history"></i>History</a></li>
                <li><a href="import_data.php" class="nav-link"><i class="fas fa-upload"></i>Import Data</a></li>
                <li><a href="view_rooms.php" class="nav-link active"><i class="fas fa-bed"></i>Rooms</a></li>
            </ul>
        </div>
        <div class="nav-section" style="margin-top: auto;">
            <ul class="nav-links">
                <li><a href="../controllers/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>
    </aside>
    <!-- Main Content -->
    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Manage Rooms</h1>
                <div class="breadcrumb">
                    <a href="../index.php">Dashboard</a>
                    <span>&gt;</span>
                    <span>Rooms</span>
                </div>
            </div>
        </div>
        <?php if ($success): ?>
        <div class="alert-success">Room rate updated successfully!</div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <table class="room-table">
            <thead>
                <tr>
                    <th>Room Number</th>
                    <th>Status</th>
                    <th>Current Rate</th>
                    <th>Edit Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_rooms as $room): ?>
                <tr>
                    <td><?= htmlspecialchars($room['room_number']) ?></td>
                    <td>
                        <?php if ($room['is_available']): ?>
                            <span class="room-status-available">Available</span>
                        <?php else: ?>
                            <span class="room-status-occupied">Occupied</span>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format($room['room_rate'], 2) ?></td>
                    <td>
                        <form class="edit-form" method="POST" action="">
                            <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                            <input type="number" step="0.01" name="room_rate" value="<?= $room['room_rate'] ?>" required>
                            <button type="submit" class="btn">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html> 