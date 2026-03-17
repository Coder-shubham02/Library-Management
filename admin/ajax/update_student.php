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

$admin_id = $_SESSION['admin_id'];

// Validate required fields
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Student ID is required'
    ]);
    exit;
}

$student_id = intval($_POST['id']);
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$dob = $_POST['dob'] ?? null;
$gender = $_POST['gender'] ?? null;
$address = trim($_POST['address'] ?? '');
$status = $_POST['status'] ?? 'active';

// Seat Allocation Fields
$seat_id = isset($_POST['seat_id']) ? intval($_POST['seat_id']) : null;
$shift_id = isset($_POST['shift_id']) ? intval($_POST['shift_id']) : null;
$start_date = $_POST['start_date'] ?? date('Y-m-d');
$end_date = $_POST['end_date'] ?? null;
$allocation_remarks = trim($_POST['allocation_remarks'] ?? '');

// Check if we need to remove current allocation
$remove_allocation = isset($_POST['remove_allocation']) && $_POST['remove_allocation'] == '1';

// Validate required fields
if (empty($full_name) || empty($email) || empty($phone)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please fill all required fields'
    ]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Validate phone number
if (!preg_match('/^[0-9]{10}$/', $phone)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Phone number must be 10 digits'
    ]);
    exit;
}

// Check if email already exists for another student
$check_email = $conn->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
$check_email->bind_param("si", $email, $student_id);
$check_email->execute();
$check_email->store_result();

if ($check_email->num_rows > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email already registered to another student'
    ]);
    $check_email->close();
    exit;
}
$check_email->close();

// Check if phone already exists for another student
$check_phone = $conn->prepare("SELECT id FROM students WHERE phone = ? AND id != ?");
$check_phone->bind_param("si", $phone, $student_id);
$check_phone->execute();
$check_phone->store_result();

if ($check_phone->num_rows > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Phone number already registered to another student'
    ]);
    $check_phone->close();
    exit;
}
$check_phone->close();

// If seat is selected, check if it's available
if ($seat_id && !$remove_allocation) {
    $check_seat = $conn->prepare("
        SELECT sa.id FROM seat_allocations sa 
        WHERE sa.seat_id = ? 
        AND sa.student_id != ?
        AND sa.status = 'active' 
        AND (
            (sa.start_date <= ? AND (sa.end_date IS NULL OR sa.end_date >= ?))
        )
    ");
    $check_seat->bind_param("iiss", $seat_id, $student_id, $start_date, $start_date);
    $check_seat->execute();
    $check_seat->store_result();

    if ($check_seat->num_rows > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Selected seat is already allocated to another student for this period'
        ]);
        $check_seat->close();
        exit;
    }
    $check_seat->close();
}

// Start transaction
$conn->begin_transaction();

