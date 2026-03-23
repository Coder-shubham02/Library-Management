<?php
require "config/database.php";
include 'includes/header.php';
include 'includes/sidebar.php';
include 'config/helpers.php';

// Toast configuration
// require_once 'includes/toast-config.php';

// Pagination Logic
$limit = 10; // Records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$start = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$shift_filter = isset($_GET['shift']) ? $_GET['shift'] : '';

// Build query with filters
$where_conditions = [];
$params = [];
$types = "";

if (!empty($search)) {
    $where_conditions[] = "(s.full_name LIKE ? OR s.email LIKE ? OR s.phone LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if (!empty($status_filter)) {
    $where_conditions[] = "s.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($shift_filter)) {
    $where_conditions[] = "sa.shift_id = ?";
    $params[] = $shift_filter;
    $types .= "i";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total students count with filters
$count_query = "SELECT COUNT(DISTINCT s.id) as total 
                FROM students s 
                LEFT JOIN seat_allocations sa ON s.id = sa.student_id AND sa.status = 'active'
                $where_clause";
                
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_row = $count_result->fetch_assoc();
$total_students = $total_row['total'];
$total_pages = ceil($total_students / $limit);

// [NEW] ============ UPDATED QUERY WITH MONTHLY PAYMENT LOGIC ============
// Yeh query ab sirf due amount tab dikhayegi jab due date aaj ya usse pehle ki ho
// In your students.php query, update the due calculation:

$query = "SELECT 
            s.*,
            sa.id as allocation_id,
            sa.seat_id,
            sa.shift_id,
            sa.start_date,
            sa.end_date,
            sa.status as allocation_status,
            se.seat_number,
            se.room,
            sh.shift_name,
            sh.start_time as shift_start,
            sh.end_time as shift_end,
            sh.fee_amount,
            
            -- ===== FIX: Sirf wo payments jinka due date aa chuka hai =====
            (SELECT COUNT(*) 
             FROM payments p 
             WHERE p.student_id = s.id 
             AND p.payment_status = 'pending'
             AND p.due_date <= CURDATE()
            ) as pending_payments,
            
            -- Total due (past due only)
            (SELECT SUM(p.amount - p.paid_amount) 
             FROM payments p 
             WHERE p.student_id = s.id 
             AND p.payment_status IN ('pending', 'partial')
             AND p.due_date <= CURDATE()
            ) as total_due,
            
            -- Next due date
            (SELECT MIN(p.due_date) 
             FROM payments p 
             WHERE p.student_id = s.id 
             AND p.payment_status = 'pending'
             AND p.due_date > CURDATE()
            ) as next_due_date
            
          FROM students s
          LEFT JOIN seat_allocations sa ON s.id = sa.student_id AND sa.status = 'active'
          LEFT JOIN seats se ON sa.seat_id = se.id
          LEFT JOIN shifts sh ON sa.shift_id = sh.id
          $where_clause
          GROUP BY s.id
          ORDER BY s.created_at DESC
          LIMIT ?, ?";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    // Add pagination params
    $params[] = $start;
    $params[] = $limit;
    $types .= "ii";
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $start, $limit);
}
$stmt->execute();
$result = $stmt->get_result();

