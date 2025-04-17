<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: views/login.php');
    exit;
}

require_once 'classes/Database.php';
require_once 'classes/Customers.php';
require_once 'classes/Rooms.php';

// Initialize classes to access real data
$db = new Database();
$customers = new Customers();
$rooms = new Rooms();

// Get actual metrics from database
$allCustomers = $customers->getAllCustomers();
$activeCustomers = array_filter($allCustomers, function($customer) {
    return $customer['status'] != 0; // Only counting customers who haven't checked out
});

$availableRooms = $rooms->getAvailableRooms();

// Calculate customers with departure date within next 2 days
$twoDaysFromNow = date('Y-m-d', strtotime('+2 days'));
$today = date('Y-m-d');
$pendingCheckouts = array_filter($activeCustomers, function($customer) use ($twoDaysFromNow, $today) {
    $departureDate = date('Y-m-d', strtotime($customer['departure_datetime']));
    return $departureDate >= $today && $departureDate <= $twoDaysFromNow;
});

// Calculate metrics
$metrics = [
    'total_guests' => count($activeCustomers),
    'occupied_rooms' => count($activeCustomers), // Since one customer occupies one room
    'available_rooms' => count($availableRooms),
    'pending_checkouts' => count($pendingCheckouts), // Customers departing within 2 days
    'monthly_revenue' => 0 
];

// Calculate monthly revenue
$currentMonth = date('m');
$currentYear = date('Y');
$monthlyRevenue = $customers->getMonthlyRevenue();

// Get customer history for current month to calculate revenue
$customerHistory = $customers->getCustomerHistory();
$monthlyRevenue = 0;
foreach ($customerHistory as $history) {
    $departureDate = new DateTime($history['departure_datetime']);
    if ($departureDate->format('m') == $currentMonth && $departureDate->format('Y') == $currentYear) {
        $monthlyRevenue += $history['total_amount'];
    }
}

$metrics['monthly_revenue'] = $monthlyRevenue;

