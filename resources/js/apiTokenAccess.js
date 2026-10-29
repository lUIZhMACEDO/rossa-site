/**
 * API token access panel on /api-docs (show/copy token, submit request modal).
 */

function onReady(callback) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);
    } else {
        callback();
    }
}

function initTokenDisplay(root) {
    const input = root.querySelector('[data-api-token-input]');
    const toggle = root.querySelector('[data-api-token-toggle]');
    const copy = root.querySelector('[data-api-token-copy]');
    const icon = root.querySelector('[data-api-token-toggle-icon]');

    if (!input || !toggle) {
        return;
    }

    toggle.addEventListener('click', () => {
        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        icon?.classList.toggle('bi-eye', showing);
        icon?.classList.toggle('bi-eye-slash', !showing);
        toggle.setAttribute('title', showing ? 'Show token' : 'Hide token');
    });

    if (!copy) {
        return;
    }

    const copyIcon = '<i class="bi bi-clipboard" aria-hidden="true"></i>';

    copy.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(input.value);
            copy.textContent = 'Copied!';
            setTimeout(() => {
                copy.innerHTML = copyIcon;
            }, 2000);
        } catch {
            copy.textContent = 'Copy failed';
            setTimeout(() => {
                copy.innerHTML = copyIcon;
            }, 2000);
        }
    });
}

function initRequestForm(root) {
    const form = root.querySelector('[data-api-token-request-form]');
    const submitBtn = root.querySelector('[data-api-token-request-submit]');
    const openBtn = root.querySelector('[data-api-token-request-open]');
    const errorEl = root.querySelector('[data-api-token-request-error]');
    const messageField = root.querySelector('[data-api-token-message]');

    if (!form || !submitBtn || !errorEl || !messageField) {
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorEl.textContent = '';
        messageField.classList.remove('is-invalid');
        submitBtn.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                const message = data.message ||
                    data.errors?.message?.[0] ||
                    'Unable to submit your request. Please try again.';
                errorEl.textContent = message;
                messageField.classList.add('is-invalid');
                submitBtn.disabled = false;
                return;
            }

            if (openBtn) {
                openBtn.disabled = true;
            }

            window.location.reload();
        } catch {
            errorEl.textContent = 'Unable to submit your request. Please try again.';
            messageField.classList.add('is-invalid');
            submitBtn.disabled = false;
        }
    });
}

function init() {
    const root = document.querySelector('[data-api-token-access]');

    if (!root) {
        return;
    }

    initTokenDisplay(root);
    initRequestForm(root);
}

onReady(init);
