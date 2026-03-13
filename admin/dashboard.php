<?php
// index.php - Main Dashboard Page
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Main Content -->
<div class="col-lg-10 col-md-12 main-content" id="mainContent">
    <!-- Top Navbar -->
    <div class="navbar-top d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <!-- <i class="fas fa-bars text-secondary fs-5 d-lg-none" id="mobileMenuBtn" role="button"></i> -->
            <h5 class="mb-0 fw-semibold">Dashboard Overview</h5>
            <span class="badge bg-primary bg-opacity-10 text-primary">Live</span>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <!-- Theme Toggle -->
            <div class="theme-toggle" id="themeToggle">
                <i class="fas fa-moon" id="themeIcon"></i>
            </div>
            
            <!-- Notifications -->
            <div class="theme-toggle position-relative">
                <i class="fas fa-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    3
                </span>
            </div>
            
            <!-- Profile - Hidden on mobile -->
            <div class="d-none d-md-flex align-items-center gap-2">
                <img src="https://ui-avatars.com/api/?name=Library+Owner&background=4361ee&color=fff&bold=true" 
                     alt="Profile" 
                     class="rounded-circle"
                     width="40"
                     height="40">
                <div>
                    <p class="mb-0 fw-semibold small">Library Owner</p>
                    <p class="mb-0 text-secondary small">admin@library.com</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 g-md-4 mb-4">
        <div class="col-6 col-xl-3" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon me-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <p class="text-secondary mb-1 small">Total Students</p>
                        <h3 class="mb-0 fw-bold">248</h3>
                        <small class="text-success">
                            <i class="fas fa-arrow-up me-1"></i>+12
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-xl-3" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon" style="background: var(--gradient-2);">
                        <i class="fas fa-chair"></i>
                    </div>
                    <div>
                        <p class="text-secondary mb-1 small">Total Seats</p>
                        <h3 class="mb-0 fw-bold">120</h3>
                        <small class="text-warning">
                            <i class="fas fa-clock me-1"></i>42
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-xl-3" data-aos="fade-up" data-aos-delay="300">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon" style="background: var(--gradient-3);">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div>
                        <p class="text-secondary mb-1 small">Today's Revenue</p>
                        <h3 class="mb-0 fw-bold">₹4.2k</h3>
                        <small class="text-success">
                            <i class="fas fa-chart-line me-1"></i>+8%
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-xl-3" data-aos="fade-up" data-aos-delay="400">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ef476f, #d90467);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <p class="text-secondary mb-1 small">Pending Dues</p>
                        <h3 class="mb-0 fw-bold">₹12.4k</h3>
                        <small class="text-danger">
                            <i class="fas fa-users me-1"></i>18
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 g-md-4 mb-4">
        <div class="col-xl-8" data-aos="fade-right">
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        Revenue (Last 7 Days)
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary">Week</button>
                        <button class="btn btn-primary">Month</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4" data-aos="fade-left">
            <div class="table-card h-100">
                <h6 class="mb-3">
                    <i class="fas fa-pie-chart me-2 text-primary"></i>
                    Shift Distribution
                </h6>
                <div class="chart-container" style="height: 180px;">
                    <canvas id="shiftChart"></canvas>
                </div>
                <div class="mt-3 small">
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-circle text-primary me-2"></i>Morning</span>
                        <span class="fw-bold">45</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="fas fa-circle text-warning me-2"></i>Evening</span>
                        <span class="fw-bold">38</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><i class="fas fa-circle text-success me-2"></i>Full Day</span>
                        <span class="fw-bold">27</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Seat Map - Mobile Optimized -->
    <div class="table-card" data-aos="zoom-in">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <h6 class="mb-2 mb-md-0">
                <i class="fas fa-map-marked-alt me-2 text-primary"></i>
                Live Seats
            </h6>
            <div class="d-flex gap-2">
                <span class="badge bg-primary">M:45</span>
                <span class="badge bg-warning">E:38</span>
                <span class="badge bg-success">F:27</span>
            </div>
        </div>

        <!-- Scrollable Seat Map for Mobile -->
        <div class="seat-map-wrapper">
            <div class="seat-map">
                <!-- Row A -->
                <div class="seat-row">
                    <div class="row-label">A</div>
                    <div class="seat-item occupied-full" data-student="Rahul Sharma">
                        <div class="seat-number">A1</div>
                        <div class="student-name">Rahul</div>
                    </div>
                    <div class="seat-item occupied-morning" data-student="Priya Singh">
                        <div class="seat-number">A2</div>
                        <div class="student-name">Priya</div>
                    </div>
                    <div class="seat-item occupied-evening" data-student="Amit Kumar">
                        <div class="seat-number">A3</div>
                        <div class="student-name">Amit</div>
                    </div>
                    <div class="seat-item occupied-morning" data-student="Neha Gupta">
                        <div class="seat-number">A4</div>
                        <div class="student-name">Neha</div>
                    </div>
                    <div class="seat-item">
                        <div class="seat-number">A5</div>
                        <div class="student-name">Available</div>
                    </div>
                    <div class="seat-item occupied-full" data-student="Vikram Patel">
                        <div class="seat-number">A6</div>
                        <div class="student-name">Vikram</div>
                    </div>
                </div>

                <!-- Row B -->
                <div class="seat-row">
                    <div class="row-label">B</div>
                    <div class="seat-item occupied-morning" data-student="Anjali Verma">
                        <div class="seat-number">B1</div>
                        <div class="student-name">Anjali</div>
                    </div>
                    <div class="seat-item occupied-evening" data-student="Suresh Yadav">
                        <div class="seat-number">B2</div>
                        <div class="student-name">Suresh</div>
                    </div>
                    <div class="seat-item occupied-full" data-student="Deepa Krishnan">
                        <div class="seat-number">B3</div>
                        <div class="student-name">Deepa</div>
                    </div>
                    <div class="seat-item">
                        <div class="seat-number">B4</div>
                        <div class="student-name">Available</div>
                    </div>
                    <div class="seat-item occupied-morning" data-student="Raj Malhotra">
                        <div class="seat-number">B5</div>
                        <div class="student-name">Raj</div>
                    </div>
                    <div class="seat-item occupied-evening" data-student="Kavita Rani">
                        <div class="seat-number">B6</div>
                        <div class="student-name">Kavita</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-3 d-flex gap-3 flex-wrap small">
            <span><i class="fas fa-square text-primary me-1"></i> Morning</span>
            <span><i class="fas fa-square text-warning me-1"></i> Evening</span>
            <span><i class="fas fa-square text-success me-1"></i> Full Day</span>
            <span><i class="fas fa-square text-secondary me-1"></i> Available</span>
        </div>
    </div>

    <!-- Students Table - Mobile Responsive -->
    <div class="table-card" data-aos="fade-up">
        <!-- Mobile View Cards (visible only on mobile) -->
        <div class="d-block d-md-none">
            <div class="student-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <img src="https://ui-avatars.com/api/?name=Rahul+Sharma&background=4361ee&color=fff" 
                             class="rounded-circle me-2" width="40" height="40">
                        <div>
                            <h6 class="mb-0">Rahul Sharma</h6>
                            <small class="text-secondary">Seat A1 · Full Day</small>
                        </div>
                    </div>
                    <span class="badge bg-success">Paid</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-secondary">Fee: ₹2,500</small>
                        <small class="text-secondary d-block">Valid till: 25 May 2025</small>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-outline-success"><i class="fas fa-edit"></i></button>
                    </div>
                </div>
            </div>

            <div class="student-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <img src="https://ui-avatars.com/api/?name=Priya+Singh&background=4361ee&color=fff" 
                             class="rounded-circle me-2" width="40" height="40">
                        <div>
                            <h6 class="mb-0">Priya Singh</h6>
                            <small class="text-secondary">Seat A2 · Morning</small>
                        </div>
                    </div>
                    <span class="badge bg-success">Paid</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-secondary">Fee: ₹1,500</small>
                        <small class="text-secondary d-block">Valid till: 20 May 2025</small>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-outline-success"><i class="fas fa-edit"></i></button>
                    </div>
                </div>
            </div>

            <div class="student-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <img src="https://ui-avatars.com/api/?name=Amit+Kumar&background=ef476f&color=fff" 
                             class="rounded-circle me-2" width="40" height="40">
                        <div>
                            <h6 class="mb-0">Amit Kumar</h6>
                            <small class="text-secondary">Seat A3 · Evening</small>
                        </div>
                    </div>
                    <span class="badge bg-danger">Pending</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-secondary">Fee: ₹1,500</small>
                        <small class="text-danger d-block">Expired</small>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-warning"><i class="fas fa-bell"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Table View (hidden on mobile) -->
        <div class="d-none d-md-block">
            <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="current-tab" data-bs-toggle="tab" data-bs-target="#current">Current</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments">Payments</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending">Pending</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="current">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <!-- Table content from previous version -->
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions d-flex flex-wrap gap-2">
        <button class="btn btn-primary btn-sm flex-fill">
            <i class="fas fa-user-plus me-2"></i>Add
        </button>
        <button class="btn btn-outline-success btn-sm flex-fill">
            <i class="fas fa-credit-card me-2"></i>Pay
        </button>
        <button class="btn btn-outline-warning btn-sm flex-fill">
            <i class="fas fa-chair me-2"></i>Seat
        </button>
        <button class="btn btn-outline-info btn-sm flex-fill">
            <i class="fas fa-file-pdf me-2"></i>Report
        </button>
    </div>
</div>

<?php include 'includes/footer.php'; ?>