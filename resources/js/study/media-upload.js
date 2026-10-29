/**
 * Chunked / resumable study media uploads (Image Upload) to local storage.
 */

const SCAN_POLL_TIMEOUT_MS = 25 * 60 * 1000;
const VERTEBRAL_LEVEL_MAX = { cervical: 7, thoracic: 12, lumbar: 6 };
const IN_PROGRESS = ['pending', 'uploading'];
const EMPTY_LABELS = {
    long_bone_scan_extent: {},
    vertebra_scan_extent: {},
};

/** @type {Map<number|string, AbortController>} */
const uploadControllers = new Map();

/** Media initiated this session that have not started receiving chunks yet. */
const awaitingFirstChunk = new Set();

/** Media IDs with an in-flight table-row HTML fetch (avoids duplicate DOM rows). */
const pendingRowInserts = new Set();

let proceedingWithNavigation = false;
let pendingNavigationUrl = null;

/** @type {{long_bone_scan_extent: Record<string, string>, vertebra_scan_extent: Record<string, string>}|null} */
let uploadLabelsCache = null;

function byId(id) {
    return document.getElementById(id);
}

function el(tag, props = {}, children = []) {
    const node = document.createElement(tag);
    const { className, text, html, dataset, ...attrs } = props;

    if (className) {
        node.className = className;
    }
    if (text != null) {
        node.textContent = text;
    }
    if (html != null) {
        node.innerHTML = html;
    }
    if (dataset) {
        Object.assign(node.dataset, dataset);
    }

    for (const [key, value] of Object.entries(attrs)) {
        if (value == null || value === false) {
            continue;
        }
        if (key in node) {
            node[key] = value;
        } else {
            node.setAttribute(key, value);
        }
    }

    children.forEach((child) => child && node.appendChild(child));
    return node;
}

function formatBytes(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    if (bytes < 1024 * 1024 * 1024) return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    return `${(bytes / (1024 * 1024 * 1024)).toFixed(2)} GB`;
}

function formatDuration(seconds) {
    if (!Number.isFinite(seconds) || seconds < 0) {
        return '';
    }
    const total = Math.round(seconds);
    if (total < 60) {
        return `${total}s`;
    }
    const minutes = Math.floor(total / 60);
    const remSeconds = total % 60;
    if (minutes < 60) {
        return remSeconds ? `${minutes}m ${remSeconds}s` : `${minutes}m`;
    }
    const hours = Math.floor(minutes / 60);
    const remMinutes = minutes % 60;
    return remMinutes ? `${hours}h ${remMinutes}m` : `${hours}h`;
}

function formatSpeed(bytesPerSecond) {
    if (!Number.isFinite(bytesPerSecond) || bytesPerSecond <= 0) {
        return '';
    }
    return `${formatBytes(bytesPerSecond)}/s`;
}

function estimateTransfer(uploadedBytes, totalBytes, startedAtMs, startBytes) {
    const elapsedSec = (performance.now() - startedAtMs) / 1000;
    const bytesThisSession = Math.max(0, uploadedBytes - startBytes);
    if (elapsedSec < 1 || bytesThisSession < 1) {
        return null;
    }

    const speed = bytesThisSession / elapsedSec;
    const remaining = Math.max(0, totalBytes - uploadedBytes);
    if (speed <= 0 || remaining <= 0) {
        return null;
    }

    return {
        speedLabel: formatSpeed(speed),
        etaLabel: formatDuration(remaining / speed),
    };
}

function replaceTemplate(template, mediaId, index = null) {
    let url = template.replace('__MEDIA__', String(mediaId));
    if (index !== null) {
        url = url.replace('__INDEX__', String(index));
    }
    return url;
}

function getUploadLabels() {
    if (uploadLabelsCache) {
        return uploadLabelsCache;
    }

    try {
        const data = JSON.parse(byId('media-upload-labels')?.textContent || '{}');
        uploadLabelsCache = {
            long_bone_scan_extent: data.long_bone_scan_extent || {},
            vertebra_scan_extent: data.vertebra_scan_extent || {},
        };
    } catch {
        uploadLabelsCache = { ...EMPTY_LABELS };
    }

    return uploadLabelsCache;
}

function scanExtentOptionsForBoneType(boneType) {
    if (!boneType) {
        return {};
    }
    const labels = getUploadLabels();
    return {
        ...(boneType === 'vertebra' ? labels.vertebra_scan_extent : labels.long_bone_scan_extent),
        other: 'Other',
    };
}

