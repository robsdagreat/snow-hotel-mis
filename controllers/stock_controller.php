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

// Check if a consumable_id is provided for viewing stock history
if (isset($_GET['consumable_id'])) {
    $consumable_id = intval($_GET['consumable_id']);
    
    // Fetch consumable details
    $consumable = $stock->getConsumableDetails($consumable_id);
    $consumable_name = $consumable['item'];
    
    // Fetch stock history for this consumable
    $stock_history = $stock->getStockHistory($consumable_id);
    
    // Prepare data to pass to the view
    $data = [
        'consumable' => $consumable,
        'consumable_name' => $consumable_name,
        'stock_history' => $stock_history
    ];
    
    // Include the view
    require_once '../views/view_stock_history.php';
    exit;
}

// Redirect if no consumable_id is provided
header('Location: view_stock.php');
exit;