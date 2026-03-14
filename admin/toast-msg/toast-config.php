<?php
// includes/toast-config.php
// Toast Notification Configuration

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set a toast message to be shown on next page load
 * 
 * @param string $message The message to display
 * @param string $type Type of toast: 'success', 'error', 'warning', 'info'
 * @param string $title Optional title
 * @param int $duration Duration in milliseconds (0 for permanent)
 * @param string $position Position: 'top-right', 'top-left', 'bottom-right', 'bottom-left', 'top-center', 'bottom-center'
 */
function setToast($message, $type = 'info', $title = '', $duration = 5000, $position = 'top-right') {
    $_SESSION['toast'] = [
        'message' => $message,
        'type' => $type,
        'title' => $title,
        'duration' => $duration,
        'position' => $position,
        'time' => time()
    ];
}

/**
 * Set multiple toast messages
 * 
 * @param array $toasts Array of toast configurations
 */
function setMultipleToasts($toasts) {
    if (!isset($_SESSION['multiple_toasts'])) {
        $_SESSION['multiple_toasts'] = [];
    }
    
    foreach ($toasts as $toast) {
        $_SESSION['multiple_toasts'][] = [
            'message' => $toast['message'],
            'type' => $toast['type'] ?? 'info',
            'title' => $toast['title'] ?? '',
            'duration' => $toast['duration'] ?? 5000,
            'position' => $toast['position'] ?? 'top-right',
            'time' => time()
        ];
    }
}

/**
 * Get and clear toast from session
 * 
 * @return array|null Toast data or null if no toast
 */
function getToast() {
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        unset($_SESSION['toast']);
        return $toast;
    }
    return null;
}

/**
 * Get and clear multiple toasts from session
 * 
 * @return array|null Array of toasts or null if none
 */
function getMultipleToasts() {
    if (isset($_SESSION['multiple_toasts']) && !empty($_SESSION['multiple_toasts'])) {
        $toasts = $_SESSION['multiple_toasts'];
        unset($_SESSION['multiple_toasts']);
        return $toasts;
    }
    return null;
}

/**
 * Clear all toasts
 */
function clearAllToasts() {
    unset($_SESSION['toast']);
    unset($_SESSION['multiple_toasts']);
}

/**
 * Helper functions for common toast types
 */
function toastSuccess($message, $title = 'Success!', $duration = 5000, $position = 'top-right') {
    setToast($message, 'success', $title, $duration, $position);
}

function toastError($message, $title = 'Error!', $duration = 5000, $position = 'top-right') {
    setToast($message, 'error', $title, $duration, $position);
}

function toastWarning($message, $title = 'Warning!', $duration = 5000, $position = 'top-right') {
    setToast($message, 'warning', $title, $duration, $position);
}

function toastInfo($message, $title = 'Information', $duration = 5000, $position = 'top-right') {
    setToast($message, 'info', $title, $duration, $position);
}

// For AJAX requests - Return JSON response
function sendToastResponse($message, $type = 'success', $title = '', $duration = 3000) {
    header('Content-Type: application/json');
    echo json_encode([
        'toast' => [
            'message' => $message,
            'type' => $type,
            'title' => $title,
            'duration' => $duration
        ]
    ]);
    exit;
}
?>