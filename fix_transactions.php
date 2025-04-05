<?php
require_once 'config/database.php';
require_once 'classes/Stock.php';

// Initialize Stock class
$stock = new Stock();

// Fix invalid dates
if ($stock->fixInvalidDates()) {
    echo "Successfully fixed invalid dates.\n";
} else {
    echo "Error fixing invalid dates.\n";
}

// Fix invalid service IDs
if ($stock->fixInvalidServiceIds()) {
    echo "Successfully fixed invalid service IDs.\n";
} else {
    echo "Error fixing invalid service IDs.\n";
}

// Show updated records
$sql = "SELECT 
    st.id,
    st.consumable_id,
    c.item,
    st.transaction_type,
    st.quantity,
    st.description,
    st.service_id,
    st.unit_price,
    st.total_value,
    st.transaction_datetime,
    st.last_updated
FROM 
    stock_transactions st
JOIN 
    consumables c ON st.consumable_id = c.id
WHERE 
    st.transaction_type = 'consumption'
ORDER BY 
    st.transaction_datetime DESC";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    echo "\nUpdated Consumption Records:\n";
    echo str_repeat("-", 100) . "\n";
    echo sprintf("%-5s %-30s %-12s %-10s %-20s %-25s\n", 
        "ID", "Item", "Service ID", "Quantity", "Transaction Date", "Description");
    echo str_repeat("-", 100) . "\n";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%-5s %-30s %-12s %-10s %-20s %-25s\n",
            $row['id'],
            substr($row['item'], 0, 28),
            $row['service_id'],
            $row['quantity'],
            $row['transaction_datetime'],
            substr($row['description'], 0, 23)
        );
    }
} catch (PDOException $e) {
    echo "Error showing updated records: " . $e->getMessage() . "\n";
}
?> 