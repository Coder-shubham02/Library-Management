<?php
require "config/database.php";
include 'includes/header.php';
include 'includes/sidebar.php';

// Toast configuration
// require_once 'includes/toast-config.php';

$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id === 0) {
    header("Location: students.php");
    exit;
}

// Get student details with all related info
$query = "SELECT 
            s.*,
            sa.id as allocation_id,
            sa.seat_id,
            sa.shift_id,
            sa.start_date,
            sa.end_date,
            sa.remarks as allocation_remarks,
            se.seat_number,
            se.room,
            sh.shift_name,
            sh.start_time as shift_start,
            sh.end_time as shift_end,
            sh.fee_amount,
            (SELECT SUM(amount - paid_amount) FROM payments WHERE student_id = s.id AND payment_status IN ('pending', 'partial')) as total_due,
            (SELECT COUNT(*) FROM payments WHERE student_id = s.id AND payment_status = 'pending') as pending_count,
            (SELECT SUM(paid_amount) FROM payments WHERE student_id = s.id) as total_paid,
            (SELECT COUNT(*) FROM payments WHERE student_id = s.id) as total_payments
          FROM students s
          LEFT JOIN seat_allocations sa ON s.id = sa.student_id AND sa.status = 'active'
          LEFT JOIN seats se ON sa.seat_id = se.id
          LEFT JOIN shifts sh ON sa.shift_id = sh.id
          WHERE s.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: students.php");
    exit;
}

$student = $result->fetch_assoc();

// Get payment history
$payment_query = "SELECT 
                    p.*,
                    ph.payment_date as paid_date,
                    ph.amount as paid_amount,
                    ph.payment_method,
                    ph.transaction_id,
                    ph.reference_number
                  FROM payments p
                  LEFT JOIN payment_history ph ON p.id = ph.payment_id
                  WHERE p.student_id = ?
                  ORDER BY p.created_at DESC
                  LIMIT 10";

$payment_stmt = $conn->prepare($payment_query);
$payment_stmt->bind_param("i", $student_id);
$payment_stmt->execute();
$payments_result = $payment_stmt->get_result();

// Get allocation history
$history_query = "SELECT 
                    sa.*,
                    se.seat_number,
                    se.room,
                    sh.shift_name,
                    sh.start_time,
                    sh.end_time,
                    sh.fee_amount
                  FROM seat_allocations sa
                  JOIN seats se ON sa.seat_id = se.id
                  JOIN shifts sh ON sa.shift_id = sh.id
                  WHERE sa.student_id = ?
                  ORDER BY sa.created_at DESC";

