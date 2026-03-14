<?php
// includes/toast.php
// Toast Notification Container and Styles

// Include config if not already included
require_once dirname(__FILE__) . '/toast-config.php';

// Get toasts from session
$singleToast = getToast();
$multipleToasts = getMultipleToasts();

$allToasts = [];
if ($singleToast) {
    $allToasts[] = $singleToast;
}
if ($multipleToasts) {
    $allToasts = array_merge($allToasts, $multipleToasts);
}
?>

<!-- Toast Notification System -->
<div class="toast-container" id="toastContainer"></div>

<!-- Toast Styles -->
<style>
:root {
    --toast-success-bg: linear-gradient(135deg, #06d6a0, #05b386);
    --toast-error-bg: linear-gradient(135deg, #ef476f, #d43f62);
    --toast-warning-bg: linear-gradient(135deg, #ffb703, #faa51a);
    --toast-info-bg: linear-gradient(135deg, #4361ee, #3a4fbf);
    --toast-dark-bg: linear-gradient(135deg, #1e293b, #0f172a);
    --toast-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    --toast-shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.toast-container {
    position: fixed;
    z-index: 9999;
    pointer-events: none;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 100%;
    padding: 20px;
}

/* Positions */
.toast-container.top-right {
    top: 0;
    right: 0;
    align-items: flex-end;
}

.toast-container.top-left {
    top: 0;
    left: 0;
    align-items: flex-start;
}

.toast-container.bottom-right {
    bottom: 0;
    right: 0;
    align-items: flex-end;
    justify-content: flex-end;
}

.toast-container.bottom-left {
    bottom: 0;
    left: 0;
    align-items: flex-start;
    justify-content: flex-end;
}

.toast-container.top-center {
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    align-items: center;
}

.toast-container.bottom-center {
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    align-items: center;
    justify-content: flex-end;
}

/* Toast Item */
.toast-item {
    pointer-events: auto;
    background: var(--card-bg, white);
    border-radius: 16px;
    box-shadow: var(--toast-shadow);
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    min-width: 300px;
    max-width: 450px;
    border: 1px solid var(--border-color, #e2e8f0);
    backdrop-filter: blur(10px);
    animation: toastSlideIn 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.toast-item:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: var(--toast-shadow-hover);
}

.toast-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background: var(--toast-color);
}

/* Toast Types */
.toast-item.success {
    background: var(--toast-success-bg);
    color: white;
}

.toast-item.error {
    background: var(--toast-error-bg);
    color: white;
}

.toast-item.warning {
    background: var(--toast-warning-bg);
    color: white;
}

.toast-item.info {
    background: var(--toast-info-bg);
    color: white;
}

.toast-item.dark {
    background: var(--toast-dark-bg);
    color: white;
}

/* Toast Icon */
.toast-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    background: rgba(255, 255, 255, 0.2);
    flex-shrink: 0;
}

/* Toast Content */
.toast-content {
    flex: 1;
}

.toast-title {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 4px;
}

.toast-message {
    font-size: 0.9rem;
    opacity: 0.9;
    line-height: 1.4;
}

/* Toast Close Button */
.toast-close {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
    border: none;
    font-size: 0.9rem;
}

.toast-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

/* Toast Progress Bar */
.toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: rgba(255, 255, 255, 0.4);
    width: 100%;
    transform-origin: left;
    animation: toastProgress linear forwards;
}

/* Animations */
@keyframes toastSlideIn {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes toastSlideOut {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(30px);
    }
}

@keyframes toastProgress {
    from {
        transform: scaleX(1);
    }
    to {
        transform: scaleX(0);
    }
}

/* Mobile Responsive */
@media (max-width: 576px) {
    .toast-item {
        min-width: 250px;
        max-width: 350px;
        padding: 12px 16px;
    }
    
    .toast-container {
        padding: 10px;
    }
}

/* Dark Theme Support */
[data-theme="dark"] .toast-item:not(.success):not(.error):not(.warning):not(.info) {
    background: var(--card-bg);
    border-color: var(--border-color);
    color: var(--text-primary);
}

[data-theme="dark"] .toast-close {
    background: var(--border-color);
    color: var(--text-primary);
}
</style>

<!-- Initialize PHP toasts -->
<script>
<?php if (!empty($allToasts)): ?>
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($allToasts as $toast): ?>
    showToast({
        message: '<?php echo addslashes($toast['message']); ?>',
        type: '<?php echo $toast['type']; ?>',
        title: '<?php echo addslashes($toast['title']); ?>',
        duration: <?php echo $toast['duration']; ?>,
        position: '<?php echo $toast['position']; ?>'
    });
    <?php endforeach; ?>
});
<?php endif; ?>
</script>