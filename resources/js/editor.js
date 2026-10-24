/*
 * Small formatting editor for the admin panel. The contenteditable area writes its HTML into a
 * hidden textarea bound with wire:model; the server cleans it (App\Support\RichText) on save.
 */

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function toEditorHtml(value) {
    const text = (value || '').trim();
    if (text === '') {
        return '<p><br></p>';
    }
    if (/<\/?(p|br|strong|b|em|i|u|h[1-6]|ul|ol|li|blockquote|a|div)\b/i.test(text)) {
        return text;
    }
    // Older plain text: a blank line starts a new paragraph.
    return text.split(/\n\s*\n/).map((p) => `<p>${escapeHtml(p.trim()).replace(/\n/g, '<br>')}</p>`).join('');
}

function updateCount(box) {
    const area = box.querySelector('[data-rte-area]');
    const count = box.querySelector('[data-rte-count]');
    if (count) {
        count.textContent = area.textContent.trim().length.toLocaleString();
    }
}

function writeBack(box) {
    const area = box.querySelector('[data-rte-area]');
    const source = box.querySelector('.rte-source');
    const html = cleanHtml(area).replace(/<p><br><\/p>$/, '').trim();
    box.dataset.last = html;
    source.value = html;
    source.dispatchEvent(new Event('input', { bubbles: true }));
    updateCount(box);
}

function refreshState(box) {
    box.querySelectorAll('[data-rte-cmd]').forEach((button) => {
        const cmd = button.dataset.rteCmd;
        if (['bold', 'italic', 'underline', 'insertUnorderedList', 'insertOrderedList'].includes(cmd)) {
            let active = false;
            try {
                active = document.queryCommandState(cmd);
            } catch {
                active = false;
            }
            button.classList.toggle('is-active', active);
        }
    });
    let block = '';
    try {
        block = String(document.queryCommandValue('formatBlock') || '').toLowerCase();
    } catch {
        block = '';
    }
    box.querySelectorAll('[data-rte-block]').forEach((button) => {
        button.classList.toggle('is-active', block === button.dataset.rteBlock);
    });
}

/** Load the bound value into each editor; runs on page load and after every Livewire update. */
export function syncEditors() {
    document.querySelectorAll('[data-rte]').forEach((box) => {
        const source = box.querySelector('.rte-source');
        const area = box.querySelector('[data-rte-area]');
        if (!source || !area) {
            return;
        }
        if (box.dataset.ready !== '1' || (source.value !== box.dataset.last && document.activeElement !== area)) {
            area.innerHTML = toEditorHtml(source.value);
            box.dataset.last = source.value;
            box.dataset.ready = '1';
            updateCount(box);
        }
    });
}

function boxOf(target) {
    return target.closest?.('[data-rte]');
}

document.addEventListener('mousedown', (event) => {
    // Keep the text selection while pressing toolbar buttons.
    if (event.target.closest('.rte-toolbar button')) {
        event.preventDefault();
    }
});

document.addEventListener('click', (event) => {
    const box = boxOf(event.target);
    if (!box) {
        return;
    }
    const area = box.querySelector('[data-rte-area]');
    const cmdButton = event.target.closest('[data-rte-cmd]');
    const blockButton = event.target.closest('[data-rte-block]');
    const linkBar = box.querySelector('[data-rte-linkbar]');

    if (cmdButton || blockButton) {
        area.focus();
        if (cmdButton) {
            document.execCommand(cmdButton.dataset.rteCmd, false, null);
        } else {
            const tag = blockButton.dataset.rteBlock;
            let current = '';
            try {
                current = String(document.queryCommandValue('formatBlock') || '').toLowerCase();
            } catch {
                current = '';
            }
            document.execCommand('formatBlock', false, current === tag && tag !== 'p' ? 'p' : tag);
        }
        writeBack(box);
        refreshState(box);
        return;
    }

    if (event.target.closest('[data-rte-link]')) {
        const selection = window.getSelection();
        box.savedRange = selection.rangeCount ? selection.getRangeAt(0).cloneRange() : null;
        const anchor = selection.anchorNode?.parentElement?.closest('a');
        linkBar.hidden = !linkBar.hidden;
        const input = linkBar.querySelector('[data-rte-url]');
        input.value = anchor ? anchor.getAttribute('href') : '';
        if (!linkBar.hidden) {
            input.focus();
        }
        return;
    }

    if (event.target.closest('[data-rte-link-apply]') || event.target.closest('[data-rte-link-remove]')) {
        const remove = Boolean(event.target.closest('[data-rte-link-remove]'));
        let url = linkBar.querySelector('[data-rte-url]').value.trim();
        area.focus();
        if (box.savedRange) {
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(box.savedRange);
        }
        if (remove) {
            document.execCommand('unlink', false, null);
        } else if (url !== '') {
            if (!/^(https?:\/\/|mailto:|tel:)/i.test(url)) {
                url = `https://${url}`;
            }
            document.execCommand('createLink', false, url);
        }
        linkBar.hidden = true;
        writeBack(box);
    }
});

