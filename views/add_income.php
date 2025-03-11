<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../classes/Database.php';
require_once '../classes/Income.php';
require_once '../classes/Validation.php';
require_once '../classes/Customers.php'; 
require_once '../classes/Services.php'; // Add Services class

// Initialize classes
$db = new Database();
$income = new Income();
$validation = new Validation();
$customer = new Customers();
$service = new Services(); // Initialize Services class

// Get all services for dropdown
$services = $service->getAllServices();

// Set breadcrumb variables
$breadcrumb_section = "Revenue";
$breadcrumb_section_url = "view_income.php";
$breadcrumb_page = "Add Income";

$today = date('F d, Y'); // Format: March 07, 2025
$current_time = date('h:i A'); 

// Process form submission
$errors = [];
$success = false;
$searchResults = [];
$selectedCustomer = null;

// If a customer ID was provided, get the customer details
if (isset($_GET['customer_id']) && !empty($_GET['customer_id'])) {
    $customerId = $validation->sanitizeInput($_GET['customer_id']);
    $selectedCustomer = $customer->getCustomerById($customerId);
}
// Handle main form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $amount = $validation->sanitizeInput($_POST['amount'] ?? '');
    $description = $validation->sanitizeInput($_POST['description'] ?? '');
    $income_date = $validation->sanitizeInput($_POST['income_date'] ?? '');
    $income_type = $validation->sanitizeInput($_POST['income_type'] ?? '');
    $service_id = $validation->sanitizeInput($_POST['serrvice_id'] ?? '');
    $customer_id = $validation->sanitizeInput($_POST['customer_id'] ?? '');
    
    // Check for errors
    if (empty($amount)) {
        $errors['amount'] = 'Amount is required';
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $errors['amount'] = 'Amount must be a positive number';
    }
    
    if (empty($description)) {
        $errors['description'] = 'Description is required';
    }
    
    if (empty($income_date)) {
        $errors['income_date'] = 'Date is required';
    }
    
    if (empty($income_type)) {
        $errors['income_type'] = 'Income type is required';
    }
    
    // If no errors, save the income
    if (empty($errors)) {
        try {
            // Get the service name for the selected service ID
            $serviceDetails = $service->getServiceById($service_id);
            $income_type = $serviceDetails ? $serviceDetails['service'] : 'Unknown';
            
            $income_data = [
                'amount' => $amount,
                'description' => $description,
                'date' => $income_date,
                'type' => $income_type, // The service name
                'service_id' => $service_id, // The actual service ID
                'added_by' => $_SESSION['user_id'],
                'customer_id' => !empty($customer_id) ? $customer_id : null
            ];
            
            if ($income->addIncomeData($income_data)) {
                $success = true;
            } else {
                $errors['general'] = 'Failed to add income. Please try again.';
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Database error: ' . $e->getMessage();
        }
    }
}

// If a customer ID was provided, get the customer details
if (isset($_GET['customer_id']) && !empty($_GET['customer_id'])) {
    $customerId = $validation->sanitizeInput($_GET['customer_id']);
    $selectedCustomer = $customer->getCustomerById($customerId);
}

