<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../classes/Customers.php';
require_once '../classes/Rooms.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_customer') {
        $guestName = $_POST['guest_name'];
        $nationality = $_POST['nationality'];
        $idPassport = $_POST['id_passport'] ?? null;
        $arrivalDatetime = $_POST['arrival_datetime'];
        $departureDatetime = $_POST['departure_datetime'] ?? null;
        $roomId = $_POST['room_number'];
        $roomRate = $_POST['room_rate'];
        $discount = $_POST['discount'];
        $discountedRoomRate = $_POST['discounted_room_rate'];
        $totalAmount = $_POST['total_amount'];
        $numPersons = $_POST['num_persons'];
        $numChildren = $_POST['num_children'];
        $modeOfPayment = $_POST['mode_of_payment'] ?? null;
        $companyAgency = $_POST['company_agency'] ?? null;
        $emailAddress = $_POST['email_address'] ?? null;
        $mobileNumber = $_POST['mobile_number'] ?? null;
        
        $customers = new Customers();
        $rooms = new Rooms();
        
        $customerId = $customers->addCustomer(
            $guestName,
            $nationality,
            $idPassport,
            $arrivalDatetime,
            $departureDatetime,
            $roomId,
            $roomRate,
            $discount, 
            $discountedRoomRate, 
            $totalAmount,             
            $numPersons,
            $numChildren,
            $modeOfPayment,
            $companyAgency,
            $emailAddress,
            $mobileNumber
        );
        
        $customers->updateCustomerHistory(
            $customerId, 
            $guestName, 
            $nationality, 
            $idPassport, 
            $arrivalDatetime, 
            $departureDatetime, 
            $roomId, 
            $roomRate, 
            $numPersons, 
            $numChildren, 
            $modeOfPayment, 
            $companyAgency, 
            $emailAddress, 
            $mobileNumber,
            $arrivalDatetime,
            ""
        );
    
        if($customerId){
            $rooms->markRoomOccupied($roomId);
            $rooms->updateRoomHistory($roomId, $customerId, $arrivalDatetime, "", $numPersons, $numChildren, $roomRate, $modeOfPayment);
            header("Location: ../views/view_customers.php?success=1");
            exit();
        } 
        else{
            header("Location: ../views/add_customer.php?error=1");
            exit();
        }
    }
}
