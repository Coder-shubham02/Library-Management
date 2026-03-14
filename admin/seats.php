<?php
require "config/database.php";
include 'includes/header.php';
include 'includes/sidebar.php';

// Toast configuration - Uncomment if you have toast
// require_once 'includes/toast-config.php';

// Pagination Logic
$limit = 10; // Records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$start = ($page - 1) * $limit;

// Get total seats count
$total_query = "SELECT COUNT(*) as total FROM seats";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_seats = $total_row['total'];
$total_pages = ceil($total_seats / $limit);

// Ensure page doesn't exceed total pages
if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
    $start = ($page - 1) * $limit;
}

// Get paginated seats
$query = "SELECT * FROM seats ORDER BY seat_number ASC LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
?>
<link rel="stylesheet" href="assets/css/seat.css">
<!-- Page Content -->
<div class="container-fluid py-4 main-content-area">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 px-2 gap-3">
                <div data-aos="fade-right">
                    <h3 class="fw-bold mb-0 text-gradient">Seat Inventory</h3>
                    <p class="text-secondary small mb-0">
                        <i class="fas fa-chair me-1"></i>
                        Total Registered Seats: 
                        <span class="badge bg-primary rounded-pill ms-2">
                            <?php echo $total_seats; ?>
                        </span>
                    </p>
                </div>
                
                <div class="d-flex gap-2" data-aos="fade-left">
                    <div class="input-group search-group shadow-sm">
                        <span class="input-group-text border-0">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               id="tableSearch" 
                               class="form-control border-0" 
                               placeholder="Search by seat or room..."
                               autocomplete="off">
                    </div>
                    <a href="add_seat.php" class="btn btn-primary btn-add shadow-sm">
                        <i class="fas fa-plus me-1"></i> Add New
                    </a>
                </div>
            </div>

            <!-- Status Filter Tabs -->
            <div class="mb-3 px-2" data-aos="fade-up">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary active" id="filterAll">All</button>
                    <button class="btn btn-outline-success" id="filterActive">Active</button>
                    <button class="btn btn-outline-danger" id="filterInactive">Inactive</button>
                </div>
            </div>

            <!-- Seats Table Card -->
            <div class="card border-0 shadow-sm custom-theme-card overflow-hidden" data-aos="zoom-in">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 custom-table">
                            <thead class="table-light-header">
                                <tr>
                                    <th class="ps-4" style="width: 30%;">Seat Info</th>
                                    <th style="width: 25%;">Room</th>
                                    <th style="width: 20%;">Status</th>
                                    <th class="text-end pe-4" style="width: 25%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="seatTableBody">
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): 
                                        $status = strtolower($row['status']);
                                        $statusClass = ($status == 'active') ? 'active' : 'inactive';
                                        $statusIcon = ($status == 'active') ? 'fa-circle-check' : 'fa-circle-exclamation';
                                    ?>
                                    <tr id="row_<?php echo $row['id']; ?>" class="fade-in seat-row" data-status="<?php echo $status; ?>">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="seat-icon me-3">
                                                    <?php echo $row['seat_number']; ?>
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-main d-block">
                                                        Seat <?php echo $row['seat_number']; ?>
                                                    </span>
                                                    <small class="text-secondary">ID: #<?php echo $row['id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-secondary">
                                                <i class="fas fa-door-open me-2"></i>
                                                <?php echo htmlspecialchars($row['room']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $statusClass; ?>" 
                                                  data-id="<?php echo $row['id']; ?>"
                                                  data-status="<?php echo $status; ?>">
                                                <i class="fas <?php echo $statusIcon; ?>"></i>
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button class="btn btn-icon edit-seat-btn" 
                                                        data-id="<?php echo $row['id']; ?>" 
                                                        title="Edit Seat"
                                                        data-bs-toggle="tooltip">
                                                    <i class="fas fa-pen-nib"></i>
                                                </button>
                                                <button class="btn btn-icon btn-delete delete-seat" 
                                                        data-id="<?php echo $row['id']; ?>" 
                                                        title="Delete Seat"
                                                        data-bs-toggle="tooltip">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <div class="empty-state">
                                                <i class="fas fa-chair"></i>
                                                <h5 class="text-muted mt-3">No Seats Found</h5>
                                                <p class="text-muted small">Click "Add New" to create your first seat</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PAGINATION - FIXED AND WORKING -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <ul class="pagination">
                        <!-- First Page -->
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=1" aria-label="First">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        
                        <!-- Previous Page -->
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>" aria-label="Previous">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                        
                        <!-- Page Numbers -->
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Next Page -->
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>" aria-label="Next">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        
                        <!-- Last Page -->
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $total_pages; ?>" aria-label="Last">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Page Info -->
                    <div class="page-info">
                        Showing page <?php echo $page; ?> of <?php echo $total_pages; ?> 
                        (<?php echo $total_seats; ?> total seats)
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Seat Modal -->
<div class="modal fade" id="editSeatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-edit me-2 text-primary"></i>
                    Edit Seat Information
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSeatForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Seat Number</label>
                        <div class="input-group">
                            <span class="input-group-text modal-input border-end-0">
                                <i class="fas fa-hashtag"></i>
                            </span>
                            <input type="number" 
                                   name="seat_number" 
                                   id="edit_seat_number" 
                                   class="form-control modal-input border-start-0" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Room / Hall Name</label>
                        <div class="input-group">
                            <span class="input-group-text modal-input border-end-0">
                                <i class="fas fa-door-open"></i>
                            </span>
                            <input type="text" 
                                   name="room" 
                                   id="edit_room" 
                                   class="form-control modal-input border-start-0" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Status</label>
                        <select name="status" id="edit_status" class="form-select modal-input">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer px-4 pb-4 border-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 btn-gradient">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- JavaScript -->
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // ============ SEARCH ============
    $("#tableSearch").on("keyup", function() {
        let searchText = $(this).val().toLowerCase().trim();
        let visibleCount = 0;
        
        $("#seatTableBody tr.seat-row").each(function() {
            let rowText = $(this).text().toLowerCase();
            if (searchText === '' || rowText.indexOf(searchText) > -1) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        
        // Show no results message
        $("#noResultsRow").remove();
        if (visibleCount === 0 && $("#seatTableBody tr.seat-row").length > 0) {
            $("#seatTableBody").append(`
                <tr id="noResultsRow">
                    <td colspan="4" class="text-center py-5">
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <h5 class="text-muted mt-3">No matching seats found</h5>
                            <p class="text-muted small">Try different search terms</p>
                        </div>
                    </td>
                </tr>
            `);
        }
    });

    // ============ FILTER BY STATUS ============
    $('#filterAll, #filterActive, #filterInactive').click(function() {
        $(this).addClass('active').siblings().removeClass('active');
        let status = $(this).attr('id').replace('filter', '').toLowerCase();
        let visibleCount = 0;
        
        $("#seatTableBody tr.seat-row").each(function() {
            let rowStatus = $(this).data('status');
            if (status === 'all' || rowStatus === status) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        
        // Clear search
        $('#tableSearch').val('');
        $("#noResultsRow").remove();
        
        // Show no results message
        if (visibleCount === 0) {
            $("#seatTableBody").append(`
                <tr id="noResultsRow">
                    <td colspan="4" class="text-center py-5">
                        <div class="empty-state">
                            <i class="fas fa-filter"></i>
                            <h5 class="text-muted mt-3">No ${status} seats found</h5>
                            <p class="text-muted small">Try another filter</p>
                        </div>
                    </td>
                </tr>
            `);
        }
    });

    // ============ EDIT SEAT ============
    $('.edit-seat-btn').on('click', function() {
        let id = $(this).data('id');
        let btn = $(this);
        let originalHtml = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

        $.ajax({
            url: 'ajax/get_seat_details.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(data) {
                $('#edit_id').val(data.id);
                $('#edit_seat_number').val(data.seat_number);
                $('#edit_room').val(data.room);
                $('#edit_status').val(data.status);
                
                btn.html(originalHtml).prop('disabled', false);
                $('#editSeatModal').modal('show');
            },
            error: function() {
                alert('Failed to load seat details');
                btn.html(originalHtml).prop('disabled', false);
            }
        });
    });

    // ============ UPDATE SEAT ============
    $('#editSeatForm').on('submit', function(e) {
        e.preventDefault();
        
        let btn = $(this).find('button[type="submit"]');
        let originalText = btn.html();
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

        $.ajax({
            url: 'ajax/update_seat.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    $('#editSeatModal').modal('hide');
                    
                    // Update row
                    let row = $(`#row_${response.id}`);
                    row.find('.seat-icon').text(response.seat_number);
                    row.find('.text-main').text(`Seat ${response.seat_number}`);
                    row.find('td:nth-child(2)').html(`<i class="fas fa-door-open me-2"></i>${response.room}`);
                    
                    let statusBadge = row.find('.status-badge');
                    let newStatus = response.status.toLowerCase();
                    let statusIcon = newStatus === 'active' ? 'fa-circle-check' : 'fa-circle-exclamation';
                    
                    statusBadge.removeClass('active inactive')
                              .addClass(newStatus)
                              .attr('data-status', newStatus)
                              .html(`<i class="fas ${statusIcon}"></i> ${response.status}`);
                    
                    alert('Seat updated successfully!');
                } else {
                    alert('Update Failed: ' + response.message);
                }
            },
            error: function() {
                alert('Failed to update seat');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // ============ DELETE SEAT ============
    $('.delete-seat').on('click', function() {
        let id = $(this).data('id');
        let row = $(`#row_${id}`);
        
        if (confirm('Are you sure you want to delete this seat?')) {
            $.ajax({
                url: 'ajax/delete_seat.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        row.fadeOut(400, function() {
                            $(this).remove();
                            alert('Seat deleted successfully');
                            
                            // Update count
                            let count = parseInt($('.badge.bg-primary').text());
                            $('.badge.bg-primary').text(count - 1);
                        });
                    } else {
                        alert('Delete Failed: ' + response.message);
                    }
                }
            });
        }
    });

    // ============ TOGGLE STATUS ============
    $(document).on('click', '.status-badge', function() {
        let badge = $(this);
        let id = badge.data('id');
        
        badge.css('pointer-events', 'none').css('opacity', '0.7');

        $.ajax({
            url: 'ajax/toggle_status.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    let newStatus = response.new_status.toLowerCase();
                    let statusIcon = newStatus === 'active' ? 'fa-circle-check' : 'fa-circle-exclamation';
                    
                    badge.removeClass('active inactive')
                         .addClass(newStatus)
                         .attr('data-status', newStatus)
                         .html(`<i class="fas ${statusIcon}"></i> ${response.new_status}`);
                    
                    $(`#row_${id}`).attr('data-status', newStatus);
                }
            },
            complete: function() {
                badge.css('pointer-events', 'auto').css('opacity', '1');
            }
        });
    });

    // ============ ESC CLEAR SEARCH ============
    $('#tableSearch').on('keyup', function(e) {
        if (e.key === 'Escape') {
            $(this).val('').trigger('keyup');
        }
    });
});
</script>