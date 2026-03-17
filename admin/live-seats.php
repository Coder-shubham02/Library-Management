<?php
require "config/database.php";
include 'includes/header.php';
include 'includes/sidebar.php';

// Get seats with current allocation and student info
$query = "SELECT 
            s.*,
            sh.shift_name,
            sa.id as allocation_id,
            sa.student_id,
            stu.full_name as student_name,
            stu.photo as student_photo,
            sa.start_date,
            sa.end_date
          FROM seats s 
          LEFT JOIN shifts sh ON s.shift_id = sh.id 
          LEFT JOIN seat_allocations sa ON s.id = sa.seat_id AND sa.status = 'active'
          LEFT JOIN students stu ON sa.student_id = stu.id
          ORDER BY s.room, s.seat_number ASC";

$result = $conn->query($query);

$seats_by_room = [];
$stats = [
    'active' => 0, 
    'inactive' => 0, 
    'total' => 0,
    'occupied' => 0,
    'free' => 0
];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $room = $row['room'];
        $seats_by_room[$room][] = $row;
        
        // Stats update
        $stats['total']++;
        if (strtolower($row['status']) == 'active') {
            $stats['active']++;
            if ($row['student_id']) {
                $stats['occupied']++;
            } else {
                $stats['free']++;
            }
        } else {
            $stats['inactive']++;
        }
    }
}
?>
<link rel="stylesheet" href="assets/css/live-seats.css">

