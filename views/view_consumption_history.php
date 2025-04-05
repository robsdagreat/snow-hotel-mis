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

$stock = new Stock();
$consumables = new Consumables();

// Get all consumables for filtering
$all_consumables = $consumables->getAllConsumables();

// Set default filter values
$selected_consumable = $_GET['consumable_id'] ?? 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;

// Get consumption history with pagination
$history_data = $stock->getConsumptionHistory($selected_consumable, $page, $per_page);
$consumption_history = $history_data['items'];
$total_pages = $history_data['total_pages'];
$total_items = $history_data['total_items'];

// Set breadcrumb variables
$breadcrumb_section = "Inventory";
$breadcrumb_section_url = "manage_stock.php";
$breadcrumb_page = "Consumption History";

$today = date('F d, Y');
$current_time = date('h:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consumption History - Snow Hotel Management System</title>
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
            display: none;
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

        .avatar {
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

        .user-info {
            line-height: 1.3;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .user-role {
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
            margin-right: auto;
        }

        .card-header-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
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

        .filter-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .filter-label {
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            padding: 8px 12px;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            font-size: 14px;
            min-width: 200px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        .table-responsive {
            overflow-x: auto;
            margin-bottom: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid var(--gray-light);
        }

        tbody tr:hover {
            background-color: var(--light);
        }

        .no-data-message {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
            font-style: italic;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .pagination-info {
            text-align: center;
            margin-top: 0.5rem;
            color: var(--gray);
            font-size: 0.875rem;
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
                display: block;
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

            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }

            .form-control {
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
                    <h1>Consumption History</h1>
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
                    <h2 class="card-title">Stock Consumption History</h2>
                    <a href="record_consumption.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Record New Consumption
                    </a>
                </div>

                <div class="filter-section">
                    <span class="filter-label">Filter by Item:</span>
                    <select id="consumableFilter" class="form-control" style="width: auto;" onchange="this.form.submit()">
                        <option value="all">All Items</option>
                        <?php foreach ($all_consumables as $item): ?>
                            <option value="<?= $item['id'] ?>" <?= $selected_consumable == $item['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($item['item']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (empty($consumption_history)): ?>
                    <div class="no-data-message">
                        <p>No consumption history available.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Value</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($consumption_history as $record): ?>
                                    <tr>
                                        <td><?= date('Y-m-d H:i', strtotime($record['transaction_datetime'])) ?></td>
                                        <td><?= htmlspecialchars($record['item']) ?></td>
                                        <td><?= htmlspecialchars($record['quantity']) ?></td>
                                        <td><?= number_format($record['unit_price'], 2) ?></td>
                                        <td><?= number_format($record['total_value'], 2) ?></td>
                                        <td><?= htmlspecialchars($record['description']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&consumable_id=<?= $selected_consumable ?>" class="btn btn-sm btn-primary">Previous</a>
                        <?php endif; ?>

                        <?php
                        $max_pages_to_show = 5;
                        $start_page = max(1, min($page - floor($max_pages_to_show / 2), $total_pages - $max_pages_to_show + 1));
                        $end_page = min($start_page + $max_pages_to_show - 1, $total_pages);

                        if ($start_page > 1) {
                            echo '<a href="?page=1&consumable_id=' . $selected_consumable . '" class="btn btn-sm btn-primary">1</a>';
                            if ($start_page > 2) {
                                echo '<span class="btn btn-sm">...</span>';
                            }
                        }

                        for ($i = $start_page; $i <= $end_page; $i++):
                            $active_class = ($i == $page) ? 'btn-success' : 'btn-primary';
                        ?>
                            <a href="?page=<?= $i ?>&consumable_id=<?= $selected_consumable ?>" class="btn btn-sm <?= $active_class ?>"><?= $i ?></a>
                        <?php endfor;

                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<span class="btn btn-sm">...</span>';
                            }
                            echo '<a href="?page=' . $total_pages . '&consumable_id=' . $selected_consumable . '" class="btn btn-sm btn-primary">' . $total_pages . '</a>';
                        }

                        if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>&consumable_id=<?= $selected_consumable ?>" class="btn btn-sm btn-primary">Next</a>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination Info -->
                    <div class="pagination-info">
                        Showing <?= (($page - 1) * $per_page) + 1 ?> to 
                        <?= min($page * $per_page, $total_items) ?> 
                        of <?= $total_items ?> records
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
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

            // Handle consumable filter change
            const consumableFilter = document.getElementById('consumableFilter');
            if (consumableFilter) {
                consumableFilter.addEventListener('change', function() {
                    window.location.href = '?consumable_id=' + this.value;
                });
            }
        });
    </script>
</body>
</html> 