import './bootstrap';
import './saveChanges';
if (document.getElementById('inv-save-continue-form')) {
    import('./study/investigators.js');
}
if (document.getElementById('updateUploadForm')) {
    import('./study/upload.js');
}
if (document.querySelector('[data-media-upload]')) {
    import('./study/media-upload.js');
}
import './tooltips';
import './htmxFix';
import './livewireAlerts';
import './modal.js';
import './disableOnSubmit.js';
import './typeahead.js';
import './clinicalChemistryForm.js';
import './inputlocks.js';
import './register.js';
if (document.querySelector('[data-api-token-access]')) {
    import('./apiTokenAccess.js');
}
if (document.querySelector('[data-sidebar-layout]')) {
    import('./sidebar-drawer.js');
}
