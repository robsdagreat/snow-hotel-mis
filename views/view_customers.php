<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}

require_once '../classes/Customers.php';
$customer = new Customers();

// Get filter parameters
$search_term = $_GET['search_term'] ?? '';
$status = $_GET['status'] ?? '';
$payment_mode = $_GET['payment_mode'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$nationality = $_GET['nationality'] ?? '';

// Pagination settings
$records_per_page = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $records_per_page;

// Create search parameters array
$search_params = array_filter([
    'search_term' => $search_term,
    'status' => $status,
    'payment_mode' => $payment_mode,
    'date_from' => $date_from,
    'date_to' => $date_to,
    'nationality' => $nationality,
    'limit' => $records_per_page,
    'offset' => $offset
], function($value) {
    return $value !== '';
});

// Get filtered data and total count
$data = $customer->searchCustomer($search_params);
$total_customers = $customer->getCustomerSearchCount($search_params);
$total_pages = ceil($total_customers / $records_per_page);

// Set breadcrumb variables
$breadcrumb_section = "Customers";
$breadcrumb_section_url = "view_customers.php";
$breadcrumb_page = "View Customers";

$today = date('F d, Y'); // Format: March 09, 2025
$current_time = date('h:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customers - Snow Hotel Management System</title>
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

        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
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
            background-color: #e69500;
        }
        
        .btn-inactive {
            background-color: var(--gray);
            color: white;
            pointer-events: none;
        }

        .no-data-message {
            padding: 1.5rem;
            text-align: center;
            color: var(--gray);
            font-style: italic;
        }

        .add-new-link {
            display: inline-block;
            /* margin-top: 1.5rem; */
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 0.5rem;
        }

        .pagination a, .pagination span {
            display: inline-block;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius);
            text-decoration: none;
            transition: all 0.2s;
            color: var(--dark);
            font-weight: 500;
            background-color: white;
            box-shadow: var(--shadow);
        }

        .pagination a:hover {
            background-color: var(--gray-light);
        }

        .pagination span.current {
            background-color: var(--primary);
            color: white;
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
            
            .pagination {
                flex-wrap: wrap;
            }
        }

        /* Advanced Filters Section */
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

        .date-range-inputs {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .date-separator {
            color: #666;
            font-size: 0.9rem;
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
            padding-top: 10px;
            border-top: 1px solid #eee;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .filters-row {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .filters-actions {
                flex-direction: column;
            }
            
            .filters-actions button {
                width: 100%;
            }

            .date-range-inputs {
                flex-direction: column;
            }

            .date-separator {
                text-align: center;
                padding: 5px 0;
            }
        }

        /* Reset any existing pagination styles */
        .pagination,
        .pagination * {
            box-sizing: border-box;
        }

        .pagination {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            gap: 5px !important;
            margin: 20px 0 !important;
        }

        /* More specific selector to ensure our styles take precedence */
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

        /* Extra specific selector to override any existing styles */
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

        /* Remove any other pagination styles that might be interfering */
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
                    <li><a href="view_customers.php" class="nav-link active"><i class="fas fa-users"></i>Customers</a></li>
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
                    <h1>Customers</h1>
                    <div class="breadcrumb">
                        <a href="../index.php">Dashboard</a>
                        <span>&gt;</span>
                        <a href="<?= $breadcrumb_section_url ?>"><?= $breadcrumb_section ?></a>
                        <span>&gt;</span>
                        <span><?= $breadcrumb_page ?></span>
                        <span class="time-display" style="margin-left: auto;"><?= $today ?> | <?= $current_time ?></span>
                    </div>
                </div>
                <div class="add-new-link">
                    <a href="add_customer.php" class="btn btn-primary">Add New Customer</a>
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
            
            <!-- Move this right after your top bar section and BEFORE the table container -->
            <div class="filters-container">
                <!-- <h2 class="search-title">Advanced Customer Search</h2> -->
                
                <form id="filterForm" class="filters-form">
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
                                       placeholder="Name, Room, ID, or Phone">
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label>Guest Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">All Guests</option>
                                <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Currently Checked In</option>
                                <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Checked Out</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Payment Mode</label>
                            <select id="payment_mode" name="payment_mode" class="form-control">
                                <option value="">All Payment Modes</option>
                                <option value="Cash" <?= $payment_mode === 'Cash' ? 'selected' : '' ?>>Cash</option>
                                <option value="Credit Card" <?= $payment_mode === 'Credit Card' ? 'selected' : '' ?>>Credit Card</option>
                                <option value="Bank Transfer" <?= $payment_mode === 'Bank Transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                                <option value="Online Payment" <?= $payment_mode === 'Online Payment' ? 'selected' : '' ?>>Online Payment</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filters-row">
                        <div class="filter-group">
                            <label>Check-in Date Range</label>
                            <div class="date-range-inputs">
                                <input type="date" 
                                       id="date_from" 
                                       name="date_from" 
                                       class="form-control"
                                       value="<?= htmlspecialchars($date_from) ?>"
                                       placeholder="From">
                                <span class="date-separator">to</span>
                                <input type="date" 
                                       id="date_to" 
                                       name="date_to" 
                                       class="form-control"
                                       value="<?= htmlspecialchars($date_to) ?>"
                                       placeholder="To">
                            </div>
                        </div>

                        <div class="filter-group">
                            <label>Nationality</label>
                            <input type="text" 
                                   id="nationality" 
                                   name="nationality" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($nationality) ?>"
                                   placeholder="Enter nationality">
                        </div>

                        <div class="filter-group">
                            <label>Stay Duration</label>
                            <select id="stay_duration" name="stay_duration" class="form-control">
                                <option value="">Any Duration</option>
                                <option value="1" <?= isset($_GET['stay_duration']) && $_GET['stay_duration'] === '1' ? 'selected' : '' ?>>1 Day</option>
                                <option value="2-3" <?= isset($_GET['stay_duration']) && $_GET['stay_duration'] === '2-3' ? 'selected' : '' ?>>2-3 Days</option>
                                <option value="4-7" <?= isset($_GET['stay_duration']) && $_GET['stay_duration'] === '4-7' ? 'selected' : '' ?>>4-7 Days</option>
                                <option value="8+" <?= isset($_GET['stay_duration']) && $_GET['stay_duration'] === '8+' ? 'selected' : '' ?>>8+ Days</option>
                            </select>
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
            
            <!-- After filters, add the search results info section -->
            <?php if (!empty($search_term) || $status !== '' || !empty($payment_mode) || !empty($date_from) || !empty($date_to) || !empty($nationality) || !empty($_GET['stay_duration'])): ?>
                <div class="search-results-info">
                    <p>
                        <?php if (!empty($search_term)): ?>
                            Showing results for "<?= htmlspecialchars($search_term) ?>"
                        <?php endif; ?>
                        <?php if ($status !== ''): ?>
                            (Status: <?= $status === '1' ? 'Currently Checked In' : 'Checked Out' ?>)
                        <?php endif; ?>
                        <?php if (!empty($payment_mode)): ?>
                            (Payment: <?= htmlspecialchars($payment_mode) ?>)
                        <?php endif; ?>
                        <?php if (!empty($date_from) || !empty($date_to)): ?>
                            (Date Range: <?= !empty($date_from) ? date('M d, Y', strtotime($date_from)) : 'Any' ?> 
                            to 
                            <?= !empty($date_to) ? date('M d, Y', strtotime($date_to)) : 'Any' ?>)
                        <?php endif; ?>
                        <?php if (!empty($nationality)): ?>
                            (Nationality: <?= htmlspecialchars($nationality) ?>)
                        <?php endif; ?>
                        <?php if (!empty($_GET['stay_duration'])): ?>
                            (Stay Duration: <?= htmlspecialchars($_GET['stay_duration']) ?> 
                            <?= $_GET['stay_duration'] === '1' ? 'Day' : 'Days' ?>)
                        <?php endif; ?>
                        - <?= $total_customers ?> customers found
                    </p>
                </div>
            <?php endif; ?>
            
            <!-- Table Container -->
            <div class="table-container">
                <h2 class="table-title">All Customers (<?= $total_customers ?> total)</h2>
                
                <?php if (empty($data)): ?>
                    <div class="no-data-message">
                        <p>No customers found. Get started by adding your first customer.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
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
                                            <div class="actions">
                                                <a href="view_customer_details.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Details</a>
                                                <?php if ($row['status'] == 1): ?>
                                                    <a href="checkout_customer.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-sm btn-warning">Check Out</a>
                                                <?php else: ?>
                                                    <span class="btn btn-sm btn-inactive">Checked Out</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
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
                            Showing <?= ($offset + 1) ?> to <?= min($offset + $records_per_page, $total_customers) ?> of <?= $total_customers ?> entries
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <div class="add-new-link">
                    <a href="add_customer.php" class="btn btn-primary">Add New Customer</a>
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

            const filterForm = document.getElementById('filterForm');
            
            // Handle form submission
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                this.submit();
            });
            
            // Handle form reset
            filterForm.addEventListener('reset', function(e) {
                setTimeout(() => {
                    window.location.href = 'view_customers.php';
                }, 0);
            });
        });
    </script>
</body>
</html>