// HTML header and the rest of the file remains the same
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Income - Snow Hotel Management System</title>
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

        /* Form styles */
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ccc; /* Make borders more visible */
            border-radius: var(--radius);
            font-size: 1rem;
            color: var(--dark);
            transition: border-color 0.2s, box-shadow 0.2s;
            background-color: #fff; /* Ensure background is visible */
        }

        /* Customer search results dropdown */
        .search-container {
            position: relative;
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            z-index: 10;
            box-shadow: var(--shadow);
            display: none;
        }
        
        .search-results.show {
            display: block;
        }
        
        .search-result-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .search-result-item:last-child {
            border-bottom: none;
        }
        
        .search-result-item:hover {
            background-color: var(--gray-light);
        }
        
        .customer-info {
            padding: 1rem;
            background-color: var(--light);
            border-radius: var(--radius);
            margin-bottom: 1rem;
            border: 1px solid var(--gray-light);
        }
        
        .customer-details {
            font-size: 0.9rem;
            color: var(--gray);
            margin-top: 0.5rem;
        }
        
        .customer-actions {
            margin-top: 0.5rem;
        }
        
        .customer-name {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--dark);
        }

        /* Improved mobile sidebar */
        @media (max-width: 992px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                width: 260px;
                z-index: 1000;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            /* Make the toggle button more visible */
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

        /* Make sure inputs are visible on all devices */
        input, select, textarea {
            border: 1px solid #ccc !important;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(90, 90, 241, 0.2);
            outline: none;
        }

        .form-control.is-invalid {
            border-color: var(--danger);
        }

        .invalid-feedback {
            display: block;
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
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
        
        .btn-link {
            background: none;
            color: var(--primary);
            text-decoration: underline;
            padding: 0;
            font-size: 0.9rem;
        }
        
        .btn-link:hover {
            color: var(--primary-dark);
        }

        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            border-left: 4px solid var(--success);
            color: var(--success);
        }

        .alert-danger {
            background-color: rgba(244, 67, 54, 0.1);
            border-left: 4px solid var(--danger);
            color: var(--danger);
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
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .user-nav {
                gap: 0.75rem; /* Reduce the gap on very small screens */
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
                    <li><a href="manage_stock.php" class="nav-link"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="view_income.php" class="nav-link active"><i class="fas fa-money-bill-wave"></i>Revenue</a></li>
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
                    <h1>Add Income</h1>
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
                <h2 class="form-title">Record New Income</h2>
                
                <?php if ($success): ?>
                <div class="alert alert-success">
                    Income has been successfully recorded!
                </div>
                <?php endif; ?>
                
                <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger">
                    <?= $errors['general'] ?>
                </div>
                <?php endif; ?>
                
                <form action="" method="POST">
                    <!-- Customer Search Section -->
                    <div class="form-group">
                        <label for="customer_search" class="form-label">Associate with Customer</label>
                        <div class="search-container">
                            <input type="text" id="customer_search" class="form-control" 
                                   placeholder="Search customer by name, email, or ID..." 
                                   <?= $selectedCustomer ? 'disabled' : '' ?>>
                            <div id="searchResults" class="search-results"></div>
                        </div>
                        
                        <!-- Hidden input to store selected customer ID -->
                        <input type="hidden" id="customer_id" name="customer_id" value="<?= $selectedCustomer ? $selectedCustomer['id'] : '' ?>">
                        
                        <!-- Display selected customer info -->
                        <div id="selectedCustomerInfo" class="customer-info" style="<?= $selectedCustomer ? '' : 'display:none;' ?>">
                            <?php if ($selectedCustomer): ?>
                            <div class="customer-name"><?= htmlspecialchars($selectedCustomer['guest_name']) ?></div>
                            <div class="customer-details">
                                ID/Passport: <?= htmlspecialchars($selectedCustomer['id_passport']) ?> | 
                                Email: <?= htmlspecialchars($selectedCustomer['email_address']) ?> | 
                                Phone: <?= htmlspecialchars($selectedCustomer['mobile_number']) ?>
                            </div>
                            <?php endif; ?>
                            <div class="customer-actions">
                                <button type="button" id="clearCustomer" class="btn-link">Change Customer</button>
                            </div>
                        </div>
                        
                        <div id="systemAccountInfo" style="<?= $selectedCustomer ? 'display:none;' : '' ?>">
                            <small class="text-muted">If no customer is selected, income will be recorded under the system account.</small>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" step="0.01" id="amount" name="amount" class="form-control <?= isset($errors['amount']) ? 'is-invalid' : '' ?>" value="<?= $_POST['amount'] ?? '' ?>">
                            <?php if (isset($errors['amount'])): ?>
                            <div class="invalid-feedback"><?= $errors['amount'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="income_date" class="form-label">Date</label>
                            <input type="date" id="income_date" name="income_date" class="form-control <?= isset($errors['income_date']) ? 'is-invalid' : '' ?>" value="<?= $_POST['income_date'] ?? date('Y-m-d') ?>">
                            <?php if (isset($errors['income_date'])): ?>
                            <div class="invalid-feedback"><?= $errors['income_date'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="income_type" class="form-label">Income Type</label>
                        <select id="income_type" name="income_type" class="form-control <?= isset($errors['income_type']) ? 'is-invalid' : '' ?>">
                            <option value="">Select Income Type</option>
                            <?php foreach ($services as $srv): ?>
                            <option value="<?= htmlspecialchars($srv['id']) ?>" 
                                    <?= (isset($_POST['income_type']) && $_POST['income_type'] == $srv['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($srv['service']) ?>
                            </option>
                            <?php endforeach; ?>
                            
                            <?php if (empty($services)): ?>
                            <option value="other" disabled>No services found. Please add services first.</option>
                            <?php endif; ?>
                        </select>
                        <?php if (isset($errors['income_type'])): ?>
                        <div class="invalid-feedback"><?= $errors['income_type'] ?></div>
                        <?php endif; ?>
                        
                        <small class="form-text text-muted">
                            Select the type of service or income source. 
                            <a href="manage_services.php">Manage services</a>
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" rows="4"><?= $_POST['description'] ?? '' ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                        <div class="invalid-feedback"><?= $errors['description'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-actions">
                        <a href="view_income.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Income</button>
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
    
    // Customer search functionality
    const customerSearch = document.getElementById('customer_search');
    const searchResults = document.getElementById('searchResults');
    const customerIdInput = document.getElementById('customer_id');
    const selectedCustomerInfo = document.getElementById('selectedCustomerInfo');
    const systemAccountInfo = document.getElementById('systemAccountInfo');
    const clearCustomerBtn = document.getElementById('clearCustomer');

    // Function to clear customer selection
    function clearCustomerSelection() {
        if (customerIdInput) customerIdInput.value = '';
        if (selectedCustomerInfo) selectedCustomerInfo.style.display = 'none';
        if (systemAccountInfo) systemAccountInfo.style.display = 'block';
        if (customerSearch) {
            customerSearch.disabled = false;
            customerSearch.value = '';
            customerSearch.focus();
        }
    }

    // Initial clear button event listener
    if (clearCustomerBtn) {
        clearCustomerBtn.addEventListener('click', clearCustomerSelection);
    }

    // Handle customer search input
    let searchTimeout;
    if (customerSearch) {
        customerSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                if (searchResults) searchResults.classList.remove('show');
                return;
            }
            
            // Debounce search requests
            searchTimeout = setTimeout(function() {
                fetch(`search_customer_api.php?search_term=${encodeURIComponent(query)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!searchResults) return;
                        
                        searchResults.innerHTML = '';
                        
                        if (data.length === 0) {
                            searchResults.innerHTML = '<div class="search-result-item">No customers found</div>';
                        } else {
                            data.forEach(customer => {
                                const item = document.createElement('div');
                                item.className = 'search-result-item';
                                item.innerHTML = `
                                    <strong>${customer.guest_name}</strong><br>
                                    <small>ID: ${customer.id} | Phone: ${customer.mobile_number || 'N/A'}</small>
                                `;
                                
                                item.addEventListener('click', function() {
                                    // Set the customer ID in the hidden input
                                    if (customerIdInput) customerIdInput.value = customer.id;
                                    
                                    // Update the selected customer info section
                                    if (selectedCustomerInfo) {
                                        const customerNameEl = document.createElement('div');
                                        customerNameEl.className = 'customer-name';
                                        customerNameEl.textContent = customer.guest_name;
                                        
                                        // Create customer details element
                                        const customerDetailsEl = document.createElement('div');
                                        customerDetailsEl.className = 'customer-details';
                                        customerDetailsEl.innerHTML = `
                                            ID/Passport: ${customer.id_passport || 'N/A'} | 
                                            Email: ${customer.email_address || 'N/A'} | 
                                            Phone: ${customer.mobile_number || 'N/A'}
                                        `;
                                        
                                        // Clear previous content and add new customer info
                                        selectedCustomerInfo.innerHTML = '';
                                        selectedCustomerInfo.appendChild(customerNameEl);
                                        selectedCustomerInfo.appendChild(customerDetailsEl);

                                        // Add customer actions (change customer button)
                                        const customerActionsEl = document.createElement('div');
                                        customerActionsEl.className = 'customer-actions';
                                        customerActionsEl.innerHTML = '<button type="button" id="clearCustomer" class="btn-link">Change Customer</button>';
                                        selectedCustomerInfo.appendChild(customerActionsEl);

                                        // Show customer info and hide system account message
                                        selectedCustomerInfo.style.display = 'block';
                                        if (systemAccountInfo) systemAccountInfo.style.display = 'none';

                                        // Disable the search input
                                        if (customerSearch) {
                                            customerSearch.disabled = true;
                                            customerSearch.value = customer.guest_name;
                                        }

                                        // Re-add event listener to the clear button (since we recreated it)
                                        const newClearBtn = document.getElementById('clearCustomer');
                                        if (newClearBtn) {
                                            newClearBtn.addEventListener('click', clearCustomerSelection);
                                        }
                                    }
                                    
                                    // Hide search results
                                    if (searchResults) searchResults.classList.remove('show');
                                });

                                searchResults.appendChild(item);
                            });
                        }
                        
                        searchResults.classList.add('show');
                    })
                    .catch(error => {
                        console.error('Error fetching customer data:', error);
                        if (searchResults) {
                            searchResults.innerHTML = '<div class="search-result-item">Error loading results</div>';
                            searchResults.classList.add('show');
                        }
                    });
            }, 300);
        });

        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (customerSearch && searchResults && 
                !customerSearch.contains(e.target) && 
                !searchResults.contains(e.target)) {
                searchResults.classList.remove('show');
            }
        });

        // Show search results when focusing on the search input
        customerSearch.addEventListener('focus', function() {
            if (this.value.trim().length >= 2 && searchResults) {
                searchResults.classList.add('show');
            }
        });
    }
});
</script>
</body>
</html>