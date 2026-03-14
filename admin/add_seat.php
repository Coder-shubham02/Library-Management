<?php
require "config/database.php";
include 'includes/header.php';
include 'includes/sidebar.php';

?>

<div class="container-fluid py-5" style="background-color: var(--bg-primary); min-height: 100vh;">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg" data-aos="zoom-in" style="background: var(--card-bg);">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="mb-0 fw-bold" style="color: var(--text-primary);">
                        <i class="fa-solid fa-chair me-2" style="color: var(--primary-color);"></i> Add New Seat
                    </h5>
                </div>
                
                <div class="card-body p-4">
                    <form id="addSeatForm">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase" style="color: var(--text-secondary);">Seat Number</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0" style="background: var(--input-bg); border-color: var(--border-color); color: var(--text-secondary);">
                                    <i class="fa-solid fa-hashtag"></i>
                                </span>
                                <input type="number" name="seat_number" class="form-control border-start-0 ps-0" 
                                       style="background: var(--input-bg); border-color: var(--border-color); color: var(--text-primary);" 
                                       placeholder="Ex: 10" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-uppercase" style="color: var(--text-secondary);">Room / Hall Name</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0" style="background: var(--input-bg); border-color: var(--border-color); color: var(--text-secondary);">
                                    <i class="fa-solid fa-door-open"></i>
                                </span>
                                <input type="text" name="room" class="form-control border-start-0 ps-0" 
                                       style="background: var(--input-bg); border-color: var(--border-color); color: var(--text-primary);" 
                                       placeholder="Ex: Conference Room" required>
                            </div>
                        </div>

                        <div class="d-grid mt-2">
                            <button type="submit" id="submitBtn" class="btn btn-primary btn-lg fw-bold shadow-sm border-0" 
                                    style="background: linear-gradient(135deg, #4361ee, #3a0ca3);">
                                <i class="fa-solid fa-check-circle me-2"></i> Register Seat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'includes/footer.php'; ?>

<!-- Custom Script for this page -->
<script>
$(document).ready(function() {
    // AOS Initialize
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true
        });
    }
    
    $('#addSeatForm').on('submit', function(e) {
        e.preventDefault(); // Page reload rokne ke liye
        
        const btn = $('#submitBtn');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Saving...');

        $.ajax({
            url: 'ajax/ajax_seat.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    // Toast function defined in toast.js
                    if (typeof toastSuccess === 'function') {
                        toastSuccess(response.message, 'Success', 4000);
                    } else {
                        alert(response.message); // Fallback
                    }
                    $('#addSeatForm')[0].reset(); // Form clear
                } else {
                    if (typeof toastError === 'function') {
                        toastError(response.message, 'Error', 5000);
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
                btn.prop('disabled', false).html('<i class="fa-solid fa-check-circle me-2"></i> Register Seat');
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                if (typeof toastError === 'function') {
                    toastError('Something went wrong. Please try again.', 'System Error', 5000);
                } else {
                    alert('System Error: ' + error);
                }
                btn.prop('disabled', false).html('<i class="fa-solid fa-check-circle me-2"></i> Register Seat');
            }
        });
    });
});
</script>

<!-- Extra Theme Toggle Debug Script -->
<script>
// Theme toggle debug - Check if working
(function() {
    console.log('Theme Check:', {
        htmlTheme: document.documentElement.getAttribute('data-theme'),
        localStorage: localStorage.getItem('theme'),
        toggleButton: !!document.getElementById('themeToggle')
    });
    
    // Agar toggle button nahi mila to error show karo
    if (!document.getElementById('themeToggle')) {
        console.error('Theme toggle button not found in header!');
        
        // Emergency theme toggle - Keyboard shortcut
        document.addEventListener('keydown', function(e) {
            // Press 'T' to toggle theme
            if (e.key === 't' || e.key === 'T') {
                const html = document.documentElement;
                const current = html.getAttribute('data-theme') || 'light';
                const newTheme = current === 'light' ? 'dark' : 'light';
                html.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                console.log('Theme toggled to:', newTheme);
            }
        });
        
        // Show floating button as fallback
        const fallbackBtn = document.createElement('button');
        fallbackBtn.innerHTML = '🌓 Toggle Theme';
        fallbackBtn.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 99999;
            background: #4361ee;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        `;
        fallbackBtn.onclick = function() {
            const html = document.documentElement;
            const current = html.getAttribute('data-theme') || 'light';
            const newTheme = current === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        };
        document.body.appendChild(fallbackBtn);
    }
})();
</script>