try {
    // Handle file uploads
    $photo_path = null;
    $id_proof_path = null;

    // Get current student data
    $current_query = "SELECT photo, id_proof FROM students WHERE id = ?";
    $current_stmt = $conn->prepare($current_query);
    $current_stmt->bind_param("i", $student_id);
    $current_stmt->execute();
    $current_result = $current_stmt->get_result();
    $current_data = $current_result->fetch_assoc();
    $current_stmt->close();

    // Upload photo if provided
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['photo']['type'], $allowed)) {
            $upload_dir = '../uploads/students/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Delete old photo if exists and not using remove flag
            if (!empty($current_data['photo']) && file_exists('../' . $current_data['photo']) && !isset($_POST['remove_photo'])) {
                unlink('../' . $current_data['photo']);
            }
            
            $photo_path = 'uploads/students/' . time() . '_' . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], '../' . $photo_path);
        }
    } elseif (isset($_POST['remove_photo']) && $_POST['remove_photo'] == '1') {
        // Remove photo if requested
        if (!empty($current_data['photo']) && file_exists('../' . $current_data['photo'])) {
            unlink('../' . $current_data['photo']);
        }
        $photo_path = null;
    }

    // Upload ID proof if provided
    if (isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] == 0) {
        $allowed = ['image/jpeg', 'image/png', 'application/pdf'];
        if (in_array($_FILES['id_proof']['type'], $allowed)) {
            $upload_dir = '../uploads/id_proofs/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Delete old ID proof if exists and not using remove flag
            if (!empty($current_data['id_proof']) && file_exists('../' . $current_data['id_proof']) && !isset($_POST['remove_id_proof'])) {
                unlink('../' . $current_data['id_proof']);
            }
            
            $id_proof_path = 'uploads/id_proofs/' . time() . '_' . basename($_FILES['id_proof']['name']);
            move_uploaded_file($_FILES['id_proof']['tmp_name'], '../' . $id_proof_path);
        }
    } elseif (isset($_POST['remove_id_proof']) && $_POST['remove_id_proof'] == '1') {
        // Remove ID proof if requested
        if (!empty($current_data['id_proof']) && file_exists('../' . $current_data['id_proof'])) {
            unlink('../' . $current_data['id_proof']);
        }
        $id_proof_path = null;
    }

    // Build update query dynamically
    $updates = [];
    $params = [];
    $types = "";

    $updates[] = "full_name = ?";
    $params[] = $full_name;
    $types .= "s";

    $updates[] = "email = ?";
    $params[] = $email;
    $types .= "s";

    $updates[] = "phone = ?";
    $params[] = $phone;
    $types .= "s";

    $updates[] = "dob = ?";
    $params[] = $dob;
    $types .= "s";

    $updates[] = "gender = ?";
    $params[] = $gender;
    $types .= "s";

    $updates[] = "address = ?";
    $params[] = $address;
    $types .= "s";

    $updates[] = "status = ?";
    $params[] = $status;
    $types .= "s";

    if ($photo_path !== null) {
        $updates[] = "photo = ?";
        $params[] = $photo_path;
        $types .= "s";
    }

    if ($id_proof_path !== null) {
        $updates[] = "id_proof = ?";
        $params[] = $id_proof_path;
        $types .= "s";
    }

    $updates[] = "updated_at = NOW()";

    // Add student_id to params
    $params[] = $student_id;
    $types .= "i";

    $query = "UPDATE students SET " . implode(", ", $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update student: " . $stmt->error);
    }
    $stmt->close();

    // Handle seat allocation
    if ($remove_allocation) {
        // End current active allocation
        $end_allocation = $conn->prepare("
            UPDATE seat_allocations 
            SET status = 'completed', end_date = ?, updated_at = NOW() 
            WHERE student_id = ? AND status = 'active'
        ");
        $end_date_val = date('Y-m-d');
        $end_allocation->bind_param("si", $end_date_val, $student_id);
        $end_allocation->execute();
        $end_allocation->close();
    } 
    elseif ($seat_id && $shift_id) {
        // First, end any current active allocation
        $end_current = $conn->prepare("
            UPDATE seat_allocations 
            SET status = 'completed', end_date = ?, updated_at = NOW() 
            WHERE student_id = ? AND status = 'active'
        ");
        $end_date_val = date('Y-m-d', strtotime('-1 day', strtotime($start_date)));
        $end_current->bind_param("si", $end_date_val, $student_id);
        $end_current->execute();
        $end_current->close();

        // Create new allocation
        $allocation_query = "INSERT INTO seat_allocations 
                            (seat_id, student_id, shift_id, allot_date, start_date, end_date, remarks, created_by, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        $allocation_stmt = $conn->prepare($allocation_query);
        $allot_date = date('Y-m-d');
        $allocation_stmt->bind_param("iiissssi", $seat_id, $student_id, $shift_id, $allot_date, $start_date, $end_date, $allocation_remarks, $admin_id);
        
        if (!$allocation_stmt->execute()) {
            throw new Exception("Failed to create seat allocation: " . $allocation_stmt->error);
        }
        $allocation_stmt->close();
    }

    // Commit transaction
    $conn->commit();

    // Get updated data for response
    $response_data = [
        'id' => $student_id,
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'status' => $status,
        'seat_id' => $seat_id,
        'shift_id' => $shift_id
    ];

    echo json_encode([
        'status' => 'success',
        'message' => 'Student updated successfully',
        'data' => $response_data
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>