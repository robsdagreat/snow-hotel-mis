<?php
session_start();
$basePath = __DIR__;
$baseUrl = str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath($basePath . '/../'));
$loggedIn = isset($_SESSION['user_id']);
$username = $loggedIn ? $_SESSION['username'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= $baseUrl ?>/styles/navbar.css">
    <title>Snow Hotel</title>
</head>
<body>
    <div class="navbar">
        <a href="<?= $baseUrl ?>/index.php" class="navbar-brand">Snow Hotel</a>
        <div class="menu">
            <div class="dropdown">
                <button class="dropdown-btn">Customers</button>
                <div class="dropdown-content">
                    <a href="<?= $baseUrl ?>/views/add_customer.php">Add Customer</a>
                    <a href="<?= $baseUrl ?>/views/view_customers.php">View Customers</a>
                    <a href="<?= $baseUrl ?>/views/search_customer.php">Find Customers</a>
                    <a href="<?= $baseUrl ?>/views/view_customer_history.php">Customer History</a>
                </div>
            </div>
            
            <div class="dropdown">
                <button class="dropdown-btn">Stock</button>
                <div class="dropdown-content">
                    <a href="<?= $baseUrl ?>/views/manage_stock.php">Manage Stock</a>
                    <a href="<?= $baseUrl ?>/views/view_stock.php">View Stock</a>
                </div>
            </div>
            
            <div class="dropdown">
                <button class="dropdown-btn">Consumables</button>
                <div class="dropdown-content">
                    <a href="<?= $baseUrl ?>/views/add_consumable.php">Add Consumable</a>
                    <a href="<?= $baseUrl ?>/views/view_consumables.php">View Consumables</a>
                </div>
            </div>
            <div class="dropdown">
                <button class="dropdown-btn">Services</button>
                <div class="dropdown-content">
                    <a href="<?= $baseUrl ?>/views/add_service.php">Add Service</a>
                    <a href="<?= $baseUrl ?>/views/view_services.php">View Services</a>
                </div>
            </div>
            <!--
            <div class="dropdown">
                <button class="dropdown-btn">Income</button>
                <div class="dropdown-content">
                    <a href="<?= $baseUrl ?>/views/add_income.php">Add Income</a>
                    <a href="<?= $baseUrl ?>/views/view_income.php">View Income</a>
                </div>
            </div>
            -->
        </div>
        <?php if ($loggedIn): ?>
            <div class="user-info">
                <span>Welcome, <?= htmlspecialchars($username) ?></span>
                <a href="<?= $baseUrl ?>/controllers/logout.php" class="logout-btn">Logout</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
