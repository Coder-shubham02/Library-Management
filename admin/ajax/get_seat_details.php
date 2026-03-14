<?php
require_once "../config/database.php";

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode(['error' => 'ID not provided']);
    exit;
}

$id = intval($_POST['id']);
$query = "SELECT * FROM seats WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Seat not found']);
}

$stmt->close();
$conn->close();
?>