<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}

require_once '../classes/Services.php';
$id = $_GET['id'] ?? null;
$services = new Services();
$service = $services->getServiceById($id);
if (!$service) {
    header("Location: view_services.php?error=not_found");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service - Snow Hotel</title>
    <link rel="stylesheet" href="../styles/navbar.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #5052db;
            text-align: center;
            margin-bottom: 30px;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button[type="submit"] {
            background-color: #5052db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button[type="submit"]:hover {
            background-color: #4041b0;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <h2>Edit Service</h2>
        <form action="../controllers/services_controller.php" method="POST">
            <input type="hidden" name="action" value="update_service">
            <input type="hidden" name="id" value="<?= $service['id'] ?>">
            <div class="form-group">
                <label for="service">Service Name:</label>
                <input type="text" id="service" name="service" value="<?= htmlspecialchars($service['service']) ?>" required>
            </div>
            <div class="form-group" style="text-align: center;">
                <button type="submit">Update Service</button>
            </div>
        </form>
    </div>
</body>
</html>