<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}

// Setup for sorting
require_once '../classes/Consumables.php';
$consumables = new Consumables();

// Get filter parameters
$search_term = $_GET['search_term'] ?? '';
$service_id = $_GET['service_id'] ?? '';
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$sort_by = $_GET['sort_by'] ?? 'id';
$order = $_GET['order'] ?? 'asc';

// Pagination setup
$items_per_page = 15; // Number of items to display per page
if ($page < 1) $page = 1;

// Allowed columns and orders for sorting
$allowed_columns = ['id', 'item', 'service_name', 'unit', 'unit_price'];
if (!in_array($sort_by, $allowed_columns)) $sort_by = 'id';
$allowed_orders = ['asc', 'desc'];
if (!in_array($order, $allowed_orders)) $order = 'asc';

// Get total number of consumables for pagination
$total_items = $consumables->countConsumables();
$total_pages = ceil($total_items / $items_per_page);

// Ensure current_page doesn't exceed total_pages
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}

// Calculate offset for SQL LIMIT clause
$offset = ($page - 1) * $items_per_page;

// Create search parameters array
$search_params = array_filter([
    'search_term' => $search_term,
    'service_id' => $service_id,
    'price_min' => $price_min,
    'price_max' => $price_max,
    'sort_by' => $sort_by,
    'order' => $order,
    'limit' => $items_per_page,
    'offset' => $offset
], function($value) {
    return $value !== '';
});

// Get filtered data and total count
$data = $consumables->searchConsumables($search_params);
$total_items = $consumables->getSearchCount($search_params);
$total_pages = ceil($total_items / $items_per_page);

// Get all services for the filter dropdown
$all_services = $consumables->getAllServices();

// Sort icons for headers
$id_sort_icon = $sort_by === 'id' ? ($order === 'asc' ? '▲' : '▼') : '';
$item_sort_icon = $sort_by === 'item' ? ($order === 'asc' ? '▲' : '▼') : '';
$service_sort_icon = $sort_by === 'service_name' ? ($order === 'asc' ? '▲' : '▼') : '';
$toggle_order = $order === 'asc' ? 'desc' : 'asc';

// Set breadcrumb variables
$breadcrumb_section = "Inventory";
$breadcrumb_section_url = "manage_stock.php";
$breadcrumb_page = "View Consumables";

