function initRegisterForm() {
    const countrySelect = document.getElementById('country');
    const stateSelect = document.getElementById('state');
    const stateLabel = document.getElementById('state-label');

    if (!countrySelect || !stateSelect) {
        return;
    }

    const toggleStateField = () => {
        const isUnitedStates = countrySelect.value === 'US';
        stateSelect.disabled = !isUnitedStates;
        stateSelect.required = isUnitedStates;

        if (stateLabel) {
            stateLabel.classList.toggle('required', isUnitedStates);
        }

        if (!isUnitedStates) {
            stateSelect.value = '';
        }
    };

    countrySelect.addEventListener('change', toggleStateField);
    toggleStateField();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRegisterForm);
} else {
    initRegisterForm();
}
