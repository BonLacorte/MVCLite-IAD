<?php include '../app/views/partials/public/head.php'; ?>
<?php include '../app/views/partials/public/nav.php'; ?>

<main>
    <section class="hero hero-premium">
        <div class="container animate-on-load">
            <h1 class="hero-title">Discover Our Events</h1>
            <p class="hero-description">
                Stay updated with the latest happenings, training sessions, and open conferences at the Internal Audit Department.
            </p>
        </div>
    </section>

    <section class="events-feed-section">
        <div class="container">
            <div class="events-grid" id="events-container">
                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $event): ?>
                        <article class="premium-event-card animate-on-scroll">
                            
                            <!-- Instagram-like Image Slider -->
                            <div class="event-slider-container">
                                <div class="event-slider" data-event-id="<?= $event['id'] ?>">
                                    <?php if (!empty($event['photos'])): ?>
                                        <?php foreach ($event['photos'] as $index => $photo): ?>
                                            <div class="event-slide">
                                                <img src="<?= htmlspecialchars($photo) ?>" alt="Event Photo <?= $index + 1 ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="event-slide">
                                            <div class="event-slide-placeholder">No photos available</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Slider Navigation Arrows -->
                                <?php if (!empty($event['photos']) && count($event['photos']) > 1): ?>
                                    <button class="slider-nav slider-nav-prev" aria-label="Previous image">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                                    </button>
                                    <button class="slider-nav slider-nav-next" aria-label="Next image">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                                    </button>
                                    
                                    <!-- Slider Dots -->
                                    <div class="slider-dots">
                                        <?php foreach ($event['photos'] as $index => $photo): ?>
                                            <span class="slider-dot <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>"></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Event Details -->
                            <div class="event-card-content">
                                <div class="event-status-badge <?= strtolower($event['status']) ?>">
                                    <?= htmlspecialchars($event['status']) ?>
                                </div>
                                <h3 class="event-title"><?= htmlspecialchars($event['title']) ?></h3>
                                <div class="event-meta">
                                    <div class="meta-item">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                        <span><?= date('M d, Y', strtotime($event['date'])) ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                        <span><?= htmlspecialchars($event['venue']) ?></span>
                                    </div>
                                </div>
                                <a href="/events/<?= $event['id'] ?>" class="btn-event-view">View Details</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No events found.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Infinite Scroll Loader -->
            <div id="infinite-scroll-loader" class="loader-container">
                <div class="spinner"></div>
                <p>Loading more events</p>
            </div>
            <div id="no-more-events" class="end-of-content" style="display: none;">
                <p>You've reached the end!</p>
            </div>
        </div>
    </section>
</main>

<script>
// --- Instagram-like Slider Logic ---
function initSliders() {
    const sliderContainers = document.querySelectorAll('.event-slider-container');
    
    sliderContainers.forEach(container => {
        if (container.dataset.initialized) return;
        container.dataset.initialized = 'true';
        
        const slider = container.querySelector('.event-slider');
        const prevBtn = container.querySelector('.slider-nav-prev');
        const nextBtn = container.querySelector('.slider-nav-next');
        const dots = container.querySelectorAll('.slider-dot');
        
        if (!slider || !prevBtn || !nextBtn) return;
        
        let currentIndex = 0;
        const slideCount = slider.children.length;
        
        function updateSlider() {
            const width = slider.clientWidth;
            slider.scrollTo({
                left: currentIndex * width,
                behavior: 'smooth'
            });
            
            dots.forEach((dot, idx) => {
                dot.classList.toggle('active', idx === currentIndex);
            });
        }
        
        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                updateSlider();
            }
        });
        
        nextBtn.addEventListener('click', () => {
            if (currentIndex < slideCount - 1) {
                currentIndex++;
                updateSlider();
            }
        });
        
        // Handle scroll snapping updates
        slider.addEventListener('scroll', () => {
            const width = slider.clientWidth;
            const scrollPos = slider.scrollLeft;
            const newIndex = Math.round(scrollPos / width);
            if (newIndex !== currentIndex) {
                currentIndex = newIndex;
                dots.forEach((dot, idx) => {
                    dot.classList.toggle('active', idx === currentIndex);
                });
            }
        }, { passive: true });
    });
}

// Initialize sliders on load
document.addEventListener('DOMContentLoaded', initSliders);

