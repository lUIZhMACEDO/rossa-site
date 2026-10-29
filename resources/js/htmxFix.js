// HTMX Fix - form validation swap
// Workaround for but in htmx v2.0.4
// where 422 responses are not swapped
// this will force the swap for 422 responses
htmx.on("htmx:beforeSwap", (e) => {
    if (e.detail.xhr.status === 422) {
        // allow swap
        e.detail.shouldSwap = true;
        e.detail.isError = false;
    }
});