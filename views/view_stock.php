<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}

require_once '../classes/Stock.php';
$stock = new Stock();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 15;

$stock_data = $stock->searchStock($search, $filter, $current_page, $items_per_page);
$data = $stock_data['items'] ?? [];
$total_pages = $stock_data['total_pages'] ?? 0;
$total_items = $stock_data['total_items'] ?? 0;

// Set breadcrumb variables
$breadcrumb_section = "Inventory";
$breadcrumb_section_url = "view_stock.php";
$breadcrumb_page = "Current Stock";

$today = date('F d, Y');
$current_time = date('h:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Stock - Snow Hotel Management System</title>
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

        /* Table Container */
        .table-container {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .table-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .table-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        thead th {
            background-color: var(--primary);
            color: white;
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
        }

        tbody tr:nth-child(even) {
            background-color: var(--light);
        }

        tbody tr:hover {
            background-color: var(--gray-light);
        }

        td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: #3d8b40;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #d32f2f;
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background-color: #e68a00;
        }

        .no-data-message {
            padding: 1.5rem;
            text-align: center;
            color: var(--gray);
            font-style: italic;
        }

        .add-new-link {
            display: flex;
            margin-top: 1.5rem;
        }

        .add-new-link a:nth-child(2) {
            margin-left: 1rem;
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
            
            .table-responsive {
                overflow-x: auto;
            }
        }

        /* Add these new search styles */
        .search-container {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .search-form {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-form .form-group {
            flex: 1;
            min-width: 200px;
        }

        .search-input-wrapper {
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .search-form .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: all 0.2s;
        }

        .search-form select.form-control {
            padding-left: 1rem;
            cursor: pointer;
        }

        .search-form .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(90, 90, 241, 0.1);
            outline: none;
        }

        .search-form .button-group {
            display: flex;
            gap: 0.5rem;
            flex: 0 0 auto;
            min-width: auto;
        }

        .search-form button {
            white-space: nowrap;
        }

        .search-results-info {
            margin-bottom: 1rem;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .search-results-info p {
            margin: 0;
        }

        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            
            .search-form .form-group {
                width: 100%;
            }
            
            .search-form .button-group {
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
                <button id="menuToggle" class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="page-title">
                    <h1>Current Stock</h1>
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
            
            <!-- Search Container -->
            <div class="search-container">
                <form action="" method="GET" class="search-form">
                    <div class="form-group">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   name="search" 
                                   placeholder="Search by item name..." 
                                   value="<?= htmlspecialchars($search) ?>"
                                   class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <select name="filter" class="form-control">
                            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Items</option>
                            <option value="low_stock" <?= $filter === 'low_stock' ? 'selected' : '' ?>>Low Stock</option>
                            <option value="in_stock" <?= $filter === 'in_stock' ? 'selected' : '' ?>>In Stock</option>
                        </select>
                    </div>
                    <div class="form-group button-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <?php if (!empty($search) || $filter !== 'all'): ?>
                            <a href="view_stock.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Search Results Info -->
            <?php if (!empty($search) || $filter !== 'all'): ?>
                <div class="search-results-info">
                    <p>
                        <?php if (!empty($search)): ?>
                            Showing results for "<?= htmlspecialchars($search) ?>"
                        <?php endif; ?>
                        <?php if ($filter !== 'all'): ?>
                            (Filter: <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $filter))) ?>)
                        <?php endif; ?>
                        - <?= $total_items ?> items found
                    </p>
                </div>
            <?php endif; ?>

            <!-- Table Container -->
            <div class="table-container">
                <div class="table-header">
                    <h2 class="table-title">Inventory Stock</h2>
                    <div class="table-actions">
                        <a href="record_consumption.php" class="btn btn-primary">
                            <i class="fas fa-minus-circle"></i> Record Consumption
                        </a>
                    </div>
                </div>
                
                <?php if (empty($data)): ?>
                    <div class="no-data-message">
                        <p>No stock data available. Get started by adding consumables to your inventory.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                <th>ID</th>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Total value</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
    <?php foreach ($data as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['item']) ?></td>
            <td><?= htmlspecialchars($row['quantity']) ?></td>
            <td><?= htmlspecialchars($row['cost_price']) ?></td>
            <td><?= htmlspecialchars($row['total_value']) ?></td>
            <td>
                <?php 
                $threshold = 10; // Set a default threshold value
                if ($row['quantity'] <= $threshold): ?>
                    <span style="color: var(--danger); font-weight: bold;">Low Stock</span>
                <?php else: ?>
                    <span style="color: var(--success);">In Stock</span>
                <?php endif; ?>
            </td>
            <td>
                <div class="actions">
                    <a href="update_stock.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success">Update</a>
                    <a href="view_stock_history.php?id=<?= $row['consumable_id'] ?>" class="btn btn-sm btn-primary">History</a>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
 </table>
 </div> <!-- Pagination Navigation -->
            <div class="pagination" style="margin-top: 1rem; display: flex; justify-content: center; align-items: center; gap: 0.5rem;">
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?= $current_page - 1 ?>" class="btn btn-sm btn-primary">Previous</a>
                <?php endif; ?>

                <?php 
                // Smart page number display
                $max_pages_to_show = 5;
                $start_page = max(1, min($current_page - floor($max_pages_to_show / 2), $total_pages - $max_pages_to_show + 1));
                $end_page = min($start_page + $max_pages_to_show - 1, $total_pages);

                // Show first page if not in range
                if ($start_page > 1) {
                    echo '<a href="?page=1" class="btn btn-sm btn-primary">1</a>';
                    if ($start_page > 2) {
                        echo '<span class="btn btn-sm">...</span>';
                    }
                }

                for ($i = $start_page; $i <= $end_page; $i++): 
                    $active_class = ($i == $current_page) ? 'btn-success' : 'btn-primary';
                ?>
                    <a href="?page=<?= $i ?>" class="btn btn-sm <?= $active_class ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php 
                // Show last page if not in range
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<span class="btn btn-sm">...</span>';
                    }
                    echo '<a href="?page=' . $total_pages . '" class="btn btn-sm btn-primary">' . $total_pages . '</a>';
                }

                if ($current_page < $total_pages): ?>
                    <a href="?page=<?= $current_page + 1 ?>" class="btn btn-sm btn-primary">Next</a>
                <?php endif; ?>
            </div>

            <!-- Optional: Show total items count -->
            <div class="pagination-info" style="text-align: center; margin-top: 0.5rem; color: var(--gray);">
                Showing <?= (($current_page - 1) * $items_per_page) + 1 ?> to 
                <?= min($current_page * $items_per_page, $total_items) ?> 
                of <?= $total_items ?> items
            </div>
        <?php endif; ?>
    </div>

<div class="add-new-link">
    <a href="add_new_stock.php" class="btn btn-primary">Add New Item</a>
    <a href="restock_item.php" class="btn btn-success">Restock Items</a>
</div>
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
                e.stopPropagation(); // Prevent event from bubbling up
                sidebar.classList.toggle('active');
                
                // Update toggle icon based on sidebar state
                if (sidebar.classList.contains('active')) {
                    menuToggle.innerHTML = '<i class="fas fa-times"></i>'; // Change to X icon when open
                } else {
                    menuToggle.innerHTML = '<i class="fas fa-bars"></i>'; // Change back to bars when closed
                }
            });
        }
        
        // Close sidebar when clicking on main content (for mobile)
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
    });
</script>
</body>
</html>