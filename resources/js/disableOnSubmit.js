/**
 * Disable forms and submit buttons on form submission
 * Usage: Add data-disable-onsubmit attribute to any form
 * Example: <form data-disable-onsubmit>...</form>
 */

(function() {
    'use strict';

    // Function to disable form elements
    function disableForm(form) {
        // Disable all form inputs, selects, textareas, and buttons
        const formElements = form.querySelectorAll('input, select, textarea, button');

        // Specifically target submit buttons and add loading text
        const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        
        submitButtons.forEach(function(button) {
            // Store original text/value
            const originalText = button.textContent || button.value;
            button.setAttribute('data-original-text', originalText);
            
            // Update button text to show loading state
            if (button.tagName.toLowerCase() === 'button') {
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
            } else {
                button.value = 'Processing...';
            }
            
            // Add loading class for additional styling
            button.classList.add('btn-loading');
        });

        // Add a class to the form for styling
        form.classList.add('form-submitting');
    }

    // Function to re-enable form elements (useful for AJAX forms or error handling)
    function enableForm(form) {
        const formElements = form.querySelectorAll('input, select, textarea, button');
        
        formElements.forEach(function(element) {
            element.disabled = false;
            element.classList.remove('disabled-on-submit');
        });

        // Restore submit button text
        const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        
        submitButtons.forEach(function(button) {
            const originalText = button.getAttribute('data-original-text');
            
            if (originalText) {
                if (button.tagName.toLowerCase() === 'button') {
                    button.textContent = originalText;
                } else {
                    button.value = originalText;
                }
                
                button.removeAttribute('data-original-text');
            }
            
            button.classList.remove('btn-loading');
        });

        form.classList.remove('form-submitting');
    }

    // Initialize when DOM is loaded
    function init() {
        // Find all forms with data-disable-onsubmit attribute
        const forms = document.querySelectorAll('form[data-disable-onsubmit]');
        
        forms.forEach(function(form) {
            // Prevent multiple event listeners on the same form
            if (form.hasAttribute('data-disable-onsubmit-initialized')) {
                return;
            }
            
            form.addEventListener('submit', function(event) {
                // Check if form is already being processed
                if (form.classList.contains('form-submitting')) {
                    event.preventDefault();
                    return false;
                }
                
                // Disable the form
                disableForm(form);
                
                // For AJAX forms, you might want to handle re-enabling
                // This is just in case the form submission fails or is handled via AJAX
                setTimeout(function() {
                    // Re-enable after 30 seconds as a failsafe
                    if (form.classList.contains('form-submitting')) {
                        enableForm(form);
                    }
                }, 30000);
            });
            
            // Mark form as initialized
            form.setAttribute('data-disable-onsubmit-initialized', 'true');
        });
    }

    // Initialize immediately if DOM is already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-initialize when new content is added to the page (for dynamic content)
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            let shouldReinit = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            if (node.matches && node.matches('form[data-disable-onsubmit]')) {
                                shouldReinit = true;
                            } else if (node.querySelector && node.querySelector('form[data-disable-onsubmit]')) {
                                shouldReinit = true;
                            }
                        }
                    });
                }
            });
            
            if (shouldReinit) {
                init();
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Expose functions globally for manual use if needed
    window.disableFormOnSubmit = {
        disable: disableForm,
        enable: enableForm,
        init: init
    };

})();