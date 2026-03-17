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

if (!isset($_POST['student_id']) || empty($_POST['student_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Student ID is required'
    ]);
    exit;
}

$student_id = intval($_POST['student_id']);

// Start transaction
$conn->begin_transaction();

try {
    // Get student files to delete
    $file_query = "SELECT photo, id_proof FROM students WHERE id = ?";
    $file_stmt = $conn->prepare($file_query);
    $file_stmt->bind_param("i", $student_id);
    $file_stmt->execute();
    $file_result = $file_stmt->get_result();
    $files = $file_result->fetch_assoc();
    $file_stmt->close();

    // Delete student (cascading will delete allocations, payments, etc.)
    $delete_query = "DELETE FROM students WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $student_id);
    
    if (!$delete_stmt->execute()) {
        throw new Exception("Failed to delete student");
    }
    
    // Delete physical files
    if (!empty($files['photo']) && file_exists('../' . $files['photo'])) {
        unlink('../' . $files['photo']);
    }
    
    if (!empty($files['id_proof']) && file_exists('../' . $files['id_proof'])) {
        unlink('../' . $files['id_proof']);
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Student deleted successfully'
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to delete student: ' . $e->getMessage()
    ]);
}

$delete_stmt->close();
$conn->close();
?>