<?php
require_once '../classes/Customers.php';
header('Content-Type: application/json');
$term = $_GET['search_term'] ?? '';
$customers = new Customers();
$results = $term ? $customers->searchCustomer($term) : [];
echo json_encode($results);
?>
