// livewire does not rerender page - must dispatch event to update alert container in layout
document.addEventListener('livewire:init', () => {
    Livewire.on('livewire-alert', (event) => {
        const alertContainer = document.getElementById('alert-container');
        const alertType = event[0].status === 'success' ? 'success' : 'danger';
        const alertMessage = event[0].message;

        // create alert
        const alert = document.createElement('div');
        alert.className = `alert alert-${alertType}`;
        alert.role = 'alert';
        alert.innerHTML = `${alertMessage}`;

        // clear previous alerts
        alertContainer.innerHTML = '';
        alertContainer.appendChild(alert);
    });
});