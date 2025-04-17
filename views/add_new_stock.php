<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}

require_once '../classes/Stock.php';
require_once '../classes/Consumables.php';
require_once '../classes/Services.php';

$stock = new Stock();
$consumable = new Consumables();
$service = new Services();

// Get all services for the dropdown
$services = $service->getAllServices();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_consumable_name = isset($_POST['new_consumable_name']) ? trim($_POST['new_consumable_name']) : '';
    $quantity = isset($_POST['quantity']) ? floatval($_POST['quantity']) : 0;
    $unit_price = isset($_POST['unit_price']) ? floatval($_POST['unit_price']) : 0;
    $cost_price = isset($_POST['cost_price']) ? floatval($_POST['cost_price']) : 0;
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $unit = isset($_POST['unit']) ? trim($_POST['unit']) : '';
    
    // Validate inputs
    $errors = [];
    if (empty($new_consumable_name)) {
        $errors[] = "Please enter a consumable item name.";
    }
    if ($quantity < 0) {
        $errors[] = "Quantity cannot be negative.";
    }
    if ($unit_price < 0) {
        $errors[] = "Unit price cannot be negative.";
    }
    if ($cost_price < 0) {
        $errors[] = "Cost price cannot be negative.";
    }
    if ($service_id <= 0) {
        $errors[] = "Please select a service.";
    }
    if (empty($unit)) {
        $errors[] = "Please select a unit of measurement.";
    }
    
    if (empty($errors)) {
        try {
            // First, create a new consumable item with the selected service and unit
            $new_consumable_id = $consumable->addConsumable(
                $new_consumable_name,
                $service_id,
                $unit,
                $unit_price
            );
            
            // Then add the stock item using the new consumable ID
            $result = $stock->addNewStockItem($new_consumable_id, $quantity, $unit_price, $cost_price);
            
            if ($result) {
                $_SESSION['success_message'] = "New stock item added successfully.";
                header('Location: view_stock.php');
                exit;
            } else {
                $errors[] = "Failed to add stock item. Please try again.";
            }
        } catch (Exception $e) {
            $errors[] = "An error occurred: " . $e->getMessage();
        }
    }
}

// Set breadcrumb variables
$breadcrumb_section = "Inventory";
$breadcrumb_section_url = "view_stock.php";
$breadcrumb_page = "Add New Item";

