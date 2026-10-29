if ( !window.saveChangesLoaded ) {
    console.log('saveChanges.js loaded');
    window.saveChangesLoaded = true;
    document.addEventListener('DOMContentLoaded', (event) => {
        
        let formChanged = false;

        // Select all input elements in the form
        let inputs = document.querySelectorAll('form[data-confirmchanges] input, form[data-confirmchanges] textarea, form[data-confirmchanges] select');
        // Add change event listener to each input
        inputs.forEach(input => {
            input.dataset.changeEventAssigned = true;
            input.addEventListener('change', () => {
                formChanged = true;
            });
        });

        // Unbind beforeunload event on form submit
        document.querySelectorAll('form[data-confirmchanges]').forEach(form => {
            form.addEventListener('submit', function() {
                formChanged = false;
            });
        });

        // Warn the user before leaving the page if the form has changed
        window.addEventListener('beforeunload', (event) => {
            console.log(event.target.tagName)
            if (event.target.tagName !== 'FORM' && formChanged) {
                const confirmationMessage = 'You have unsaved changes. Are you sure you want to leave without saving?';
                event.returnValue = confirmationMessage; // Standard for most browsers
                return confirmationMessage; // For some older browsers
            }
        });

        document.body.addEventListener('htmx:beforeRequest', (event) => {
            if (event.target.tagName !== 'FORM' && formChanged) {
                if (!confirm('You have unsaved changes. Are you sure you want to leave without saving?')) {
                    event.preventDefault();
                }
            }
        });


        document.body.addEventListener('htmx:afterOnLoad', (event) => {
            console.log('htmx:afterOnLoad');
            formChanged = false;
            // Select all input elements in the form
            inputs = document.querySelectorAll('form[data-confirmchanges] input:not([data-change-event-assigned]), form[data-confirmchanges] textarea:not([data-change-event-assigned]), form[data-confirmchanges] select:not([data-change-event-assigned])');
            console.log(inputs);
            // Add change event listener to each input
            inputs.forEach(input => {
                input.dataset.changeEventAssigned = true;
                input.addEventListener('change', () => {
                    formChanged = true;
                });
            });
        });
    });
}
