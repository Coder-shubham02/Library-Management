<?php
// sidebar.php - Sidebar Navigation (Mobile Responsive)
?>
<!-- Mobile Header -->
<div class="mobile-header d-lg-none">
    <div class="d-flex justify-content-between align-items-center p-3">
        <div class="d-flex align-items-center">
            <i class="fas fa-book-open fa-2x me-2" style="color: var(--primary-color);"></i>
            <h4 class="mb-0">LibraryPro X</h4>
        </div>
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</div>

<!-- Sidebar -->
<div class="col-lg-2 sidebar" id="sidebar">
    <div class="sidebar-content">
        <!-- Close Button for Mobile -->
        <button class="sidebar-close d-lg-none" id="sidebarClose">
            <i class="fas fa-times"></i>
        </button>

        <div class="brand text-center">
            <i class="fas fa-book-open fa-3x mb-3" style="color: var(--primary-color);"></i>
            <h4 class="mb-0">LibraryPro X</h4>
            <p class="text-secondary small mt-2">Enterprise Management</p>
        </div>

        <nav class="nav flex-column">
            <a class="nav-link active" href="index.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>

            <div class="nav-item">
                <a class="nav-link dropdown-toggle-custom" data-bs-toggle="collapse" href="#seatMenu">
                    <i class="fas fa-chair"></i>
                    <span>Seat Manager</span>
                    <i class="fas fa-chevron-right ms-auto arrow-icon"></i>
                </a>
                <div class="collapse submenu-container" id="seatMenu">
                    <a class="nav-link submenu-link" href="add_seat.php">Add New Seat</a>
                </div>
            </div>

            <div class="nav-item">
                <a class="nav-link dropdown-toggle-custom" data-bs-toggle="collapse" href="#studentMenu">
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                    <i class="fas fa-chevron-right ms-auto arrow-icon"></i>
                </a>
                <div class="collapse submenu-container" id="studentMenu">
                    <a class="nav-link submenu-link" href="add-student.php">Add New Student</a>
                    <a class="nav-link submenu-link" href="manage-students.php">Manage Students</a>
                    <a class="nav-link submenu-link" href="attendance.php">Attendance</a>
                </div>
            </div>

            <a class="nav-link" href="#">
                <i class="fas fa-clock"></i>
                <span>Shifts</span>
            </a>

            <div class="nav-item">
                <a class="nav-link dropdown-toggle-custom" data-bs-toggle="collapse" href="#paymentMenu">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                    <i class="fas fa-chevron-right ms-auto arrow-icon"></i>
                </a>
                <div class="collapse submenu-container" id="paymentMenu">
                    <a class="nav-link submenu-link" href="collect-fees.php">Collect Fees</a>
                    <a class="nav-link submenu-link" href="fee-history.php">Fee History</a>
                </div>
            </div>

            <a class="nav-link" href="#">
                <i class="fas fa-chart-pie"></i>
                <span>Analytics</span>
            </a>

            <a class="nav-link" href="#">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </nav>

        <div class="mt-auto p-3">
            <div class="d-flex align-items-center text-secondary">
                <i class="fas fa-circle text-success me-2" style="font-size: 8px;"></i>
                <span class="small">System Online</span>
            </div>
            <div class="mt-3">
                <div class="d-flex align-items-center">
                    <img src="https://ui-avatars.com/api/?name=Library+Owner&background=4361ee&color=fff&bold=true"
                        alt="Profile"
                        class="rounded-circle me-2"
                        width="32"
                        height="32">
                    <div>
                        <p class="mb-0 small fw-semibold">Library Owner</p>
                        <p class="mb-0 text-secondary small">admin@library.com</p>
                    </div>
                </div>
                <!-- Logout Button with Confirmation -->
                <a href="logout.php" class="btn btn-danger w-100 mt-2" id="logoutBtn">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</div>