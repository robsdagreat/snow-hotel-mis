<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}
?>
<?php
require_once '../classes/Consumables.php';
require_once '../classes/Validation.php';
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
$consumables = new Consumables();
$validator = new Validation();
// Handle ADD action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_consumable') {
    $item = $_POST['item'] ?? '';
    $service_id = $_POST['service_id'] ?? '';
    $unit = $_POST['unit'] ?? '';
    $unit_price = $_POST['unit_price'] ?? '';
    // Validate inputs
    $errors = $validator->validateConsumable($item, $service_id, $unit, $unit_price);
    if (empty($errors)) {
        if ($consumables->addConsumable($item, $service_id, $unit, $unit_price)) {
            header("Location: ../views/view_consumables.php?success=1");
            exit();
        } else {
            header("Location: ../views/add_consumable.php?error=add_failed");
            exit();
        }
    } else {
        header("Location: ../views/add_consumable.php?error=" . urlencode(json_encode($errors)));
        exit();
    }
}
// Handle UPDATE action
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_consumable') {
    $id = $_POST['id'] ?? null;
    $item = $_POST['item'] ?? '';
    $service_id = $_POST['service_id'] ?? '';
    $unit = $_POST['unit'] ?? '';
    $unit_price = $_POST['unit_price'] ?? '';    
    // Validate inputs
    $errors = $validator->validateConsumable($item, $service_id, $unit, $unit_price);
    if (empty($errors)) {
        if ($consumables->updateConsumable($id, $item, $service_id, $unit, $unit_price)) {
            header("Location: ../views/view_consumables.php?success=1");
            exit();
        } else {
            header("Location: ../views/edit_consumable.php?id=$id&error=update_failed");
            exit();
        }
    } else {
        header("Location: ../views/edit_consumable.php?id=$id&error=" . urlencode(json_encode($errors)));
        exit();
    }
}
// Handle DELETE action
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = $_GET['id'] ?? null;
    if ($id && $consumables->deleteConsumable($id)) {
        header("Location: ../views/view_consumables.php?success=1");
        exit();
    } else {
        header("Location: ../views/view_consumables.php?error=delete_failed");
        exit();
    }
}
// Redirect to the default page if no valid action
else {
    header("Location: ../views/add_consumable.php");
    exit();
}
