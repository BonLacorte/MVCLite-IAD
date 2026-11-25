<?php
$eventPhotoImages = $eventPhotoImages ?? [];
$eventPhotoHasExisting = !empty($eventPhotoImages);
?>

<div class="form-group event-photo-manager">
    <div class="event-photo-manager-head">
        <div>
            <label>Event Photos</label>
            <span class="field-hint">JPG, PNG, or WebP only. Drag photos to set their display order.</span>
        </div>
        <button type="button" class="btn-photo-delete-all" id="deleteAllEventPhotos" <?= $eventPhotoHasExisting ? '' : 'style="display:none;"' ?>>
            Delete all
        </button>
    </div>

    <?php if ($eventPhotoHasExisting): ?>
        <div style="margin-bottom: 0.75rem;">
            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.025em;">Image Upload Options</label>
            <div class="radio-group">
                <label><input type="radio" name="image_upload_mode" value="add" checked> Add new photos to existing gallery</label>
                <label><input type="radio" name="image_upload_mode" value="replace"> Replace all existing photos with selected photos</label>
            </div>
        </div>
    <?php else: ?>
        <input type="hidden" name="image_upload_mode" value="add">
    <?php endif; ?>

    <input type="hidden" name="deleted_image_ids" id="deletedImageIds" value="">
    <input type="hidden" name="event_image_order" id="eventImageOrder" value="">

    <div class="event-photo-grid" id="eventPhotoGrid" aria-live="polite">
        <?php foreach ($eventPhotoImages as $image): ?>
            <div class="event-photo-item" draggable="true" data-existing-id="<?= (int)$image['id'] ?>">
                <img src="<?= htmlspecialchars($image['src'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($image['original_name'] ?? 'Event photo', ENT_QUOTES, 'UTF-8') ?>" data-lightbox-photo>
                <button type="button" class="event-photo-delete" title="Delete this photo" aria-label="Delete this photo">&times;</button>
                <span class="event-photo-drag-hint">Drag</span>
            </div>
        <?php endforeach; ?>
    </div>

    <label class="event-photo-dropzone" for="fimages">
        <span class="material-symbols-rounded">add_photo_alternate</span>
        <strong>Select event photos</strong>
        <small>New photos appear at the end, then can be dragged into position.</small>
    </label>
    <input type="file" name="images[]" id="fimages" multiple accept="image/jpeg,image/png,image/webp" class="event-photo-input">
</div>

<div class="event-photo-lightbox" id="eventPhotoLightbox" aria-hidden="true">
    <div class="event-photo-lightbox-backdrop" data-lightbox-close></div>
    <div class="event-photo-lightbox-panel" role="dialog" aria-modal="true" aria-label="Event photo preview">
        <div class="event-photo-lightbox-toolbar">
            <button type="button" data-lightbox-zoom-out>-</button>
            <button type="button" data-lightbox-zoom-reset>Reset</button>
            <button type="button" data-lightbox-zoom-in>+</button>
            <button type="button" data-lightbox-close aria-label="Close photo preview">&times;</button>
        </div>
        <img src="" alt="Event photo preview" id="eventPhotoLightboxImage">
    </div>
</div>

