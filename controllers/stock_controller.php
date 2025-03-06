<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../classes/Stock.php';
$stock = new Stock();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $consumable_id = $_POST['consumable_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 0;
    $description = $_POST['description'] ?? null;
    $unit_price = $_POST['unit_price'] ?? null;
    $transaction_type = $_POST['transaction_type'] ?? '';
    if ($consumable_id && $quantity > 0 && in_array($transaction_type, ['purchase', 'consumption'])) {
        if ($stock->updateStock($consumable_id, $quantity, $description, $unit_price, $transaction_type)) {
            header("Location: ../views/view_stock.php?success=1");
            exit();
        } else {
            header("Location: ../views/manage_stock.php?error=1");
            exit();
        }
    } else {
        header("Location: ../views/manage_stock.php?error=invalid_input");
        exit();
    }
}
