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
    
    public function getCustomerHistory() {
        $sql = "
            SELECT 
                c.id, 
                c.guest_name, 
                r.room_number, 
                c.arrival_datetime, 
                c.departure_datetime,
                c.total_amount
            FROM 
                customers c
            INNER JOIN 
                rooms r ON c.room_id = r.id
            WHERE 
                c.status = 0
            ORDER BY 
                c.departure_datetime DESC
        ";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching customer history: " . $e->getMessage());
            return [];
        }
    }

public function getCustomerHistoryPaginated($offset, $limit, $filters = []) {
    $sql = "SELECT * FROM customer_history WHERE 1=1";
    $params = [];

    // Add date range filter
    if (!empty($filters['start_date'])) {
        $sql .= " AND DATE(check_in_time) >= :start_date";
        $params[':start_date'] = $filters['start_date'];
    }
    if (!empty($filters['end_date'])) {
        $sql .= " AND DATE(check_in_time) <= :end_date";
        $params[':end_date'] = $filters['end_date'];
    }

    // Add sorting
    $sql .= " ORDER BY check_in_time DESC";
    
    // Add pagination using named parameters
    $sql .= " LIMIT :limit OFFSET :offset";
    $params[':limit'] = (int)$limit;
    $params[':offset'] = (int)$offset;

    $stmt = $this->pdo->prepare($sql);
    
    // Bind all parameters at once
    foreach ($params as $key => $value) {
        if (in_array($key, [':limit', ':offset'])) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getTotalCustomerHistoryCount($filters = []) {
    $sql = "SELECT COUNT(*) as total FROM customer_history WHERE 1=1";
    $params = [];

    // Add date range filter
    if (!empty($filters['start_date'])) {
        $sql .= " AND DATE(check_in_time) >= :start_date";
        $params[':start_date'] = $filters['start_date'];
    }
    if (!empty($filters['end_date'])) {
        $sql .= " AND DATE(check_in_time) <= :end_date";
        $params[':end_date'] = $filters['end_date'];
    }

    $stmt = $this->pdo->prepare($sql);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)$result['total'];
}
    public function searchCustomer($params = []) {
    $sql = "
        SELECT 
            c.id, 
            c.guest_name, 
            r.room_number, 
            c.arrival_datetime, 
            c.departure_datetime,
            c.email_address, 
            c.mobile_number, 
            c.nationality,
            c.status,
            c.company_agency,
            c.mode_of_payment
        FROM customers c
        LEFT JOIN rooms r ON c.room_id = r.id
        WHERE 1=1
        ";
        
        $conditions = [];
        $parameters = [];
        
        // Search term for name, room number, or ID/passport
        if (!empty($params['search_term'])) {
            $conditions[] = "(c.guest_name LIKE :search_term 
                            OR r.room_number LIKE :search_term 
                            OR c.id_passport LIKE :search_term
                            OR c.mobile_number LIKE :search_term)";
            $parameters[':search_term'] = "%" . $params['search_term'] . "%";
        }
        
        // Filter by status
        if (isset($params['status']) && $params['status'] !== '') {
            $conditions[] = "c.status = :status";
            $parameters[':status'] = $params['status'];
        }
        
        // Filter by nationality
        if (!empty($params['nationality'])) {
            $conditions[] = "c.nationality LIKE :nationality";
            $parameters[':nationality'] = "%" . $params['nationality'] . "%";
        }
        
        // Filter by date range
        if (!empty($params['date_from'])) {
            $conditions[] = "c.arrival_datetime >= :date_from";
            $parameters[':date_from'] = $params['date_from'] . " 00:00:00";
        }
        if (!empty($params['date_to'])) {
            $conditions[] = "c.departure_datetime <= :date_to";
            $parameters[':date_to'] = $params['date_to'] . " 23:59:59";
        }
        
        // Filter by payment mode
        if (!empty($params['payment_mode'])) {
            $conditions[] = "c.mode_of_payment = :payment_mode";
            $parameters[':payment_mode'] = $params['payment_mode'];
        }
        
        // Filter by room type
        if (!empty($params['room_type'])) {
            $conditions[] = "r.room_type = :room_type";
            $parameters[':room_type'] = $params['room_type'];
        }
        
        // Filter by payment status
        if (!empty($params['payment_status'])) {
            switch($params['payment_status']) {
                case 'paid':
                    $conditions[] = "c.payment_status = 'paid'";
                    break;
                case 'partial':
                    $conditions[] = "c.payment_status = 'partial'";
                    break;
                case 'pending':
                    $conditions[] = "c.payment_status = 'pending'";
                    break;
            }
        }
        
        // Filter by booking source
        if (!empty($params['booking_source'])) {
            $conditions[] = "c.booking_source = :booking_source";
            $parameters[':booking_source'] = $params['booking_source'];
        }
        
        // Filter by stay duration
        if (!empty($params['stay_duration'])) {
            switch($params['stay_duration']) {
                case '1':
                    $conditions[] = "DATEDIFF(c.departure_datetime, c.arrival_datetime) = 1";
                    break;
                case '2-3':
                    $conditions[] = "DATEDIFF(c.departure_datetime, c.arrival_datetime) BETWEEN 2 AND 3";
                    break;
                case '4-7':
                    $conditions[] = "DATEDIFF(c.departure_datetime, c.arrival_datetime) BETWEEN 4 AND 7";
                    break;
                case '8+':
                    $conditions[] = "DATEDIFF(c.departure_datetime, c.arrival_datetime) >= 8";
                    break;
            }
        }
        
        // Add conditions to SQL
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
        
        // Add sorting
        $sql .= " ORDER BY c.arrival_datetime DESC";
        
        // Add pagination
        if (isset($params['limit']) && isset($params['offset'])) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $parameters[':limit'] = (int)$params['limit'];
            $parameters[':offset'] = (int)$params['offset'];
        }
    
    try {
        $stmt = $this->pdo->prepare($sql);
            foreach ($parameters as $key => $value) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
            error_log("Error searching customers: " . $e->getMessage());
            return [];
    }
}

    public function getCustomerSearchCount($params = []) {
    $sql = "
            SELECT COUNT(*) as total
            FROM customers c
            LEFT JOIN rooms r ON c.room_id = r.id
            WHERE 1=1
        ";
        
        $conditions = [];
        $parameters = [];
        
        // Search term for name, room number, or ID/passport
        if (!empty($params['search_term'])) {
            $conditions[] = "(c.guest_name LIKE :search_term 
                            OR r.room_number LIKE :search_term 
                            OR c.id_passport LIKE :search_term
                            OR c.mobile_number LIKE :search_term)";
            $parameters[':search_term'] = "%" . $params['search_term'] . "%";
        }
        
        // Filter by status
        if (isset($params['status']) && $params['status'] !== '') {
            $conditions[] = "c.status = :status";
            $parameters[':status'] = $params['status'];
        }
        
        // Filter by nationality
        if (!empty($params['nationality'])) {
            $conditions[] = "c.nationality LIKE :nationality";
            $parameters[':nationality'] = "%" . $params['nationality'] . "%";
        }
        
        // Filter by date range
        if (!empty($params['date_from'])) {
            $conditions[] = "c.arrival_datetime >= :date_from";
            $parameters[':date_from'] = $params['date_from'] . " 00:00:00";
        }
        if (!empty($params['date_to'])) {
            $conditions[] = "c.departure_datetime <= :date_to";
            $parameters[':date_to'] = $params['date_to'] . " 23:59:59";
        }
        
        // Filter by payment mode
        if (!empty($params['payment_mode'])) {
            $conditions[] = "c.mode_of_payment = :payment_mode";
            $parameters[':payment_mode'] = $params['payment_mode'];
        }
        
        // Filter by room type
        if (!empty($params['room_type'])) {
            $conditions[] = "r.room_type = :room_type";
            $parameters[':room_type'] = $params['room_type'];
        }
        
        // Filter by payment status
        if (!empty($params['payment_status'])) {
            switch($params['payment_status']) {
                case 'paid':
                    $conditions[] = "c.payment_status = 'paid'";
                    break;
                case 'partial':
                    $conditions[] = "c.payment_status = 'partial'";
                    break;
                case 'pending':
                    $conditions[] = "c.payment_status = 'pending'";
                    break;
            }
        }
        
        // Filter by booking source
        if (!empty($params['booking_source'])) {
            $conditions[] = "c.booking_source = :booking_source";
            $parameters[':booking_source'] = $params['booking_source'];
        }
        
        // Filter by stay duration
        if (!empty($params['stay_duration'])) {
            switch($params['stay_duration']) {
                case '1':
                    $conditions[] = "DATEDIFF(c.departure_datetime, c.arrival_datetime) = 1";
                    break;
                case '2-3':
                    $conditions[] = "DATEDIFF(c.departure_datetime, c.arrival_datetime) BETWEEN 2 AND 3";
                    break;
                case '4-7':
                    $conditions[] = "DATEDIFF(c.departure_datetime, c.arrival_datetime) BETWEEN 4 AND 7";
                    break;
                case '8+':
                    $conditions[] = "DATEDIFF(c.departure_datetime, c.arrival_datetime) >= 8";
                    break;
            }
        }
        
        // Add conditions to SQL
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($parameters as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    } catch (PDOException $e) {
            error_log("Error counting customer search results: " . $e->getMessage());
            return 0;
        }
    }

    public function getMonthlyRevenue($month = null, $year = null) {
        // If no month/year provided, use current month and year
        $month = $month ?? date('m');
        $year = $year ?? date('Y');
    
        $sql = "
            SELECT 
                COALESCE(SUM(total_amount), 0) as monthly_revenue
            FROM 
                customers
            WHERE 
                status = 0 
                AND MONTH(departure_datetime) = :month 
                AND YEAR(departure_datetime) = :year
        ";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['monthly_revenue'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error calculating monthly revenue: " . $e->getMessage());
            return 0;
        }
    }

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

    public function getCustomerIncomes($customer_id) {
        require_once __DIR__ . '/Income.php';
        $income = new Income();
        return $income->getIncomesByCustomerId($customer_id);
    }

    public function searchActiveCustomers($search_term) {
        $sql = "
            SELECT 
                c.id, 
                c.guest_name, 
                r.room_number, 
                c.mobile_number
            FROM customers c
            LEFT JOIN rooms r ON c.room_id = r.id
            WHERE c.status = 1 
            AND (
                c.guest_name LIKE :search_term 
                OR r.room_number LIKE :search_term 
                OR c.id_passport LIKE :search_term
                OR c.mobile_number LIKE :search_term
            )
            ORDER BY c.guest_name ASC
            LIMIT 10
        ";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':search_term' => "%" . $search_term . "%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching active customers: " . $e->getMessage());
            return [];
        }
    }
}
?>
