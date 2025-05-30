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

// Handle AJAX requests
if ($_GET['action'] === 'get_stock' && isset($_GET['consumable_id'])) {
    $consumable_id = intval($_GET['consumable_id']);
    $stock_item = $stock->getStockByConsumableId($consumable_id); // Changed to getStockByConsumableId
    
    // Debug output
    error_log("Fetching stock for $consumable_id: " . print_r($stock_item, true));
    
    if ($stock_item) {
        echo json_encode([
            'success' => true,
            'quantity' => $stock_item['quantity'],
            'unit_price' => $stock_item['unit_price'], // Selling price
            'cost_price' => $stock_item['cost_price']  // Cost price
        ]);
    } else {
        error_log("No stock found for $consumable_id");
        echo json_encode([
            'success' => false,
            'message' => 'Stock item not found'
        ]);
    }
    exit;
}

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