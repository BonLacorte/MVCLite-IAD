<?php include '../app/views/partials/public/head.php'; ?>
<?php include '../app/views/partials/public/nav.php'; ?>
<?php
$loginEscape = static fn($value) => htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
$loginError = $login_error ?? '';
$loginNotice = $login_notice ?? '';
$submittedUsername = $submitted_username ?? '';
$submittedPassword = $submitted_password ?? '';
?>

<main class="flex flex-col min-h-screen" style="background-color: var(--secondary);">
    <!-- Hero Section Aligned with other pages -->
    <!-- <section class="hero" style="padding: 4rem 0 1rem 0;">
        <div class="container text-center">
            <h1 class="hero-title">Audit Portal Access</h1>
            <p class="hero-description mx-auto" style="max-width: 600px;">
                Secure access for authorized Philippine Coconut Authority personnel to the Internal Audit Department management system.
            </p>
        </div>
    </section> -->

    <section style="padding: 2rem 0 6rem 0;">
        <div class="container flex flex-col items-center">
            
            <!-- Login Card Aligned with Site Card Style -->
            <div class="login-card">
                <div class="text-center">
                    <div style="width: 72px; height: 72px; background: rgba(5, 87, 65, 0.05); border-radius: 1.25rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--primary);">
                        <span class="material-symbols-rounded" style="font-size: 36px;">verified_user</span>
                    </div>
                    <h2 class="event-title" style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; text-align: center; ">PCA IAD Portal</h2>
                    <p style="color: #666; font-size: 0.95rem;">Institutional Access & Verification</p>
                </div>

                <form action="/login" method="POST">
                    <?= Csrf::input(); ?>

                    <?php if ($loginNotice !== ''): ?>
                        <div style="margin-top: 1.5rem; padding: 1rem; background: #dcfce7; color: #166534; border-radius: 0.75rem; font-size: 0.9rem; text-align: center; border: 1px solid #bbf7d0; font-weight: 700;">
                            <?= $loginEscape($loginNotice) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($loginError !== ''): ?>
                        <div style="margin-top: 1.5rem; padding: 1rem; background: #fee2e2; color: #b91c1c; border-radius: 0.75rem; font-size: 0.9rem; text-align: center; border: 1px solid #fecaca; font-weight: 700;">
                            <?= $loginEscape($loginError) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="login-field" style="margin-top: 3rem;">
                        <label for="username">Government ID / Username</label>
                        <input type="text" id="username" name="username" class="login-input" required placeholder="Enter your credentials" value="<?= $loginEscape($submittedUsername) ?>">
                    </div>

                    <div class="login-field">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label for="password" style="margin-bottom: 0;">Secret Password</label>
                            <a href="#" style="font-size: 0.75rem; font-weight: 700; color: var(--primary); text-decoration: none;">Forgot password?</a>
                        </div>
                        <input type="password" id="password" name="password" class="login-input" required placeholder="Enter password" value="<?= $loginEscape($submittedPassword) ?>">
                    </div>

                    <!-- <div style="margin-bottom: 2rem;">
                        <label style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.9rem; color: #555; cursor: pointer;">
                            <input type="checkbox" name="remember" style="width: 18px; height: 18px; border-radius: 4px; accent-color: var(--primary);">
                            Keep me authenticated
                        </label>
                    </div> -->

                    <button type="submit" class="login-btn">
                        Sign In to Portal
                    </button>

                    <?php if ($loginError === '' && isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 0) : ?>
                        <div style="margin-top: 1.5rem; padding: 1rem; background: #fee2e2; color: #b91c1c; border-radius: 0.5rem; font-size: 0.85rem; text-align: center; border: 1px solid #fecaca;">
                            Invalid credentials. Please try again.
                        </div>
                    <?php endif; ?>

                    <div class="login-footer-text">
                        <p style="margin-bottom: 1.5rem;">
                            Unauthorized access is strictly prohibited and monitored under the<br>
                            <strong>Data Privacy Act of 2012</strong>.
                        </p>
                        <!-- <div style="display: flex; justify-content: center; gap: 2rem; font-size: 0.85rem; font-weight: 700;">
                            <a href="#" style="color: #666; text-decoration: none; transition: color 0.2s;">Internal Helpdesk</a>
                            <a href="#" style="color: #666; text-decoration: none; transition: color 0.2s;">System Status</a>
                        </div> -->
                    </div>
                </form>
            </div>

            <!-- Status Cards Aligned with Site Value Cards Style -->
            <!-- <div class="login-status-grid">
                <div class="value-card" style="padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 1rem; border-top-width: 3px;">
                    <div style="color: var(--primary);">
                        <span class="material-symbols-rounded">shield</span>
                    </div>
                    <div>
                        <h5 style="font-size: 0.65rem; color: #999; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">Security Level</h5>
                        <p style="font-size: 0.85rem; font-weight: 700; margin: 0.1rem 0 0; color: #333;">Institutional</p>
                    </div>
                </div>
                <div class="value-card" style="padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 1rem; border-top-width: 3px;">
                    <div style="color: var(--primary);">
                        <span class="material-symbols-rounded">dns</span>
                    </div>
                    <div>
                        <h5 style="font-size: 0.65rem; color: #999; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">Active Server</h5>
                        <p style="font-size: 0.85rem; font-weight: 700; margin: 0.1rem 0 0; color: #333;">NCR-Main-04</p>
                    </div>
                </div>
            </div> -->

        </div>
    </section>
</main>

<?php include '../app/views/partials/public/footer.php'; ?>