// Get all shifts for filter dropdown
$shifts_query = "SELECT * FROM shifts WHERE status = 'active' ORDER BY shift_name";
$shifts_result = $conn->query($shifts_query);
?>
<link rel="stylesheet" href="assets/css/students.css">
<!-- Page Content -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            
            <!-- Page Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3" data-aos="fade-down">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="fas fa-users me-2 text-primary"></i>
                        Students Management
                    </h4>
                    <p class="text-secondary small mb-0">
                        <i class="fas fa-database me-1"></i>
                        Total Students: <span class="fw-bold"><?php echo $total_students; ?></span>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="add-student.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Add New Student
                    </a>
                    <button class="btn btn-outline-secondary" onclick="exportStudents()">
                        <i class="fas fa-download me-2"></i>
                        Export
                    </button>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="card border-0 shadow-sm mb-4" data-aos="fade-up" style="background: var(--card-bg); border-radius: 20px;">
                <div class="card-body p-3 p-md-4">
                    <form method="GET" id="filterForm" class="row g-3 align-items-end">
                        <!-- Search Field -->
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-semibold text-secondary mb-1">
                                <i class="fas fa-search me-1"></i>Search
                            </label>
                            <div class="input-group">
                                <span class="input-group-text  border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" 
                                       name="search" 
                                       class="form-control border-start-0" 
                                       placeholder="Name, email or phone..."
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">
                                <i class="fas fa-flag me-1"></i>Status
                            </label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="blocked" <?php echo $status_filter == 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                            </select>
                        </div>

                        <!-- Shift Filter -->
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">
                                <i class="fas fa-clock me-1"></i>Shift
                            </label>
                            <select name="shift" class="form-select">
                                <option value="">All Shifts</option>
                                <?php if ($shifts_result && $shifts_result->num_rows > 0): ?>
                                    <?php while($shift = $shifts_result->fetch_assoc()): ?>
                                        <option value="<?php echo $shift['id']; ?>" 
                                                <?php echo $shift_filter == $shift['id'] ? 'selected' : ''; ?>>
                                            <?php echo $shift['shift_name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Filter Actions -->
                        <div class="col-12 col-md-1">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i>
                                </button>
                                <a href="students.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Students Table Card -->
            <div class="card border-0 shadow-lg" data-aos="zoom-in" style="background: var(--card-bg); border-radius: 24px;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" style="width: 5%">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                        </div>
                                    </th>
                                    <th style="width: 20%">Student</th>
                                    <th style="width: 12%">Contact</th>
                                    <th style="width: 15%">Seat Details</th>
                                    <th style="width: 10%">Shift</th>
                                    <th style="width: 10%">Due Amount</th>
                                    <th style="width: 8%">Status</th>
                                    <th class="text-end pe-4" style="width: 20%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($student = $result->fetch_assoc()): 
                                        $status_class = '';
                                        $status_icon = '';
                                        
                                        switch($student['status']) {
                                            case 'active':
                                                $status_class = 'success';
                                                $status_icon = 'fa-circle-check';
                                                break;
                                            case 'inactive':
                                                $status_class = 'secondary';
                                                $status_icon = 'fa-circle-pause';
                                                break;
                                            case 'blocked':
                                                $status_class = 'danger';
                                                $status_icon = 'fa-circle-exclamation';
                                                break;
                                        }
                                        
                                        $due_amount = $student['total_due'] ?? 0;
                                        $due_class = $due_amount > 0 ? 'danger' : 'success';
                                        $seat_info = $student['seat_number'] ? "Seat {$student['seat_number']}, {$student['room']}" : 'Not Allocated';
                                        
                                        // [NEW] Check if student has future payments
                                        $has_future_payment = !empty($student['next_due_date']);
                                    ?>
                                    <tr id="student_<?php echo $student['id']; ?>">
                                        <td class="ps-4">
                                            <div class="form-check">
                                                <input class="form-check-input student-checkbox" 
                                                       type="checkbox" 
                                                       value="<?php echo $student['id']; ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="student-avatar me-3">
                                                    <?php if($student['photo']): ?>
                                                        <img src="<?php echo $student['photo']; ?>" 
                                                             class="rounded-circle" 
                                                             width="40" 
                                                             height="40"
                                                             style="object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="avatar-placeholder">
                                                            <?php echo strtoupper(substr($student['full_name'], 0, 2)); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($student['full_name']); ?></h6>
                                                    <small class="text-secondary">ID: #STU<?php echo str_pad($student['id'], 4, '0', STR_PAD_LEFT); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span><i class="fas fa-envelope me-2 small"></i><?php echo htmlspecialchars($student['email']); ?></span>
                                                <span><i class="fas fa-phone me-2 small"></i><?php echo htmlspecialchars($student['phone']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if($student['seat_number']): ?>
                                                <span class="fw-bold"><?php echo $student['seat_number']; ?></span>
                                                <br>
                                                <small class="text-secondary"><?php echo $student['room']; ?></small>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning">
                                                    <i class="fas fa-exclamation-circle me-1"></i>Not Allocated
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($student['shift_name']): ?>
                                                <span class="badge bg-primary-subtle text-primary">
                                                    <?php echo $student['shift_name']; ?>
                                                </span>
                                                <br>
                                                <small class="text-secondary">
                                                    <?php echo date('h:i A', strtotime($student['shift_start'])); ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- [NEW] Due amount with next due date info -->
                                            <span class="fw-bold text-<?php echo $due_class; ?>">
                                                ₹<?php echo number_format($due_amount, 2); ?>
                                            </span>
                                            
                                            <?php if($student['pending_payments'] > 0): ?>
                                                <br>
                                                <small class="text-danger">
                                                    (<?php echo $student['pending_payments']; ?> overdue)
                                                </small>
                                            <?php endif; ?>
                                            
                                            <?php if($has_future_payment && $due_amount == 0): ?>
                                                <br>
                                                <small class="text-success">
                                                    <i class="fas fa-clock me-1"></i>
                                                    Next: <?php echo date('d M', strtotime($student['next_due_date'])); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $status_class; ?>-subtle text-<?php echo $status_class; ?> px-3 py-2">
                                                <i class="fas <?php echo $status_icon; ?> me-1"></i>
                                                <?php echo ucfirst($student['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <!-- Mobile Dropdown -->
                                            <div class="dropdown d-md-none">
                                                <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="view-student.php?id=<?php echo $student['id']; ?>">
                                                            <i class="fas fa-eye me-2 text-primary"></i>View Details
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="edit-student.php?id=<?php echo $student['id']; ?>">
                                                            <i class="fas fa-edit me-2 text-warning"></i>Edit Student
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="allocate-seat.php?student_id=<?php echo $student['id']; ?>">
                                                            <i class="fas fa-chair me-2 text-success"></i>Allocate Seat
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="payments.php?student_id=<?php echo $student['id']; ?>">
                                                            <i class="fas fa-credit-card me-2 text-info"></i>View Payments
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" onclick="return deleteStudent(<?php echo $student['id']; ?>)">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            
                                            <!-- Desktop Buttons -->
                                            <div class="btn-group gap-1 d-none d-md-inline-flex">
                                                <a href="view-student.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-icon" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-student.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-icon" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-icon btn-delete" onclick="deleteStudent(<?php echo $student['id']; ?>)" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                                <h5 class="text-muted">No Students Found</h5>
                                                <p class="text-muted small mb-3">Try adjusting your filters or add a new student</p>
                                                <a href="add-student.php" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>Add New Student
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination & Info -->
                <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-transparent border-0 py-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <div class="text-secondary small">
                            Showing <?php echo $start + 1; ?> to <?php echo min($start + $limit, $total_students); ?> of <?php echo $total_students; ?> entries
                        </div>
                        
                        <nav aria-label="Student pagination">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo buildPaginationUrl($page - 1); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                for($i = $start_page; $i <= $end_page; $i++): ?>
                                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo buildPaginationUrl($i); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo buildPaginationUrl($page + 1); ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Bar -->
<div class="bulk-action-bar" id="bulkActionBar">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span class="fw-bold me-2" id="selectedCount">0</span>
                <span class="text-secondary">students selected</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-success" onclick="bulkAction('active')">
                    <i class="fas fa-check-circle me-1"></i>Activate
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="bulkAction('inactive')">
                    <i class="fas fa-pause-circle me-1"></i>Deactivate
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="bulkAction('delete')">
                    <i class="fas fa-trash me-1"></i>Delete
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>



<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // ============ DELETE STUDENT ============
    window.deleteStudent = function(id) {
        if (confirm('⚠️ Are you sure you want to delete this student?\n\nThis action cannot be undone and will remove all associated records (allocations, payments, etc.).')) {
            
            const row = $('#student_' + id);
            row.addClass('bg-danger-subtle').css('opacity', '0.5');
            
            $.ajax({
                url: 'ajax/delete_student.php',
                type: 'POST',
                data: { student_id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        row.fadeOut(400, function() {
                            $(this).remove();
                            alert('Student deleted successfully');
                            
                            // Update total count
                            const currentCount = parseInt($('.badge.bg-primary').text());
                            if (!isNaN(currentCount)) {
                                $('.badge.bg-primary').text(currentCount - 1);
                            }
                            
                            // Reload page if no rows left
                            if ($('#studentTableBody tr').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        row.removeClass('bg-danger-subtle').css('opacity', '1');
                        alert('Delete Failed: ' + response.message);
                    }
                },
                error: function() {
                    row.removeClass('bg-danger-subtle').css('opacity', '1');
                    alert('Failed to connect to server');
                }
            });
        }
        return false;
    };

    // ============ BULK ACTIONS ============
    window.bulkAction = function(action) {
        const selectedIds = [];
        $('.student-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Please select at least one student');
            return;
        }

        let actionUrl = '';
        let confirmMessage = '';

        switch(action) {
            case 'active':
                actionUrl = 'ajax/bulk_activate_students.php';
                confirmMessage = `Are you sure you want to activate ${selectedIds.length} student(s)?`;
                break;
            case 'inactive':
                actionUrl = 'ajax/bulk_deactivate_students.php';
                confirmMessage = `Are you sure you want to deactivate ${selectedIds.length} student(s)?`;
                break;
            case 'delete':
                actionUrl = 'ajax/bulk_delete_students.php';
                confirmMessage = `⚠️ WARNING: Are you sure you want to PERMANENTLY DELETE ${selectedIds.length} student(s)?\n\nThis action cannot be undone!`;
                break;
        }

        if (confirm(confirmMessage)) {
            $('#bulkActionBar').addClass('loading');
            
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: { student_ids: selectedIds },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Success: ' + response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert('Error: ' + response.message);
                        $('#bulkActionBar').removeClass('loading');
                    }
                },
                error: function() {
                    alert('Failed to process bulk action');
                    $('#bulkActionBar').removeClass('loading');
                }
            });
        }
    };

    // ============ EXPORT STUDENTS ============
    window.exportStudents = function() {
        const search = $('input[name="search"]').val();
        const status = $('select[name="status"]').val();
        const shift = $('select[name="shift"]').val();
        
        window.location.href = 'export-students.php?search=' + encodeURIComponent(search) + 
                              '&status=' + status + '&shift=' + shift;
    };

    // ============ BULK ACTION BAR UPDATE ============
    function updateBulkActionBar() {
        const count = $('.student-checkbox:checked').length;
        
        if (count > 0) {
            $('#selectedCount').text(count);
            $('#bulkActionBar').addClass('show');
        } else {
            $('#bulkActionBar').removeClass('show');
        }
    }

    // ============ SELECT ALL ============
    $('#selectAll').change(function() {
        $('.student-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkActionBar();
    });

    // ============ INDIVIDUAL CHECKBOX ============
    $(document).on('change', '.student-checkbox', function() {
        const totalCheckboxes = $('.student-checkbox').length;
        const checkedCheckboxes = $('.student-checkbox:checked').length;
        
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
        updateBulkActionBar();
    });

    // ============ CLEAR SELECTION ============
    window.clearSelection = function() {
        $('.student-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        $('#bulkActionBar').removeClass('show');
    };

    // ============ DEBOUNCE SEARCH ============
    let searchTimeout;
    $('input[name="search"]').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            $('#filterForm').submit();
        }, 500);
    });

    // ============ AUTO SUBMIT FILTERS ============
    $('select[name="status"], select[name="shift"]').change(function() {
        $('#filterForm').submit();
    });

    // ============ ESC KEY TO CLEAR SELECTION ============
    $(document).on('keyup', function(e) {
        if (e.key === 'Escape') {
            clearSelection();
        }
    });
});
</script>
