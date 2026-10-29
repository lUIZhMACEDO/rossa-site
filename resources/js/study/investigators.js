let proceedingWithNavigation = false;

function isAddFormDirty() {
    const textFields = ['firstName', 'lastName', 'email', 'department', 'organization'];
    const hasText = textFields.some(id => {
        const el = document.getElementById(id);
        return el && el.value.trim() !== '';
    });
    const countryEl = document.getElementById('country');
    const hasCountry = !!(countryEl && countryEl.value !== '');
    const hasCheckbox = !!(document.getElementById('isSubmitting')?.checked
                        || document.getElementById('isCorresponding')?.checked
                        || document.getElementById('isContributing')?.checked);
    return hasText || hasCountry || hasCheckbox;
}

// The edit row is present in the DOM whenever an investigator is being edited.
function isEditFormOpen() {
    return !!document.querySelector('tr.table-active');
}

function dirtyModalMessage() {
    const addDirty  = isAddFormDirty();
    const editOpen  = isEditFormOpen();

    if (addDirty && editOpen) {
        return 'You have an unsaved investigator edit <strong>and</strong> unsaved information in the '
             + '<strong>Add New Investigator</strong> form. Please save or cancel both before continuing.';
    }
    if (editOpen) {
        return 'You have an unsaved investigator edit in progress. '
             + 'Click <strong>Update</strong> or <strong>Cancel</strong> before continuing.';
    }
    return 'You have unsaved information in the <strong>Add New Investigator</strong> form. '
         + 'Click <strong>Add Investigator to Study</strong> first if you\'d like to keep this investigator before continuing.';
}

window.addEventListener('beforeunload', function (e) {
    if (!proceedingWithNavigation && (isAddFormDirty() || isEditFormOpen())) {
        e.preventDefault();
    }
});

document.addEventListener('submit', function (e) {
    const form = e.target;
    if (form.id !== 'inv-save-continue-form') return;
    if (form.dataset.confirmed) return;

    if (isAddFormDirty() || isEditFormOpen()) {
        e.preventDefault();
        e.stopImmediatePropagation();

        document.querySelector('#unsavedChangesModal .modal-body').innerHTML = dirtyModalMessage();

        const modal = new bootstrap.Modal(document.getElementById('unsavedChangesModal'));
        modal.show();

        document.getElementById('confirmContinueBtn').onclick = function () {
            proceedingWithNavigation = true;
            modal.hide();
            form.dataset.confirmed = 'true';
            form.requestSubmit();
        };
    }
}, true);
