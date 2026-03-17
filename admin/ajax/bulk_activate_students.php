<?php
require_once "../config/database.php";
session_start();

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit;
}

if (!isset($_POST['student_ids']) || empty($_POST['student_ids'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No students selected'
    ]);
    exit;
}

$student_ids = $_POST['student_ids'];
$placeholders = implode(',', array_fill(0, count($student_ids), '?'));
$types = str_repeat('i', count($student_ids));

$query = "UPDATE students SET status = 'active', updated_at = NOW() WHERE id IN ($placeholders)";
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$student_ids);

if ($stmt->execute()) {
    $affected = $stmt->affected_rows;
    echo json_encode([
        'status' => 'success',
        'message' => "$affected students activated successfully"
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to activate students'
    ]);
}

$stmt->close();
$conn->close();
?>