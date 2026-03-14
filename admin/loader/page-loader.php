<?php
// includes/simple-loader.php
// Simplified Working Loader
?>

<style>
/* Simple Loader CSS */
.simple-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    z-index: 999999;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: opacity 0.5s ease;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.simple-loader.fade-out {
    opacity: 0;
    pointer-events: none;
}

.loader-spinner {
    width: 60px;
    height: 60px;
    border: 5px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
    margin-bottom: 20px;
}

.loader-text {
    color: white;
    font-size: 1.2rem;
    font-weight: 500;
    margin-bottom: 10px;
}

.loader-progress {
    width: 200px;
    height: 4px;
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
    overflow: hidden;
}

.loader-progress-bar {
    height: 100%;
    width: 0%;
    background: white;
    transition: width 0.3s ease;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Dark mode support */
[data-theme="dark"] .simple-loader {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}
</style>

<div class="simple-loader" id="simpleLoader">
    <div class="loader-spinner"></div>
    <div class="loader-text">LibraryPro X</div>
    <div class="loader-progress">
        <div class="loader-progress-bar" id="loaderProgressBar"></div>
    </div>
    <div style="color: rgba(255,255,255,0.8); margin-top: 10px; font-size: 0.9rem;" id="loaderStatus">
        Initializing...
    </div>
</div>

<script>
// Simple and Working Loader Script
(function() {
    const loader = document.getElementById('simpleLoader');
    const progressBar = document.getElementById('loaderProgressBar');
    const statusText = document.getElementById('loaderStatus');
    
    let progress = 0;
    let resourcesLoaded = 0;
    const totalResources = 5; // Bootstrap, FontAwesome, etc.
    
    // Update progress function
    function updateProgress(message) {
        resourcesLoaded++;
        progress = (resourcesLoaded / totalResources) * 100;
        
        if (progressBar) {
            progressBar.style.width = progress + '%';
        }
        
        if (statusText) {
            statusText.textContent = message || `Loading... ${Math.round(progress)}%`;
        }
        
        // Hide loader when all resources loaded
        if (resourcesLoaded >= totalResources) {
            setTimeout(function() {
                loader.classList.add('fade-out');
                // Remove loader from DOM after animation
                setTimeout(function() {
                    loader.style.display = 'none';
                }, 500);
            }, 500);
        }
    }
    
    // Track resources
    function checkResources() {
        // Check Bootstrap
        if (typeof bootstrap !== 'undefined') {
            updateProgress('✓ Bootstrap loaded');
        } else {
            setTimeout(checkResources, 100);
        }
        
        // Check Font Awesome
        if (document.querySelector('.fa, .fas, .far')) {
            updateProgress('✓ Font Awesome loaded');
        }
        
        // Check jQuery if used
        if (typeof jQuery !== 'undefined') {
            updateProgress('✓ jQuery loaded');
        }
        
        // Check AOS
        if (typeof AOS !== 'undefined') {
            updateProgress('✓ AOS loaded');
        }
        
        // Check Chart.js
        if (typeof Chart !== 'undefined') {
            updateProgress('✓ Chart.js loaded');
        }
    }
    
    // Start checking
    let checkInterval = setInterval(checkResources, 100);
    
    // Fallback - hide loader after 5 seconds max
    setTimeout(function() {
        clearInterval(checkInterval);
        loader.classList.add('fade-out');
        setTimeout(function() {
            loader.style.display = 'none';
        }, 500);
    }, 5000);
    
    // Window load event
    window.addEventListener('load', function() {
        updateProgress('✓ Page loaded');
    });
})();
</script>