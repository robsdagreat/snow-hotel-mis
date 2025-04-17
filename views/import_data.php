<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../classes/Database.php';
require_once '../classes/Validation.php';

// Set breadcrumb variables
$breadcrumb_section = "System"; 
$breadcrumb_section_url = "index.php";
$breadcrumb_page = "Import Data";

$today = date('F d, Y');
$current_time = date('h:i A');

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'import_rooms':
                if (isset($_FILES['rooms_file']) && $_FILES['rooms_file']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['rooms_file']['tmp_name'];
                    $handle = fopen($file, "r");
                    
                    // Skip header row
                    fgetcsv($handle);
                    
                    $db = new Database();
                    $pdo = $db->getConnection();
                    
                    try {
                        $pdo->beginTransaction();
                        
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            $sql = "INSERT INTO rooms (room_number, room_type, room_rate, status) 
                                   VALUES (:room_number, :room_type, :room_rate, 1)";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([
                                ':room_number' => $data[0],
                                ':room_type' => $data[1],
                                ':room_rate' => $data[2]
                            ]);
                        }
                        
                        $pdo->commit();
                        $success_message = "Rooms imported successfully!";
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error_message = "Error importing rooms: " . $e->getMessage();
                    }
                    
                    fclose($handle);
                }
                break;
                
            case 'import_services':
                if (isset($_FILES['services_file']) && $_FILES['services_file']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['services_file']['tmp_name'];
                    $handle = fopen($file, "r");
                    
                    // Skip header row
                    fgetcsv($handle);
                    
                    $db = new Database();
                    $pdo = $db->getConnection();
                    
                    try {
                        $pdo->beginTransaction();
                        
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            $sql = "INSERT INTO services (service, description, price) 
                                   VALUES (:service, :description, :price)";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([
                                ':service' => $data[0],
                                ':description' => $data[1],
                                ':price' => $data[2]
                            ]);
                        }
                        
                        $pdo->commit();
                        $success_message = "Services imported successfully!";
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error_message = "Error importing services: " . $e->getMessage();
                    }
                    
                    fclose($handle);
                }
                break;
                
            case 'import_consumables':
                if (isset($_FILES['consumables_file']) && $_FILES['consumables_file']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['consumables_file']['tmp_name'];
                    $handle = fopen($file, "r");
                    
                    // Skip header row
                    fgetcsv($handle);
                    
                    $db = new Database();
                    $pdo = $db->getConnection();
                    
                    try {
                        $pdo->beginTransaction();
                        
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            $sql = "INSERT INTO consumables (item, service, unit, unit_price, cost_price) 
                                   VALUES (:item, :service, :unit, :unit_price, :cost_price)";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([
                                ':item' => $data[0],
                                ':service' => $data[1],
                                ':unit' => $data[2],
                                ':unit_price' => $data[3],
                                ':cost_price' => $data[4]
                            ]);
                        }
                        
                        $pdo->commit();
                        $success_message = "Consumables imported successfully!";
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error_message = "Error importing consumables: " . $e->getMessage();
                    }
                    
                    fclose($handle);
                }
                break;
                
            case 'import_stock':
                if (isset($_FILES['stock_file']) && $_FILES['stock_file']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['stock_file']['tmp_name'];
                    $handle = fopen($file, "r");
                    
                    // Skip header row
                    fgetcsv($handle);
                    
                    $db = new Database();
                    $pdo = $db->getConnection();
                    
                    try {
                        $pdo->beginTransaction();
                        
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            // First, get the consumable_id
                            $sql = "SELECT id FROM consumables WHERE item = :item LIMIT 1";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([':item' => $data[0]]);
                            $consumable = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($consumable) {
                                $sql = "INSERT INTO stock (consumable_id, quantity, unit_price, cost_price) 
                                       VALUES (:consumable_id, :quantity, :unit_price, :cost_price)";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([
                                    ':consumable_id' => $consumable['id'],
                                    ':quantity' => $data[1],
                                    ':unit_price' => $data[2],
                                    ':cost_price' => $data[3]
                                ]);
                            }
                        }
                        
                        $pdo->commit();
                        $success_message = "Stock imported successfully!";
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error_message = "Error importing stock: " . $e->getMessage();
                    }
                    
                    fclose($handle);
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data - Snow Hotel Management System</title>
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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6fc;
            color: var(--dark);
            /* line-height: 1.6; */
        }
        .layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }
        .sidebar {
            background: var(--primary);
            color: white;
            padding: 0.1rem;
            position: fixed;
            height: 100vh;
            width: 260px;
            z-index: 100;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            top: 0;
            left: 0;
            margin: 0;
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
            margin-left: 1rem;
            margin-top: 2rem;
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
            margin-left: 2.5rem;
            opacity: 0.8;
        }
        .nav-links {
            list-style: none;
        }
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.7rem 1rem;
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
        .main-content {
            grid-column: 2;
            padding: 1.5rem 2rem;
            /* margin-left: 260px; */
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
        .import-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .import-section h3 {
            margin-bottom: 15px;
            color: #333;
        }
        .file-format {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .file-format h4 {
            margin-bottom: 10px;
            color: #666;
        }
        .file-format pre {
            background: #fff;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            overflow-x: auto;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
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
                    <li><a href="view_stock.php" class="nav-link"><i class="fas fa-boxes"></i>Inventory</a></li>
                    <li><a href="views/add_income.php" class="nav-link"><i class="fas fa-money-bill-wave"></i>Revenue</a></li>
                    <li><a href="view_customer_history.php" class="nav-link"><i class="fas fa-history"></i>History</a></li>
                    <li><a href="import_data.php" class="nav-link active"><i class="fas fa-upload"></i>Import Data</a></li>
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
                <div class="page-title">
                    <h1>Import Data</h1>
                    <div class="breadcrumb">
                        <a href="../index.php">Dashboard</a>
                        <span>&gt;</span>
                        <span><?= $breadcrumb_page ?></span>
                        <span class="time-display"><?= $today ?> | <?= $current_time ?></span>
                    </div>
                </div>
            </div>

            <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?= $success_message ?>
            </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <?= $error_message ?>
            </div>
            <?php endif; ?>

            <div class="import-section">
                <h3>Import Rooms</h3>
                <div class="file-format">
                    <h4>CSV Format:</h4>
                    <pre>room_number,room_type,room_rate
101,Standard,100
102,Deluxe,150
103,Suite,200</pre>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="import_rooms">
                    <input type="file" name="rooms_file" accept=".csv" required>
                    <button type="submit" class="btn btn-primary">Import Rooms</button>
                </form>
            </div>

            <div class="import-section">
                <h3>Import Services</h3>
                <div class="file-format">
                    <h4>CSV Format:</h4>
                    <pre>service,description,price
Room Service,24/7 room service,50
Laundry,Laundry service,30
Spa,Spa treatment,100</pre>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="import_services">
                    <input type="file" name="services_file" accept=".csv" required>
                    <button type="submit" class="btn btn-primary">Import Services</button>
                </form>
            </div>

            <div class="import-section">
                <h3>Import Consumables</h3>
                <div class="file-format">
                    <h4>CSV Format:</h4>
                    <pre>item,service,unit,unit_price,cost_price
Soap,Bathroom,piece,5,3
Shampoo,Bathroom,bottle,15,10
Towel,Bathroom,piece,20,15</pre>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="import_consumables">
                    <input type="file" name="consumables_file" accept=".csv" required>
                    <button type="submit" class="btn btn-primary">Import Consumables</button>
                </form>
            </div>

            <div class="import-section">
                <h3>Import Stock</h3>
                <div class="file-format">
                    <h4>CSV Format:</h4>
                    <pre>item,quantity,unit_price,cost_price
Soap,100,5,3
Shampoo,50,15,10
Towel,200,20,15</pre>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="import_stock">
                    <input type="file" name="stock_file" accept=".csv" required>
                    <button type="submit" class="btn btn-primary">Import Stock</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html> 