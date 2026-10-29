import TomSelect from 'tom-select';

/**
 * Initialize Tom Select on select elements with data-typeahead attribute
 * 
 * Basic usage in Blade templates:
 * <select data-typeahead>...</select>
 * 
 * With custom options:
 * <select data-typeahead='{"maxOptions": 500, "placeholder": "Select an option..."}'>...</select>
 *
 * Compact multi-select (one-line summary until focused):
 * <select multiple data-typeahead='{"compactSelection": true, "maxItems": null}'>...</select>
 * 
 * Performance options:
 * data-typeahead-load="eager"  - Initialize immediately (default for ≤3 selects)
 * data-typeahead-load="lazy"   - Initialize on interaction/visibility (default for >3 selects)
 * data-typeahead-load="auto"   - Let the script decide based on total count
 * 
 * With synonyms for better search:
 * <option value="1" data-synonyms="weight mass">Body Weight</option>
 * <option value="2" data-search="femur thigh" data-synonyms="femoral">Femur Length</option>
 */

// Default Tom Select configuration
const defaultConfig = {
    // Allow searching within the dropdown
    plugins: {
        'dropdown_input': {}
    },
    
    // Maximum number of options to display at once (helps with performance)
    maxOptions: 1000,
    
    // Automatically highlight first option
    selectOnTab: true,
    
    // Close dropdown after selection
    closeAfterSelect: true,
    
    // Highlight matching text
    highlight: true,
    
    // Allow opening dropdown by clicking
    openOnFocus: true,
    
    // Search fields - searches text and any data-search or data-synonyms attributes
    searchField: ['text', 'search', 'synonyms'],
    
    // Load data-* attributes from options
    dataAttr: 'data-data',
    
    // Render customizations
    render: {
        // Keep optgroup labels visible and styled
        optgroup_header: function(data, escape) {
            return '<div class="optgroup-header fw-bold text-muted">' + escape(data.label) + '</div>';
        },
        
        // Enhance option rendering to show more context
        option: function(data, escape) {
            return '<div>' + escape(data.text) + '</div>';
        },
        
        // Show selected item clearly
        item: function(data, escape) {
            return '<div>' + escape(data.text) + '</div>';
        }
    },
    
    // Sorting - respect the original order from the HTML
    sortField: {
        field: '$order'
    }
};

// Cache parsed option data for identical selects to avoid redundant processing
const optionDataCache = new Map();

/**
 * Initialize Tom Select on a single element
 * @param {HTMLSelectElement} element 
 */