$today = date('F d, Y');
$current_time = date('h:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Item - Snow Hotel Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Inherit all CSS styles from view_service.php */
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6fc;
            color: var(--dark);
            line-height: 1.6;
        }

        .layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            background: var(--primary);
            color: white;
            padding: 1.5rem;
            position: fixed;
            height: 100vh;
            width: 260px;
            z-index: 100;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
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
            opacity: 0.8;
        }

        .nav-links {
            list-style: none;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
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

        /* Main Content Styles */
        .main-content {
            grid-column: 2;
            padding: 1.5rem 2rem;
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

        .user-nav {
            display: flex;
            align-items: center;
        }

        .menu-toggle {
            background: none;
            border: none;
            color: var(--dark);
            font-size: 1.25rem;
            cursor: pointer;
            display: none;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            cursor: pointer;
        }

        .user-profile .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .user-profile .user-info {
            line-height: 1.3;
        }

        .user-profile .user-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .user-profile .user-role {
            font-size: 0.8rem;
            color: var(--gray);
        }

        /* Form Container */
        .form-container {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(90, 90, 241, 0.1);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
            padding-right: 2.5rem;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: var(--gray-light);
            color: var(--dark);
        }

        .btn-secondary:hover {
            background-color: #d1d7e0;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--radius);
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Footer Styles */
        footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.875rem;
            color: var(--gray);
            padding: 1.5rem 0;
            border-top: 1px solid var(--gray-light);
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 992px) {
            .layout {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                grid-column: 1;
            }
            
            .menu-toggle {
                display: block;
                width: 40px;
                height: 40px;
                background: white;
                border-radius: 50%;
                box-shadow: var(--shadow);
                color: var(--primary);
            }
        }
        
        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .user-nav {
                width: 100%;
                justify-content: space-between;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions .btn {
                width: 100%;
            }
        }
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
                    <li><a href="view_stock.php" class="nav-link active"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="add_income.php" class="nav-link"><i class="fas fa-money-bill-wave"></i>Revenue</a></li>
                    <li><a href="view_customer_history.php" class="nav-link"><i class="fas fa-history"></i>History</a></li>
                </ul>
            </div>
            
            <div class="nav-section" style="margin-top: auto;">
                <ul class="nav-links">
                    <li><a href="import_data.php" class="nav-link"><i class="fas fa-upload"></i>Import Data</a></li>
                    <li><a href="view_rooms.php" class="nav-link"><i class="fas fa-bed"></i>Rooms</a></li>
                    <li><a href="../controllers/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
                </ul>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <button id="menuToggle" class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="page-title">
                    <h1>Add New Stock Item</h1>
                    <div class="breadcrumb">
                        <a href="../index.php">Dashboard</a>
                        <span>&gt;</span>
                        <a href="<?= $breadcrumb_section_url ?>"><?= $breadcrumb_section ?></a>
                        <span>&gt;</span>
                        <span><?= $breadcrumb_page ?></span>
                        <span class="time-display" style="margin-left: auto;"><?= $today ?> | <?= $current_time ?></span>
                    </div>
                </div>
                
                <div class="user-nav">
                    <div class="user-profile" id="userProfileButton">
                        <div class="avatar">
                            <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="user-info">
                            <div class="user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></div>
                            <div class="user-role">Administrator</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Container -->
            <div class="form-container">
                <h2 class="form-title">Add New Inventory Item</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="new_consumable_name" class="form-label">Consumable Item Name</label>
                        <input type="text" id="new_consumable_name" name="new_consumable_name" class="form-control" 
                               value="<?= isset($_POST['new_consumable_name']) ? htmlspecialchars($_POST['new_consumable_name']) : '' ?>" 
                               required 
                               placeholder="Enter new consumable item name">
                    </div>
                    
                    <div class="form-group">
                        <label for="service_id" class="form-label">Service</label>
                        <select id="service_id" name="service_id" class="form-control" required>
                            <option value="">Select a service</option>
                            <?php foreach ($services as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= (isset($_POST['service_id']) && $_POST['service_id'] == $s['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['service']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="unit" class="form-label">Unit of Measurement</label>
                        <select id="unit" name="unit" class="form-control" required>
                            <option value="">-- Select Unit --</option>
                            <option value="Kilogram (kg)" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Kilogram (kg)') ? 'selected' : '' ?>>Kilogram (kg)</option>
                            <option value="Gram (g)" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Gram (g)') ? 'selected' : '' ?>>Gram (g)</option>
                            <option value="Milligram (mg)" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Milligram (mg)') ? 'selected' : '' ?>>Milligram (mg)</option>
                            <option value="Liter (L)" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Liter (L)') ? 'selected' : '' ?>>Liter (L)</option>
                            <option value="Milliliter (mL)" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Milliliter (mL)') ? 'selected' : '' ?>>Milliliter (mL)</option>
                            <option value="Cup" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Cup') ? 'selected' : '' ?>>Cup</option>
                            <option value="Piece" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Piece') ? 'selected' : '' ?>>Piece</option>
                            <option value="Unit" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Unit') ? 'selected' : '' ?>>Unit</option>
                            <option value="Pack" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Pack') ? 'selected' : '' ?>>Pack</option>
                            <option value="Box" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Box') ? 'selected' : '' ?>>Box</option>
                            <option value="Carton" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Carton') ? 'selected' : '' ?>>Carton</option>
                            <option value="Bundle" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Bundle') ? 'selected' : '' ?>>Bundle</option>
                            <option value="Dozen" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Dozen') ? 'selected' : '' ?>>Dozen</option>
                            <option value="Pair" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Pair') ? 'selected' : '' ?>>Pair</option>
                            <option value="Set" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Set') ? 'selected' : '' ?>>Set</option>
                            <option value="Bottle" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Bottle') ? 'selected' : '' ?>>Bottle</option>
                            <option value="Jar" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Jar') ? 'selected' : '' ?>>Jar</option>
                            <option value="Can" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Can') ? 'selected' : '' ?>>Can</option>
                            <option value="Tube" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Tube') ? 'selected' : '' ?>>Tube</option>
                            <option value="Tin" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Tin') ? 'selected' : '' ?>>Tin</option>
                            <option value="Bag" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Bag') ? 'selected' : '' ?>>Bag</option>
                            <option value="Packet" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Packet') ? 'selected' : '' ?>>Packet</option>
                            <option value="Sachet" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Sachet') ? 'selected' : '' ?>>Sachet</option>
                            <option value="Meter (m)" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Meter (m)') ? 'selected' : '' ?>>Meter (m)</option>
                            <option value="Centimeter (cm)" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Centimeter (cm)') ? 'selected' : '' ?>>Centimeter (cm)</option>
                            <option value="Roll" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Roll') ? 'selected' : '' ?>>Roll</option>
                            <option value="Tray" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Tray') ? 'selected' : '' ?>>Tray</option>
                            <option value="Slice" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Slice') ? 'selected' : '' ?>>Slice</option>
                            <option value="Case" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Case') ? 'selected' : '' ?>>Case</option>
                            <option value="Sack" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Sack') ? 'selected' : '' ?>>Sack</option>
                            <option value="Envelope" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Envelope') ? 'selected' : '' ?>>Envelope</option>
                            <option value="Cylinder" <?= (isset($_POST['unit']) && $_POST['unit'] == 'Cylinder') ? 'selected' : '' ?>>Cylinder</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity" class="form-label">Initial Quantity</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" 
                               value="<?= isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : '' ?>" 
                               step="0.01" min="0" required 
                               placeholder="Enter initial stock quantity">
                    </div>
                    
                    <div class="form-group">
                        <label for="unit_price" class="form-label">Unit Price</label>
                        <input type="number" id="unit_price" name="unit_price" class="form-control" 
                               value="<?= isset($_POST['unit_price']) ? htmlspecialchars($_POST['unit_price']) : '' ?>" 
                               step="0.01" min="0" required 
                               placeholder="Enter unit price">
                    </div>
                    
                    <div class="form-group">
                        <label for="cost_price" class="form-label">Cost Price</label>
                        <input type="number" id="cost_price" name="cost_price" class="form-control" 
                               value="<?= isset($_POST['cost_price']) ? htmlspecialchars($_POST['cost_price']) : '' ?>" 
                               step="0.01" min="0" required 
                               placeholder="Enter cost price">
                    </div>
                    
                    <div class="form-actions">
                        <a href="view_stock.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Add Stock Item</button>
                    </div>
                </form>
            </div>
            
            <footer>
                &copy; <?= date('Y') ?> Snow Hotel Management System. All rights reserved.
            </footer>
        </main>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    sidebar.classList.toggle('active');
                    
                    if (sidebar.classList.contains('active')) {
                        menuToggle.innerHTML = '<i class="fas fa-times"></i>';
                    } else {
                        menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                });
            }
            
            if (mainContent) {
                mainContent.addEventListener('click', function() {
                    if (window.innerWidth <= 992 && sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                        if (menuToggle) {
                            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                        }
                    }
                });
            }
            
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const consumableName = form.querySelector('#new_consumable_name');
                    const quantity = form.querySelector('#quantity');
                    const unitPrice = form.querySelector('#unit_price');
                    const costPrice = form.querySelector('#cost_price');
                    let isValid = true;
                    
                    if (consumableName.value.trim() === '') {
                        alert('Please enter a consumable item name.');
                        isValid = false;
                    }
                    
                    if (quantity.value < 0) {
                        alert('Quantity cannot be negative.');
                        isValid = false;
                    }
                    
                    if (unitPrice.value < 0) {
                        alert('Unit price cannot be negative.');
                        isValid = false;
                    }
                    
                    if (costPrice.value < 0) {
                        alert('Cost price cannot be negative.');
                        isValid = false;
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
</body>
</html> 