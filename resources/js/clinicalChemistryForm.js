window.ClinicalChemistryForm = (initialSpecimenTypeId, measurements) => {
    return {
        selectedSpecimenTypeId: initialSpecimenTypeId,
        measurements: measurements,
        
        get filteredMeasurements() {
            if (!this.selectedSpecimenTypeId) return [];
            return this.measurements.filter(m => m.specimen_type_id == this.selectedSpecimenTypeId);
        },
        
        get groupedMeasurements() {
            const groups = {};
            this.filteredMeasurements.forEach(m => {
                const group = m.grouping || 'Other';
                if (!groups[group]) {
                    groups[group] = [];
                }
                groups[group].push(m);
            });
            return Object.entries(groups).sort((a, b) => a[0].localeCompare(b[0]));
        },
        
        get hasMeasurements() {
            return this.filteredMeasurements.length > 0;
        },
        
        get isEmpty() {
            return !this.selectedSpecimenTypeId;
        }
    };
};