$today = date('F d, Y'); // Format: March 07, 2025
$current_time = date('h:i A'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Consumables - Snow Hotel Management System</title>
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
            background-color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .table-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }
        
        .note {
            padding: 1rem;
            background-color: rgba(255, 235, 59, 0.1);
            border-left: 4px solid var(--warning);
            color: var(--dark);
            margin-bottom: 1.5rem;
            border-radius: var(--radius);
            font-size: 0.9rem;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th, 
        .data-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .data-table thead th {
            background-color: var(--light);
            color: var(--dark);
            font-weight: 600;
            border-bottom: 2px solid var(--gray-light);
        }
        
        .data-table th a {
            color: var(--dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .data-table th a:hover {
            color: var(--primary);
        }
        
        .data-table th a .sort-icon {
            margin-left: 0.5rem;
            color: var(--primary);
        }
        
        .data-table tbody tr:hover {
            background-color: rgba(90, 90, 241, 0.05);
        }
        
        .data-table .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .data-table .btn-action {
            padding: 0.4rem 0.75rem;
            border-radius: var(--radius);
            font-size: 0.8rem;
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        
        .data-table .btn-edit {
            background-color: var(--primary-light);
            color: white;
        }
        
        .data-table .btn-edit:hover {
            background-color: var(--primary);
        }
        
        .data-table .btn-delete {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }
        
        .data-table .btn-delete:hover {
            background-color: var(--danger);
            color: white;
        }

        .add-new-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary);
            color: white;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 500;
            margin-top: 1rem;
            transition: background-color 0.2s;
        }
        
        .add-new-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .empty-state {
            padding: 2rem;
            text-align: center;
            color: var(--gray);
        }
        
        .empty-state p {
            margin-bottom: 1rem;
        }

        /* Pagination Styles */
        .pagination-info {
            text-align: center;
            margin-top: 0.5rem;
            color: var(--gray);
        }

        .pagination {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            gap: 5px !important;
            margin: 20px 0 !important;
        }

        .pagination .page-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 36px !important;
            height: 36px !important;
            padding: 0 !important;
            border-radius: 4px !important;
            text-decoration: none !important;
            background-color: #fff !important;  /* White background for inactive buttons */
            color: #5a5af1 !important;         /* Blue text for inactive buttons */
            border: 1px solid #dee2e6 !important;
            font-size: 14px !important;
            font-weight: normal !important;
            transition: all 0.2s ease !important;
        }

        .pagination .page-btn:hover:not(.current):not(.disabled) {
            background-color: #f8f9fa !important;
            color: #5a5af1 !important;
            z-index: 2 !important;
        }

        .pagination .page-btn.current,
        .pagination a.page-btn.current {
            background-color: #5a5af1 !important;  /* Blue background for active button */
            color: #fff !important;               /* White text for active button */
            border-color: #5a5af1 !important;
            z-index: 3 !important;
        }

        .pagination .page-btn.disabled {
            background-color: #fff !important;    /* White background for disabled buttons */
            color: #6c757d !important;           /* Gray text for disabled buttons */
            pointer-events: none !important;
            opacity: 0.65 !important;
        }

        .pagination .page-btn:focus,
        .pagination .page-btn:active {
            outline: none !important;
            box-shadow: none !important;
        }

        .showing-text {
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            margin-top: 10px;
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
            
            .data-table {
                display: block;
                overflow-x: auto;
            }
            
            .pagination {
                flex-wrap: wrap;
            }
        }

        .filters-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .filters-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .filters-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 250px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .search-input-wrapper {
            position: relative;
        }

        .search-input-wrapper i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .search-input-wrapper input {
            padding-left: 35px;
        }

        .price-range-inputs {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .price-separator {
            color: #666;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #5a5af1;
            outline: none;
            box-shadow: 0 0 0 2px rgba(90, 90, 241, 0.1);
        }

        .filters-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding-top: 15px;
            border-top: 1px solid #eee;
            margin-top: 15px;
        }

        /* Button styles */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn i {
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        /* Filter action styles */
        .filters-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding-top: 15px;
            border-top: 1px solid #eee;
            margin-top: 15px;
        }

        /* Pagination styles */
        .pagination {
            display: flex;
            gap: 5px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .page-btn {
            padding: 8px 16px;
            background-color: #5a5af1;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .page-btn:hover {
            background-color: #4747c2;
        }

        .page-btn.current {
            background-color: #4caf50;
        }

        .showing-text {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .filters-actions {
                flex-direction: column;
                gap: 8px;
            }

            .filters-actions .btn {
                width: 100%;
                justify-content: center;
            }

            .pagination {
                gap: 4px;
            }

            .page-btn {
                padding: 6px 12px;
                font-size: 0.9rem;
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
                    <li><a href="view_consumables.php" class="nav-link active"><i class="fas fa-shopping-basket"></i>Consumables</a></li>
                </ul>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Management</div>
                <ul class="nav-links">
                    <li><a href="view_stock.php" class="nav-link"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="add_income.php" class="nav-link"><i class="fas fa-money-bill-wave"></i>Revenue</a></li>
                    <li><a href="view_customer_history.php" class="nav-link"><i class="fas fa-history"></i>History</a></li>
                    <li><a href="import_data.php" class="nav-link"><i class="fas fa-upload"></i>Import Data</a></li>
<li><a href="view_rooms.php" class="nav-link"><i class="fas fa-bed"></i>Rooms</a></li>
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
                    <h1>View Consumables</h1>
                    <div class="breadcrumb">
                        <a href="../index.php">Dashboard</a>
                        <span>&gt;</span>
                        <a href="<?= $breadcrumb_section_url ?>"><?= $breadcrumb_section ?></a>
                        <span>&gt;</span>
                        <span><?= $breadcrumb_page ?></span>
                        <span class="time-display" style="margin-left: auto;"><?= $today ?> | <?= $current_time ?></span>
                    </div>
                </div>

                <div style="text-align: right;">
                    <a href="add_consumable.php" class="add-new-btn">
                        <i class="fas fa-plus"></i> Add New Consumable
                    </a>
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
            
            <!-- Add this after your top bar section and before the table container -->
            <div class="filters-container">
                <form id="filterForm" class="filters-form" method="GET">
                    <!-- Preserve sorting parameters -->
                    <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sort_by) ?>">
                    <input type="hidden" name="order" value="<?= htmlspecialchars($order) ?>">
                    
                    <div class="filters-row">
                        <div class="filter-group">
                            <label>Search Term</label>
                            <div class="search-input-wrapper">
                                <i class="fas fa-search"></i>
                                <input type="text" 
                                       id="search_term" 
                                       name="search_term" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($search_term) ?>"
                                       placeholder="Search by item name or service">
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label>Service</label>
                            <select id="service_id" name="service_id" class="form-control">
                                <option value="">All Services</option>
                                <?php 
                                if (!empty($all_services)):
                                    foreach ($all_services as $service): 
                                ?>
                                    <option value="<?= $service['id'] ?>" 
                                            <?= $service_id == $service['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($service['service']) ?>
                                    </option>
                                <?php 
                                    endforeach;
                                endif;
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filters-row">
                        <div class="filter-group">
                            <label>Price Range</label>
                            <div class="price-range-inputs">
                                <input type="number" 
                                       id="price_min" 
                                       name="price_min" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($price_min) ?>"
                                       placeholder="Min Price"
                                       min="0"
                                       step="0.01">
                                <span class="price-separator">to</span>
                                <input type="number" 
                                       id="price_max" 
                                       name="price_max" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($price_max) ?>"
                                       placeholder="Max Price"
                                       min="0"
                                       step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <div class="filters-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear Filters
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Table Container -->
            <div class="table-container">
                <h2 class="table-title">Consumables Inventory</h2>
                
                <div class="note">
                    <strong><i class="fas fa-info-circle"></i> Note:</strong> Hotel Management regularly sends a team to the market to get updated about the changes in Units and Unit Prices. Ensure to validate the latest details before submission.
                </div>
                
                <?php if (empty($data)): ?>
                <div class="empty-state">
                    <p>No consumables found in the inventory.</p>
                    <a href="add_consumable.php" class="add-new-btn">
                        <i class="fas fa-plus"></i> Add New Consumable
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">
                                    <a href="?sort_by=item&order=<?= $toggle_order ?>&page=<?= $page ?>">
                                        Item
                                        <?php if ($sort_by === 'item'): ?>
                                        <span class="sort-icon"><?= $order === 'asc' ? '▲' : '▼' ?></span>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th width="25%">
                                    <a href="?sort_by=service_name&order=<?= $toggle_order ?>&page=<?= $page ?>">
                                        Service
                                        <?php if ($sort_by === 'service_name'): ?>
                                        <span class="sort-icon"><?= $order === 'asc' ? '▲' : '▼' ?></span>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th width="15%">Unit</th>
                                <th width="15%">Unit Price</th>
                                <th width="15%">Cost Price</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $starting_number = ($page - 1) * $items_per_page + 1;
                            foreach ($data as $index => $row): 
                            ?>
                            <tr>
                                <td><?= $starting_number + $index ?></td>
                                <td><?= htmlspecialchars($row['item']) ?></td>
                                <td><?= htmlspecialchars($row['service_name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($row['unit']) ?></td>
                                <td><?= htmlspecialchars(number_format($row['unit_price'], 2)) ?></td>
                                <td><?= htmlspecialchars(number_format($row['cost_price'] ?? 0, 2)) ?></td>
                                <td class="actions">
                                    <a href="edit_consumable.php?id=<?= $row['id'] ?>" class="btn-action btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="javascript:void(0)" onclick="confirmDelete(<?= $row['id'] ?>)" class="btn-action btn-delete">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    // Create query string with current filters
                    $filters = $_GET;
                    unset($filters['page']); // Remove page from filters
                    $query_string = http_build_query($filters);
                    $query_string = $query_string ? '&' . $query_string : '';
                    ?>

                    <!-- First page -->
                    <a href="?page=1<?= $query_string ?>" 
                       class="page-btn <?= ($page == 1) ? 'disabled' : '' ?>">
                        «
                    </a>

                    <!-- Previous page -->
                    <a href="?page=<?= max(1, $page - 1) . $query_string ?>" 
                       class="page-btn <?= ($page == 1) ? 'disabled' : '' ?>">
                        ‹
                    </a>

                    <!-- Page numbers -->
                    <?php
                    $range = 2;
                    $start_page = max(1, $page - $range);
                    $end_page = min($total_pages, $page + $range);

                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <a href="?page=<?= $i . $query_string ?>" 
                           class="page-btn <?= ($i == $page) ? 'current' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Next page -->
                    <a href="?page=<?= min($total_pages, $page + 1) . $query_string ?>" 
                       class="page-btn <?= ($page == $total_pages) ? 'disabled' : '' ?>">
                        ›
                    </a>

                    <!-- Last page -->
                    <a href="?page=<?= $total_pages . $query_string ?>" 
                       class="page-btn <?= ($page == $total_pages) ? 'disabled' : '' ?>">
                        »
                    </a>
                </div>

                <div class="showing-text">
                    Showing <?= ($offset + 1) ?> to <?= min($offset + $items_per_page, $total_items) ?> of <?= $total_items ?> entries
                </div>
                <?php endif; ?>
                
                <div style="margin-top: 2rem; text-align: right;">
                    <a href="add_consumable.php" class="add-new-btn">
                        <i class="fas fa-plus"></i> Add New Consumable
                    </a>
                </div>
                <?php endif; ?>
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

            const filterForm = document.getElementById('filterForm');
            
            // Handle form reset
            filterForm.addEventListener('reset', function(e) {
                setTimeout(() => {
                    window.location.href = 'view_consumables.php';
                }, 0);
            });
        });
        
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this consumable? This action cannot be undone.")) {
                window.location.href = '../controllers/consumables_controller.php?action=delete&id=' + id;
            }
        }
    </script>
</body>
</html>