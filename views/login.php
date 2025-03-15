<?php
session_start();
require_once '../classes/Database.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Get user from database
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user exists and password matches
    if ($user && $user['password'] === $password) { // Direct comparison for now
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect to dashboard
        header('Location: ../index.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Snow Hotel Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #5a5af1;
            --primary-dark: #4747c2;
            --primary-light: #8080ff;
            --accent: #ff6b6b;
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
            background-image: linear-gradient(135deg, #f4f6fc 0%, #e2e6f3 100%);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            line-height: 1.6;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
        }

        .brand-logo {
            font-size: 2.5rem;
            color: var(--primary);
            margin-right: 0.75rem;
        }

        .brand-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
        }

        .brand-name span {
            color: var(--primary);
        }

        .login-title {
            font-size: 1.25rem;
            font-weight: 500;
            color: var(--gray);
            margin-top: 0.5rem;
        }

        .version {
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 0.25rem;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .form-group {
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 2.75rem;
            font-size: 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(90, 90, 241, 0.15);
        }

        .submit-btn {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            padding: 0.9rem;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin-top: 0.75rem;
        }

        .submit-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .error-message {
            background-color: rgba(244, 67, 54, 0.1);
            color: #d32f2f;
            padding: 0.75rem;
            border-radius: var(--radius);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .error-message i {
            font-size: 1rem;
        }

        .login-footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.875rem;
            color: var(--gray);
        }

        .form-help {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        .form-help a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.2s;
        }

        .form-help a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="brand">
                <i class="fas fa-snowflake brand-logo"></i>
                <div class="brand-name">Snow <span>Hotel</span></div>
            </div>
            <div class="login-title">Management Information System</div>
            <div class="version">Version 1.0</div>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
                <?
            print($_SESSION['user_id']);
            print($_SESSION['username']);
            print($_SESSION['role']);
            print($_SESSION['password']);
                ?>

            </div>
        <?php endif; ?>

        <form method="POST" class="login-form">
            <div class="form-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" class="form-control" placeholder="Username" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" class="form-control" placeholder="Password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="submit-btn">
                Sign In <i class="fas fa-arrow-right" style="margin-left: 5px;"></i>
            </button>
        </form>

        <div class="login-footer">
            &copy; <?= date('Y') ?> Snow Hotel Management System. All rights reserved.
        </div>
    </div>
</body>
</html>