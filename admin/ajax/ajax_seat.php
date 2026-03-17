<?php
error_reporting(0);
ini_set('display_errors', 0);

require "../config/database.php";
header('Content-Type: application/json');

$seat = intval($_POST['seat_number']);
$room = trim($_POST['room']);
$shift_id = intval($_POST['shift_id']);

try {

    $stmt = $conn->prepare("INSERT INTO seats (seat_number, room, shift_id, status) VALUES (?, ?, ?, 'active')");
    $stmt->bind_param("isi", $seat, $room, $shift_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Seat successfully added!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: The query could not be executed.']);
    }
} catch (mysqli_sql_exception $e) {
    
    echo json_encode(['status' => 'error', 'message' => 'Warning: This seat is already registered in the shift!']);
}

$conn->close();
exit;
?>