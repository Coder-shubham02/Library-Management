// includes/toast.js
// Toast Notification JavaScript Functions

// Toast configuration
const toastConfig = {
    defaultDuration: 5000,
    defaultPosition: 'top-right',
    maxToasts: 5,
    animationDuration: 300
};

// Store active toasts
let activeToasts = [];

/**
 * Show a toast notification
 * 
 * @param {Object} options Toast options
 * @param {string} options.message Toast message
 * @param {string} options.type Toast type: 'success', 'error', 'warning', 'info', 'dark'
 * @param {string} options.title Toast title
 * @param {number} options.duration Duration in ms (0 for permanent)
 * @param {string} options.position Position: 'top-right', 'top-left', etc.
 */
function showToast(options = {}) {
    const {
        message = 'Notification message',
        type = 'info',
        title = '',
        duration = toastConfig.defaultDuration,
        position = toastConfig.defaultPosition
    } = options;

    // Get or create container for this position
    let container = document.querySelector(`.toast-container.${position}`);
    if (!container) {
        container = document.createElement('div');
        container.className = `toast-container ${position}`;
        container.id = `toastContainer-${position}`;
        document.body.appendChild(container);
    }

    // Limit number of toasts
    if (container.children.length >= toastConfig.maxToasts) {
        container.removeChild(container.firstChild);
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-item ${type}`;
    
    // Set icon based on type
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle',
        dark: 'fa-bell'
    };
    
    // Toast HTML structure
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas ${icons[type] || icons.info}"></i>
        </div>
        <div class="toast-content">
            ${title ? `<div class="toast-title">${title}</div>` : ''}
            <div class="toast-message">${message}</div>
        </div>
        <div class="toast-close">
            <i class="fas fa-times"></i>
        </div>
        ${duration > 0 ? '<div class="toast-progress"></div>' : ''}
    `;

    // Add to container
    container.appendChild(toast);

    // Setup progress bar animation
    if (duration > 0) {
        const progress = toast.querySelector('.toast-progress');
        progress.style.animation = `toastProgress ${duration}ms linear forwards`;
    }

    // Setup auto-close timer
    let timeoutId = null;
    if (duration > 0) {
        timeoutId = setTimeout(() => {
            closeToast(toast);
        }, duration);
    }

    // Close button functionality
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => {
        if (timeoutId) clearTimeout(timeoutId);
        closeToast(toast);
    });

    // Add to active toasts
    activeToasts.push({
        element: toast,
        timeoutId: timeoutId
    });

    // Hover pause functionality
    if (duration > 0) {
        toast.addEventListener('mouseenter', () => {
            if (timeoutId) {
                clearTimeout(timeoutId);
                const progress = toast.querySelector('.toast-progress');
                if (progress) {
                    progress.style.animationPlayState = 'paused';
                }
            }
        });

        toast.addEventListener('mouseleave', () => {
            const remainingTime = duration - (Date.now() - toast.startTime);
            if (remainingTime > 0) {
                timeoutId = setTimeout(() => closeToast(toast), remainingTime);
                const progress = toast.querySelector('.toast-progress');
                if (progress) {
                    progress.style.animationPlayState = 'running';
                }
            }
        });

        toast.startTime = Date.now();
    }

    return toast;
}

/**
 * Close a specific toast
 * 
 * @param {HTMLElement} toast Toast element to close
 */
function closeToast(toast) {
    // Remove from active toasts array
    activeToasts = activeToasts.filter(t => t.element !== toast);
    
    // Animate out
    toast.style.animation = 'toastSlideOut 0.3s ease forwards';
    
    // Remove after animation
    setTimeout(() => {
        if (toast.parentElement) {
            toast.parentElement.removeChild(toast);
            
            // Remove container if empty
            if (toast.parentElement.children.length === 0) {
                toast.parentElement.remove();
            }
        }
    }, 300);
}

/**
 * Close all toasts
 */
function closeAllToasts() {
    activeToasts.forEach(({ element }) => {
        closeToast(element);
    });
    activeToasts = [];
}

/**
 * Show success toast (shortcut)
 * 
 * @param {string} message Message to display
 * @param {string} title Optional title
 * @param {number} duration Duration in ms
 */
function toastSuccess(message, title = 'Success!', duration = 3000) {
    showToast({ message, type: 'success', title, duration });
}

/**
 * Show error toast (shortcut)
 * 
 * @param {string} message Message to display
 * @param {string} title Optional title
 * @param {number} duration Duration in ms
 */
function toastError(message, title = 'Error!', duration = 5000) {
    showToast({ message, type: 'error', title, duration });
}

/**
 * Show warning toast (shortcut)
 * 
 * @param {string} message Message to display
 * @param {string} title Optional title
 * @param {number} duration Duration in ms
 */
function toastWarning(message, title = 'Warning!', duration = 4000) {
    showToast({ message, type: 'warning', title, duration });
}

/**
 * Show info toast (shortcut)
 * 
 * @param {string} message Message to display
 * @param {string} title Optional title
 * @param {number} duration Duration in ms
 */
function toastInfo(message, title = 'Information', duration = 3000) {
    showToast({ message, type: 'info', title, duration });
}

/**
 * Show promise toast (for async operations)
 * 
 * @param {Promise} promise The promise to track
 * @param {Object} messages Messages for different states
 */
async function toastPromise(promise, messages = {}) {
    const loading = showToast({
        message: messages.loading || 'Processing...',
        type: 'info',
        title: 'Please wait',
        duration: 0
    });

    try {
        const result = await promise;
        closeToast(loading);
        showToast({
            message: messages.success || 'Operation completed successfully!',
            type: 'success',
            title: 'Success'
        });
        return result;
    } catch (error) {
        closeToast(loading);
        showToast({
            message: messages.error || 'An error occurred!',
            type: 'error',
            title: 'Error'
        });
        throw error;
    }
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showToast,
        closeToast,
        closeAllToasts,
        toastSuccess,
        toastError,
        toastWarning,
        toastInfo,
        toastPromise
    };
}