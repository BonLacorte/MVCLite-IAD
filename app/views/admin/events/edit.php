<?php include __DIR__ . '/../../partials/public/head.php'; ?>
<?php include __DIR__ . '/../../partials/public/nav.php'; ?>
<link rel="stylesheet" href="/assets/css/admin.css">

<div id="flash-msg" class="flash-msg" style="display:none;"></div>

<main style="min-height: 80vh; padding: 3rem 0; background-color: var(--admin-bg);">
    <div class="container" style="max-width: 800px;">

        <a href="/admin/events" class="page-back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Events
        </a>

        <div class="page-card">
            <h1 class="page-title">Edit Event: <?= htmlspecialchars($event['title'] ?? '') ?></h1>

            <form id="editEventForm" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $event['id'] ?>">

                <div style="display: flex; flex-direction: column; gap: 1.25rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label>Title <span style="color: var(--admin-danger);">*</span></label>
                        <input type="text" name="title" id="ftitle" required value="<?= htmlspecialchars($event['title'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Venue <span style="color: var(--admin-danger);">*</span></label>
                        <input type="text" name="venue" id="fvenue" value="<?= htmlspecialchars($event['venue'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Date <span style="color: var(--admin-danger);">*</span></label>
                        <input type="date" name="date" id="fdate" required value="<?= htmlspecialchars($event['date'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Time <span style="color: var(--admin-danger);">*</span></label>
                        <input type="time" name="time" id="ftime" value="<?= htmlspecialchars($event['time'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Status <span style="color: var(--admin-danger);">*</span></label>
                        <select name="status" id="fstatus">
                            <option value="active"   <?= ($event['status'] ?? '') === 'active'   ? 'selected' : '' ?>>Active</option>
                            <option value="finished" <?= ($event['status'] ?? '') === 'finished' ? 'selected' : '' ?>>Finished</option>
                            <option value="inactive" <?= ($event['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <?php
                        $eventPhotoImages = $event['images'] ?? [];
                        include __DIR__ . '/_photo_manager.php';
                    ?>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="fdescription" rows="4" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; background: #f8fafc; font-size: 0.95rem; resize: vertical;"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-error-box" id="formErrors"></div>

                <div class="form-actions">
                    <a href="/admin/events" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                        Update Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
document.getElementById('editEventForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    const errEl = document.getElementById('formErrors');
    errEl.style.display = 'none';
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const fd = new FormData(this);
    if (window.EventPhotoManager) {
        window.EventPhotoManager.appendFiles(fd);
    }

    fetch('/admin/events/update', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showFlash(data.message || 'Event updated successfully!', 'success');
                setTimeout(() => window.location.href = '/admin/events', 1200);
            } else {
                btn.disabled = false;
                btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg> Update Event';
                errEl.textContent = data.message || 'An error occurred.';
                errEl.style.display = 'block';
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg> Update Event';
        });
});

function showFlash(msg, type) {
    const el = document.getElementById('flash-msg');
    el.textContent = msg;
    el.className = 'flash-msg flash-' + type;
    el.style.display = 'block';
    setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity 0.5s'; setTimeout(() => el.style.display = 'none', 500); }, 3500);
}
</script>

<?php include __DIR__ . '/../../partials/public/footer.php'; ?>
