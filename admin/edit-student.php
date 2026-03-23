<?php
require "config/database.php";
include 'includes/header.php';
include 'includes/sidebar.php';


$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id === 0) {
    header("Location: students.php");
    exit;
}

// Get student details with current allocation
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
            sh.fee_amount
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

// Get all active shifts for dropdown
$shifts_query = "SELECT * FROM shifts WHERE status = 'active' ORDER BY shift_name";
$shifts_result = $conn->query($shifts_query);

// Get all available seats
$seats_query = "SELECT s.*, sh.shift_name 
                FROM seats s 
                LEFT JOIN shifts sh ON s.shift_id = sh.id 
                WHERE s.status = 'active' 
                ORDER BY s.room, s.seat_number";
$seats_result = $conn->query($seats_query);

// Group seats by room
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
?><link rel="stylesheet" href="assets/css/edit-students.css">
<!-- Page Content -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 col-lg-8 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="fas fa-edit me-2 text-primary"></i>
                        Edit Student
                    </h4>
                    <p class="text-secondary small">Update student information and seat allocation</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="view-student.php?id=<?php echo $student_id; ?>" class="btn btn-outline-info">
                        <i class="fas fa-eye me-2"></i>View
                    </a>
                    <a href="students.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </div>

            <!-- Edit Form Card -->
            <div class="card border-0 shadow-lg">
                <div class="card-body p-4">
                    <form id="editStudentForm" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $student_id; ?>">
                        
                        <!-- Personal Information Section -->
                        <h6 class="fw-bold mb-3 pb-2 border-bottom">
                            <i class="fas fa-user me-2 text-primary"></i>
                            Personal Information
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="full_name" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($student['full_name']); ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($student['email']); ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">
                                    Phone <span class="text-danger">*</span>
                                </label>
                                <input type="tel" 
                                       name="phone" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($student['phone']); ?>" 
                                       pattern="[0-9]{10}" 
                                       maxlength="10"
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">
                                    Date of Birth
                                </label>
                                <input type="date" 
                                       name="dob" 
                                       class="form-control" 
                                       value="<?php echo $student['dob']; ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">
                                    Gender
                                </label>
                                <select name="gender" class="form-select">
                                    <option value="">Select Gender</option>
                                    <option value="male" <?php echo $student['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="female" <?php echo $student['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="other" <?php echo $student['gender'] == 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">
                                    Status
                                </label>
                                <select name="status" class="form-select">
                                    <option value="active" <?php echo $student['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $student['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="blocked" <?php echo $student['status'] == 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-uppercase">
                                    Address
                                </label>
                                <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($student['address']); ?></textarea>
                            </div>
                        </div>

                        <!-- Seat Allocation Section -->
                        <h6 class="fw-bold mb-3 mt-4 pb-2 border-bottom">
                            <i class="fas fa-chair me-2 text-primary"></i>
                            Seat Allocation
                        </h6>

                        <div class="row g-3">
                            <!-- Current Allocation Info -->
                            <?php if($student['seat_id']): ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Currently allocated to <strong>Seat <?php echo $student['seat_number']; ?></strong> in <strong><?php echo $student['room']; ?></strong> (<?php echo $student['shift_name']; ?> shift)
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Shift Selection -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">
                                    Select Shift
                                </label>
                                <select name="shift_id" id="shiftSelect" class="form-select">
                                    <option value="">-- Select Shift --</option>
                                    <?php if ($shifts_result && $shifts_result->num_rows > 0): ?>
                                        <?php while($shift = $shifts_result->fetch_assoc()): ?>
                                            <option value="<?php echo $shift['id']; ?>" 
                                                    data-fee="<?php echo $shift['fee_amount']; ?>"
                                                    <?php echo ($student['shift_id'] == $shift['id']) ? 'selected' : ''; ?>>
                                                <?php echo $shift['shift_name']; ?> 
                                                (₹<?php echo $shift['fee_amount']; ?>/month)
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Room Selection -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">
                                    Select Room
                                </label>
                                <select name="room" id="roomSelect" class="form-select">
                                    <option value="">-- Select Room --</option>
                                    <?php foreach(array_keys($seats_by_room) as $room): ?>
                                        <option value="<?php echo htmlspecialchars($room); ?>"
                                                <?php echo ($student['room'] == $room) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($room); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Seat Selection -->
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-uppercase">
                                    Select Seat
                                </label>
                                <div class="seat-selection-wrapper p-3 bg-light rounded-3">
                                    <div class="row g-2" id="seatsContainer">
                                        <?php if (!empty($seats_by_room)): ?>
                                            <?php foreach($seats_by_room as $room => $seats): ?>
                                                <?php foreach($seats as $seat): ?>
                                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 seat-option" 
                                                         data-room="<?php echo htmlspecialchars($room); ?>"
                                                         data-shift="<?php echo $seat['shift_id']; ?>"
                                                         style="<?php echo ($room != $student['room']) ? 'display: none;' : ''; ?>">
                                                        <div class="seat-item border rounded-3 p-2 text-center <?php echo ($student['seat_id'] == $seat['id']) ? 'selected' : ''; ?>" 
                                                             data-seat-id="<?php echo $seat['id']; ?>"
                                                             data-seat-number="<?php echo $seat['seat_number']; ?>"
                                                             onclick="selectSeat(this)">
                                                            <div class="seat-number fw-bold"><?php echo $seat['seat_number']; ?></div>
                                                            <small class="text-secondary"><?php echo $room; ?></small>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <input type="hidden" name="seat_id" id="selectedSeatId" value="<?php echo $student['seat_id']; ?>">
                                <input type="hidden" name="seat_number" id="selectedSeatNumber" value="<?php echo $student['seat_number']; ?>">
                                <small class="text-secondary mt-2 d-block">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Click on a seat to select it
                                </small>
                            </div>

                            <!-- Allocation Dates -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">
                                    Start Date
                                </label>
                                <input type="date" 
                                       name="start_date" 
                                       class="form-control" 
                                       value="<?php echo $student['start_date'] ?? date('Y-m-d'); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">
                                    End Date (Optional)
                                </label>
                                <input type="date" 
                                       name="end_date" 
                                       class="form-control" 
                                       value="<?php echo $student['end_date']; ?>">
                                <small class="text-secondary">Leave empty for ongoing</small>
                            </div>

                            <!-- Allocation Remarks -->
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-uppercase">
                                    Allocation Remarks
                                </label>
                                <textarea name="allocation_remarks" class="form-control" rows="2"><?php echo htmlspecialchars($student['allocation_remarks'] ?? ''); ?></textarea>
                            </div>

                            <!-- Remove Allocation Option -->
                            <?php if($student['seat_id']): ?>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_allocation" id="removeAllocation" value="1">
                                    <label class="form-check-label text-danger" for="removeAllocation">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Remove current seat allocation
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Photo Upload Section -->
                        <h6 class="fw-bold mb-3 mt-4 pb-2 border-bottom">
                            <i class="fas fa-camera me-2 text-primary"></i>
                            Photo & Documents
                        </h6>
                        
                        <div class="row g-3">
                            <?php if($student['photo']): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">Current Photo</label>
                                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                                    <img src="<?php echo $student['photo']; ?>" 
                                         class="rounded-circle" 
                                         width="60" 
                                         height="60"
                                         style="object-fit: cover;">
                                    <div>
                                        <small class="text-secondary d-block">Current photo</small>
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removePhoto()">
                                            <i class="fas fa-trash me-1"></i>Remove
                                        </button>
                                        <input type="hidden" name="remove_photo" id="removePhoto" value="0">
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="<?php echo $student['photo'] ? 'col-md-6' : 'col-12'; ?>">
                                <label class="form-label fw-semibold small text-uppercase">
                                    <?php echo $student['photo'] ? 'Change Photo' : 'Upload Photo'; ?> (Optional)
                                </label>
                                <input type="file" 
                                       name="photo" 
                                       class="form-control" 
                                       accept="image/*">
                            </div>
                            
                            <?php if($student['id_proof']): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">Current ID Proof</label>
                                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                                    <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                    <div>
                                        <a href="<?php echo $student['id_proof']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removeIdProof()">
                                            <i class="fas fa-trash me-1"></i>Remove
                                        </button>
                                        <input type="hidden" name="remove_id_proof" id="removeIdProof" value="0">
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="<?php echo $student['id_proof'] ? 'col-md-6' : 'col-12'; ?>">
                                <label class="form-label fw-semibold small text-uppercase">
                                    <?php echo $student['id_proof'] ? 'Change ID Proof' : 'Upload ID Proof'; ?> (Optional)
                                </label>
                                <input type="file" 
                                       name="id_proof" 
                                       class="form-control" 
                                       accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>

                        <!-- Created Info -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="bg-light p-3 rounded-3">
                                    <div class="d-flex justify-content-between text-secondary small">
                                        <span><i class="fas fa-clock me-1"></i> Created: <?php echo date('d M Y h:i A', strtotime($student['created_at'])); ?></span>
                                        <?php if($student['updated_at']): ?>
                                        <span><i class="fas fa-edit me-1"></i> Last Updated: <?php echo date('d M Y h:i A', strtotime($student['updated_at'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Form Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="view-student.php?id=<?php echo $student_id; ?>" class="btn btn-outline-info">
                                <i class="fas fa-eye me-2"></i>View
                            </a>
                            <button type="button" class="btn btn-light" onclick="window.location.href='students.php'">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-2"></i>Update Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script>
$(document).ready(function() {
    // ============ ROOM FILTER ============
    $('#roomSelect').change(function() {
        const selectedRoom = $(this).val();
        
        $('.seat-option').each(function() {
            const seatRoom = $(this).data('room');
            if (!selectedRoom || seatRoom == selectedRoom) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Clear selected seat when room changes
        $('.seat-item').removeClass('selected');
        $('#selectedSeatId').val('');
        $('#selectedSeatNumber').val('');
    });

    // ============ SHIFT FILTER ============
    $('#shiftSelect').change(function() {
        const shiftId = $(this).val();
        
        if (!shiftId) {
            $('.seat-option').show();
            return;
        }

        $('.seat-option').each(function() {
            const seatShift = $(this).data('shift');
            if (!seatShift || seatShift == shiftId) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Re-apply room filter
        $('#roomSelect').trigger('change');
    });

    // ============ REMOVE PHOTO ============
    window.removePhoto = function() {
        if (confirm('Are you sure you want to remove the current photo?')) {
            $('#removePhoto').val('1');
            $(this).closest('.col-md-6').fadeOut();
        }
    };

    // ============ REMOVE ID PROOF ============
    window.removeIdProof = function() {
        if (confirm('Are you sure you want to remove the current ID proof?')) {
            $('#removeIdProof').val('1');
            $(this).closest('.col-md-6').fadeOut();
        }
    };

    // ============ FORM SUBMISSION ============
    $('#editStudentForm').on('submit', function(e) {
        e.preventDefault();
        
        const btn = $('#submitBtn');
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

        const formData = new FormData(this);

        $.ajax({
            url: 'ajax/update_student.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    if (typeof toastSuccess === 'function') {
                        toastSuccess(response.message, 'Success');
                    } else {
                        alert('Success: ' + response.message);
                    }
                    
                    setTimeout(function() {
                        window.location.href = 'view-student.php?id=<?php echo $student_id; ?>';
                    }, 1500);
                } else {
                    if (typeof toastError === 'function') {
                        toastError(response.message, 'Error');
                    } else {
                        alert('Error: ' + response.message);
                    }
                    btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                if (typeof toastError === 'function') {
                    toastError('Failed to update student', 'System Error');
                } else {
                    alert('Failed to update student');
                }
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // ============ TRIGGER ROOM FILTER ON LOAD ============
    setTimeout(function() {
        $('#roomSelect').trigger('change');
    }, 100);
});

// ============ GLOBAL SEAT SELECT FUNCTION ============
function selectSeat(element) {
    $('.seat-item').removeClass('selected');
    $(element).addClass('selected');
    
    const seatId = $(element).data('seat-id');
    const seatNumber = $(element).data('seat-number');
    
    $('#selectedSeatId').val(seatId);
    $('#selectedSeatNumber').val(seatNumber);
}
</script>