function fillSelect(select, options, { placeholder = null, current = '', disabled = false, fallbackFirst = false } = {}) {
    if (!select) {
        return;
    }

    const value = current || select.value;
    select.disabled = disabled;
    select.replaceChildren();

    if (placeholder != null) {
        select.appendChild(el('option', { value: '', text: placeholder }));
    }

    Object.entries(options).forEach(([optionValue, label]) => {
        select.appendChild(el('option', { value: optionValue, text: label }));
    });

    if (value && [...select.options].some((option) => option.value === value)) {
        select.value = value;
    } else if (fallbackFirst) {
        select.value = Object.keys(options)[0] || '';
    }
}

function setVisible(node, visible) {
    if (!node) {
        return;
    }

    node.hidden = !visible;
    if (visible) {
        return;
    }

    const fields = node.matches('input, select, textarea')
        ? [node]
        : node.querySelectorAll('input, select, textarea');
    fields.forEach((field) => {
        field.value = '';
    });
}

function rowField(row, name) {
    const field = row.querySelector(`[name="${name}"]`);
    return field ? String(field.value || '').trim() : '';
}

function refreshRowConditionalFields(row) {
    if (!row) {
        return;
    }

    const boneType = row.querySelector('[data-media-bone-type]')?.value || '';
    const scanExtent = row.querySelector('[data-media-scan-extent]')?.value || '';

    setVisible(row.querySelector('[data-user-upload-extra="bone_type_other"]'), boneType === 'other');
    setVisible(row.querySelector('[data-user-upload-extra="vertebra"]'), boneType === 'vertebra');
    setVisible(row.querySelector('[data-user-upload-extra="scan_extent_other"]'), scanExtent === 'other');
}

function rowNeedsExtraFieldsBeforeSave(row) {
    const boneType = rowField(row, 'user_upload[bone_type]');
    const scanExtent = rowField(row, 'user_upload[scan_extent]');

    return (boneType === 'other' && !rowField(row, 'user_upload[bone_type_other]'))
        || (boneType === 'vertebra' && (!rowField(row, 'user_upload[spinal_region]') || !rowField(row, 'user_upload[vertebral_levels]')))
        || (scanExtent === 'other' && !rowField(row, 'user_upload[scan_extent_other]'));
}

function isScanEditable(root) {
    return root.dataset.scanEditable === '1';
}

function isAbortError(err) {
    return err?.name === 'AbortError' || err?.message === 'The user aborted a request.';
}

function uploadsTbody() {
    return byId('media-uploads-tbody');
}

function removeEmptyRow() {
    byId('media-uploads-empty')?.remove();
}

function maybeShowEmptyRow() {
    const tbody = uploadsTbody();
    if (!tbody || tbody.querySelector('tr')) {
        return;
    }

    tbody.appendChild(el('tr', { id: 'media-uploads-empty' }, [
        el('td', { colSpan: 7, className: 'text-center text-muted', text: 'No images uploaded yet.' }),
    ]));
}

function beginUploadControl(mediaId) {
    uploadControllers.get(mediaId)?.abort();
    const controller = new AbortController();
    uploadControllers.set(mediaId, controller);
    return controller.signal;
}

function endUploadControl(mediaId) {
    uploadControllers.delete(mediaId);
}

function abortUpload(mediaId) {
    const controller = uploadControllers.get(mediaId);
    if (controller) {
        controller.abort();
        uploadControllers.delete(mediaId);
    }
}

function fillScanExtentSelect(select, boneType, selectedValue = '') {
    fillSelect(select, scanExtentOptionsForBoneType(boneType), {
        placeholder: boneType ? null : 'Select bone type first…',
        current: selectedValue,
        disabled: !boneType,
        fallbackFirst: Boolean(boneType),
    });
}

function statusBadgeClass(status) {
    return {
        complete: 'text-bg-success',
        scanning: 'text-bg-warning',
        uploading: 'text-bg-warning',
        pending: 'text-bg-warning',
        failed: 'text-bg-danger',
    }[status] || 'text-bg-secondary';
}

function updateStatusCell(statusCell, media) {
    statusCell.replaceChildren(el('span', {
        className: `badge ${statusBadgeClass(media.status)}`,
        text: media.status_label || media.status,
    }));

    if (media.size && media.bytes_received != null && IN_PROGRESS.includes(media.status)) {
        statusCell.appendChild(el('span', {
            className: 'small text-muted ms-1',
            text: `${Math.round((media.bytes_received / Math.max(media.size, 1)) * 100)}%`,
        }));
    }

    if (media.status === 'failed' && media.malware_scan_message) {
        statusCell.appendChild(el('div', {
            className: 'small text-muted',
            text: media.malware_scan_message,
        }));
    }
}

