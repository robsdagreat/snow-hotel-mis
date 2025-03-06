<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}
?>
<?php
require_once '../classes/Services.php';
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
$services = new Services();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;
    $service = $_POST['service'] ?? '';
    if ($action === 'add_service' && !empty($service)) {
        if ($services->addService($service)) {
            header("Location: ../views/view_services.php?success=add");
            exit();
        } else {
            header("Location: ../views/add_service.php?error=add_failed");
            exit();
        }
    }
    if ($action === 'update_service' && !empty($id) && !empty($service)) {
        if ($services->updateService($id, $service)) {
            header("Location: ../views/view_services.php?success=update");
            exit();
        } else {
            header("Location: ../views/edit_service.php?id=$id&error=update_failed");
            exit();
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    if ($action === 'delete') {
        if ($services->deleteService($id)) {
            header("Location: ../views/view_services.php?success=delete");
            exit();
        } else {
            header("Location: ../views/view_services.php?error=delete_failed");
            exit();
        }
    }
}
header("Location: ../views/view_services.php");
exit();
?>
