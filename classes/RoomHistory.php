<?php
require_once 'Database.php';
class RoomHistory {
    private $pdo;
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }
    public function addRoomHistory($room_id, $guest_id, $check_in, $check_out, $num_persons, $num_children) {
        $sql = "
            INSERT INTO room_history (room_id, guest_id, check_in, check_out, number_of_persons, number_of_children)
            VALUES (:room_id, :guest_id, :check_in, :check_out, :num_persons, :num_children)
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':room_id' => $room_id,
            ':guest_id' => $guest_id,
            ':check_in' => $check_in,
            ':check_out' => $check_out,
            ':num_persons' => $num_persons,
            ':num_children' => $num_children,
        ]);
    }
    public function getHistoryByGuestId($guest_id) {
        $sql = "SELECT * FROM room_history WHERE guest_id = :guest_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':guest_id' => $guest_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
