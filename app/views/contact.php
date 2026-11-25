
<?php include '../app/views/partials/public/head.php'; ?>
<?php include '../app/views/partials/public/nav.php'; ?>

<main class="flex flex-col min-h-screen" style="background-color: var(--secondary);">

    <section class="hero" style="padding: 4rem 0;">
        <div class="container">
            <h1 class="hero-title">Contact Us</h1>
            <p class="hero-description">
                Have questions or need assistance? Our team is here to help you. Reach out to the Internal Audit Department through any of the channels below.
            </p>
        </div>
    </section>  

    <section style="padding: 4rem 0;">
        <div class="container">

            <div class="premium-card">
                <div style="margin-bottom: 3rem;">
                    <span class="section-tag">Get in Touch</span>
                    <!-- <h2 class="event-title">Contact Information</h2> -->
                    <h3 class="event-title" style="font-weight: 800; font-size: 1.5rem; margin-bottom: 0.75rem;">Contact Information</h3>
                    <div style="width: 60px; height: 2px; background: #cda85f; margin-bottom: 2.5rem;"></div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 4rem;">
                    <!-- Address -->
                    <div style="background: #fbfbfb; border-radius: 8px; padding: 2rem 1.5rem; border: 1px solid #f0f0f0; border-top: 4px solid #055741; display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; color: #055741;">
                            <span class="material-symbols-rounded">location_on</span>
                            <span style="font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Office Location</span>
                        </div>
                        <p style="color: #4b5563; font-size: 0.95rem; line-height: 1.6; margin: 0;">
                            6th Floor, PCA Building, Elliptical Road, Diliman, Quezon City
                        </p>
                    </div>

                    <!-- Phone -->
                    <div style="background: #fbfbfb; border-radius: 8px; padding: 2rem 1.5rem; border: 1px solid #f0f0f0; border-top: 4px solid #055741; display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; color: #055741;">
                            <span class="material-symbols-rounded">call</span>
                            <span style="font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Phone Extensions</span>
                        </div>
                        <p style="color: #4b5563; font-size: 0.95rem; line-height: 1.6; margin: 0;">
                            local 333 / 513
                        </p>
                    </div>

                    <!-- Email -->
                    <div style="background: #fbfbfb; border-radius: 8px; padding: 2rem 1.5rem; border: 1px solid #f0f0f0; border-top: 4px solid #055741; display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; color: #055741;">
                            <span class="material-symbols-rounded">mail</span>
                            <span style="font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">Email Address</span>
                        </div>
                        <p style="color: #4b5563; font-size: 0.95rem; line-height: 1.6; margin: 0;">
                            pca_iad@pca.gov.ph
                        </p>
                    </div>
                </div>

                <div style="border-top: 1px solid #eaeaea; padding-top: 3rem;">
                    <span class="section-tag">Locate Us</span>
                    <h3 class="event-title" style="font-weight: 800; font-size: 1.5rem; margin-bottom: 0.75rem;">Office Map</h3>
                    <div style="width: 60px; height: 2px; background: #cda85f; margin-bottom: 2.5rem;"></div>
                    <div style="padding: 1rem; background: #fbfbfb; border: 1px solid #eee; border-radius: 8px;">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d405.7376302403406!2d121.05126085830159!3d14.653181914998994!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b70fbf863ef9%3A0xb4208ffc3b148193!2sPhilippine%20Coconut%20Authority!5e0!3m2!1sen!2sph!4v1737609535746!5m2!1sen!2sph" 
                            width="100%" 
                            height="450" 
                            style="border:0; border-radius: 4px;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../app/views/partials/public/footer.php'; ?>
