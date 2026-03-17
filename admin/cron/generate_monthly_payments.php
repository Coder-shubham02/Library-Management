<?php
// cron/generate_monthly_payments.php
// Run this script daily via cron job

require_once "../config/database.php";

echo "Starting monthly payment generation...\n";

// Get all active allocations where payment is due
$query = "SELECT 
            sa.*,
            s.id as student_id,
            s.full_name,
            s.email,
            sh.fee_amount,
            sh.shift_name
          FROM seat_allocations sa
          JOIN students s ON sa.student_id = s.id
          JOIN shifts sh ON sa.shift_id = sh.id
          WHERE sa.status = 'active'
          AND s.status = 'active'";

$result = $conn->query($query);
$count = 0;

while($allocation = $result->fetch_assoc()) {
    $allocation_id = $allocation['id'];
    $student_id = $allocation['student_id'];
    $fee_amount = $allocation['fee_amount'];
    
    // Calculate next due date (first of next month)
    $today = date('Y-m-d');
    $next_month = date('Y-m-d', strtotime('first day of next month'));
    $month = date('m', strtotime($next_month));
    $year = date('Y', strtotime($next_month));
    
    // Check if payment already exists for this month
    $check_query = "SELECT id FROM payments WHERE allocation_id = ? AND month = ? AND year = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("iii", $allocation_id, $month, $year);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows == 0) {
        // Generate new invoice number
        $invoice_number = "INV-" . $year . $month . str_pad($allocation_id, 6, '0', STR_PAD_LEFT);
        
        // Insert new payment record
        $insert_query = "INSERT INTO payments 
                        (allocation_id, student_id, invoice_number, payment_date, due_date, amount, payment_status, month, year) 
                        VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("iisssdii", $allocation_id, $student_id, $invoice_number, $today, $next_month, $fee_amount, $month, $year);
        
        if ($insert_stmt->execute()) {
            $count++;
            echo "Generated payment for student ID: $student_id - Month: $month/$year\n";
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
}

echo "Total $count new payments generated.\n";
$conn->close();
?>