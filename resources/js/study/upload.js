// MAP_MEASUREMENTS step — body weight mapping check
const saveMappingsBtn = document.getElementById('saveMappingsBtn');
if (saveMappingsBtn) {
    const bodyWeightId = saveMappingsBtn.dataset.bodyWeightId || null;

    saveMappingsBtn.addEventListener('click', () => {
        if (bodyWeightId) {
            const selects = document.querySelectorAll('select[name^="column_measurement_mappings"]');
            const isMapped = Array.from(selects).some(s => !s.disabled && s.value == bodyWeightId);
            if (!isMapped) {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('bodyWeightWarningModal')).show();
                return;
            }
        }
        document.getElementById('updateUploadForm').submit();
    });

    document.getElementById('confirmSkipBodyWeightBtn')?.addEventListener('click', () => {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('bodyWeightWarningModal')).hide();
        document.getElementById('updateUploadForm').submit();
    });
}

// GROUP_SAMPLES step — unselected samples check
const saveGroupingsBtn = document.getElementById('saveGroupingsBtn');
if (saveGroupingsBtn) {
    saveGroupingsBtn.addEventListener('click', () => {
        const radios = document.querySelectorAll('input[name^="sample_group_id["]');
        const rowIndices = new Set(
            [...radios].map(r => r.name.match(/\[(\d+)\]/)?.[1]).filter(Boolean)
        );
        const unselectedCount = [...rowIndices].filter(
            i => !document.querySelector(`input[name="sample_group_id[${i}]"]:checked`)
        ).length;

        if (unselectedCount > 0) {
            document.getElementById('unselectedSamplesCount').textContent = unselectedCount;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('unselectedSamplesModal')).show();
        } else {
            document.getElementById('updateUploadForm').submit();
        }
    });

    document.getElementById('confirmUnselectedSamplesBtn')?.addEventListener('click', () => {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('unselectedSamplesModal')).hide();
        document.getElementById('updateUploadForm').submit();
    });
}
