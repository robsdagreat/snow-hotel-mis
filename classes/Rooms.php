<?php
require_once 'Database.php';
class Rooms {
    private $pdo;
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }
    public function updateRoomHistory(
        $room_id, 
        $guest_id, 
        $check_in, 
        $check_out, 
        $number_of_persons, 
        $number_of_children, 
        $total_payment, 
        $payment_mode) {
            
        $sql = "INSERT INTO room_history(
                    
                    room_id, 
                    guest_id, 
                    check_in, 
                    check_out, 
                    number_of_persons, 
                    number_of_children, 
                    total_payment, 
                    payment_mode
                ) VALUES (
                    :room_id, 
                    :guest_id, 
                    :check_in, 
                    :check_out, 
                    :number_of_persons, 
                    :number_of_children, 
                    :total_payment, 
                    :payment_mode
                )";
    
        $stmt = $this->pdo->prepare($sql);
    
        return $stmt->execute([
            ':room_id' => $room_id,
            ':guest_id' => $guest_id,
            ':check_in' => $check_in,
            ':check_out' => $check_out,
            ':number_of_persons' => $number_of_persons,
            ':number_of_children' => $number_of_children,
            ':total_payment' => $total_payment,
            ':payment_mode' => $payment_mode
        ]);
    }
    
    public function markRoomOccupied($roomId) {
        $sql = "UPDATE rooms SET is_available = 0 WHERE id = :room_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':room_id' => $roomId]);
    }
    public function markRoomAvailable($roomId) {
        $sql = "UPDATE rooms SET is_available = 1 WHERE id = :room_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':room_id' => $roomId]);
    }
    
    public function getAvailableRooms() {
        $sql = "SELECT id, room_number, room_rate FROM rooms WHERE is_available = 1";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllRoomsWithStatus() {
        $sql = "SELECT id, room_number, room_rate, is_available FROM rooms ORDER BY room_number ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRoomRate($room_id, $room_rate) {
        $sql = "UPDATE rooms SET room_rate = :room_rate WHERE id = :room_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':room_rate' => $room_rate,
            ':room_id' => $room_id
        ]);
    }
}
?>