function initializeTomSelect(element) {
    // Skip if already initialized
    if (element.tomselect) {
        return;
    }
    
    // Get custom options from data attribute
    let customOptions = {};
    try {
        const dataOptions = element.getAttribute('data-typeahead');
        if (dataOptions && dataOptions !== '') {
            customOptions = JSON.parse(dataOptions);
        }
    } catch (e) {
        console.warn('Invalid JSON in data-typeahead attribute:', e);
    }
    
    // Merge configurations
    const config = { ...defaultConfig, ...customOptions };
    const compactSelection = Boolean(config.compactSelection);
    delete config.compactSelection;

    // Multi-selects that collapse chips into a summary should stay open while assigning.
    if (compactSelection && config.closeAfterSelect === undefined) {
        config.closeAfterSelect = false;
    }
    
    // Generate cache key based on select content (for identical selects)
    const optionsHTML = element.innerHTML;
    const cacheKey = optionsHTML.substring(0, 200); // Use first 200 chars as key
    
    // Check cache for identical selects
    if (!optionDataCache.has(cacheKey)) {
        // Process options to extract data-search and data-synonyms attributes
        const options = Array.from(element.querySelectorAll('option')).map(option => {
            const searchText = option.getAttribute('data-search') || '';
            const synonyms = option.getAttribute('data-synonyms') || '';
            
            return {
                value: option.value,
                text: option.textContent.trim(),
                optgroup: option.parentElement.tagName === 'OPTGROUP' ? option.parentElement.label : undefined,
                search: searchText,
                synonyms: synonyms
            };
        }); // Keep all options including those with empty values
        
        // Cache the processed data
        if (options.some(opt => opt.search || opt.synonyms)) {
            optionDataCache.set(cacheKey, {
                options: options,
                optgroups: Array.from(element.querySelectorAll('optgroup')).map(group => ({
                    label: group.label,
                    value: group.label
                }))
            });
        }
    }
    
    // Use cached data if available
    const cachedData = optionDataCache.get(cacheKey);
    if (cachedData) {
        config.options = cachedData.options;
        config.optgroups = cachedData.optgroups;
    }
    
    // Initialize Tom Select
    const ts = new TomSelect(element, config);

    // when empty value is selected, clear to return to placeholder state.
    // always re-dispatch a native change event so Alpine x-model bindings stay in sync.
    ts.on('change', function(value) {
        if (value === '') {
            this.clear(true); // true = silent, doesn't trigger another change event
        }
        this.input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    if (compactSelection) {
        enableCompactSelection(ts);
    }
}

/**
 * Collapse multi-select chips into a one-line summary until focused/open.
 * Toggle via data-typeahead: {"compactSelection": true}
 */
function enableCompactSelection(ts) {
    const wrapper = ts.wrapper;
    wrapper.classList.add('compact-selection');

    let summary = ts.control.querySelector('.media-selection-summary');
    if (!summary) {
        summary = document.createElement('span');
        summary.className = 'media-selection-summary';
        ts.control.insertBefore(summary, ts.control_input);
    }

    const refresh = () => {
        const count = ts.items.length;
        const expanded = ts.isFocused || ts.isOpen;
        wrapper.classList.toggle('is-expanded', expanded);
        wrapper.classList.toggle('has-selection', count > 0);

        if (count === 0) {
            summary.textContent = '';
            summary.title = '';
            summary.hidden = true;
            return;
        }

        if (count === 1) {
            const value = ts.items[0];
            const label = ts.options[value]?.text || String(value);
            summary.textContent = label;
            summary.title = label;
        } else {
            const label = `${count} samples`;
            summary.textContent = label;
            summary.title = ts.items
                .map((value) => ts.options[value]?.text || String(value))
                .join(', ');
        }
        summary.hidden = expanded;
    };

    ts.on('initialize', refresh);
    ts.on('item_add', refresh);
    ts.on('item_remove', refresh);
    ts.on('clear', refresh);
    ts.on('focus', refresh);
    ts.on('blur', refresh);
    ts.on('dropdown_open', refresh);
    ts.on('dropdown_close', refresh);
    refresh();
}

/**
 * Initialize all typeahead elements on the page with performance optimizations
 */
function initializeAll() {
    const elements = document.querySelectorAll('select[data-typeahead]');
    
    if (elements.length === 0) return;
    
    // Separate eager and lazy elements
    const eagerElements = [];
    const lazyElements = [];
    
    elements.forEach(element => {
        const loadMode = element.getAttribute('data-typeahead-load') || 'auto';
        if (loadMode === 'eager') {
            eagerElements.push(element);
        } else if (loadMode === 'lazy') {
            lazyElements.push(element);
        } else {
            // Auto mode: lazy if more than 3 total selects
            if (elements.length > 3) {
                lazyElements.push(element);
            } else {
                eagerElements.push(element);
            }
        }
    });
    
    // Initialize eager elements in batches
    if (eagerElements.length > 0) {
        const batchSize = 5;
        let currentIndex = 0;
        
        const processBatch = () => {
            const endIndex = Math.min(currentIndex + batchSize, eagerElements.length);
            
            for (let i = currentIndex; i < endIndex; i++) {
                initializeTomSelect(eagerElements[i]);
            }
            
            currentIndex = endIndex;
            
            if (currentIndex < eagerElements.length) {
                if (typeof requestIdleCallback !== 'undefined') {
                    requestIdleCallback(processBatch, { timeout: 100 });
                } else {
                    requestAnimationFrame(processBatch);
                }
            }
        };
        
        if (typeof requestIdleCallback !== 'undefined') {
            requestIdleCallback(processBatch, { timeout: 100 });
        } else {
            requestAnimationFrame(processBatch);
        }
    }
    
    // Set up lazy loading for remaining elements
    if (lazyElements.length > 0) {
        lazyElements.forEach(element => {
            let initialized = false;
            
            const init = () => {
                if (!initialized) {
                    initialized = true;
                    initializeTomSelect(element);
                }
            };
            
            // Initialize when user focuses/clicks
            element.addEventListener('mousedown', init, { once: true });
            element.addEventListener('focus', init, { once: true });
            
            // Or when it becomes visible (using IntersectionObserver)
            if (typeof IntersectionObserver !== 'undefined') {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            init();
                            observer.disconnect();
                        }
                    });
                }, {
                    rootMargin: '50px' // Initialize slightly before it comes into view
                });
                
                observer.observe(element);
            } else {
                // Fallback: initialize immediately if IntersectionObserver not supported
                init();
            }
        });
    }
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAll);
} else {
    initializeAll();
}

// Reinitialize after HTMX swaps (for dynamically loaded content)
if (typeof htmx !== 'undefined') {
    document.body.addEventListener('htmx:afterSwap', function(event) {
        const newElements = event.detail.target.querySelectorAll('select[data-typeahead]');
        newElements.forEach(initializeTomSelect);
    });
}

// Reinitialize after Livewire updates (for Livewire components)
if (typeof Livewire !== 'undefined') {
    document.addEventListener('livewire:navigated', initializeAll);
    
    Livewire.hook('morph.updated', ({ el, component }) => {
        const newElements = el.querySelectorAll('select[data-typeahead]');
        newElements.forEach(initializeTomSelect);
    });
}

// Export for manual initialization if needed
export { initializeTomSelect, initializeAll };
