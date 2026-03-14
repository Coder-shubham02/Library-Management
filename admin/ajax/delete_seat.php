<?php
require_once "../config/database.php";

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID not provided'
    ]);
    exit;
}

$id = intval($_POST['id']);

$query = "DELETE FROM seats WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Seat deleted successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to delete seat: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>