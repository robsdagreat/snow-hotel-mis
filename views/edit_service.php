<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}
require_once '../classes/Services.php';
$services = new Services();

// Get service ID from URL parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: view_services.php?error=invalid_id");
    exit();
}

$id = $_GET['id'];
$service = $services->getServiceById($id);
if (!$service) {
    header("Location: view_services.php?error=not_found");
    exit();
}

// Set breadcrumb variables
$breadcrumb_section = "Services";
$breadcrumb_section_url = "view_services.php";
$breadcrumb_page = "Edit Service";

$today = date('F d, Y'); // Format: March 11, 2025
$current_time = date('h:i A'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service - Snow Hotel Management System</title>
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
            border: 1px solid #ccc;
            border-radius: var(--radius);
            font-size: 1rem;
            color: var(--dark);
            transition: border-color 0.2s, box-shadow 0.2s;
            background-color: #fff;
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

        textarea {
            resize: vertical;
            min-height: 100px;
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
                    <li><a href="view_services.php" class="nav-link active"><i class="fas fa-concierge-bell"></i>Services</a></li>
                    <li><a href="view_consumables.php" class="nav-link"><i class="fas fa-shopping-basket"></i>Consumables</a></li>
                </ul>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Management</div>
                <ul class="nav-links">
                    <li><a href="view_stock.php" class="nav-link"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="view_income.php" class="nav-link"><i class="fas fa-money-bill-wave"></i>Revenue</a></li>
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
                    <h1>Edit Service</h1>
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
                <h2 class="form-title">Update Service Details</h2>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success_message'] ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error_message'] ?>
                    <?php unset($_SESSION['error_message']); ?>
                </div>
                <?php endif; ?>
                
                <form action="../controllers/service_controller.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="service_id" value="<?= htmlspecialchars($service['id']) ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="service" class="form-label">Service Name:</label>
                        <input type="text" id="service" name="service" class="form-control" 
                            value="<?= htmlspecialchars($service['service'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="is_active" class="form-label">Status:</label>
                        <select name="is_active" id="is_active" class="form-control" required>
                            <option value="1" <?= ($service['is_active'] ?? 0) == 1 ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= ($service['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">Description:</label>
                    <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-actions">
                    <a href="view_services.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Service</button>
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
        });
    </script>
</body>
</html>