// Get current date and formatted time
$today = date('l, F j, Y');
$current_time = date('h:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Snow Hotel Management System</title>
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
            justify-content: space-between;
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
            color: var(--gray);
            font-size: 0.875rem;
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

        /* Dashboard Sections */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .metric-card {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .metric-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .metric-icon.blue {
            background: rgba(33, 150, 243, 0.1);
            color: #2196F3;
        }

        .metric-icon.purple {
            background: rgba(90, 90, 241, 0.1);
            color: var(--primary);
        }

        .metric-icon.red {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }

        .metric-icon.green {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }

        .metric-icon.orange {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        .metric-details h3 {
            font-weight: 400;
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 0.25rem;
        }

        .metric-details .metric-value {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            color: var(--dark);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: white;
            border-radius: var(--radius);
            padding: 1.25rem;
            text-align: center;
            text-decoration: none;
            color: var(--dark);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            color: var(--primary);
        }

        .action-card i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .action-card .action-name {
            font-weight: 500;
            font-size: 0.95rem;
        }

        .recent-activity {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.checkin {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }

        .status-badge.checkout {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }

        .status-badge.reservation {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            padding: 1rem 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--gray-light);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-details {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .activity-text {
            line-height: 1.4;
        }

        .activity-text .activity-title {
            font-weight: 500;
            font-size: 0.95rem;
        }

        .activity-text .activity-time {
            font-size: 0.8rem;
            color: var(--gray);
        }

        /* Burger Menu */
        .menu-toggle {
            background: none;
            border: none;
            color: var(--dark);
            font-size: 1.25rem;
            cursor: pointer;
            display: none;
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

        .logout-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .logout-link:hover {
            text-decoration: underline;
        }
        
        /* Media Queries for Responsiveness */
        @media (max-width: 992px) {
            .layout {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                grid-column: 1;
            }
            
            .sidebar.active {
                transform: translateX(0);
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
                justify-content: flex-end;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                padding: 1.5rem 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }

        .section-wrapper {
            margin-bottom: 2rem;
        }

        .data-container {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .data-table th,
        .data-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }

        .data-table th {
            background-color: var(--light);
            font-weight: 600;
            color: var(--dark);
        }

        .data-table tbody tr:hover {
            background-color: rgba(90, 90, 241, 0.05);
        }

        .departure-date {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .departure-date.today {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }

        .departure-date.tomorrow {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        .contact-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray);
            font-size: 0.875rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .empty-state i {
            font-size: 2.5rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: var(--gray);
            font-size: 0.95rem;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .section-title i {
            color: var(--primary);
        }
    </style>
</head>
<body>
    
    <div class="layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-snowflake"></i>
                    <span>Snow Hotel</span>
                </div>  
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <ul class="nav-links">
                    <li><a href="index.php" class="nav-link active"><i class="fas fa-th-large"></i>Dashboard</a></li>
                    <li><a href="views/view_customers.php" class="nav-link"><i class="fas fa-users"></i>Customers</a></li>
                    <li><a href="views/view_services.php" class="nav-link"><i class="fas fa-concierge-bell"></i>Services</a></li>
                    <li><a href="views/view_consumables.php" class="nav-link"><i class="fas fa-shopping-basket"></i>Consumables</a></li>
                </ul>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Management</div>
                <ul class="nav-links">
                    <li><a href="views/view_stock.php" class="nav-link"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="views/add_income.php" class="nav-link"><i class="fas fa-money-bill-wave"></i>Revenue</a></li>
                    <li><a href="views/view_customer_history.php" class="nav-link"><i class="fas fa-history"></i>History</a></li>
                    <li><a href="views/import_data.php" class="nav-link"><i class="fas fa-upload"></i>Import Data</a></li>
                    <li><a href="views/view_rooms.php" class="nav-link"><i class="fas fa-bed"></i>Rooms</a></li>
                </ul>
            </div>
            
            <div class="nav-section" style="margin-top: auto;">
                <ul class="nav-links">
                    <li><a href="controllers/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
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
                    <h1>Dashboard</h1>
                    <div class="breadcrumb"><?= $today ?> | <?= $current_time ?></div>
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
            
            <!-- Dashboard Metrics -->
            <div class="dashboard-grid">
                <div class="dashboard-card metric-card">
                    <div class="metric-icon purple">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="metric-details">
                        <h3>Total Guests</h3>
                        <div class="metric-value"><?= $metrics['total_guests'] ?></div>
                    </div>
                </div>
                
                <div class="dashboard-card metric-card">
                    <div class="metric-icon green">
                        <i class="fas fa-door-closed"></i>
                    </div>
                    <div class="metric-details">
                        <h3>Occupied Rooms</h3>
                        <div class="metric-value"><?= $metrics['occupied_rooms'] ?></div>
                    </div>
                </div>
                
                <div class="dashboard-card metric-card">
                    <div class="metric-icon orange">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div class="metric-details">
                        <h3>Available Rooms</h3>
                        <div class="metric-value"><?= $metrics['available_rooms'] ?></div>
                    </div>
                </div>
                
                <div class="dashboard-card metric-card">
                    <div class="metric-icon red">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="metric-details">
                        <h3>Monthly Revenue</h3>
                        <div class="metric-value"><?= number_format($metrics['monthly_revenue']) ?></div>
                    </div>
                </div>
                <div class="dashboard-card metric-card">
                    <div class="metric-icon blue">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="metric-details">
                        <h3>Pending Checkouts</h3>
                        <div class="metric-value"><?= $metrics['pending_checkouts'] ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Pending Checkouts Section -->
            <div class="section-wrapper">
                <h2 class="section-title">
                    <i class="fas fa-clock"></i>
                    Pending Checkouts (Next 48 Hours)
                </h2>
                
                <?php if (count($pendingCheckouts) > 0): ?>
                    <div class="data-container">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Room</th>
                                        <th>Guest Name</th>
                                        <th>Check-in Date</th>
                                        <th>Departure Date</th>
                                        <th>Contact</th>
                                        <th>Total Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingCheckouts as $customer): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($customer['room_number']) ?></td>
                                            <td><?= htmlspecialchars($customer['guest_name']) ?></td>
                                            <td><?= date('M d, Y', strtotime($customer['arrival_datetime'])) ?></td>
                                            <td>
                                                <span class="departure-date <?= date('Y-m-d', strtotime($customer['departure_datetime'])) === date('Y-m-d') ? 'today' : 'tomorrow' ?>">
                                                    <?= date('M d, Y', strtotime($customer['departure_datetime'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($customer['mobile_number']): ?>
                                                    <span class="contact-info">
                                                        <i class="fas fa-phone"></i> <?= htmlspecialchars($customer['mobile_number']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= number_format($customer['total_amount'], 2) ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="views/view_customer_details.php?id=<?= $customer['id'] ?>" 
                                                       class="btn btn-primary btn-sm" 
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="views/checkout_customer.php?id=<?= $customer['id'] ?>" 
                                                       class="btn btn-danger btn-sm"
                                                       title="Process Checkout"
                                                       onclick="return confirm('Are you sure you want to check out this customer?');">
                                                        <i class="fas fa-sign-out-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-check"></i>
                        <p>No pending checkouts for the next 48 hours</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Quick Actions -->
            <h2 class="section-title">Quick Actions</h2>
            <div class="quick-actions">
                <a href="views/add_customer.php" class="action-card">
                    <i class="fas fa-user-plus"></i>
                    <span class="action-name">Add Customer</span>
                </a>
                
                <a href="views/search_customer.php" class="action-card">
                    <i class="fas fa-search"></i>
                    <span class="action-name">Search Customer</span>
                </a>
                
                <a href="views/checkout_customer.php" class="action-card">
                    <i class="fas fa-check-circle"></i>
                    <span class="action-name">Checkout</span>
                </a>
                
                <a href="views/add_income.php" class="action-card">
                    <i class="fas fa-cash-register"></i>
                    <span class="action-name">Record Income</span>
                </a>
                
                <a href="views/view_stock.php" class="action-card">
                    <i class="fas fa-boxes"></i>
                    <span class="action-name">Manage Stock</span>
                </a>
            </div>
            
            <!-- Recent Activity
            <h2 class="section-title">Recent Activity</h2>
            <div class="recent-activity dashboard-card">
                <ul class="activity-list">
                    <li class="activity-item">
                        <div class="activity-details">
                            <div class="activity-icon" style="background: rgba(76, 175, 80, 0.1); color: var(--success);">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="activity-text">
                                <div class="activity-title">James Wilson checked in</div>
                                <div class="activity-time">Room 203 • 2 hours ago</div>
                            </div>
                        </div>
                        <span class="status-badge checkin">Check-in</span>
                    </li>
                    
                    <li class="activity-item">
                        <div class="activity-details">
                            <div class="activity-icon" style="background: rgba(244, 67, 54, 0.1); color: var(--danger);">
                                <i class="fas fa-sign-out-alt"></i>
                            </div>
                            <div class="activity-text">
                                <div class="activity-title">Emily Parker checked out</div>
                                <div class="activity-time">Room 105 • 4 hours ago</div>
                            </div>
                        </div>
                        <span class="status-badge checkout">Check-out</span>
                    </li>
                    
                    <li class="activity-item">
                        <div class="activity-details">
                            <div class="activity-icon" style="background: rgba(255, 152, 0, 0.1); color: var(--warning);">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="activity-text">
                                <div class="activity-title">New reservation by Michael Brown</div>
                                <div class="activity-time">For next week • 5 hours ago</div>
                            </div>
                        </div>
                        <span class="status-badge reservation">Reservation</span>
                    </li>
                    
                    <li class="activity-item">
                        <div class="activity-details">
                            <div class="activity-icon" style="background: rgba(90, 90, 241, 0.1); color: var(--primary);">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="activity-text">
                                <div class="activity-title">Inventory updated</div>
                                <div class="activity-time">By Admin • Yesterday</div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div> -->
            
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