<?php include '../app/views/partials/public/head.php'; ?>
<?php include '../app/views/partials/public/nav.php'; ?>

<?php
$calendarIcon = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>';
$clockIcon = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>';
$pinIcon = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>';
?>

<main>
    <section class="hero hero-full-height">
        <div class="container">
            <h1 class="hero-title animate-on-load">Accountability & Transparency<br>Across All Operations</h1>
            <p class="hero-description animate-on-load delay-100">
                The Internal Audit Department plays a crucial role in maintaining the integrity and efficiency of our organization. We provide independent and objective assurance designed to add value and improve operations.
            </p>
            <div class="flex justify-center gap-4 animate-on-load delay-200" style="justify-content: center;">
                <a href="/about" class="btn-hero">Learn More About Us</a>
                <a href="/contact" class="btn-hero">Contact Us</a>
            </div>
        </div>
    </section>

    <section class="gallery-section gallery-full-height">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <div>
                    <span class="section-tag">Visual Archives</span>
                    <h2 class="section-title">Photo Gallery of Recent Events</h2>
                </div>
                <a href="/events" class="btn-primary">Explore All Events</a>
            </div>

            <?php if (!empty($recent_events)): ?>
                <?php foreach ($recent_events as $event): ?>
                    <div class="event-card animate-on-scroll">
                        <div class="event-info">
                            <div>
                                <h3 class="event-title"><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <div class="event-details">
                                    <span><?= $calendarIcon ?> Date: <?= date('M d, Y', strtotime($event['date'])) ?></span>
                                    <span><?= $clockIcon ?> Time: <?= date('g:i a', strtotime($event['time'])) ?></span>
                                    <span><?= $pinIcon ?> Venue: <?= htmlspecialchars($event['venue'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </div>
                            <a href="/events/<?= (int)$event['id'] ?>" class="btn-primary">View Details</a>
                        </div>

                        <div class="image-stack" id="stack-<?= (int)$event['id'] ?>">
                            <img src="/assets/images/field1.png" class="pos-1" alt="Event Image 1">
                            <img src="/assets/images/field2.jpg" class="pos-2" alt="Event Image 2">
                            <img src="/assets/images/field3.jpg" class="pos-3" alt="Event Image 3">
                            <img src="/assets/images/field4.png" class="pos-4" alt="Event Image 4">
                            <img src="/assets/images/field1.webp" class="pos-5" alt="Event Image 5">
                        </div>
                    </div>
                <?php endforeach; ?>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const stacks = document.querySelectorAll('.image-stack');
                    stacks.forEach(stack => {
                        const imgs = stack.querySelectorAll('img');
                        let currentOrder = [1, 2, 3, 4, 5];

                        setInterval(() => {
                            currentOrder.push(currentOrder.shift());

                            imgs.forEach((img, idx) => {
                                img.className = '';
                                img.classList.add('pos-' + currentOrder[idx]);
                            });
                        }, 3000);
                    });

                    const observerOptions = {
                        root: null,
                        rootMargin: '0px',
                        threshold: 0.15
                    };

                    const observer = new IntersectionObserver((entries, observer) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('is-visible');
                                observer.unobserve(entry.target);
                            }
                        });
                    }, observerOptions);

                    document.querySelectorAll('.animate-on-scroll').forEach((el) => {
                        observer.observe(el);
                    });
                });
                </script>
            <?php else: ?>
                <p class="text-center" style="text-align: center; color: #666; padding: 3rem; background: white; border-radius: 1rem;">No recent events found.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="upcoming-section upcoming-split-height">
        <div class="container">
            <div class="animate-on-scroll" style="margin-bottom: 3rem;">
                <span class="section-tag">Calendars</span>
                <h2 class="section-title">Upcoming Events</h2>
            </div>
            <div class="upcoming-grid">
                <?php if (!empty($upcoming_events)): ?>
                    <?php foreach ($upcoming_events as $event): ?>
                        <div class="upcoming-card">
                            <h4 style="color: #055741; margin-top: 0;"><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></h4>
                            <div class="home-event-meta">
                                <span class="home-event-meta-line"><?= $calendarIcon ?> Date: <?= date('M d, Y', strtotime($event['date'])) ?></span>
                                <span class="home-event-meta-line"><?= $clockIcon ?> Time: <?= date('g:i a', strtotime($event['time'])) ?></span>
                                <span class="home-event-meta-line"><?= $pinIcon ?> Venue: <?= htmlspecialchars($event['venue'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No upcoming events scheduled.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php include '../app/views/partials/public/footer.php'; ?>
