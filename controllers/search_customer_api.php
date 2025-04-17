<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

require_once '../classes/Database.php';
require_once '../classes/Customers.php';
require_once '../classes/Validation.php';

// Initialize classes
$db = new Database();
$customer = new Customers();
$validation = new Validation();

// Validate and sanitize input
if (isset($_GET['search_term']) && !empty($_GET['search_term'])) {
    $searchTerm = $validation->sanitizeInput($_GET['search_term']);
    
    // Use the existing searchCustomer method that's already working
    $results = $customer->searchCustomer($searchTerm);
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($results);
} else {
    // Return empty array if no search term provided
    header('Content-Type: application/json');
    echo json_encode([]);
}
?>