<script>
window.EventPhotoManager = (function() {
    const grid = document.getElementById('eventPhotoGrid');
    const input = document.getElementById('fimages');
    const deletedInput = document.getElementById('deletedImageIds');
    const orderInput = document.getElementById('eventImageOrder');
    const deleteAllButton = document.getElementById('deleteAllEventPhotos');
    const lightbox = document.getElementById('eventPhotoLightbox');
    const lightboxImage = document.getElementById('eventPhotoLightboxImage');
    const deletedIds = new Set();
    const selectedFiles = [];
    let draggedItem = null;
    let zoom = 1;

    function setZoom(nextZoom) {
        zoom = Math.min(4, Math.max(0.4, nextZoom));
        if (lightboxImage) {
            lightboxImage.style.transform = `scale(${zoom})`;
        }
    }

    function openLightbox(src, alt) {
        if (!lightbox || !lightboxImage || !src) return;
        lightboxImage.src = src;
        lightboxImage.alt = alt || 'Event photo preview';
        setZoom(1);
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
    }

    function closeLightbox() {
        if (!lightbox) return;
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        if (lightboxImage) lightboxImage.src = '';
    }

    function updateHiddenFields() {
        const orderTokens = Array.from(grid.querySelectorAll('.event-photo-item'))
            .map(item => item.dataset.existingId ? `existing:${item.dataset.existingId}` : 'new')
            .filter(token => !token.startsWith('existing:') || !deletedIds.has(token.replace('existing:', '')));
        deletedInput.value = Array.from(deletedIds).join(',');
        orderInput.value = orderTokens.join(',');
        if (deleteAllButton) {
            deleteAllButton.style.display = grid.querySelector('[data-existing-id]') ? '' : 'none';
        }
    }

    function wireItem(item) {
        item.addEventListener('dragstart', () => {
            draggedItem = item;
            item.classList.add('is-dragging');
        });
        item.addEventListener('dragend', () => {
            item.classList.remove('is-dragging');
            draggedItem = null;
            updateHiddenFields();
        });
        item.addEventListener('dragover', event => {
            event.preventDefault();
            if (!draggedItem || draggedItem === item) return;
            const rect = item.getBoundingClientRect();
            const shouldInsertAfter = event.clientY > rect.top + rect.height / 2;
            grid.insertBefore(draggedItem, shouldInsertAfter ? item.nextSibling : item);
        });

        const previewImage = item.querySelector('[data-lightbox-photo]');
        if (previewImage) {
            previewImage.addEventListener('click', event => {
                event.stopPropagation();
                openLightbox(previewImage.src, previewImage.alt);
            });
        }

        const deleteButton = item.querySelector('.event-photo-delete');
        if (deleteButton) {
            deleteButton.addEventListener('click', event => {
                event.stopPropagation();
                const existingId = item.dataset.existingId;
                if (existingId) {
                    deletedIds.add(existingId);
                }
                item.remove();
                updateHiddenFields();
            });
        }
    }

    function addPreview(file) {
        const index = selectedFiles.push(file) - 1;
        const item = document.createElement('div');
        item.className = 'event-photo-item event-photo-item-new';
        item.draggable = true;
        item.dataset.newIndex = String(index);

        const image = document.createElement('img');
        image.alt = file.name;
        image.src = URL.createObjectURL(file);
        image.dataset.lightboxPhoto = 'true';

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'event-photo-delete';
        deleteButton.title = 'Remove this new photo';
        deleteButton.setAttribute('aria-label', 'Remove this new photo');
        deleteButton.textContent = 'x';

        const hint = document.createElement('span');
        hint.className = 'event-photo-drag-hint';
        hint.textContent = 'New';

        item.append(image, deleteButton, hint);
        grid.appendChild(item);
        wireItem(item);
        updateHiddenFields();
    }

    function appendFiles(formData) {
        updateHiddenFields();
        formData.delete('images[]');
        Array.from(grid.querySelectorAll('[data-new-index]')).forEach(item => {
            const file = selectedFiles[Number(item.dataset.newIndex)];
            if (file) {
                formData.append('images[]', file, file.name);
            }
        });
    }

    if (grid && input) {
        grid.querySelectorAll('.event-photo-item').forEach(wireItem);
        input.addEventListener('change', () => {
            Array.from(input.files || []).forEach(addPreview);
            input.value = '';
        });
    }

    if (deleteAllButton) {
        deleteAllButton.addEventListener('click', () => {
            grid.querySelectorAll('[data-existing-id]').forEach(item => {
                deletedIds.add(item.dataset.existingId);
                item.remove();
            });
            updateHiddenFields();
        });
    }

    if (lightbox) {
        lightbox.querySelectorAll('[data-lightbox-close]').forEach(button => {
            button.addEventListener('click', closeLightbox);
        });
        lightbox.addEventListener('click', event => {
            if (event.target === lightbox) closeLightbox();
        });
        lightbox.querySelector('[data-lightbox-zoom-out]')?.addEventListener('click', () => setZoom(zoom - 0.25));
        lightbox.querySelector('[data-lightbox-zoom-reset]')?.addEventListener('click', () => setZoom(1));
        lightbox.querySelector('[data-lightbox-zoom-in]')?.addEventListener('click', () => setZoom(zoom + 0.25));
        lightbox.addEventListener('wheel', event => {
            if (!lightbox.classList.contains('is-open')) return;
            event.preventDefault();
            setZoom(zoom + (event.deltaY < 0 ? 0.15 : -0.15));
        }, { passive: false });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') closeLightbox();
        });
    }

    updateHiddenFields();

    return { appendFiles, updateHiddenFields };
})();
</script>
