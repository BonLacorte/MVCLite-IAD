<?php include '../app/views/partials/public/head.php'; ?>
<?php include '../app/views/partials/public/nav.php'; ?>

<main class="event-detail-page" style="background-color: #fafaf5; min-height: 100vh;">
    <section class="hero hero-premium-small">
        <div class="container">
            <a href="/events" class="back-link event-detail-back-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back to Events
            </a>
            <div class="hero-content-wrapper">
                <div class="hero-text-content">
                    <h1 class="hero-title"><?= htmlspecialchars($event['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
                    <div class="event-meta-hero">
                        <div class="meta-item">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            <span><?= !empty($event['date']) ? date('l, F d, Y', strtotime((string)$event['date'])) : 'Date unavailable' ?></span>
                        </div>
                        <div class="meta-item">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <span><?= !empty($event['time']) ? date('g:i a', strtotime((string)$event['time'])) : 'Time unavailable' ?></span>
                        </div>
                        <div class="meta-item">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span><?= htmlspecialchars($event['venue'] ?? 'Venue unavailable', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="event-content-section" style="padding: 4rem 0;">
        <div class="container">
            <div class="event-detail-grid">
                <div class="main-card premium-card event-photos-card">
                    <h2 class="section-heading">Event Photos</h2>
                    <div class="photo-grid" id="photo-grid-container">
                        <?php if (!empty($event['photos'])): ?>
                            <?php foreach ($event['photos'] as $index => $photo): ?>
                                <button type="button" class="photo-item animate-on-scroll event-photo-open" data-photo-index="<?= (int)$index ?>" data-photo-src="<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') ?>" data-photo-alt="Event Photo <?= $index + 1 ?>">
                                    <img src="<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') ?>" alt="Event Photo <?= $index + 1 ?>" loading="lazy">
                                </button>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No photos available for this event yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($event['photos'])): ?>
                        <div id="photos-loader" class="loader-container">
                            <div class="spinner"></div>
                            <p>Loading more photos...</p>
                        </div>
                        <div id="no-more-photos" class="end-of-content" style="display: none;">
                            <p>All photos loaded.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <aside class="sidebar-cards event-sidebar-compact">
                    <div class="sidebar-card premium-card">
                        <h3 class="sidebar-heading">Recent Events</h3>
                        <div class="sidebar-event-list">
                            <?php if (!empty($recentEvents)): ?>
                                <?php foreach ($recentEvents as $recent): ?>
                                    <a href="/events/<?= (int)$recent['id'] ?>" class="sidebar-event-item">
                                        <div class="sidebar-event-thumb">
                                            <img src="/assets/images/field1.png" alt="">
                                        </div>
                                        <div class="sidebar-event-info">
                                            <h4 class="sidebar-event-title"><?= htmlspecialchars($recent['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h4>
                                            <span class="sidebar-event-date"><?= !empty($recent['date']) ? date('M d, Y', strtotime((string)$recent['date'])) : 'No date' ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="empty-text">No recent events found.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="sidebar-card premium-card" style="margin-top: 1.25rem;">
                        <h3 class="sidebar-heading">Upcoming Events</h3>
                        <div class="sidebar-event-list">
                            <?php if (!empty($upcomingEvents)): ?>
                                <?php foreach ($upcomingEvents as $upcoming): ?>
                                    <a href="/events/<?= (int)$upcoming['id'] ?>" class="sidebar-event-item">
                                        <div class="sidebar-event-thumb">
                                            <img src="/assets/images/field2.jpg" alt="">
                                        </div>
                                        <div class="sidebar-event-info">
                                            <h4 class="sidebar-event-title"><?= htmlspecialchars($upcoming['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h4>
                                            <span class="sidebar-event-date"><?= !empty($upcoming['date']) ? date('M d, Y', strtotime((string)$upcoming['date'])) : 'No date' ?></span>
                                            <span class="sidebar-event-venue"><?= htmlspecialchars($upcoming['venue'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="empty-text">No upcoming events found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</main>

<div class="public-photo-lightbox" id="publicPhotoLightbox" aria-hidden="true">
    <div class="public-photo-lightbox-backdrop" data-lightbox-close></div>
    <button type="button" class="public-photo-lightbox-nav public-photo-lightbox-prev" data-lightbox-prev aria-label="Previous photo">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
    </button>
    <button type="button" class="public-photo-lightbox-nav public-photo-lightbox-next" data-lightbox-next aria-label="Next photo">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
    </button>
    <div class="public-photo-lightbox-panel" role="dialog" aria-modal="true" aria-label="Event photo preview">
        <div class="public-photo-lightbox-toolbar">
            <button type="button" data-lightbox-zoom-out>-</button>
            <button type="button" data-lightbox-zoom-reset>Reset</button>
            <button type="button" data-lightbox-zoom-in>+</button>
            <button type="button" data-lightbox-close aria-label="Close photo preview">&times;</button>
        </div>
        <img id="publicPhotoLightboxImage" src="" alt="Event photo preview">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const loader = document.getElementById('photos-loader');
    const noMorePhotos = document.getElementById('no-more-photos');
    const container = document.getElementById('photo-grid-container');
    const lightbox = document.getElementById('publicPhotoLightbox');
    const lightboxImage = document.getElementById('publicPhotoLightboxImage');
    const gallery = [];
    let zoom = 1;
    let activeIndex = 0;
    let panX = 0;
    let panY = 0;
    let dragStartX = 0;
    let dragStartY = 0;
    let startPanX = 0;
    let startPanY = 0;
    let isDragging = false;

    function applyImageTransform() {
        lightboxImage.style.transform = `translate(${panX}px, ${panY}px) scale(${zoom})`;
        lightboxImage.classList.toggle('is-zoomed', zoom > 1);
    }

    function resetView() {
        zoom = 1;
        panX = 0;
        panY = 0;
        applyImageTransform();
    }

    function setZoom(nextZoom) {
        zoom = Math.min(4, Math.max(0.4, nextZoom));
        if (zoom <= 1) {
            panX = 0;
            panY = 0;
        }
        applyImageTransform();
    }

    function showPhoto(index) {
        if (!gallery.length) return;
        activeIndex = (index + gallery.length) % gallery.length;
        const photo = gallery[activeIndex];
        lightboxImage.src = photo.src;
        lightboxImage.alt = photo.alt || 'Event photo preview';
        resetView();
    }

    function openLightbox(index) {
        showPhoto(index);
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
    }

    function closeLightbox() {
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        lightboxImage.src = '';
        resetView();
    }

    function wirePhotoButton(button) {
        const existingIndex = Number(button.dataset.photoIndex);
        let index = Number.isFinite(existingIndex) ? existingIndex : gallery.length;
        if (!gallery[index]) {
            gallery[index] = {
                src: button.dataset.photoSrc || '',
                alt: button.dataset.photoAlt || 'Event photo preview'
            };
            button.dataset.photoIndex = String(index);
        }
        button.addEventListener('click', () => openLightbox(Number(button.dataset.photoIndex)));
    }

    document.querySelectorAll('.event-photo-open').forEach(wirePhotoButton);
    lightbox.querySelectorAll('[data-lightbox-close]').forEach(button => button.addEventListener('click', closeLightbox));
    lightbox.addEventListener('click', event => {
        if (event.target === lightbox) closeLightbox();
    });
    lightbox.querySelector('[data-lightbox-prev]').addEventListener('click', () => showPhoto(activeIndex - 1));
    lightbox.querySelector('[data-lightbox-next]').addEventListener('click', () => showPhoto(activeIndex + 1));
    lightbox.querySelector('[data-lightbox-zoom-out]').addEventListener('click', () => setZoom(zoom - 0.25));
    lightbox.querySelector('[data-lightbox-zoom-reset]').addEventListener('click', () => setZoom(1));
    lightbox.querySelector('[data-lightbox-zoom-in]').addEventListener('click', () => setZoom(zoom + 0.25));
    lightbox.addEventListener('wheel', event => {
        if (!lightbox.classList.contains('is-open')) return;
        event.preventDefault();
        setZoom(zoom + (event.deltaY < 0 ? 0.15 : -0.15));
    }, { passive: false });
    document.addEventListener('keydown', event => {
        if (!lightbox.classList.contains('is-open')) return;
        if (event.key === 'Escape') closeLightbox();
        if (event.key === 'ArrowLeft') showPhoto(activeIndex - 1);
        if (event.key === 'ArrowRight') showPhoto(activeIndex + 1);
    });

    lightboxImage.addEventListener('pointerdown', event => {
        if (zoom <= 1) return;
        isDragging = true;
        dragStartX = event.clientX;
        dragStartY = event.clientY;
        startPanX = panX;
        startPanY = panY;
        lightboxImage.classList.add('is-dragging');
        lightboxImage.setPointerCapture(event.pointerId);
    });
    lightboxImage.addEventListener('pointermove', event => {
        if (!isDragging) return;
        panX = startPanX + event.clientX - dragStartX;
        panY = startPanY + event.clientY - dragStartY;
        applyImageTransform();
    });
    lightboxImage.addEventListener('pointerup', event => {
        isDragging = false;
        lightboxImage.classList.remove('is-dragging');
        lightboxImage.releasePointerCapture(event.pointerId);
    });
    lightboxImage.addEventListener('pointercancel', event => {
        isDragging = false;
        lightboxImage.classList.remove('is-dragging');
        try {
            lightboxImage.releasePointerCapture(event.pointerId);
        } catch (error) {}
    });

    if (!loader || !container) return;

    let page = 2;
    let isLoading = false;
    let hasMore = true;

    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !isLoading && hasMore) {
            loadMorePhotos();
        }
    }, { rootMargin: '100px' });

    observer.observe(loader);

    async function loadMorePhotos() {
        if (!hasMore || isLoading) return;

        isLoading = true;
        try {
            const response = await fetch(`/events/api/loadMorePhotos?event_id=<?= (int)$event['id'] ?>&page=${page}`);
            const data = await response.json();

            if (data.success && data.data.length > 0) {
                data.data.forEach((photo, index) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'photo-item animate-on-scroll event-photo-open is-visible';
                    button.dataset.photoIndex = String(gallery.length);
                    button.dataset.photoSrc = photo;
                    button.dataset.photoAlt = `Event Photo ${page}-${index + 1}`;

                    const image = document.createElement('img');
                    image.src = photo;
                    image.alt = button.dataset.photoAlt;
                    image.loading = 'lazy';
                    button.appendChild(image);
                    wirePhotoButton(button);
                    container.appendChild(button);
                });

                page++;
                hasMore = data.hasMore;
            } else {
                hasMore = false;
            }
        } catch (error) {
            console.error('Error loading photos:', error);
            hasMore = false;
        } finally {
            isLoading = false;
            if (!hasMore) {
                loader.style.display = 'none';
                if (noMorePhotos) noMorePhotos.style.display = 'block';
            }
        }
    }

    const animationObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                animationObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-on-scroll').forEach(el => animationObserver.observe(el));
});
</script>

<?php include '../app/views/partials/public/footer.php'; ?>
