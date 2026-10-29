function buildTooltipHtml(lines) {
    return lines.map(([label, value]) => {
        const row = document.createElement('span');
        row.className = 'text-nowrap';
        const strong = document.createElement('strong');
        strong.textContent = label + ': ';
        row.appendChild(strong);
        row.appendChild(document.createTextNode(value));
        return row.outerHTML;
    }).join('<br>');
}

window.enableBootstrapTooltip = function() {
    // Enable tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"], [data-bs-tooltip]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => {
        const existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
        if (existingTooltip) {
            existingTooltip.dispose();
        }

        const tooltipType = tooltipTriggerEl.getAttribute('data-tooltip-type');

        if (tooltipType === 'cf-status') {
            const el = tooltipTriggerEl;
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                html: true,
                title: function () {
                    const lines = [];
                    const status = el.getAttribute('data-tooltip-status');
                    if (status) lines.push(['Status', status]);
                    const createdBy = el.getAttribute('data-tooltip-created-by');
                    if (createdBy) lines.push(['Created By', createdBy]);
                    const parent = el.getAttribute('data-tooltip-parent');
                    if (parent) lines.push(['Parent', parent]);
                    const rejectionLabel = el.getAttribute('data-tooltip-rejection-label');
                    const rejectionReason = el.getAttribute('data-tooltip-rejection-reason');
                    if (rejectionLabel && rejectionReason) lines.push([rejectionLabel, rejectionReason]);
                    const reviewedAt = el.getAttribute('data-tooltip-reviewed-at');
                    if (reviewedAt) lines.push(['Reviewed At', reviewedAt]);
                    const reviewedBy = el.getAttribute('data-tooltip-reviewed-by');
                    if (reviewedBy) lines.push(['Reviewed By', reviewedBy]);
                    return buildTooltipHtml(lines);
                },
            });
        }

        if (tooltipType === 'cf-web-status') {
            const el = tooltipTriggerEl;
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                html: true,
                title: function () {
                    const lines = [];
                    const desc = el.getAttribute('data-tooltip-web-desc');
                    if (desc) lines.push(['Description', desc]);
                    const error = el.getAttribute('data-tooltip-web-error');
                    if (error) lines.push(['Error', error]);
                    const checkedAt = el.getAttribute('data-tooltip-web-checked-at');
                    if (checkedAt) lines.push(['Checked At', checkedAt]);
                    return buildTooltipHtml(lines);
                },
            });
        }

        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

window.enableBootstrapPopover = function() {
    // Enable popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => {
        const existingPopover = bootstrap.Popover.getInstance(popoverTriggerEl);
        if (existingPopover) {
            existingPopover.dispose();
        }
        
        const config = {
            html: true,
            sanitize: true,
            allowList: {
                ...bootstrap.Popover.Default.allowList,  // Keep defaults
                img: ['src', 'alt', 'class', 'style'],   // Allow img tags
                figure: ['class', 'style'],              // Allow figure
                figcaption: ['class', 'style']           // Allow figcaption
            },
            customClass: 'card-style-popover'
        };
        
        // Get title
        const title = popoverTriggerEl.getAttribute('data-bs-title');
        if (title) {
            config.title = title;
        }
        
        // Get user's content
        let content = popoverTriggerEl.getAttribute('data-bs-content') || '';
        
        // Check if they want to add an image
        const imageUrl = popoverTriggerEl.getAttribute('data-image-url');
        const imageCaption = popoverTriggerEl.getAttribute('data-image-caption');
        
        if (imageUrl) {
            content += `<figure class='mb-0 mt-2'><img src='${imageUrl}' alt='${imageCaption || ''}' class='img-fluid border' style='max-width: 100%;'>`;
            
            if (imageCaption) {
                content += `<figcaption class='text-muted mt-2' style='font-size: 0.85rem;'>${imageCaption}</figcaption>`;
            }
            
            content += `</figure>`;
        }
        
        config.content = content;
        
        return new bootstrap.Popover(popoverTriggerEl, config);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // init tooltip
    window.enableBootstrapTooltip();
    // init popover
    window.enableBootstrapPopover();
})

document.addEventListener('livewire:initialized', () => {   
    Livewire.hook('morphed', ({ el, component }) => {
        window.enableBootstrapTooltip();
        window.enableBootstrapPopover();
    })

    const hideAllTooltips = () => {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"], [data-bs-tooltip]');
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            const tooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
            if (tooltip) {
                tooltip.hide();
            }
        });
    };

    const hideAllPopovers = () => {
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        popoverTriggerList.forEach(popoverTriggerEl => {
            const popover = bootstrap.Popover.getInstance(popoverTriggerEl);
            if (popover) {
                popover.hide();
            }
        });
    };

    // hide tooltips and popovers on Livewire action
    Livewire.hook('morph', () => {
        hideAllTooltips();
        hideAllPopovers();
    });
})