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
$consumables = new Consumables();
$services = new Services();

// Get all consumables for the dropdown
$all_consumables = $consumables->getAllConsumables();
$all_services = $services->getAllServices();

// Set breadcrumb variables
$breadcrumb_section = "Inventory";
$breadcrumb_section_url = "manage_stock.php";
$breadcrumb_page = "Record Consumption";

$today = date('F d, Y');
$current_time = date('h:i A');

// Check if form was submitted
$success_message = '';
$error_message = '';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $consumable_id = $_POST['consumable_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 0;
    $description = $_POST['description'] ?? '';
    $selling_price = $_POST['selling_price'] ?? 0;
    $service_id = $_POST['service_id'] ?? 0;
    
    try {
        $result = $stock->recordConsumption(
            $consumable_id,
            $quantity,
            $description,
            $selling_price,
            $service_id
        );
        
        if ($result) {
            $success_message = "Consumption recorded successfully.";
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage(); // Show the specific error message
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Consumption - Snow Hotel Management System</title>
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

        .menu-toggle {
            display: none;  /* Hide by default on large screens */
            background: none;
            border: none;
            color: var(--dark);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            margin-right: 1rem;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .top-bar-left {
            display: flex;
            align-items: center;
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

        .user-nav {
            display: flex;
            align-items: center;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
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

        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--gray-light);
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin-right: auto;  /* Push the title to the left */
        }

        .card-header .btn {
            margin-left: 10px;  /* Add spacing between buttons */
        }

        .card-header-buttons {
            display: flex;
            gap: 10px;  /* Consistent spacing between buttons */
            align-items: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            font-size: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
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
            background-color: var(--gray);
            color: white;
        }

        .alert {
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .alert-danger {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .current-stock-info {
            background-color: rgba(90, 90, 241, 0.1);
            padding: 15px;
            border-radius: var(--radius);
            margin-top: 10px;
            display: none;
        }

        .current-stock-info.visible {
            display: block;
        }

        .stock-quantity {
            font-weight: 600;
            color: var(--primary);
        }

        /* Media Queries */
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
                display: block;  /* Show only on mobile */
            }
            
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .user-nav {
                width: 100%;
                justify-content: flex-end;
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
                justify-content: flex-end;
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
                    <li><a href="import_data.php" class="nav-link"><i class="fas fa-upload"></i>Import Data</a></li>
<li><a href="view_rooms.php" class="nav-link"><i class="fas fa-bed"></i>Rooms</a></li>
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
                <div class="top-bar-left">
                    <button id="menuToggle" class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="page-title">
                        <h1>Record Consumption</h1>
                        <div class="breadcrumb">
                            <a href="../index.php">Dashboard</a>
                            <span>&gt;</span>
                            <a href="<?= $breadcrumb_section_url ?>"><?= $breadcrumb_section ?></a>
                            <span>&gt;</span>
                            <span><?= $breadcrumb_page ?></span>
                            <span class="time-display" style="margin-left: auto;"><?= $today ?> | <?= $current_time ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="user-nav">
                    <div class="user-profile">
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

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Record Stock Consumption</h2>
                    <div class="card-header-buttons">
                        <a href="view_consumption_history.php" class="btn btn-primary">
                            <i class="fas fa-history"></i> View Consumption History
                        </a>
                        <a href="view_stock.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Stock
                        </a>
                    </div>
                </div>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                <div class="form-group">
                    <label for="consumable_id" class="form-label">Select Item</label>
                    <select name="consumable_id" id="consumable_id" class="form-control" required>
                        <option value="">-- Select Item --</option>
                        <?php foreach ($all_consumables as $item): ?>
                            <option value="<?php echo $item['id']; ?>">
                                <?php echo htmlspecialchars($item['item']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="service_id" class="form-label">Service</label>
                    <select name="service_id" id="service_id" class="form-control" required>
                        <option value="">-- Select Service --</option>
                        <?php foreach ($all_services as $service): ?>
                            <option value="<?php echo $service['id']; ?>"><?php echo htmlspecialchars($service['service']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                        <label for="quantity" class="form-label">Quantity Consumed</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" step="0.01" min="0.01" required>
                    </div>

                <!-- Add this stock info container -->
                <div class="current-stock-info" id="current-stock-info">
                    <p>Current Stock: <span class="stock-quantity" id="stock-quantity">0</span></p>
                    <p>Cost Price: <span id="cost-price-display">0</span> per unit</p>
                </div>

                <div class="form-group">
                    <label for="selling_price" class="form-label">Your Selling Price (per unit)</label>
                    <input type="number" name="selling_price" id="selling_price" 
                        class="form-control" step="0.01" min="0.01" required>
                    <small class="text-muted">Must be greater than cost price</small>
                </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Record Consumption
                        </button>
                        <a href="view_stock.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            const consumableSelect = document.getElementById('consumable_id');
            const currentStockInfo = document.getElementById('current-stock-info');
            const stockQuantity = document.getElementById('stock-quantity');

            document.querySelector('form').addEventListener('submit', function(e) {
            const consumableId = document.getElementById('consumable_id').value;
            const quantity = parseFloat(document.getElementById('quantity').value);
            const sellingPrice = parseFloat(document.getElementById('selling_price').value);
            const costPrice = parseFloat(document.getElementById('cost-price-display').textContent);
            
            if (!consumableId) {
                alert('Please select an item');
                e.preventDefault();
                return;
            }
            
            if (quantity <= 0) {
                alert('Quantity must be greater than zero');
                e.preventDefault();
                return;
            }
            
            if (sellingPrice <= 0) {
                alert('Selling price must be greater than zero');
                e.preventDefault();
                return;
            }
            
            if (sellingPrice <= costPrice) {
                alert('Selling price must be greater than cost price');
                e.preventDefault();
                return;
            }
        });
            
        async function fetchCurrentStock(consumableId) {
    console.log("Fetching stock for item:", consumableId);
    if (!consumableId) {
        currentStockInfo.classList.remove('visible');
        return;
    }
    
    try {
        const response = await fetch(`../controllers/stock_controller.php?action=get_stock&consumable_id=${consumableId}`);
        const data = await response.json();
        console.log("Received data:", data);
        
        if (data.success) {
            console.log("Updating UI with:", {
                quantity: data.quantity,
                cost_price: data.cost_price,
                unit_price: data.unit_price
            });
            
            // Update display
            document.getElementById('stock-quantity').textContent = data.quantity;
            document.getElementById('cost-price-display').textContent = data.cost_price;
            
            // Set selling price input
            const sellingPriceInput = document.getElementById('selling_price');
            if (sellingPriceInput) {
                sellingPriceInput.min = (parseFloat(data.cost_price) + 0.01).toFixed(2);
                if (!sellingPriceInput.value) {
                    sellingPriceInput.value = data.unit_price; // Default to current selling price
                }
            }
            
            document.getElementById('current-stock-info').classList.add('visible');
        } else {
            console.error("Failed to fetch stock:", data.message);
            alert("Error: " + data.message);
        }
    } catch (error) {
        console.error("Fetch error:", error);
        alert("Network error fetching stock data");
    }
}
            
            consumableSelect.addEventListener('change', function() {
                const selectedId = this.value;
                fetchCurrentStock(selectedId);
            });
        });
    </script>
</body>
</html> 