function updateActionsCell(root, actionsCell, media) {
    const group = el('div', { className: 'btn-group gap-1' });

    if (media.downloadable) {
        group.appendChild(el('a', {
            href: replaceTemplate(root.dataset.downloadUrlTemplate, media.id),
            className: 'btn btn-sm btn-outline-secondary',
            title: 'Download',
            html: '<i class="bi-download"></i>',
        }));
    } else if (IN_PROGRESS.includes(media.status) && isScanEditable(root)) {
        group.appendChild(el('button', {
            type: 'button',
            className: 'btn btn-sm btn-primary',
            title: 'Resume (re-select the same file)',
            text: 'Resume',
            dataset: {
                resumeUpload: '',
                mediaId: String(media.id),
                filename: media.original_filename || '',
                size: String(media.size || ''),
            },
        }));
    }

    if (isScanEditable(root)) {
        group.appendChild(el('button', {
            type: 'button',
            className: 'btn btn-sm btn-outline-danger',
            title: 'Delete',
            html: '<i class="bi-trash"></i>',
            dataset: {
                deleteMedia: '',
                deleteUrl: replaceTemplate(root.dataset.destroyUrlTemplate, media.id),
            },
        }));
    }

    actionsCell.replaceChildren(group);
}

function updateMediaRowProgress(root, row, media) {
    if (!row) {
        return;
    }

    row.dataset.originalFilename = media.original_filename || row.dataset.originalFilename || '';
    if (media.sample_group_id !== undefined) {
        row.dataset.sampleGroupId = media.sample_group_id != null ? String(media.sample_group_id) : '';
    }

    const statusCell = row.querySelector('[data-media-status-cell]');
    if (statusCell) {
        updateStatusCell(statusCell, media);
    }

    const actionsCell = row.querySelector('[data-media-actions-cell]');
    if (actionsCell) {
        updateActionsCell(root, actionsCell, media);
    }
}

function mediaRowSelector(mediaId) {
    return `tr[data-media-id="${mediaId}"]`;
}

function removeDuplicateMediaRows(mediaId) {
    const tbody = uploadsTbody();
    if (!tbody) {
        return;
    }

    const rows = tbody.querySelectorAll(mediaRowSelector(mediaId));
    for (let i = 1; i < rows.length; i += 1) {
        rows[i].remove();
    }
}