/* ---------- Photos inside the text ---------- */
function selectedImage(box) {
    return box.querySelector('[data-rte-area] img.is-selected');
}

function showImageBar(box, image) {
    box.querySelectorAll('[data-rte-area] img.is-selected').forEach((img) => img !== image && img.classList.remove('is-selected'));
    const bar = box.querySelector('[data-rte-imgbar]');
    bar.hidden = !image;
    if (!image) {
        return;
    }
    image.classList.add('is-selected');
    bar.querySelectorAll('[data-rte-img-size]').forEach((b) => b.classList.toggle('is-active', image.classList.contains(b.dataset.rteImgSize)));
    bar.querySelectorAll('[data-rte-img-align]').forEach((b) => b.classList.toggle('is-active', image.classList.contains(b.dataset.rteImgAlign)));
}

function cleanHtml(area) {
    // The selection outline is editor-only.
    const copy = area.cloneNode(true);
    copy.querySelectorAll('img.is-selected').forEach((img) => img.classList.remove('is-selected'));
    return copy.innerHTML;
}

async function uploadImage(box, file) {
    const status = box.querySelector('[data-rte-uploading]');
    const area = box.querySelector('[data-rte-area]');
    const body = new FormData();
    body.append('image', file);
    status.hidden = false;
    status.classList.remove('is-error');
    status.innerHTML = '<span class="up-spin"></span> Uploading photo…';
    try {
        const response = await fetch(box.dataset.upload, {
            method: 'POST',
            body,
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', Accept: 'application/json' },
            credentials: 'same-origin',
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.url) {
            throw new Error(data.errors?.image?.[0] || data.message || 'Upload failed.');
        }
        area.focus();
        if (box.savedRange) {
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(box.savedRange);
        }
        document.execCommand('insertHTML', false, `<img src="${data.url}" alt="" class="rt-img rt-md rt-center">`);
        status.hidden = true;
        writeBack(box);
    } catch (error) {
        status.classList.add('is-error');
        status.textContent = error.message;
        setTimeout(() => {
            status.hidden = true;
        }, 5000);
    }
}

document.addEventListener('click', (event) => {
    const box = boxOf(event.target);
    if (!box) {
        return;
    }
    if (event.target.closest('[data-rte-image]')) {
        const selection = window.getSelection();
        box.savedRange = selection.rangeCount && box.querySelector('[data-rte-area]').contains(selection.anchorNode) ? selection.getRangeAt(0).cloneRange() : null;
        box.querySelector('[data-rte-file]').click();
        return;
    }
    if (event.target.matches('[data-rte-area] img')) {
        showImageBar(box, event.target);
        return;
    }
    const image = selectedImage(box);
    const sizeButton = event.target.closest('[data-rte-img-size]');
    const alignButton = event.target.closest('[data-rte-img-align]');
    if (image && (sizeButton || alignButton)) {
        const [group, value] = sizeButton ? [['rt-sm', 'rt-md', 'rt-lg', 'rt-full'], sizeButton.dataset.rteImgSize] : [['rt-left', 'rt-center', 'rt-right'], alignButton.dataset.rteImgAlign];
        image.classList.remove(...group);
        image.classList.add(value);
        showImageBar(box, image);
        writeBack(box);
        return;
    }
    if (image && event.target.closest('[data-rte-img-remove]')) {
        image.remove();
        showImageBar(box, null);
        writeBack(box);
        return;
    }
    if (event.target.closest('[data-rte-area]')) {
        showImageBar(box, null);
    }
});

document.addEventListener('change', (event) => {
    if (event.target.matches('[data-rte-file]') && event.target.files?.[0]) {
        uploadImage(boxOf(event.target), event.target.files[0]);
        event.target.value = '';
    }
});

document.addEventListener('input', (event) => {
    if (event.target.matches?.('[data-rte-area]')) {
        writeBack(boxOf(event.target));
    }
});

document.addEventListener('keydown', (event) => {
    if (event.target.matches?.('[data-rte-url]') && event.key === 'Enter') {
        event.preventDefault();
        boxOf(event.target).querySelector('[data-rte-link-apply]').click();
    }
});

// Pasted text arrives as plain text, so formatting copied from Word or websites never sneaks in.
document.addEventListener('paste', (event) => {
    if (!event.target.closest?.('[data-rte-area]')) {
        return;
    }
    event.preventDefault();
    const text = event.clipboardData?.getData('text/plain') || '';
    const html = text.split(/\n\s*\n/).map((p) => `<p>${escapeHtml(p.trim()).replace(/\n/g, '<br>')}</p>`).join('');
    document.execCommand('insertHTML', false, html);
});

document.addEventListener('selectionchange', () => {
    const box = boxOf(document.activeElement || document.body);
    if (box && document.activeElement?.matches('[data-rte-area]')) {
        refreshState(box);
    }
});

document.addEventListener('focusin', (event) => {
    if (event.target.matches?.('[data-rte-area]')) {
        // New lines become paragraphs rather than bare divs.
        document.execCommand('defaultParagraphSeparator', false, 'p');
    }
});
