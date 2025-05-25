/**
 * Lilac Toast Notification System
 * 
 * A centralized system for displaying toast notifications across the site.
 * This will handle all types of alerts including system messages, errors, and user notifications.
 */

// Define our global toast API
window.LilacToast = {
    success: null,
    error: null,
    warning: null,
    info: null
};

(function($) {
    'use strict';
    
    // Log initialization
    console.log('Lilac Toast System Initializing...');

    // Initialize the toast system
    $(document).ready(function() {
        // Create the toast container if it doesn't exist
        if ($('#lilac-toast-container').length === 0) {
            $('body').append('<div id="lilac-toast-container" class="top-right"></div>');
        }
        
        // Process URL parameters for toast messages
        processUrlToasts();
    });

    /**
     * Show a toast notification
     * 
     * @param {Object} options Toast configuration options
     */
    window.LilacShowToast = function(options) {
        const defaults = {
            message: '',
            type: 'info',
            title: '',
            duration: 5000,
            position: 'top-right',
            closable: true,
            onClose: null,
            cssClass: ''
        };

        // Merge defaults with options
        const settings = $.extend({}, defaults, options);
        
        // Don't show empty toasts
        if (!settings.message) {
            return;
        }
        
        // Set the container position
        $('#lilac-toast-container').attr('class', settings.position);
        
        // Create toast element
        const $toast = $('<div class="lilac-toast ' + settings.type + ' ' + settings.cssClass + '"></div>');
        
        // Add title if provided
        if (settings.title) {
            $toast.append('<div class="toast-header">' + settings.title + '</div>');
        }
        
        // Add message body
        $toast.append('<div class="toast-body">' + settings.message + '</div>');
        
        // Add close button if closable
        if (settings.closable) {
            const $closeBtn = $('<button class="toast-close">&times;</button>');
            $toast.append($closeBtn);
            
            // Attach close event handler
            $closeBtn.on('click', function() {
                closeToast($toast, settings.onClose);
            });
        }
        
        // Add to container
        $('#lilac-toast-container').append($toast);
        
        // Show with animation after a brief delay (allows DOM to update)
        setTimeout(function() {
            $toast.addClass('show');
        }, 10);
        
        // Auto-close after duration (if set)
        if (settings.duration > 0) {
            setTimeout(function() {
                closeToast($toast, settings.onClose);
            }, settings.duration);
        }
        
        // Return the toast element for potential future reference
        return $toast;
    };
    
    /**
     * Close a toast notification
     * 
     * @param {jQuery} $toast The toast element
     * @param {Function} callback Optional callback to run after closing
     */
    function closeToast($toast, callback) {
        $toast.removeClass('show');
        
        // Remove after animation completes
        setTimeout(function() {
            $toast.remove();
            
            // Call the callback if provided
            if (typeof callback === 'function') {
                callback();
            }
        }, 300);
    }
    
    /**
     * Process toast messages from URL parameters
     */
    function processUrlToasts() {
        // Parse query parameters
        const urlParams = new URLSearchParams(window.location.search);
        
        // Check for toast parameters
        if (urlParams.has('toast_message')) {
            const message = urlParams.get('toast_message');
            const type = urlParams.get('toast_type') || 'info';
            const title = urlParams.get('toast_title') || '';
            
            // Show the toast
            window.LilacShowToast({
                message: message,
                type: type,
                title: title
            });
            
            // Remove parameters from URL without reloading
            if (window.history && window.history.replaceState) {
                // Create a new URL without the toast parameters
                let url = window.location.href;
                url = url.replace(/([&?])(toast_message|toast_type|toast_title)=[^&]+/g, function(match, prefix) {
                    // If this is the only parameter, remove the prefix too
                    return prefix === '?' && match.indexOf('&') === -1 ? '' : prefix;
                });
                
                // Remove trailing ? or & if present
                url = url.replace(/[?&]$/, '');
                
                // Update the URL without reloading
                window.history.replaceState({}, document.title, url);
            }
        }
    }
    
    /**
     * Helper functions for common toast types
     */
    // Redefine the methods on the pre-defined object
    window.LilacToast.success = function(message, title, duration) {
        return window.LilacShowToast({
            message: message,
            type: 'success',
            title: title || 'Success',
            duration: duration || 5000
        });
    };
    
    window.LilacToast.error = function(message, title, duration) {
        return window.LilacShowToast({
            message: message,
            type: 'error',
            title: title || 'Error',
            duration: duration || 7000
        });
    };
    
    window.LilacToast.warning = function(message, title, duration) {
        return window.LilacShowToast({
            message: message,
            type: 'warning',
            title: title || 'Warning',
            duration: duration || 6000
        });
    };
    
    window.LilacToast.info = function(message, title, duration) {
        return window.LilacShowToast({
            message: message,
            type: 'info',
            title: title || 'Information',
            duration: duration || 5000
        });
    };
    
    // Log that all functionality is now available
    console.log('Lilac Toast API Ready');
    
    /**
     * Handle AJAX errors and show them as toasts
     */
    $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
        let message = thrownError || 'An unknown error occurred';
        let title = 'Error';
        
        // Try to parse a more detailed error message from the response
        try {
            const response = JSON.parse(jqXHR.responseText);
            if (response.message) {
                message = response.message;
            }
            if (response.title) {
                title = response.title;
            }
        } catch (e) {
            // If parsing fails, use status text
            if (jqXHR.statusText) {
                message = jqXHR.statusText;
            }
        }
        
        // Show the error as a toast
        window.LilacToast.error(message, title);
    });
    
    /**
     * Convert standard alerts to toasts
     */
    const originalAlert = window.alert;
    window.alert = function(message) {
        // Use LilacShowToast directly since it's always available
        // LilacToast helper might not be ready when alert is called
        if (typeof window.LilacShowToast === 'function') {
            window.LilacShowToast({
                message: message,
                type: 'info',
                title: 'Alert',
                duration: 5000
            });
        } else {
            // Fall back to original alert if toast system isn't available
            originalAlert(message);
        }
    };
    
})(jQuery);
