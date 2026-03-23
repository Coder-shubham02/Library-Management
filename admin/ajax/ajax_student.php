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

// Get form data
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$dob = $_POST['dob'] ?? null;
$gender = $_POST['gender'] ?? null;
$address = trim($_POST['address'] ?? '');
$shift_id = intval($_POST['shift_id'] ?? 0);
$seat_id = intval($_POST['seat_id'] ?? 0);
$start_date = $_POST['start_date'] ?? date('Y-m-d');
$end_date = $_POST['end_date'] ?? null;
$remarks = trim($_POST['remarks'] ?? '');
$payment_status = $_POST['payment_status'] ?? 'pending';
$paid_amount = floatval($_POST['paid_amount'] ?? 0);
$payment_method = $_POST['payment_method'] ?? 'cash';
$transaction_id = trim($_POST['transaction_id'] ?? '');

// Validate required fields
if (empty($full_name) || empty($email) || empty($phone) || empty($shift_id) || empty($seat_id)) {
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

// Check if email already exists
$check_email = $conn->prepare("SELECT id FROM students WHERE email = ?");
$check_email->bind_param("s", $email);
$check_email->execute();
$check_email->store_result();

if ($check_email->num_rows > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email already registered'
    ]);
    $check_email->close();
    exit;
}
$check_email->close();

// Check if phone already exists
$check_phone = $conn->prepare("SELECT id FROM students WHERE phone = ?");
$check_phone->bind_param("s", $phone);
$check_phone->execute();
$check_phone->store_result();

if ($check_phone->num_rows > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Phone number already registered'
    ]);
    $check_phone->close();
    exit;
}
$check_phone->close();

// Check if seat is free for the entire period
$check_seat = $conn->prepare("
    SELECT sa.id FROM seat_allocations sa 
    WHERE sa.seat_id = ? 
    AND sa.status = 'active' 
    AND (
        (sa.start_date <= ? AND (sa.end_date IS NULL OR sa.end_date >= ?))
        OR
        (sa.start_date <= ? AND (sa.end_date IS NULL OR sa.end_date >= ?))
    )
");
$check_seat->bind_param("issss", $seat_id, $start_date, $start_date, $end_date, $end_date);
$check_seat->execute();
$check_seat->store_result();

if ($check_seat->num_rows > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Seat is already allocated for this period'
    ]);
    $check_seat->close();
    exit;
}
$check_seat->close();

// Begin transaction
$conn->begin_transaction();

