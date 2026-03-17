<?php
require "config/database.php";
include 'includes/header.php';
include 'includes/sidebar.php';

// Toast configuration
// require_once 'includes/toast-config.php';

// Get all active shifts from database
$shifts_query = "SELECT * FROM shifts WHERE status = 'active' ORDER BY id";
$shifts_result = $conn->query($shifts_query);

// Get all active seats with their shift info
$seats_query = "SELECT s.*, sh.shift_name 
                FROM seats s 
                LEFT JOIN shifts sh ON s.shift_id = sh.id 
                WHERE s.status = 'active' 
                ORDER BY s.room, s.seat_number";
$seats_result = $conn->query($seats_query);

// Group seats by room for better display
$seats_by_room = [];
if ($seats_result && $seats_result->num_rows > 0) {
    while($seat = $seats_result->fetch_assoc()) {
        $room = $seat['room'];
        if (!isset($seats_by_room[$room])) {
            $seats_by_room[$room] = [];
        }
        $seats_by_room[$room][] = $seat;
    }
}
?>
<link rel="stylesheet" href="assets/css/add-student.css">
<!-- Page Content -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12 mx-auto">
            
            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between mb-4" data-aos="fade-down">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="fas fa-user-graduate me-2 text-primary"></i>
                        Add New Student
                    </h4>
                    <p class="text-secondary small mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Fill in the details to register a new student
                    </p>
                </div>
                <a href="students.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>

            <!-- Main Form Card -->
            <div class="card border-0 shadow-lg" data-aos="zoom-in" style="background: var(--card-bg); border-radius: 24px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <ul class="nav nav-pills nav-fill gap-2" id="studentFormTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                                <i class="fas fa-user me-2"></i>
                                <span class="d-none d-sm-inline">Personal Info</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="allocation-tab" data-bs-toggle="tab" data-bs-target="#allocation" type="button" role="tab">
                                <i class="fas fa-chair me-2"></i>
                                <span class="d-none d-sm-inline">Seat Allocation</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab">
                                <i class="fas fa-credit-card me-2"></i>
                                <span class="d-none d-sm-inline">Payment Details</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <form id="addStudentForm" enctype="multipart/form-data">
                        <div class="tab-content">
                            <!-- ============ TAB 1: PERSONAL INFORMATION ============ -->
                            <div class="tab-pane fade show active" id="personal" role="tabpanel">
                                <h6 class="fw-bold mb-3 pb-2 border-bottom">
                                    <i class="fas fa-id-card me-2 text-primary"></i>
                                    Personal Details
                                </h6>

                                <div class="row g-3">
                                    <!-- Full Name -->
                                    <div class="col-12">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Full Name <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text border-end-0">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <input type="text" 
                                                   name="full_name" 
                                                   class="form-control border-start-0" 
                                                   placeholder="Enter full name" 
                                                   required>
                                        </div>
                                    </div>

                                    <!-- Email & Phone -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Email Address <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text border-end-0">
                                                <i class="fas fa-envelope"></i>
                                            </span>
                                            <input type="email" 
                                                   name="email" 
                                                   class="form-control border-start-0" 
                                                   placeholder="student@example.com" 
                                                   required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Phone Number <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text border-end-0">
                                                <i class="fas fa-phone"></i>
                                            </span>
                                            <input type="tel" 
                                                   name="phone" 
                                                   class="form-control border-start-0" 
                                                   placeholder="10 digit number" 
                                                   pattern="[0-9]{10}" 
                                                   maxlength="10"
                                                   required>
                                        </div>
                                    </div>

                                    <!-- Date of Birth & Gender -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Date of Birth
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text border-end-0">
                                                <i class="fas fa-calendar"></i>
                                            </span>
                                            <input type="date" 
                                                   name="dob" 
                                                   class="form-control border-start-0">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Gender
                                        </label>
                                        <select name="gender" class="form-select">
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>

                                    <!-- Address -->
                                    <div class="col-12">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Address
                                        </label>
                                        <textarea name="address" 
                                                  class="form-control" 
                                                  rows="2" 
                                                  placeholder="Enter complete address"></textarea>
                                    </div>

                                    <!-- Photo & ID Proof Upload -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Photo
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text border-end-0">
                                                <i class="fas fa-camera"></i>
                                            </span>
                                            <input type="file" 
                                                   name="photo" 
                                                   class="form-control border-start-0" 
                                                   accept="image/*">
                                        </div>
                                        <small class="text-secondary">Max size: 2MB (JPG, PNG)</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            ID Proof
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text border-end-0">
                                                <i class="fas fa-id-card"></i>
                                            </span>
                                            <input type="file" 
                                                   name="id_proof" 
                                                   class="form-control border-start-0" 
                                                   accept=".pdf,.jpg,.jpeg,.png">
                                        </div>
                                        <small class="text-secondary">PDF or Image (Max 5MB)</small>
                                    </div>

                                    <!-- Navigation Button -->
                                    <div class="col-12 mt-4">
                                        <button type="button" class="btn btn-primary next-tab" data-next="allocation">
                                            Next: Seat Allocation
                                            <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- ============ TAB 2: SEAT ALLOCATION ============ -->
                            <div class="tab-pane fade" id="allocation" role="tabpanel">
                                <h6 class="fw-bold mb-3 pb-2 border-bottom">
                                    <i class="fas fa-chair me-2 text-primary"></i>
                                    Seat & Shift Selection
                                </h6>

                                <div class="row g-3">
                                    <!-- Shift Selection -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Select Shift <span class="text-danger">*</span>
                                        </label>
                                        <select name="shift_id" id="shiftSelect" class="form-select" required>
                                            <option value="">-- Choose Shift --</option>
                                            <?php if ($shifts_result && $shifts_result->num_rows > 0): ?>
                                                <?php while($shift = $shifts_result->fetch_assoc()): ?>
                                                    <option value="<?php echo $shift['id']; ?>" 
                                                            data-fee="<?php echo $shift['fee_amount']; ?>"
                                                            data-start="<?php echo $shift['start_time']; ?>"
                                                            data-end="<?php echo $shift['end_time']; ?>">
                                                        <?php echo $shift['shift_name']; ?> 
                                                        (<?php echo date('h:i A', strtotime($shift['start_time'])); ?> - 
                                                        <?php echo date('h:i A', strtotime($shift['end_time'])); ?>)
                                                        - ₹<?php echo $shift['fee_amount']; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <!-- Room Selection (Dynamic based on seats) -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Select Room
                                        </label>
                                        <select name="room" id="roomSelect" class="form-select">
                                            <option value="">-- Choose Room --</option>
                                            <?php foreach(array_keys($seats_by_room) as $room): ?>
                                                <option value="<?php echo htmlspecialchars($room); ?>">
                                                    <?php echo htmlspecialchars($room); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Seat Selection (Dynamic based on room & shift) -->
                                    <div class="col-12">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Select Seat <span class="text-danger">*</span>
                                        </label>
                                        <div class="seat-selection-wrapper p-3 rounded-3">
                                            <div class="row g-2" id="seatsContainer">
                                                <?php if (!empty($seats_by_room)): ?>
                                                    <?php foreach($seats_by_room as $room => $seats): ?>
                                                        <?php foreach($seats as $seat): ?>
                                                            <div class="col-6 col-sm-4 col-md-3 col-lg-2 seat-option" 
                                                                 data-room="<?php echo htmlspecialchars($room); ?>"
                                                                 data-shift="<?php echo $seat['shift_id']; ?>">
                                                                <div class="seat-item border rounded-3 p-2 text-center" 
                                                                     data-seat-id="<?php echo $seat['id']; ?>"
                                                                     data-seat-number="<?php echo $seat['seat_number']; ?>"
                                                                     data-shift-id="<?php echo $seat['shift_id']; ?>"
                                                                     onclick="selectSeat(this)">
                                                                    <div class="seat-number fw-bold"><?php echo $seat['seat_number']; ?></div>
                                                                    <small class="text-secondary"><?php echo $room; ?></small>
                                                                    <?php if($seat['shift_id']): ?>
                                                                        <div class="badge bg-info mt-1" style="font-size: 0.6rem;">
                                                                            Shift Specific
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <input type="hidden" name="seat_id" id="selectedSeatId" required>
                                        <input type="hidden" name="seat_number" id="selectedSeatNumber">
                                    </div>

                                    <!-- Allocation Dates -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Start Date <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text border-end-0">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                            <input type="date" 
                                                   name="start_date" 
                                                   class="form-control border-start-0" 
                                                   value="<?php echo date('Y-m-d'); ?>" 
                                                   required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            End Date
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text border-end-0">
                                                <i class="fas fa-calendar-check"></i>
                                            </span>
                                            <input type="date" 
                                                   name="end_date" 
                                                   class="form-control border-start-0">
                                        </div>
                                        <small class="text-secondary">Leave empty for ongoing</small>
                                    </div>

                                    <!-- Remarks -->
                                    <div class="col-12">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Remarks (Optional)
                                        </label>
                                        <textarea name="remarks" 
                                                  class="form-control" 
                                                  rows="2" 
                                                  placeholder="Any additional notes..."></textarea>
                                    </div>

                                    <!-- Navigation Buttons -->
                                    <div class="col-12 mt-4 d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary prev-tab" data-prev="personal">
                                            <i class="fas fa-arrow-left me-2"></i>
                                            Previous
                                        </button>
                                        <button type="button" class="btn btn-primary next-tab" data-next="payment">
                                            Next: Payment Details
                                            <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- ============ TAB 3: PAYMENT DETAILS ============ -->
                            <div class="tab-pane fade" id="payment" role="tabpanel">
                                <h6 class="fw-bold mb-3 pb-2 border-bottom">
                                    <i class="fas fa-credit-card me-2 text-primary"></i>
                                    Payment Information
                                </h6>

                                <div class="row g-3">
                                    <!-- Fee Display -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Monthly Fee
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text border-end-0">
                                                <i class="fas fa-rupee-sign"></i>
                                            </span>
                                            <input type="text" 
                                                   id="feeDisplay" 
                                                   class="form-control border-start-0" 
                                                   readonly 
                                                   placeholder="Select shift first">
                                        </div>
                                    </div>

                                    <!-- Payment Status -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Payment Status
                                        </label>
                                        <select name="payment_status" id="paymentStatus" class="form-select">
                                            <option value="pending">Pending</option>
                                            <option value="paid">Paid</option>
                                            <option value="partial">Partial</option>
                                        </select>
                                    </div>

                                    <!-- Paid Amount -->
                                    <div class="col-md-6" id="paidAmountField" style="display: none;">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Amount Paid
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text border-end-0">
                                                <i class="fas fa-rupee-sign"></i>
                                            </span>
                                            <input type="number" 
                                                   name="paid_amount" 
                                                   id="paidAmount" 
                                                   class="form-control border-start-0" 
                                                   step="0.01" 
                                                   min="0">
                                        </div>
                                    </div>

                                    <!-- Payment Method -->
                                    <div class="col-md-6" id="paymentMethodField" style="display: none;">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Payment Method
                                        </label>
                                        <select name="payment_method" class="form-select">
                                            <option value="cash">Cash</option>
                                            <option value="card">Card</option>
                                            <option value="upi">UPI</option>
                                            <option value="bank">Bank Transfer</option>
                                            <option value="cheque">Cheque</option>
                                        </select>
                                    </div>

                                    <!-- Transaction ID -->
                                    <div class="col-12" id="transactionField" style="display: none;">
                                        <label class="form-label fw-semibold small text-uppercase">
                                            Transaction ID / Reference
                                        </label>
                                        <input type="text" 
                                               name="transaction_id" 
                                               class="form-control" 
                                               placeholder="Enter transaction ID if any">
                                    </div>

                                    <!-- Summary Card -->
                                    <div class="col-12 mt-3">
                                        <div class="card bg-primary bg-opacity-10 border-0">
                                            <div class="card-body">
                                                <h6 class="fw-bold mb-3">Summary</h6>
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <small class="text-secondary">Monthly Fee:</small>
                                                        <div class="fw-bold" id="summaryFee">₹0.00</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-secondary">Due Amount:</small>
                                                        <div class="fw-bold text-danger" id="summaryDue">₹0.00</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="col-12 mt-4 d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary prev-tab" data-prev="allocation">
                                            <i class="fas fa-arrow-left me-2"></i>
                                            Previous
                                        </button>
                                        <button type="submit" class="btn btn-success" id="submitBtn">
                                            <i class="fas fa-save me-2"></i>
                                            Register Student
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<!-- JavaScript -->
<script>
$(document).ready(function() {
    // ============ TAB NAVIGATION ============
    $('.next-tab').click(function() {
        const nextTab = $(this).data('next');
        $(`#${nextTab}-tab`).tab('show');
    });

    $('.prev-tab').click(function() {
        const prevTab = $(this).data('prev');
        $(`#${prevTab}-tab`).tab('show');
    });

    // ============ SHIFT SELECT HANDLER ============
    $('#shiftSelect').change(function() {
        const shiftId = $(this).val();
        const selectedOption = $(this).find('option:selected');
        
        // Yahan fix kiya hai: parseFloat use karke ensure karein ki ye number hi ho
        const feeAmount = parseFloat(selectedOption.data('fee')) || 0;
        
        // Ab .toFixed(2) error nahi dega kyunki feeAmount pakka ek Number hai
        const formattedFee = '₹' + feeAmount.toFixed(2);
        
        // Update fee display
        $('#feeDisplay').val(formattedFee);
        $('#summaryFee').text(formattedFee);
        $('#summaryDue').text(formattedFee);

        // Filter seats by shift
        filterSeatsByShift(shiftId);
        
        // Clear selected seat
        $('.seat-item.selected').removeClass('selected');
        $('#selectedSeatId').val('');
        $('#selectedSeatNumber').val('');
    });

    // ============ FILTER SEATS BY SHIFT ============
    function filterSeatsByShift(shiftId) {
        if (!shiftId) {
            $('.seat-option').show();
            return;
        }

        $('.seat-option').each(function() {
            const seatShift = $(this).data('shift');
            // Show seats that match shift OR have no shift (NULL)
            if (seatShift == shiftId || seatShift === '' || seatShift === null) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // ============ ROOM FILTER ============
    $('#roomSelect').change(function() {
        const selectedRoom = $(this).val();
        
        if (!selectedRoom) {
            $('.seat-option').show();
        } else {
            $('.seat-option').each(function() {
                const seatRoom = $(this).data('room');
                if (seatRoom == selectedRoom) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        
        // Re-apply shift filter
        const shiftId = $('#shiftSelect').val();
        if (shiftId) {
            filterSeatsByShift(shiftId);
        }
    });

    // ============ PAYMENT STATUS HANDLER ============
    $('#paymentStatus').change(function() {
        const status = $(this).val();
        
        if (status === 'paid' || status === 'partial') {
            $('#paidAmountField, #paymentMethodField, #transactionField').show();
            
            if (status === 'paid') {
                const fee = parseFloat($('#feeDisplay').val().replace('₹', '')) || 0;
                $('#paidAmount').val(fee.toFixed(2));
                $('#summaryDue').text('₹0.00');
            } else {
                $('#paidAmount').val('');
                updateDueAmount();
            }
        } else {
            $('#paidAmountField, #paymentMethodField, #transactionField').hide();
            $('#summaryDue').text($('#summaryFee').text());
        }
    });

    // ============ PAID AMOUNT CHANGE ============
    $('#paidAmount').on('input', function() {
        updateDueAmount();
    });

    function updateDueAmount() {
        const fee = parseFloat($('#summaryFee').text().replace('₹', '')) || 0;
        const paid = parseFloat($('#paidAmount').val()) || 0;
        const due = Math.max(0, fee - paid);
        $('#summaryDue').text('₹' + due.toFixed(2));
    }

    // ============ FORM SUBMISSION ============
    $('#addStudentForm').submit(function(e) {
        e.preventDefault();
        
        // Validate seat selection
        if (!$('#selectedSeatId').val()) {
            if (typeof toastError === 'function') {
                toastError('Please select a seat', 'Validation Error');
            } else {
                alert('Please select a seat');
            }
            return;
        }

        const formData = new FormData(this);
        const btn = $('#submitBtn');
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Registering...');

        $.ajax({
            url: 'ajax/ajax_student.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    if (typeof toastSuccess === 'function') {
                        toastSuccess(response.message, 'Success');
                    }
                    
                    // Reset form and show success message
                    setTimeout(function() {
                        window.location.href = 'students.php';
                    }, 2000);
                } else {
                    if (typeof toastError === 'function') {
                        toastError(response.message, 'Error');
                    } else {
                        alert('Error: ' + response.message);
                    }
                    btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Register Student');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                if (typeof toastError === 'function') {
                    toastError('Failed to register student', 'System Error');
                } else {
                    alert('Failed to register student');
                }
                btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Register Student');
            }
        });
    });

    // ============ FORM VALIDATION ============
    $('#addStudentForm input, #addStudentForm select').on('invalid', function() {
        // Move to the tab containing this field
        const tabId = $(this).closest('.tab-pane').attr('id');
        if (tabId) {
            $(`#${tabId}-tab`).tab('show');
        }
    });
});

// ============ GLOBAL SEAT SELECT FUNCTION ============
function selectSeat(element) {
    // Remove selection from other seats
    $('.seat-item').removeClass('selected');
    
    // Add selection to clicked seat
    $(element).addClass('selected');
    
    // Set hidden inputs
    const seatId = $(element).data('seat-id');
    const seatNumber = $(element).data('seat-number');
    
    $('#selectedSeatId').val(seatId);
    $('#selectedSeatNumber').val(seatNumber);
    
    // Show success feedback
    if (typeof toastSuccess === 'function') {
        toastSuccess(`Seat ${seatNumber} selected`, 'Seat Selected');
    }
}
</script>
