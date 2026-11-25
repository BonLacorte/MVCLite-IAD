<?php include __DIR__ . '/../../partials/public/head.php'; ?>
<?php include __DIR__ . '/../../partials/public/nav.php'; ?>
<link rel="stylesheet" href="/assets/css/admin.css">

<?php
$eventTitle = htmlspecialchars($event['title'] ?? '', ENT_QUOTES, 'UTF-8');
$eventVenue = htmlspecialchars($event['venue'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
$eventStatus = (string)($event['status'] ?? 'inactive');
$statusClass = match ($eventStatus) {
    'active' => 'status-active',
    'finished' => 'status-finished',
    default => 'status-inactive',
};
$statusLabel = ucfirst($eventStatus);
$eventDate = !empty($event['date']) ? date('F j, Y', strtotime((string)$event['date'])) : 'N/A';
$eventTime = 'N/A';
if (!empty($event['time'])) {
    $eventTime = date('g:i A', strtotime((string)$event['time']));
}
?>

<style>
    .status-badge { padding: 0.35rem 0.85rem; border-radius: 999px; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 0.4rem; width: max-content; }
    .status-active { background: #dcfce7; color: #166534; }
    .status-finished { background: #e0f2fe; color: #0369a1; }
    .status-inactive { background: #f3f4f6; color: #64748b; }
    .status-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .admin-event-shell { max-width: 1180px; margin: 0 auto; }
    .admin-event-hero { display: flex; justify-content: space-between; gap: 1.5rem; align-items: flex-start; margin-bottom: 1.5rem; }
    .admin-event-title { margin: 0 0 0.65rem; color: var(--admin-primary); font-size: clamp(1.8rem, 4vw, 2.4rem); line-height: 1.08; }
    .admin-event-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.85rem; padding: 1rem; background: #f8fafc; border: 1px solid var(--admin-border); border-radius: 16px; margin-bottom: 1.5rem; }
    .admin-event-summary-item { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 0.9rem; min-width: 0; }
    .admin-event-summary-item label { display: block; color: var(--admin-primary); text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.72rem; font-weight: 900; margin-bottom: 0.35rem; }
    .admin-event-summary-item span { color: #0f172a; font-weight: 800; overflow-wrap: anywhere; }
    .admin-event-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 1rem; }
    .admin-event-photo-button { display: block; width: 100%; aspect-ratio: 4 / 3; padding: 0; border: 1px solid #dbe7e2; border-radius: 16px; overflow: hidden; background: #f8fafc; cursor: zoom-in; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08); transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease; }
    .admin-event-photo-button:hover { transform: translateY(-2px); border-color: var(--admin-primary); box-shadow: 0 18px 34px rgba(15, 23, 42, 0.14); }
    .admin-event-photo-button img { width: 100%; height: 100%; display: block; object-fit: cover; }
    .admin-empty-gallery { text-align: center; padding: 3rem 1rem; color: #94a3b8; background: #f8fafc; border-radius: 16px; border: 1px dashed var(--admin-border); }
    @media (max-width: 860px) {
        .admin-event-hero { flex-direction: column; }
        .admin-event-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 560px) {
        .admin-event-summary { grid-template-columns: 1fr; }
    }
</style>

<main style="min-height: 80vh; padding: 3rem 1rem; background-color: var(--admin-bg);">
    <div class="admin-event-shell">
        <a href="/admin/events" class="page-back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Events
        </a>

        <div class="page-card">
            <div class="admin-event-hero">
                <div>
                    <h1 class="admin-event-title"><?= $eventTitle ?></h1>
                    <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <a href="/admin/events/edit?id=<?= (int)$event['id'] ?>" class="btn-submit" style="text-decoration: none; padding: 0.65rem 1.1rem; font-size: 0.85rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                    Edit Event
                </a>
            </div>

            <div class="admin-event-summary">
                <div class="admin-event-summary-item">
                    <label>Date</label>
                    <span><?= htmlspecialchars($eventDate, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="admin-event-summary-item">
                    <label>Time</label>
                    <span><?= htmlspecialchars($eventTime, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="admin-event-summary-item">
                    <label>Venue</label>
                    <span><?= $eventVenue ?></span>
                </div>
                <div class="admin-event-summary-item">
                    <label>Status</label>
                    <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>

            <div class="section-divider" style="margin-bottom: 1rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--admin-primary)" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <h3>Photo Gallery (<?= count($event['images'] ?? []) ?> photos)</h3>
            </div>

            <?php if (!empty($event['images'])): ?>
                <div class="admin-event-gallery">
                    <?php foreach ($event['images'] as $img): ?>
                        <?php
                            $src = htmlspecialchars($img['src'] ?? '', ENT_QUOTES, 'UTF-8');
                            $alt = htmlspecialchars($img['original_name'] ?? 'Event Photo', ENT_QUOTES, 'UTF-8');
                        ?>
                        <button type="button" class="admin-event-photo-button" data-lightbox-src="<?= $src ?>" data-lightbox-alt="<?= $alt ?>">
                            <img src="<?= $src ?>" alt="<?= $alt ?>" loading="lazy">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="admin-empty-gallery">
                    <p style="margin: 0; font-size: 0.95rem;">No photos uploaded for this event.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<div class="event-view-lightbox" id="eventViewLightbox" aria-hidden="true">
    <div class="event-view-lightbox-backdrop" data-lightbox-close></div>
    <div class="event-view-lightbox-panel" role="dialog" aria-modal="true" aria-label="Event photo preview">
        <div class="event-view-lightbox-toolbar">
            <button type="button" data-lightbox-zoom-out>-</button>
            <button type="button" data-lightbox-zoom-reset>Reset</button>
            <button type="button" data-lightbox-zoom-in>+</button>
            <button type="button" data-lightbox-close aria-label="Close photo preview">&times;</button>
        </div>
        <img id="eventViewLightboxImage" src="" alt="Event photo preview">
    </div>
</div>

<script>
(function() {
    const lightbox = document.getElementById('eventViewLightbox');
    const image = document.getElementById('eventViewLightboxImage');
    let zoom = 1;

    function setZoom(nextZoom) {
        zoom = Math.min(4, Math.max(0.4, nextZoom));
        image.style.transform = `scale(${zoom})`;
    }

    function close() {
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        image.src = '';
    }

    document.querySelectorAll('[data-lightbox-src]').forEach(button => {
        button.addEventListener('click', () => {
            image.src = button.dataset.lightboxSrc || '';
            image.alt = button.dataset.lightboxAlt || 'Event photo preview';
            setZoom(1);
            lightbox.classList.add('is-open');
            lightbox.setAttribute('aria-hidden', 'false');
        });
    });

    lightbox.querySelectorAll('[data-lightbox-close]').forEach(button => button.addEventListener('click', close));
    lightbox.addEventListener('click', event => {
        if (event.target === lightbox) close();
    });
    lightbox.querySelector('[data-lightbox-zoom-out]').addEventListener('click', () => setZoom(zoom - 0.25));
    lightbox.querySelector('[data-lightbox-zoom-reset]').addEventListener('click', () => setZoom(1));
    lightbox.querySelector('[data-lightbox-zoom-in]').addEventListener('click', () => setZoom(zoom + 0.25));
    lightbox.addEventListener('wheel', event => {
        if (!lightbox.classList.contains('is-open')) return;
        event.preventDefault();
        setZoom(zoom + (event.deltaY < 0 ? 0.15 : -0.15));
    }, { passive: false });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') close();
    });
})();
</script>

<?php include __DIR__ . '/../../partials/public/footer.php'; ?>
