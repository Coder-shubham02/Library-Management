<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'toast-msg/toast-config.php';
include 'toast-msg/toast.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>LibraryPro X | Professional Library Management</title>

    <!-- Page Loader() -->
    <?php include 'loader/page-loader.php'; ?>

    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Loading Animation -->
    <!-- <div class="loading-bar"></div> -->

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Main Content -->
            <div class="col-lg-10 col-md-12 main-content" id="mainContent">
                <!-- Top Navbar -->
                <div class="navbar-top d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <!-- <i class="fas fa-bars text-secondary fs-5 d-lg-none" id="mobileMenuBtn" role="button"></i> -->
                        <h5 class="mb-0 fw-semibold">Dashboard Overview</h5>
                        <span class="badge bg-success bg-opacity-10 text-success">Live</span>
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