$history_stmt = $conn->prepare($history_query);
$history_stmt->bind_param("i", $student_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
?>
<link rel="stylesheet" href="assets/css/view-student.css">
<!-- Page Content -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 col-lg-10 mx-auto">
            <!-- Header with Actions -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="fas fa-user-graduate me-2 text-primary"></i>
                        Student Details
                    </h4>
                    <p class="text-secondary small">Complete information and history</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="students.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                    <a href="edit-student.php?id=<?php echo $student_id; ?>" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Student
                    </a>
                </div>
            </div>

            <!-- Student Info Cards -->
            <div class="row g-4">
                <!-- Left Column - Basic Info & Photo -->
                <div class="col-md-4">
                    <!-- Profile Card -->
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <!-- Profile Photo -->
                            <div class="mb-3">
                                <?php if($student['photo']): ?>
                                    <img src="<?php echo $student['photo']; ?>" 
                                         class="rounded-circle" 
                                         width="120" 
                                         height="120"
                                         style="object-fit: cover; border: 4px solid var(--primary-color);">
                                <?php else: ?>
                                    <div class="avatar-large mx-auto">
                                        <?php echo strtoupper(substr($student['full_name'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Student Name & ID -->
                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($student['full_name']); ?></h5>
                            <p class="text-secondary small mb-3">
                                ID: #STU<?php echo str_pad($student['id'], 4, '0', STR_PAD_LEFT); ?>
                            </p>
                            
                            <!-- Status Badge -->
                            <div class="d-flex justify-content-center gap-2 mb-4">
                                <span class="badge bg-<?php 
                                    echo $student['status'] == 'active' ? 'success' : 
                                        ($student['status'] == 'inactive' ? 'secondary' : 'danger'); 
                                ?>-subtle text-<?php 
                                    echo $student['status'] == 'active' ? 'success' : 
                                        ($student['status'] == 'inactive' ? 'secondary' : 'danger'); 
                                ?> px-3 py-2">
                                    <i class="fas <?php 
                                        echo $student['status'] == 'active' ? 'fa-circle-check' : 
                                            ($student['status'] == 'inactive' ? 'fa-circle-pause' : 'fa-circle-exclamation'); 
                                    ?> me-1"></i>
                                    <?php echo ucfirst($student['status']); ?>
                                </span>
                            </div>

                            <!-- Contact Information -->
                            <div class="text-start border-top pt-3">
                                <h6 class="fw-bold mb-3">Contact Information</h6>
                                
                                <div class="mb-3">
                                    <small class="text-secondary d-block mb-1">
                                        <i class="fas fa-envelope me-2"></i>Email Address
                                    </small>
                                    <span><?php echo htmlspecialchars($student['email']); ?></span>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-secondary d-block mb-1">
                                        <i class="fas fa-phone me-2"></i>Phone Number
                                    </small>
                                    <span><?php echo htmlspecialchars($student['phone']); ?></span>
                                </div>
                                
                                <?php if($student['dob']): ?>
                                <div class="mb-3">
                                    <small class="text-secondary d-block mb-1">
                                        <i class="fas fa-calendar me-2"></i>Date of Birth
                                    </small>
                                    <span><?php echo date('d M Y', strtotime($student['dob'])); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if($student['gender']): ?>
                                <div class="mb-3">
                                    <small class="text-secondary d-block mb-1">
                                        <i class="fas fa-venus-mars me-2"></i>Gender
                                    </small>
                                    <span><?php echo ucfirst($student['gender']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if($student['address']): ?>
                                <div class="mb-3">
                                    <small class="text-secondary d-block mb-1">
                                        <i class="fas fa-location-dot me-2"></i>Address
                                    </small>
                                    <span><?php echo nl2br(htmlspecialchars($student['address'])); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Documents -->
                            <?php if($student['id_proof']): ?>
                            <div class="text-start border-top pt-3 mt-2">
                                <h6 class="fw-bold mb-3">Documents</h6>
                                <div>
                                    <a href="<?php echo $student['id_proof']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-id-card me-2"></i>View ID Proof
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Allocation & Financial Info -->
                <div class="col-md-8">
                    <div class="row g-4">
                        <!-- Current Allocation Card -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0">
                                        <i class="fas fa-chair me-2 text-primary"></i>
                                        Current Seat Allocation
                                    </h6>
                                    <?php if(!$student['seat_number']): ?>
                                    <a href="allocate-seat.php?student_id=<?php echo $student_id; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus me-1"></i>Allocate Seat
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <?php if($student['seat_number']): ?>
                                        <div class="row">
                                            <div class="col-md-4 text-center mb-3 mb-md-0">
                                                <div class="seat-badge mx-auto">
                                                    <?php echo $student['seat_number']; ?>
                                                </div>
                                                <small class="text-secondary d-block mt-2">Seat Number</small>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row g-3">
                                                    <div class="col-sm-6">
                                                        <small class="text-secondary d-block">Room/Hall</small>
                                                        <span class="fw-bold"><?php echo $student['room']; ?></span>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <small class="text-secondary d-block">Shift</small>
                                                        <span class="fw-bold"><?php echo $student['shift_name']; ?></span>
                                                        <small class="text-secondary d-block">
                                                            <?php echo date('h:i A', strtotime($student['shift_start'])); ?> - 
                                                            <?php echo date('h:i A', strtotime($student['shift_end'])); ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <small class="text-secondary d-block">Start Date</small>
                                                        <span><?php echo date('d M Y', strtotime($student['start_date'])); ?></span>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <small class="text-secondary d-block">End Date</small>
                                                        <span><?php echo $student['end_date'] ? date('d M Y', strtotime($student['end_date'])) : 'Ongoing'; ?></span>
                                                    </div>
                                                    <?php if($student['allocation_remarks']): ?>
                                                    <div class="col-12">
                                                        <small class="text-secondary d-block">Remarks</small>
                                                        <span><?php echo nl2br(htmlspecialchars($student['allocation_remarks'])); ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-chair fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-3">No seat allocated yet</p>
                                            <a href="allocate-seat.php?student_id=<?php echo $student_id; ?>" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>Allocate Seat Now
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Summary Card -->
                        <!-- Financial Summary Card -->
<div class="col-12">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3">
            <h6 class="fw-bold mb-0">
                <i class="fas fa-credit-card me-2 text-primary"></i>
                Financial Summary
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3 col-6">
                    <div class="text-center p-3 bg-light rounded-3">
                        <small class="text-secondary d-block">Monthly Fee</small>
                        <h5 class="fw-bold mt-2">₹<?php echo number_format($student['fee_amount'] ?? 0, 2); ?></h5>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="text-center p-3 bg-light rounded-3">
                        <small class="text-secondary d-block">Total Paid</small>
                        <h5 class="fw-bold mt-2 text-success">₹<?php echo number_format($student['total_paid'] ?? 0, 2); ?></h5>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="text-center p-3 bg-light rounded-3">
                        <small class="text-secondary d-block">Due Amount</small>
                        <h5 class="fw-bold mt-2 text-<?php echo ($student['total_due'] ?? 0) > 0 ? 'danger' : 'success'; ?>">
                            ₹<?php echo number_format($student['total_due'] ?? 0, 2); ?>
                        </h5>
                        <?php if($student['next_due_date']): ?>
                        <small class="text-secondary d-block">Next due: <?php echo date('d M Y', strtotime($student['next_due_date'])); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="text-center p-3 bg-light rounded-3">
                        <small class="text-secondary d-block">Payments</small>
                        <h5 class="fw-bold mt-2"><?php echo $student['total_payments'] ?? 0; ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

                        <!-- Recent Payments Card -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0">
                                        <i class="fas fa-history me-2 text-primary"></i>
                                        Recent Payments
                                    </h6>
                                    <a href="payments.php?student_id=<?php echo $student_id; ?>" class="btn btn-sm btn-outline-primary">
                                        View All
                                    </a>
                                </div>
                                <div class="card-body">
                                    <?php if($payments_result && $payments_result->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Invoice #</th>
                                                        <th>Amount</th>
                                                        <th>Paid</th>
                                                        <th>Method</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while($payment = $payments_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
                                                        <td><small><?php echo $payment['invoice_number']; ?></small></td>
                                                        <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                                                        <td>₹<?php echo number_format($payment['paid_amount'] ?? 0, 2); ?></td>
                                                        <td>
                                                            <span class="badge bg-light text-dark">
                                                                <?php echo ucfirst($payment['payment_method'] ?? 'cash'); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                echo $payment['payment_status'] == 'paid' ? 'success' : 
                                                                    ($payment['payment_status'] == 'partial' ? 'warning' : 'danger'); 
                                                            ?>-subtle text-<?php 
                                                                echo $payment['payment_status'] == 'paid' ? 'success' : 
                                                                    ($payment['payment_status'] == 'partial' ? 'warning' : 'danger'); 
                                                            ?>">
                                                                <?php echo ucfirst($payment['payment_status']); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-3">No payment records found</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Allocation History Card -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-transparent border-bottom py-3">
                                    <h6 class="fw-bold mb-0">
                                        <i class="fas fa-clock-rotate-left me-2 text-primary"></i>
                                        Allocation History
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if($history_result && $history_result->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Period</th>
                                                        <th>Seat</th>
                                                        <th>Room</th>
                                                        <th>Shift</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while($history = $history_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td>
                                                            <?php echo date('d M Y', strtotime($history['start_date'])); ?>
                                                            <?php if($history['end_date']): ?>
                                                                <br><small>to <?php echo date('d M Y', strtotime($history['end_date'])); ?></small>
                                                            <?php else: ?>
                                                                <br><small class="text-success">Current</small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><span class="fw-bold"><?php echo $history['seat_number']; ?></span></td>
                                                        <td><?php echo $history['room']; ?></td>
                                                        <td><?php echo $history['shift_name']; ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                echo $history['status'] == 'active' ? 'success' : 
                                                                    ($history['status'] == 'completed' ? 'secondary' : 'danger'); 
                                                            ?>-subtle text-<?php 
                                                                echo $history['status'] == 'active' ? 'success' : 
                                                                    ($history['status'] == 'completed' ? 'secondary' : 'danger'); 
                                                            ?>">
                                                                <?php echo ucfirst($history['status']); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-3">No allocation history found</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>