async function insertMediaRowFromServer(root, mediaId) {
    removeEmptyRow();

    const tbody = uploadsTbody();
    const key = String(mediaId);
    if (!tbody || tbody.querySelector(mediaRowSelector(mediaId)) || pendingRowInserts.has(key)) {
        return;
    }

    pendingRowInserts.add(key);

    try {
        const response = await fetch(replaceTemplate(root.dataset.rowUrlTemplate, mediaId), {
            headers: {
                Accept: 'text/html',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            return;
        }

        // Re-check after await: another upsert may have inserted while we fetched.
        if (tbody.querySelector(mediaRowSelector(mediaId))) {
            return;
        }

        // nosemgrep: typescript.react.security.audit.react-unsanitized-method.react-unsanitized-method -- same-origin Blade partial (media-upload-row) with escaped output
        tbody.insertAdjacentHTML('afterbegin', await response.text());
        removeDuplicateMediaRows(mediaId);
        const row = tbody.querySelector(mediaRowSelector(mediaId));
        if (row && window.htmx) {
            window.htmx.process(row);
            refreshRowConditionalFields(row);
        }
    } finally {
        pendingRowInserts.delete(key);
    }
}

function upsertMediaRow(root, media) {
    const tbody = uploadsTbody();
    if (!tbody) {
        return;
    }

    removeEmptyRow();
    removeDuplicateMediaRows(media.id);

    const row = tbody.querySelector(mediaRowSelector(media.id));
    if (!row) {
        insertMediaRowFromServer(root, media.id);
        return;
    }

    updateMediaRowProgress(root, row, media);
}

function htmxFormMatching(elt, selector) {
    if (elt?.matches?.(selector)) {
        return elt;
    }
    if (elt?.form?.matches?.(selector)) {
        return elt.form;
    }
    return elt?.closest?.(selector) ?? null;
}

function firstValidationError(data) {
    return data?.errors && Object.values(data.errors).flat()[0];
}

function bindMediaUploadTable(root) {
    document.body.addEventListener('htmx:beforeRequest', (event) => {
        const elt = event.detail.elt;
        const sampleGroupForm = htmxFormMatching(elt, 'form[data-media-sample-group-form]');
        if (sampleGroupForm && root.contains(sampleGroupForm) && !sampleGroupForm.querySelector('[name="sample_group_id"]')?.value) {
            event.preventDefault();
            return;
        }

        const form = htmxFormMatching(elt, 'form[data-media-metadata-form]');
        if (!form || !root.contains(form)) {
            return;
        }

        const row = form.closest('tr');
        if (row && rowNeedsExtraFieldsBeforeSave(row)) {
            event.preventDefault();
        }
    });

    document.body.addEventListener('htmx:afterRequest', (event) => {
        const elt = event.detail.elt;
        const xhr = event.detail.xhr;
        if (!elt || !root.contains(elt) || !xhr || xhr.status < 400) {
            return;
        }

        let message = 'Request failed.';
        try {
            const data = JSON.parse(xhr.responseText);
            message = data.message || firstValidationError(data) || message;
        } catch {
            // keep default message
        }
        window.alert(message);
    });

    root.addEventListener('change', (event) => {
        const boneTypeSelect = event.target.closest('[data-media-bone-type]');
        if (boneTypeSelect) {
            const row = boneTypeSelect.closest('tr');
            fillScanExtentSelect(row?.querySelector('[data-media-scan-extent]'), boneTypeSelect.value);
            refreshRowConditionalFields(row);
            return;
        }

        const scanExtentSelect = event.target.closest('[data-media-scan-extent]');
        if (scanExtentSelect) {
            refreshRowConditionalFields(scanExtentSelect.closest('tr'));
        }
    });

    document.querySelectorAll('#media-uploads-tbody tr[data-media-id]').forEach(refreshRowConditionalFields);
}

function createQueueItem(file) {
    const item = el('div', { className: 'border rounded p-3 mb-2' });
    item.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="text-truncate me-2" style="max-width: 70%;">
                <strong></strong>
                <span class="small text-muted ms-2 file-size"></span>
            </div>
            <span class="small text-muted status-label">Queued</span>
        </div>
        <div class="progress" role="progressbar" style="height: 0.6rem;">
            <div class="progress-bar" style="width: 0%"></div>
        </div>
        <div class="small text-muted mt-1 eta-label" hidden></div>
    `;
    item.querySelector('strong').textContent = file.name;
    item.querySelector('.file-size').textContent = `(${formatBytes(file.size)})`;
    return item;
}

function syncQueueVisibility() {
    const wrap = byId('media-upload-queue-wrap');
    const queue = byId('media-upload-queue');
    if (!wrap || !queue) {
        return;
    }
    wrap.hidden = queue.children.length === 0;
}

function removeQueueItem(itemEl) {
    itemEl?.remove();
    syncQueueVisibility();
}

function appendQueueItem(file) {
    const queue = byId('media-upload-queue');
    if (!queue) {
        return null;
    }
    const queueItem = createQueueItem(file);
    queue.appendChild(queueItem);
    syncQueueVisibility();
    return queueItem;
}

function updateQueueItem(itemEl, { percent, label, error = false, eta = null }) {
    const bar = itemEl.querySelector('.progress-bar');
    const status = itemEl.querySelector('.status-label');
    const etaEl = itemEl.querySelector('.eta-label');
    const pct = Math.max(0, Math.min(100, percent));
    bar.style.width = `${pct}%`;
    bar.classList.toggle('bg-danger', error);
    bar.classList.toggle('bg-success', !error && pct >= 100);
    status.textContent = error ? label : `${pct.toFixed(0)}% · ${label}`;

    if (!etaEl) {
        return;
    }

    if (!error && eta?.etaLabel && eta?.speedLabel) {
        etaEl.hidden = false;
        etaEl.textContent = `~${eta.etaLabel} remaining · ${eta.speedLabel}`;
    } else if (!error && pct >= 100) {
        etaEl.hidden = false;
        etaEl.textContent = 'Upload complete';
    } else {
        etaEl.hidden = true;
        etaEl.textContent = '';
    }
}

function jsonHeaders(root, extra = {}) {
    return {
        Accept: 'application/json',
        'X-CSRF-TOKEN': root.dataset.csrf,
        'X-Requested-With': 'XMLHttpRequest',
        ...extra,
    };
}

async function parseJsonResponse(response) {
    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        const error = new Error(
            data.message
                || firstValidationError(data)
                || data.errors?.original_filename?.[0]
                || `Request failed (${response.status})`
        );
        error.response = { data };
        throw error;
    }
    return data;
}

function messageFromError(err, fallback = 'Upload failed') {
    return err?.response?.data?.message
        || firstValidationError(err?.response?.data)
        || err?.response?.data?.errors?.original_filename?.[0]
        || err?.message
        || fallback;
}

async function jsonRequest(root, url, { method = 'GET', body, jsonBody, signal, keepalive = false, headers = {} } = {}) {
    const extraHeaders = jsonBody !== undefined
        ? { 'Content-Type': 'application/json', ...headers }
        : headers;
    const response = await fetch(url, {
        method,
        headers: jsonHeaders(root, extraHeaders),
        body: jsonBody !== undefined ? JSON.stringify(jsonBody) : body,
        signal,
        keepalive,
    });
    if (keepalive) {
        return null;
    }
    return parseJsonResponse(response);
}

function fieldValue(name) {
    const field = document.querySelector(`[data-upload-field="${name}"]`);
    return field ? String(field.value || '').trim() : '';
}

function showFormError(message) {
    const node = byId('upload-form-error');
    if (!node) {
        return;
    }
    node.hidden = !message;
    node.textContent = message || '';
}

function vertebralLevelsAreValid(value, spinalRegion) {
    const max = VERTEBRAL_LEVEL_MAX[spinalRegion];
    if (!max) {
        return false;
    }

    if (/^\d+$/.test(value)) {
        const level = Number(value);
        return level >= 1 && level <= max;
    }

    const match = value.match(/^(\d+)\s*-\s*(\d+)$/);
    if (!match) {
        return false;
    }

    const start = Number(match[1]);
    const end = Number(match[2]);
    return start >= 1 && end <= max && start <= end;
}

function collectUploadMetadata() {
    const processingLevel = fieldValue('processing_level');
    const boneType = fieldValue('bone_type');
    const scanExtent = fieldValue('scan_extent');
    const sampleGroupId = fieldValue('sample_group_id');

    const required = [
        [processingLevel, 'Select a processing level.'],
        [boneType, 'Select a bone type.'],
        [scanExtent, 'Select a scan extent.'],
        [sampleGroupId, 'Select a sample group.'],
    ];
    for (const [value, message] of required) {
        if (!value) {
            throw new Error(message);
        }
    }

    const userUpload = {
        kind: 'raw_micro_ct',
        processing_level: processingLevel,
        bone_type: boneType,
        scan_extent: scanExtent,
    };

    if (boneType === 'vertebra') {
        const spinalRegion = fieldValue('spinal_region');
        const vertebralLevels = fieldValue('vertebral_levels');
        if (!spinalRegion) {
            throw new Error('Select a spinal region.');
        }
        if (!vertebralLevels) {
            throw new Error('Enter vertebral level(s).');
        }
        if (!vertebralLevelsAreValid(vertebralLevels, spinalRegion)) {
            throw new Error(`Enter a single level or range (e.g. 5 or 3-5) within 1–${VERTEBRAL_LEVEL_MAX[spinalRegion]} for ${spinalRegion}.`);
        }
        userUpload.spinal_region = spinalRegion;
        userUpload.vertebral_levels = vertebralLevels;
    }

    if (boneType === 'other') {
        const boneTypeOther = fieldValue('bone_type_other');
        if (!boneTypeOther) {
            throw new Error('Specify the bone type.');
        }
        userUpload.bone_type_other = boneTypeOther;
    }

    if (scanExtent === 'other') {
        const scanExtentOther = fieldValue('scan_extent_other');
        if (!scanExtentOther) {
            throw new Error('Specify the scan extent.');
        }
        userUpload.scan_extent_other = scanExtentOther;
    }

    return {
        sample_group_id: Number(sampleGroupId),
        user_upload: userUpload,
    };
}

function refreshUploadFormFields() {
    const boneType = fieldValue('bone_type');
    setVisible(byId('upload-vertebra-fields'), boneType === 'vertebra');
    setVisible(byId('upload-other-bone-fields'), boneType === 'other');
    fillSelect(byId('upload-scan-extent'), scanExtentOptionsForBoneType(boneType), {
        placeholder: boneType ? 'Select…' : 'Select bone type first…',
    });
    setVisible(byId('upload-other-extent-fields'), fieldValue('scan_extent') === 'other');
}

function bindUploadForm() {
    byId('upload-bone-type')?.addEventListener('change', () => {
        refreshUploadFormFields();
        showFormError('');
    });

    byId('upload-scan-extent')?.addEventListener('change', () => {
        setVisible(byId('upload-other-extent-fields'), fieldValue('scan_extent') === 'other');
        showFormError('');
    });

    document.querySelectorAll('[data-upload-field]').forEach((field) => {
        field.addEventListener('change', () => showFormError(''));
        field.addEventListener('input', () => showFormError(''));
    });

    refreshUploadFormFields();
}

function initiateUpload(root, file, meta, signal) {
    return jsonRequest(root, root.dataset.initUrl, {
        method: 'POST',
        signal,
        jsonBody: {
            original_filename: file.name,
            size: file.size,
            mime_type: file.type || null,
            sample_group_id: meta.sample_group_id,
            user_upload: meta.user_upload,
        },
    });
}

function fetchStatus(root, mediaId, signal) {
    return jsonRequest(root, replaceTemplate(root.dataset.statusUrlTemplate, mediaId), { signal });
}

async function uploadChunks(root, file, media, queueItem, signal) {
    const chunkSize = media.chunk_size || Number(root.dataset.chunkSize);
    let nextIndex = media.next_chunk_index ?? 0;
    const totalChunks = Math.ceil(file.size / chunkSize);
    const transferStartedAt = performance.now();
    const startBytes = media.bytes_received ?? 0;

    while (nextIndex < totalChunks && (media.bytes_received ?? 0) < file.size) {
        signal?.throwIfAborted?.();

        const start = nextIndex * chunkSize;
        const end = Math.min(start + chunkSize, file.size);
        media = await jsonRequest(root, replaceTemplate(root.dataset.chunkUrlTemplate, media.id, nextIndex), {
            method: 'PUT',
            headers: { 'Content-Type': 'application/octet-stream' },
            body: file.slice(start, end),
            signal,
        });

        awaitingFirstChunk.delete(media.id);

        nextIndex = media.next_chunk_index;
        const uploadedBytes = Math.min(media.bytes_received ?? 0, file.size);
        const done = uploadedBytes >= file.size || nextIndex >= totalChunks;
        const estimate = done
            ? null
            : estimateTransfer(uploadedBytes, file.size, transferStartedAt, startBytes);

        updateQueueItem(queueItem, {
            percent: done ? 100 : (uploadedBytes / file.size) * 100,
            label: done
                ? 'Uploaded'
                : `Uploading chunk ${Math.min(nextIndex, totalChunks)} / ${totalChunks}`,
            eta: estimate,
        });
        upsertMediaRow(root, media);
    }

    awaitingFirstChunk.delete(media.id);
    return media;
}

function completeUpload(root, mediaId, signal) {
    return jsonRequest(root, replaceTemplate(root.dataset.completeUrlTemplate, mediaId), {
        method: 'POST',
        jsonBody: {},
        signal,
    });
}

function wait(milliseconds, signal) {
    return new Promise((resolve, reject) => {
        if (signal?.aborted) {
            reject(new DOMException('The user aborted a request.', 'AbortError'));
            return;
        }

        const timer = window.setTimeout(resolve, milliseconds);
        signal?.addEventListener(
            'abort',
            () => {
                window.clearTimeout(timer);
                reject(new DOMException('The user aborted a request.', 'AbortError'));
            },
            { once: true }
        );
    });
}

async function pollScanInBackground(root, media) {
    const deadline = Date.now() + SCAN_POLL_TIMEOUT_MS;
    let current = media;
    let completeWithoutMetaTries = 0;

    while (Date.now() < deadline) {
        try {
            current = await fetchStatus(root, current.id);
            upsertMediaRow(root, current);
        } catch (err) {
            if (isAbortError(err)) {
                return;
            }
            console.error(err);
        }

        const terminal = current.status === 'complete' || current.status === 'failed';
        if (current.status === 'failed') {
            return;
        }
        // Wait for scanner-parsed format when possible; user_upload alone is not enough.
        if (terminal && current.metadata?.format) {
            return;
        }
        if (terminal) {
            completeWithoutMetaTries += 1;
            if (completeWithoutMetaTries >= 5) {
                return;
            }
            await wait(500);
            continue;
        }

        await wait(3000);
    }
}

function validateFile(root, file) {
    const maxSize = Number(root.dataset.maxFileSize);
    if (file.size > maxSize) {
        throw new Error(`File exceeds max size of ${formatBytes(maxSize)}`);
    }

    const allowed = (root.dataset.allowedExtensions || '')
        .split(',')
        .map((ext) => ext.trim().toLowerCase())
        .filter(Boolean);
    const ext = file.name.split('.').pop()?.toLowerCase();
    if (!ext || !allowed.includes(ext)) {
        throw new Error(`Allowed extensions: ${allowed.join(', ')}`);
    }
}

function deleteMedia(root, mediaId, { keepalive = false } = {}) {
    return jsonRequest(root, replaceTemplate(root.dataset.destroyUrlTemplate, mediaId), {
        method: 'DELETE',
        keepalive,
    });
}

async function runUpload(root, file, queueItem, mediaId) {
    updateQueueItem(queueItem, { percent: 0, label: 'Starting…' });
    const signal = beginUploadControl(mediaId);

    try {
        validateFile(root, file);

        let media = await fetchStatus(root, mediaId, signal);
        if (Number(media.size) !== file.size) {
            throw new Error('Selected file size does not match the in-progress upload. Choose the same file.');
        }

        updateQueueItem(queueItem, {
            percent: (media.bytes_received / file.size) * 100,
            label: media.bytes_received > 0 ? 'Resuming…' : 'Uploading…',
        });

        upsertMediaRow(root, media);

        media = await uploadChunks(root, file, media, queueItem, signal);
        updateQueueItem(queueItem, { percent: 100, label: 'Uploaded' });
        upsertMediaRow(root, media);
        removeQueueItem(queueItem);
        queueItem = null;

        try {
            const completed = await completeUpload(root, media.id, signal);
            upsertMediaRow(root, completed);
            pollScanInBackground(root, completed);
        } catch (completeErr) {
            if (isAbortError(completeErr)) {
                throw completeErr;
            }
            console.error(completeErr);
            pollScanInBackground(root, media);
        }
    } catch (err) {
        if (isAbortError(err)) {
            if (queueItem) {
                updateQueueItem(queueItem, { percent: 0, label: 'Cancelled', error: true });
            }
            return;
        }

        if (queueItem) {
            updateQueueItem(queueItem, { percent: 0, label: messageFromError(err), error: true });
        }
        console.error(err);
    } finally {
        endUploadControl(mediaId);
        awaitingFirstChunk.delete(mediaId);
    }
}

function cleanupAwaitingFirstChunk(root) {
    for (const mediaId of [...awaitingFirstChunk]) {
        awaitingFirstChunk.delete(mediaId);
        abortUpload(mediaId);
        deleteMedia(root, mediaId, { keepalive: true }).catch(() => {});
    }
}

function hasActiveUploads() {
    return uploadControllers.size > 0 || awaitingFirstChunk.size > 0;
}

function onBeforeUnload(event) {
    if (proceedingWithNavigation || !hasActiveUploads()) {
        return;
    }

    event.preventDefault();
    event.returnValue = '';
}

function shouldConfirmNavigation(anchor, event) {
    if (!anchor || anchor.tagName !== 'A') {
        return false;
    }

    const href = anchor.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
        return false;
    }

    if (anchor.target === '_blank' || anchor.hasAttribute('download')) {
        return false;
    }

    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
        return false;
    }

    if (anchor.closest('#media-upload-navigation-modal')) {
        return false;
    }

    try {
        return !new URL(anchor.href, window.location.origin).pathname.includes('/download');
    } catch {
        return false;
    }
}

function bindNavigationGuard() {
    const modalEl = byId('media-upload-navigation-modal');
    if (!modalEl) {
        return;
    }

    const modal = window.bootstrap?.Modal.getOrCreateInstance(modalEl);

    byId('media-upload-open-new-tab')?.addEventListener('click', () => {
        if (pendingNavigationUrl) {
            window.open(pendingNavigationUrl, '_blank', 'noopener');
        }
        modal?.hide();
    });

    byId('media-upload-leave-page')?.addEventListener('click', () => {
        if (!pendingNavigationUrl) {
            return;
        }
        proceedingWithNavigation = true;
        modal?.hide();
        window.location.href = pendingNavigationUrl;
    });

    document.addEventListener('click', (event) => {
        if (proceedingWithNavigation || !hasActiveUploads()) {
            return;
        }

        const anchor = event.target.closest('a[href]');
        if (!shouldConfirmNavigation(anchor, event)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        pendingNavigationUrl = anchor.href;
        modal?.show();
    }, true);
}

function filesFromList(list) {
    return Array.from(list || []).filter((file) => file instanceof File && file.name);
}

function isFileDrag(event) {
    return Array.from(event.dataTransfer?.types || []).includes('Files');
}

async function startFileBatch(root, files) {
    const selected = filesFromList(files);
    if (!selected.length) {
        return;
    }

    if (!byId('media-upload-queue')) {
        return;
    }

    let meta;
    try {
        meta = collectUploadMetadata();
        showFormError('');
    } catch (err) {
        showFormError(err?.message || 'Complete the required metadata before uploading.');
        return;
    }

    const items = selected.map((file) => ({
        file,
        queueItem: appendQueueItem(file),
        mediaId: null,
    })).filter((item) => item.queueItem);

    for (const item of items) {
        try {
            validateFile(root, item.file);
            updateQueueItem(item.queueItem, { percent: 0, label: 'Starting…' });
            let media = await initiateUpload(root, item.file, meta);
            media = {
                ...media,
                original_filename: item.file.name,
                size: item.file.size,
                status_label: media.status_label || 'Pending',
            };
            item.mediaId = media.id;
            awaitingFirstChunk.add(media.id);
            upsertMediaRow(root, media);
            updateQueueItem(item.queueItem, { percent: 0, label: 'Queued' });
        } catch (err) {
            updateQueueItem(item.queueItem, {
                percent: 0,
                label: isAbortError(err) ? 'Cancelled' : messageFromError(err),
                error: true,
            });
            if (!isAbortError(err)) {
                showFormError(messageFromError(err));
                console.error(err);
            }
        }
    }

    // Kick off transfers without awaiting the whole batch so another upload
    // can be started from the card while these continue below.
    items
        .filter((item) => item.mediaId)
        .forEach((item) => {
            runUpload(root, item.file, item.queueItem, item.mediaId);
        });
}

function bindDropzone(root, input) {
    const dropzone = root.querySelector('.media-dropzone');
    if (!dropzone || !input) {
        return;
    }

    let dragDepth = 0;
    const setDragover = (active) => dropzone.classList.toggle('is-dragover', active);

    dropzone.addEventListener('dragenter', (event) => {
        if (!isFileDrag(event)) {
            return;
        }
        event.preventDefault();
        dragDepth += 1;
        setDragover(true);
    });

    dropzone.addEventListener('dragover', (event) => {
        if (!isFileDrag(event)) {
            return;
        }
        event.preventDefault();
        event.dataTransfer.dropEffect = 'copy';
        setDragover(true);
    });

    dropzone.addEventListener('dragleave', (event) => {
        if (!isFileDrag(event)) {
            return;
        }
        event.preventDefault();
        dragDepth -= 1;
        if (dragDepth <= 0) {
            dragDepth = 0;
            setDragover(false);
        }
    });

    dropzone.addEventListener('drop', (event) => {
        if (!isFileDrag(event)) {
            return;
        }
        event.preventDefault();
        dragDepth = 0;
        setDragover(false);
        startFileBatch(root, filesFromList(event.dataTransfer?.files));
    });
}

function bindResumeAndDelete(root, input) {
    document.addEventListener('click', async (event) => {
        const resumeBtn = event.target.closest('[data-resume-upload]');
        if (resumeBtn && root.contains(resumeBtn)) {
            const { mediaId, filename: expectedName } = resumeBtn.dataset;
            const expectedSize = Number(resumeBtn.dataset.size);

            const picker = el('input', { type: 'file', accept: input?.accept || '' });
            picker.addEventListener('change', async () => {
                const file = picker.files?.[0];
                if (!file) {
                    return;
                }
                if (file.name !== expectedName || file.size !== expectedSize) {
                    alert(`Please select the same file: ${expectedName} (${formatBytes(expectedSize)})`);
                    return;
                }
                const queueItem = appendQueueItem(file);
                if (queueItem) {
                    await runUpload(root, file, queueItem, mediaId);
                }
            });
            picker.click();
            return;
        }

        const btn = event.target.closest('[data-delete-media]');
        const deleteUrl = btn?.dataset.deleteUrl;
        if (!deleteUrl) {
            return;
        }

        event.preventDefault();
        if (!window.confirm('Delete this file?')) {
            return;
        }

        const row = btn.closest('tr');
        const mediaId = row?.dataset.mediaId;
        if (mediaId) {
            abortUpload(mediaId);
            awaitingFirstChunk.delete(Number(mediaId));
            awaitingFirstChunk.delete(mediaId);
        }

        try {
            await jsonRequest(root, deleteUrl, { method: 'DELETE' });
            row?.remove();
            maybeShowEmptyRow();
        } catch (err) {
            window.alert(messageFromError(err, 'Failed to delete file.'));
            console.error(err);
        }
    });
}

function onReady(callback) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);
    } else {
        callback();
    }
}

onReady(() => {
    const root = document.querySelector('[data-media-upload]');
    if (!root) {
        return;
    }

    window.addEventListener('pagehide', () => cleanupAwaitingFirstChunk(root));
    window.addEventListener('beforeunload', onBeforeUnload);
    bindNavigationGuard();
    bindUploadForm();
    bindMediaUploadTable(root);

    const input = byId('media-file-input');
    input?.addEventListener('change', () => {
        const files = filesFromList(input.files);
        input.value = '';
        startFileBatch(root, files);
    });
    bindDropzone(root, input);
    bindResumeAndDelete(root, input);

    ['dragover', 'drop'].forEach((type) => {
        document.addEventListener(type, (event) => {
            if (isFileDrag(event)) {
                event.preventDefault();
            }
        });
    });
});
