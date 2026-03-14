<?php
require_once "../config/database.php";

header('Content-Type: application/json');

$id = intval($_POST['id']);
$seat_number = intval($_POST['seat_number']);
$room = trim($_POST['room']);
$status = trim($_POST['status']);

// Validate status (must be active or inactive)
if (!in_array($status, ['active', 'inactive'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid status value'
    ]);
    exit;
}

// Check if seat exists (excluding current)
$check_query = "SELECT id FROM seats WHERE seat_number = ? AND room = ? AND id != ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("isi", $seat_number, $room, $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Seat already exists in this room'
    ]);
    exit;
}

$query = "UPDATE seats SET seat_number = ?, room = ?, status = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("issi", $seat_number, $room, $status, $id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Seat updated successfully',
        'id' => $id,
        'seat_number' => $seat_number,
        'room' => $room,
        'status' => ucfirst($status)
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update seat: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>