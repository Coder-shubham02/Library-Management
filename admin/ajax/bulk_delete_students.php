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

// Start transaction
$conn->begin_transaction();

try {
    // Get all files to delete
    $file_query = "SELECT photo, id_proof FROM students WHERE id IN ($placeholders)";
    $file_stmt = $conn->prepare($file_query);
    $file_stmt->bind_param($types, ...$student_ids);
    $file_stmt->execute();
    $file_result = $file_stmt->get_result();
    
    // Delete physical files
    while ($row = $file_result->fetch_assoc()) {
        if (!empty($row['photo']) && file_exists('../' . $row['photo'])) {
            unlink('../' . $row['photo']);
        }
        if (!empty($row['id_proof']) && file_exists('../' . $row['id_proof'])) {
            unlink('../' . $row['id_proof']);
        }
    }
    $file_stmt->close();
    
    // Delete students (cascading will delete related records)
    $delete_query = "DELETE FROM students WHERE id IN ($placeholders)";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param($types, ...$student_ids);
    
    if (!$delete_stmt->execute()) {
        throw new Exception("Failed to delete students");
    }
    
    $affected = $delete_stmt->affected_rows;
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => "$affected students deleted successfully"
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to delete students: ' . $e->getMessage()
    ]);
}

$delete_stmt->close();
$conn->close();
?>