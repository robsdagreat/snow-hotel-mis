<?php
require_once 'Database.php';
require_once 'Rooms.php'; 
class Customers{
    
    private $pdo;
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }
    
    public function addCustomer($guestName, $nationality, $idPassport, $arrivalDatetime, $departureDatetime, $roomId, $roomRate, $discount, $discountedRoomRate, $totalAmount, $numPersons, $numChildren, $modeOfPayment, $companyAgency, $emailAddress, $mobileNumber) {
        $sql = "INSERT INTO customers(
                    guest_name, 
                    nationality, 
                    id_passport, 
                    arrival_datetime, 
                    departure_datetime,
                    room_id, 
                    room_rate,
                    discount,
                    discounted_room_rate,
                    total_amount,
                    num_persons, 
                    num_children, 
                    mode_of_payment,
                    company_agency, 
                    email_address, 
                    mobile_number
                ) VALUES (
                    :guest_name, 
                    :nationality, 
                    :id_passport, 
                    :arrival_datetime, 
                    :departure_datetime,
                    :room_id, 
                    :room_rate, 
                    :discount,
                    :discounted_room_rate,
                    :total_amount,                    
                    :num_persons, 
                    :num_children, 
                    :mode_of_payment,
                    :company_agency, 
                    :email_address, 
                    :mobile_number
                )";
    
        $stmt = $this->pdo->prepare($sql);
    
        return $stmt->execute([
            ':guest_name' => $guestName,
            ':nationality' => $nationality,
            ':id_passport' => $idPassport,
            ':arrival_datetime' => $arrivalDatetime,
            ':departure_datetime' => $departureDatetime,
            ':room_id' => $roomId,
            ':room_rate' => $roomRate,
            ':discount' => $discount,
            ':discounted_room_rate' => $discountedRoomRate,
            ':total_amount' => $totalAmount,
            ':num_persons' => $numPersons,
            ':num_children' => $numChildren,
            ':mode_of_payment' => $modeOfPayment,
            ':company_agency' => $companyAgency,
            ':email_address' => $emailAddress,
            ':mobile_number' => $mobileNumber
        ]);
    }

    public function getTotalCustomers() {
        $sql = "SELECT COUNT(*) as total FROM customers";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }
    
    public function getPaginatedCustomers($limit, $offset) {
        $sql = "SELECT c.*, r.room_number FROM customers c 
                LEFT JOIN rooms r ON c.room_id = r.id 
                ORDER BY c.id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateCustomerHistory(
        $customerId, 
        $guestName, 
        $nationality, 
        $idPassport, 
        $arrivalDatetime, 
        $departureDatetime, 
        $roomNumber, 
        $roomRate, 
        $numPersons, 
        $numChildren, 
        $modeOfPayment, 
        $companyAgency, 
        $emailAddress, 
        $mobileNumber,
        $checkInTime, 
        $checkOutTime
    ) {
        $sql = "INSERT INTO customer_history (
            customer_id, 
            guest_name, 
            nationality, 
            id_passport, 
            arrival_datetime, 
            departure_datetime, 
            room_number, 
            room_rate, 
            num_persons, 
            num_children, 
            mode_of_payment, 
            company_agency, 
            email_address, 
            mobile_number, 
            check_in_time, 
            check_out_time
        ) VALUES (
            :customer_id, 
            :guest_name, 
            :nationality, 
            :id_passport, 
            :arrival_datetime, 
            :departure_datetime,
            :room_number, 
            :room_rate, 
            :num_persons, 
            :num_children, 
            :mode_of_payment, 
            :company_agency, 
            :email_address, 
            :mobile_number, 
            :check_in_time, 
            :check_out_time
        )";
    
        $stmt = $this->pdo->prepare($sql);
    
        return $stmt->execute([
            ':customer_id' => $customerId,
            ':guest_name' => $guestName,
            ':nationality' => $nationality,
            ':id_passport' => $idPassport,
            ':arrival_datetime' => $arrivalDatetime,
            ':departure_datetime' => $departureDatetime,
            ':room_number' => $roomNumber,
            ':room_rate' => $roomRate,
            ':num_persons' => $numPersons,
            ':num_children' => $numChildren,
            ':mode_of_payment' => $modeOfPayment,
            ':company_agency' => $companyAgency,
            ':email_address' => $emailAddress,
            ':mobile_number' => $mobileNumber,
            ':check_in_time' => $checkInTime,
            ':check_out_time' => $checkOutTime
        ]);
    }
    
    public function getAllCustomers() {
        $sql = "
            SELECT customers.*, rooms.room_number
            FROM customers
            JOIN rooms ON customers.room_id = rooms.id
            ORDER BY customers.id DESC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getCustomerById($id) {
        $sql = "
            SELECT c.*, r.room_number 
            FROM customers c
            LEFT JOIN rooms r ON c.room_id = r.id
            WHERE c.id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function checkoutCustomer($customer_id) {
        try {
            $this->pdo->beginTransaction();
    
            // Fetch the customer's details
            $sql = "SELECT * FROM customers WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $customer_id]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$customer) {
                throw new Exception("Customer not found.");
            }
    
            $room_id = $customer['room_id'];
            $room_number = $customer['room_number'];
            $check_out_time = date('Y-m-d H:i:s'); // Current time for check-out
    
            // Log the customer's check-out in customer_history
            $archiveSql = "INSERT INTO 
            customer_history (
                customer_id, 
                guest_name, 
                nationality, 
                id_passport, 
                arrival_datetime, 
                departure_datetime, 
                room_number, 
                room_rate, 
                num_persons, 
                num_children, 
                mode_of_payment, 
                company_agency, 
                email_address, 
                mobile_number, 
                check_in_time, 
                check_out_time
            )
            VALUES (
                :customer_id, 
                :guest_name, 
                :nationality, 
                :id_passport, 
                :arrival_datetime, 
                :departure_datetime,
                :room_number, 
                :room_rate, 
                :num_persons, 
                :num_children, 
                :mode_of_payment, 
                :company_agency, 
                :email_address, 
                :mobile_number, 
                :check_in_time, 
                :check_out_time
            )";
            $archiveStmt = $this->pdo->prepare($archiveSql);
            $archiveStmt->execute([
                ':customer_id' => $customer_id,
                ':guest_name' => $customer['guest_name'],
                ':nationality' => $customer['nationality'],
                ':id_passport' => $customer['id_passport'],
                ':arrival_datetime' => $customer['arrival_datetime'],
                ':departure_datetime' => $check_out_time, // Log current checkout time
                ':room_number' => $customer['room_number'],
                ':room_rate' => $customer['room_rate'],
                ':num_persons' => $customer['num_persons'],
                ':num_children' => $customer['num_children'],
                ':mode_of_payment' => $customer['mode_of_payment'],
                ':company_agency' => $customer['company_agency'],
                ':email_address' => $customer['email_address'],
                ':mobile_number' => $customer['mobile_number'],
                ':check_in_time' => $customer['arrival_datetime'],
                ':check_out_time' => $check_out_time
            ]);
    
            // Mark the room as available
            $rooms = new Rooms();
            $rooms->markRoomAvailable($room_id);
            
            // Log the room usage in room_history
            $rooms->updateRoomHistory($room_id, $customer_id, $customer['arrival_datetime'], $check_out_time, $customer['num_persons'], $customer['num_children'], $customer['room_rate'], $customer['mode_of_payment']);
    
            // Update the customer's status to 'checked out'
            $updateSql = "UPDATE customers 
                SET status = 0, departure_datetime = :checkout_time 
                WHERE id = :id";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([
                ':checkout_time' => $check_out_time,
                ':id' => $customer_id
            ]);
    
            $this->pdo->commit();
    
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    // Add these methods to your Customers.php file

public function getCustomerHistoryPaginated($offset, $limit) {
    $sql = "
        SELECT 
            c.id, 
            c.guest_name, 
            r.room_number, 
            c.arrival_datetime, 
            c.departure_datetime 
        FROM 
            customers c
        INNER JOIN 
            rooms r ON c.room_id = r.id
        WHERE 
            c.status = 0
        ORDER BY 
            c.departure_datetime DESC
        LIMIT :offset, :limit
    ";
    
    try {
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching paginated customer history: " . $e->getMessage());
    }
}

public function getTotalCustomerHistoryCount() {
    $sql = "
        SELECT 
            COUNT(*) as total
        FROM 
            customers
        WHERE 
            status = 0
    ";
    
    try {
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    } catch (PDOException $e) {
        die("Error counting customer history: " . $e->getMessage());
    }
}
    public function searchCustomer($term) {
        $sql = "
            SELECT c.id, c.guest_name, r.room_number, c.arrival_datetime, c.departure_datetime, 
                   c.email_address, c.mobile_number, c.room_id
            FROM customers c
            LEFT JOIN rooms r ON c.room_id = r.id
            WHERE c.guest_name LIKE :term 
            OR r.room_number LIKE :term 
            OR c.id_passport LIKE :term";
    
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':term' => "%$term%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Add this method to your Customers class
    public function getPendingCheckouts($days = 2) {
        $today = date('Y-m-d');
        $futureDate = date('Y-m-d', strtotime("+$days days"));
        
        $sql = "
            SELECT COUNT(*) as count
            FROM customers 
            WHERE DATE(departure_datetime) BETWEEN :today AND :futureDate
            AND status != 0
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':today' => $today,
            ':futureDate' => $futureDate
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
?>