// --- Infinite Scroll Logic ---
document.addEventListener('DOMContentLoaded', () => {
    let page = 2; // We already loaded page 1 via PHP
    let isLoading = false;
    let hasMore = true;
    
    const loader = document.getElementById('infinite-scroll-loader');
    const noMoreEvents = document.getElementById('no-more-events');
    const container = document.getElementById('events-container');
    
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !isLoading && hasMore) {
            loadMoreEvents();
        }
    }, { rootMargin: '200px' });
    
    if (loader) {
        observer.observe(loader);
    }
    
    async function loadMoreEvents() {
        if (!hasMore || isLoading) return;
        
        isLoading = true;
        try {
            const response = await fetch(`/events/api/loadMoreEvents?page=${page}`);
            const data = await response.json();
            
            if (data.success && data.data.length > 0) {
                data.data.forEach(event => {
                    const article = createEventCard(event);
                    container.appendChild(article);
                });
                
                initSliders(); // Re-initialize sliders for new elements
                
                // Trigger animation for new elements
                setTimeout(() => {
                    document.querySelectorAll('.animate-on-scroll:not(.is-visible)').forEach(el => {
                        el.classList.add('is-visible');
                    });
                }, 50);
                
                page++;
                hasMore = data.hasMore;
            } else {
                hasMore = false;
            }
        } catch (error) {
            console.error('Error loading events:', error);
            hasMore = false;
        } finally {
            isLoading = false;
            if (!hasMore) {
                if(loader) loader.style.display = 'none';
                if(noMoreEvents) noMoreEvents.style.display = 'block';
            }
        }
    }
    
    function createEventCard(event) {
        const article = document.createElement('article');
        article.className = 'premium-event-card animate-on-scroll';

        const sliderContainer = document.createElement('div');
        sliderContainer.className = 'event-slider-container';

        const slider = document.createElement('div');
        slider.className = 'event-slider';
        slider.dataset.eventId = String(event.id || '');

        const photos = Array.isArray(event.photos) ? event.photos : [];
        if (photos.length > 0) {
            photos.forEach((photo, index) => {
                const slide = document.createElement('div');
                slide.className = 'event-slide';
                const image = document.createElement('img');
                image.src = photo;
                image.alt = `Event Photo ${index + 1}`;
                slide.appendChild(image);
                slider.appendChild(slide);
            });
        } else {
            const slide = document.createElement('div');
            slide.className = 'event-slide';
            const placeholder = document.createElement('div');
            placeholder.className = 'event-slide-placeholder';
            placeholder.textContent = 'No photos available';
            slide.appendChild(placeholder);
            slider.appendChild(slide);
        }
        sliderContainer.appendChild(slider);

        if (photos.length > 1) {
            const prev = document.createElement('button');
            prev.className = 'slider-nav slider-nav-prev';
            prev.setAttribute('aria-label', 'Previous image');
            prev.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>';

            const next = document.createElement('button');
            next.className = 'slider-nav slider-nav-next';
            next.setAttribute('aria-label', 'Next image');
            next.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>';

            const dots = document.createElement('div');
            dots.className = 'slider-dots';
            photos.forEach((_, index) => {
                const dot = document.createElement('span');
                dot.className = `slider-dot ${index === 0 ? 'active' : ''}`;
                dots.appendChild(dot);
            });
            sliderContainer.append(prev, next, dots);
        }

        const content = document.createElement('div');
        content.className = 'event-card-content';

        const status = document.createElement('div');
        status.className = `event-status-badge ${String(event.status || '').toLowerCase()}`;
        status.textContent = event.status || '';

        const title = document.createElement('h3');
        title.className = 'event-title';
        title.textContent = event.title || '';

        const meta = document.createElement('div');
        meta.className = 'event-meta';
        const dateItem = document.createElement('div');
        dateItem.className = 'meta-item';
        dateItem.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
        const dateText = document.createElement('span');
        dateText.textContent = event.date_formatted || '';
        dateItem.appendChild(dateText);

        const venueItem = document.createElement('div');
        venueItem.className = 'meta-item';
        venueItem.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>';
        const venueText = document.createElement('span');
        venueText.textContent = event.venue || '';
        venueItem.appendChild(venueText);
        meta.append(dateItem, venueItem);

        const link = document.createElement('a');
        link.href = `/events/${Number(event.id) || 0}`;
        link.className = 'btn-event-view';
        link.textContent = 'View Details';

        content.append(status, title, meta, link);
        article.append(sliderContainer, content);
        return article;
    }
    
    // Intersection Observer for scroll animations
    const animationObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                animationObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        animationObserver.observe(el);
    });
});
</script>

<?php include '../app/views/partials/public/footer.php'; ?>