<!-- Page Title -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header with Stats Cards -->
            <div class="page-header mb-4" data-aos="fade-down">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h1 class="display-5 fw-bold">
                            <span class="text-gradient">Live Seats</span>
                        </h1>
                        <p class="text-secondary mb-0">
                            <i class="fas fa-chair me-2"></i>
                            Real-time seat availability and occupancy
                        </p>
                    </div>
                    <button class="btn btn-outline-primary" onclick="refreshSeats()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                </div>

                <!-- Stats Cards -->
                <div class="row g-3 mt-4">
                    <div class="col-md-3 col-6">
                        <div class="stat-card-mini bg-primary bg-opacity-10">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon-mini bg-primary text-white rounded-circle me-3">
                                    <i class="fas fa-chair"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0"><?php echo $stats['total']; ?></h3>
                                    <small class="text-secondary">Total Seats</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card-mini bg-success bg-opacity-10">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon-mini bg-success text-white rounded-circle me-3">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0"><?php echo $stats['free']; ?></h3>
                                    <small class="text-secondary">Free Seats</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card-mini bg-warning bg-opacity-10">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon-mini bg-warning text-white rounded-circle me-3">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0"><?php echo $stats['occupied']; ?></h3>
                                    <small class="text-secondary">Occupied</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card-mini bg-danger bg-opacity-10">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon-mini bg-danger text-white rounded-circle me-3">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-0"><?php echo $stats['inactive']; ?></h3>
                                    <small class="text-secondary">Inactive</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Badges -->
                <div class="mt-4 d-flex gap-2 flex-wrap">
                    <span class="badge bg-primary bg-opacity-15 text-primary px-4 py-2 rounded-pill" onclick="filterSeats('all')" style="cursor: pointer;">
                        <i class="fas fa-list me-2"></i>All Seats
                    </span>
                    <span class="badge bg-success bg-opacity-15 text-success px-4 py-2 rounded-pill" onclick="filterSeats('free')" style="cursor: pointer;">
                        <i class="fas fa-check-circle me-2"></i>Free (<?php echo $stats['free']; ?>)
                    </span>
                    <span class="badge bg-light bg-opacity-15 text-warning px-4 py-2 rounded-pill" onclick="filterSeats('occupied')" style="cursor: pointer;">
                        <i class="fas fa-user-graduate me-2"></i>Occupied (<?php echo $stats['occupied']; ?>)
                    </span>
                    <span class="badge bg-danger bg-opacity-15 text-danger px-4 py-2 rounded-pill" onclick="filterSeats('inactive')" style="cursor: pointer;">
                        <i class="fas fa-ban me-2"></i>Inactive (<?php echo $stats['inactive']; ?>)
                    </span>
                </div>
            </div>

            <!-- Live Seats Grid -->
            <div class="live-seats-page" data-aos="fade-up">
                <?php if (!empty($seats_by_room)): ?>
                    <?php foreach ($seats_by_room as $room_name => $seats): ?>
                        <!-- Room Section -->
                        <div class="room-section mb-5">
                            <div class="room-header mb-3">
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="d-flex align-items-center">
                                        <div class="room-icon me-3">
                                            <i class="fas fa-door-open"></i>
                                        </div>
                                        <div>
                                            <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($room_name); ?></h3>
                                            <p class="text-secondary small mb-0">
                                                <i class="fas fa-chair me-1"></i>
                                                <?php echo count($seats); ?> seats | 
                                                <span class="text-success"><?php echo array_reduce($seats, function($carry, $seat) { 
                                                    return $carry + (strtolower($seat['status']) == 'active' && !$seat['student_id'] ? 1 : 0); 
                                                }, 0); ?> free</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="room-stats mt-2 mt-md-0">
                                        <span class="badge bg-success-subtle text-success me-2">
                                            <i class="fas fa-check-circle me-1"></i>
                                            <?php echo array_reduce($seats, function($carry, $seat) { 
                                                return $carry + (strtolower($seat['status']) == 'active' && $seat['student_id'] ? 1 : 0); 
                                            }, 0); ?> occupied
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Seats Grid for this Room -->
                            <div class="seats-grid">
                                <?php foreach ($seats as $seat): 
                                    $status = strtolower($seat['status']);
                                    $isActive = ($status == 'active');
                                    $isOccupied = $isActive && $seat['student_id'];
                                    
                                    // Determine card class and icon
                                    if ($isOccupied) {
                                        $cardClass = 'occupied';
                                        $statusIcon = 'fa-user-graduate';
                                        $statusColor = 'warning';
                                    } elseif ($isActive) {
                                        $cardClass = 'free';
                                        $statusIcon = 'fa-check-circle';
                                        $statusColor = 'success';
                                    } else {
                                        $cardClass = 'inactive';
                                        $statusIcon = 'fa-ban';
                                        $statusColor = 'danger';
                                    }
                                ?>
                                <div class="seat-card <?php echo $cardClass; ?>" 
                                     data-seat-id="<?php echo $seat['id']; ?>"
                                     data-seat-number="<?php echo $seat['seat_number']; ?>"
                                     data-room="<?php echo $room_name; ?>"
                                     data-status="<?php echo $status; ?>"
                                     data-occupied="<?php echo $isOccupied ? 'true' : 'false'; ?>"
                                     onclick="showSeatDetails(this)">
                                    
                                    <div class="seat-header">
                                        <span class="seat-number">Seat <?php echo $seat['seat_number']; ?></span>
                                        <i class="fas <?php echo $statusIcon; ?> text-<?php echo $statusColor; ?>"></i>
                                    </div>

                                    <div class="seat-body">
                                        <?php if ($isOccupied): ?>
                                            <!-- Student Info for Occupied Seat -->
                                            <div class="student-info-compact d-flex align-items-center">
                                                <div class="student-avatar-sm me-2">
                                                    <?php if($seat['student_photo']): ?>
                                                        <img src="<?php echo $seat['student_photo']; ?>" 
                                                             class="rounded-circle" 
                                                             width="35" 
                                                             height="35"
                                                             style="object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="avatar-placeholder-sm">
                                                            <?php echo strtoupper(substr($seat['student_name'], 0, 2)); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="student-info-text">
                                                    <div class="fw-bold small text-truncate" style="max-width: 120px;">
                                                        <?php echo htmlspecialchars($seat['student_name']); ?>
                                                    </div>
                                                    <small class="text-secondary d-block">
                                                        <i class="fas fa-calendar-alt me-1" style="font-size: 9px;"></i>
                                                        Since <?php echo date('d M', strtotime($seat['start_date'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <!-- View Student Link -->
                                            <a href="view-student.php?id=<?php echo $seat['student_id']; ?>" 
                                               class="student-view-link mt-2" 
                                               onclick="event.stopPropagation();"
                                               title="View Student Details">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                        <?php elseif ($isActive): ?>
                                            <!-- Free Seat -->
                                            <div class="text-center py-2">
                                                <i class="fas fa-check-circle text-success mb-1" style="font-size: 1.5rem;"></i>
                                                <span class="d-block small fw-bold text-success">Available</span>
                                                <a href="allocate-seat.php?seat_id=<?php echo $seat['id']; ?>" 
                                                   class="btn btn-sm btn-outline-success mt-2"
                                                   onclick="event.stopPropagation();">
                                                    <i class="fas fa-plus me-1"></i>Allocate
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <!-- Inactive Seat -->
                                            <div class="text-center py-3">
                                                <i class="fas fa-ban text-danger mb-1" style="font-size: 1.5rem;"></i>
                                                <span class="d-block small fw-bold text-danger">Inactive</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="seat-footer">
                                        <span class="badge bg-<?php echo $statusColor; ?>-subtle text-<?php echo $statusColor; ?>">
                                            <?php 
                                            if ($isOccupied) echo 'Occupied';
                                            elseif ($isActive) echo 'Free';
                                            else echo 'Inactive';
                                            ?>
                                        </span>
                                        <span class="shift-badge">
                                            <i class="fas fa-clock me-1" style="font-size: 9px;"></i>
                                            <?php echo $seat['shift_name'] ?? 'No Shift'; ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state text-center py-5">
                        <i class="fas fa-chair fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No Seats Found</h5>
                        <p class="text-muted small">Add seats to see them here</p>
                        <a href="add-seat.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add New Seat
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Seat Details Modal -->
<div class="modal fade" id="seatDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seat Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="seatDetailsContent">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Custom CSS for Live Seats Page -->
<style>
/* Stats Mini Cards */
.stat-card-mini {
    padding: 1rem;
    border-radius: 16px;
    transition: all 0.3s ease;
}

.stat-card-mini:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
}

.stat-icon-mini {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

/* Seat Card Variations */
.seat-card.free {
    border-color: #06d6a0;
    background: linear-gradient(135deg, rgba(6, 214, 160, 0.05), transparent);
}

.seat-card.occupied {
    border-color: #ffb703;
    background: linear-gradient(135deg, rgba(255, 183, 3, 0.05), transparent);
}

.seat-card.inactive {
    border-color: #ef476f;
    background: linear-gradient(135deg, rgba(239, 71, 111, 0.05), transparent);
    opacity: 0.8;
}

/* Student Avatar Small */
.student-avatar-sm {
    flex-shrink: 0;
}

.avatar-placeholder-sm {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: var(--gradient-1);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.8rem;
}

/* Student Info Compact */
.student-info-compact {
    background: var(--card-bg);
    border-radius: 30px;
    padding: 0.4rem 0.8rem;
    border: 1px solid var(--border-color);
}

.student-view-link {
    display: inline-block;
    font-size: 0.8rem;
    color: var(--primary-color);
    text-decoration: none;
    padding: 0.2rem 0.8rem;
    border-radius: 30px;
    background: rgba(67, 97, 238, 0.1);
    transition: all 0.3s ease;
}

.student-view-link:hover {
    background: var(--primary-color);
    color: white;
}

/* Shift Badge */
.shift-badge {
    font-size: 0.7rem;
    color: var(--text-secondary);
    background: var(--hover-bg);
    padding: 0.2rem 0.5rem;
    border-radius: 30px;
}

/* Filter Badges Clickable */
.badge {
    cursor: pointer;
    transition: all 0.3s ease;
}

.badge:hover {
    transform: translateY(-2px);
    filter: brightness(1.1);
}

.badge.active {
    box-shadow: 0 0 0 2px var(--primary-color);
}

/* Dark Mode Adjustments */
[data-theme="dark"] .student-info-compact, 
[data-theme="dark"] .modal-content {
    background: var(--hover-bg);
}

[data-theme="dark"] .shift-badge,
[data-theme="dark"] .detail-item {
    background: var(--card-bg);
}

/* [data-theme="dark"] .modal-content {
    background: var(--card-bg);
} */

/* Responsive */
@media (max-width: 768px) {
    .stat-card-mini {
        padding: 0.8rem;
    }
    
    .stat-icon-mini {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }
    
    .student-info-text {
        max-width: 100px;
    }
}
</style>

<!-- JavaScript for Live Seats -->
<script>
$(document).ready(function() {
    // Initialize tooltips if any
    $('[data-bs-toggle="tooltip"]').tooltip();
});

// ============ SHOW SEAT DETAILS ============
function showSeatDetails(element) {
    const $card = $(element);
    const seatId = $card.data('seat-id');
    const seatNumber = $card.data('seat-number');
    const room = $card.data('room');
    const status = $card.data('status');
    const isOccupied = $card.data('occupied') === 'true';
    
    let detailsHtml = `
        <div class="text-center mb-4">
            <div class="seat-detail-badge mb-3">
                <span class="badge bg-primary" style="font-size: 2rem; padding: 1rem 2rem;">
                    ${seatNumber}
                </span>
            </div>
            <h5>Seat ${seatNumber}</h5>
            <p class="text-secondary">${room}</p>
        </div>
        
        <div class="row g-3">
            <div class="col-6">
                <div class="detail-item p-3 rounded-3">
                    <small class="text-secondary d-block">Status</small>
                    <span class="fw-bold text-${isOccupied ? 'warning' : (status === 'active' ? 'success' : 'danger')}">
                        ${isOccupied ? 'Occupied' : (status === 'active' ? 'Available' : 'Inactive')}
                    </span>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-item p-3 rounded-3">
                    <small class="text-secondary d-block">Shift</small>
                    <span class="fw-bold">${$card.find('.shift-badge').text().trim() || 'No Shift'}</span>
                </div>
            </div>
        </div>
    `;
    
    if (isOccupied) {
        const studentName = $card.find('.student-info-text .fw-bold').text();
        const studentAvatar = $card.find('.student-avatar-sm img').attr('src') || '';
        const studentId = $card.find('.student-view-link').attr('href').match(/id=(\d+)/)[1];
        
        detailsHtml += `
            <div class="mt-4">
                <h6 class="fw-bold mb-3">Student Details</h6>
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                    <div class="student-avatar">
                        ${studentAvatar ? 
                            `<img src="${studentAvatar}" class="rounded-circle" width="50" height="50" style="object-fit: cover;">` :
                            `<div class="avatar-placeholder" style="width: 50px; height: 50px; font-size: 1.2rem;">${studentName.substring(0,2)}</div>`
                        }
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">${studentName}</h6>
                        <a href="view-student.php?id=${studentId}" class="btn btn-sm btn-primary" onclick="event.stopPropagation();">
                            <i class="fas fa-eye me-1"></i>View Profile
                        </a>
                    </div>
                </div>
            </div>
        `;
    } else if (status === 'active') {
        detailsHtml += `
            <div class="mt-4 text-center">
                <a href="allocate-seat.php?seat_id=${seatId}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Allocate to Student
                </a>
            </div>
        `;
    }
    
    $('#seatDetailsContent').html(detailsHtml);
    $('#seatDetailsModal').modal('show');
}

// ============ FILTER SEATS ============
function filterSeats(filter) {
    $('.badge').removeClass('active');
    $(event.currentTarget).addClass('active');
    
    if (filter === 'all') {
        $('.seat-card').show();
    } else if (filter === 'free') {
        $('.seat-card').hide();
        $('.seat-card.free').show();
    } else if (filter === 'occupied') {
        $('.seat-card').hide();
        $('.seat-card.occupied').show();
    } else if (filter === 'inactive') {
        $('.seat-card').hide();
        $('.seat-card.inactive').show();
    }
    
    // Hide empty rooms
    $('.room-section').each(function() {
        const visibleSeats = $(this).find('.seat-card:visible').length;
        if (visibleSeats === 0) {
            $(this).hide();
        } else {
            $(this).show();
        }
    });
}

// ============ REFRESH SEATS ============
function refreshSeats() {
    $('.seat-card').addClass('loading');
    
    setTimeout(function() {
        location.reload();
    }, 1000);
}

// ============ SEARCH FUNCTIONALITY ============
function searchSeats() {
    const searchInput = document.getElementById('searchSeats');
    if (!searchInput) return;
    
    const value = searchInput.value.toLowerCase();
    
    $('.seat-card').each(function() {
        const text = $(this).text().toLowerCase();
        if (text.indexOf(value) > -1) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

// Add search input if needed
$(document).ready(function() {
    const searchHtml = `
        <div class="mb-4">
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" 
                       class="form-control border-start-0" 
                       id="searchSeats" 
                       placeholder="Search seats..."
                       onkeyup="searchSeats()"
                       style="background: var(--card-bg); color: var(--text-primary);">
            </div>
        </div>
    `;
    
    // Uncomment to add search
    // $('.page-header').after(searchHtml);
});
</script>