try {
    // Handle file uploads
    $photo_path = null;
    $id_proof_path = null;
    
    // Upload photo if provided
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['photo']['type'], $allowed)) {
            $upload_dir = '../uploads/students/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $photo_path = 'uploads/students/' . time() . '_' . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], '../' . $photo_path);
        }
    }
    
    // Upload ID proof if provided
    if (isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] == 0) {
        $allowed = ['image/jpeg', 'image/png', 'application/pdf'];
        if (in_array($_FILES['id_proof']['type'], $allowed)) {
            $upload_dir = '../uploads/id_proofs/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $id_proof_path = 'uploads/id_proofs/' . time() . '_' . basename($_FILES['id_proof']['name']);
            move_uploaded_file($_FILES['id_proof']['tmp_name'], '../' . $id_proof_path);
        }
    }
    
    // Insert student
    $student_query = "INSERT INTO students (full_name, email, phone, dob, gender, address, photo, id_proof, status, created_by) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)";
    $student_stmt = $conn->prepare($student_query);
    $student_stmt->bind_param("ssssssssi", $full_name, $email, $phone, $dob, $gender, $address, $photo_path, $id_proof_path, $admin_id);
    $student_stmt->execute();
    $student_id = $conn->insert_id;
    $student_stmt->close();
    
    // Insert seat allocation
    $allocation_query = "INSERT INTO seat_allocations (seat_id, student_id, shift_id, allot_date, start_date, end_date, remarks, created_by, status) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";
    $allocation_stmt = $conn->prepare($allocation_query);
    $allocation_stmt->bind_param("iiiisssi", $seat_id, $student_id, $shift_id, $start_date, $start_date, $end_date, $remarks, $admin_id);
    $allocation_stmt->execute();
    $allocation_id = $conn->insert_id;
    $allocation_stmt->close();
    
    // Get shift fee amount
    $fee_query = "SELECT fee_amount FROM shifts WHERE id = ?";
    $fee_stmt = $conn->prepare($fee_query);
    $fee_stmt->bind_param("i", $shift_id);
    $fee_stmt->execute();
    $fee_result = $fee_stmt->get_result();
    $fee_row = $fee_result->fetch_assoc();
    $fee_amount = $fee_row['fee_amount'];
    $fee_stmt->close();
    
    // Calculate months between start date and today
    $start = new DateTime($start_date);
    $today = new DateTime();
    
    // Calculate full months difference
    $interval = $start->diff($today);
    $months = ($interval->y * 12) + $interval->m;
    
    // If day of month hasn't been reached yet in current month, subtract 1
    if ($today->format('d') < $start->format('d')) {
        $months--;
    }
    
    $months = max(0, $months); // Ensure non-negative
    
    // Store payment IDs
    $payment_ids = [];
    
    // Generate payments for each past month + current month
    for ($i = 0; $i <= $months; $i++) {
        // Create payment date
        $payment_date_obj = clone $start;
        $payment_date_obj->modify("+$i month");
        $payment_date = $payment_date_obj->format('Y-m-d');
        
        // Create due date (1 month after payment date)
        $due_date_obj = clone $payment_date_obj;
        $due_date_obj->modify("+1 month");
        $due_date = $due_date_obj->format('Y-m-d');
        
        $month_num = $payment_date_obj->format('m');
        $year_num = $payment_date_obj->format('Y');
        
        // Generate invoice number
        $invoice_number = "INV-" . $year_num . $month_num . str_pad($allocation_id, 6, '0', STR_PAD_LEFT) . "-" . ($i + 1);
        
        // Determine payment status for past months
        $past_payment_status = 'pending';
        $past_paid_amount = 0;
        
        // If this is the first month (current month) and user paid something
        if ($i == 0 && $paid_amount > 0) {
            $past_payment_status = $payment_status;
            $past_paid_amount = $paid_amount;
        }
        
        // ===== FIX: Assign all values to variables before bind_param =====
        $bind_allocation_id = $allocation_id;
        $bind_student_id = $student_id;
        $bind_invoice = $invoice_number;
        $bind_payment_date = $payment_date;
        $bind_due_date = $due_date;
        $bind_amount = $fee_amount;
        $bind_paid = $past_paid_amount;
        $bind_method = $payment_method;
        $bind_transaction = $transaction_id;
        $bind_status = $past_payment_status;
        $bind_month = intval($month_num);
        $bind_year = intval($year_num);
        $bind_admin = $admin_id;
        
        // Insert payment record
        $payment_query = "INSERT INTO payments 
                         (allocation_id, student_id, invoice_number, payment_date, due_date, amount, paid_amount, payment_method, transaction_id, payment_status, month, year, created_by) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $payment_stmt = $conn->prepare($payment_query);
        $payment_stmt->bind_param(
            "iissssdsssiii", 
            $bind_allocation_id, 
            $bind_student_id, 
            $bind_invoice, 
            $bind_payment_date, 
            $bind_due_date, 
            $bind_amount, 
            $bind_paid, 
            $bind_method, 
            $bind_transaction, 
            $bind_status, 
            $bind_month, 
            $bind_year, 
            $bind_admin
        );
        $payment_stmt->execute();
        
        // Get the inserted payment ID
        $payment_id = $conn->insert_id;
        $payment_ids[] = $payment_id;
        
        $payment_stmt->close();
        
        // If payment received, add to payment history
        if ($past_paid_amount > 0) {
            $history_query = "INSERT INTO payment_history (payment_id, amount, payment_date, payment_method, transaction_id, received_by) 
                              VALUES (?, ?, NOW(), ?, ?, ?)";
            $history_stmt = $conn->prepare($history_query);
            
            // ===== FIX: Assign history values to variables =====
            $hist_payment_id = $payment_id;
            $hist_amount = $past_paid_amount;
            $hist_method = $payment_method;
            $hist_transaction = $transaction_id;
            $hist_admin = $admin_id;
            
            $history_stmt->bind_param("idssi", $hist_payment_id, $hist_amount, $hist_method, $hist_transaction, $hist_admin);
            $history_stmt->execute();
            $history_stmt->close();
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Student registered successfully',
        'student_id' => $student_id,
        'allocation_id' => $allocation_id,
        'total_months' => $months + 1,
        'total_amount' => ($months + 1) * $fee_amount,
        'payment_ids' => $payment_ids
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>