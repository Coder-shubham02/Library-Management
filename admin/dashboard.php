<?php
// index.php - Main Dashboard Page
include 'includes/header.php';
include 'includes/sidebar.php';
?>
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