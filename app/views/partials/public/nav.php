<?php
$navEscape = static fn($value) => htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
$navEmail = $_SESSION['user']['email'] ?? '';
$navRole = $_SESSION['user']['roleid'] ?? '';
$navDisplayRole = in_array(strtoupper((string)$navRole), ['ADMIN', 'SADMIN'], true) ? 'ADMIN' : 'USER';
$navInitial = strtoupper(substr($navEmail ?: 'U', 0, 1));
$navHasProfileImage = !empty($_SESSION['user']['profile_image']);
?><!-- Main Navigation -->
<nav class="main-nav">
    <div class="container flex justify-between items-center" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="flex items-center gap-2" style="display: flex; align-items: center; gap: 0.5rem;">
            <img src="/assets/images/IAD_logo.png" alt="IAD" style="height: 32px;">
            <span style="font-weight: 800; font-size: 1.2rem; letter-spacing: -0.05em;">IAD</span>
        </div>
        
        <div class="flex gap-8" style="display: flex; gap: 2rem; align-items: center;">
            <a href="/" class="nav-link <?= ($title ?? '') === 'Home' ? 'active' : '' ?>">Home</a>
            <a href="/about" class="nav-link <?= ($title ?? '') === 'About' ? 'active' : '' ?>">About</a>
            <a href="/contact" class="nav-link <?= ($title ?? '') === 'Contact' ? 'active' : '' ?>">Contact</a>
            <a href="/events" class="nav-link <?= ($title ?? '') === 'Events' ? 'active' : '' ?>">Events</a>
            
            <?php if (isset($_SESSION['user'])): ?>
                <?php if (in_array($_SESSION['user']['roleid'], ['ENCODER', 'VALIDATOR', 'REGION'])): ?>
                    <a href="/questionnaires" class="nav-link <?= ($title ?? '') === 'Questionnaires' ? 'active' : '' ?>">Questionnaires</a>
                <?php endif; ?>

                <?php if (in_array($_SESSION['user']['roleid'], ['ADMIN', 'SADMIN'])): ?>
                    <div class="dropdown nav-dropdown" style="position: relative;">
                        <a href="#" class="nav-link <?= str_starts_with($title ?? '', 'Admin') ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 0.3rem; padding: 0.75rem 0;">
                            Admin <span style="font-size: 0.65rem; opacity: 0.7;">v</span>
                        </a>
                        <div class="dropdown-content nav-admin-dropdown" style="left: 0; min-width: 200px; padding: 0.5rem 0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid rgba(0,0,0,0.08); background: white; top: 100%; margin-top: 0;">
                            <a href="/admin/questionnaires" style="padding: 0.75rem 1.25rem; display: flex; align-items: center; gap: 0.75rem; color: #333; text-decoration: none; font-size: 0.9rem; transition: background 0.15s;">
                                <span style="width: 6px; height: 6px; background: #055744; border-radius: 50%; display: inline-block;"></span>
                                BAICS Questionnaires
                            </a>
                            <a href="/admin/events" style="padding: 0.75rem 1.25rem; display: flex; align-items: center; gap: 0.75rem; color: #333; text-decoration: none; font-size: 0.9rem; transition: background 0.15s;">
                                <span style="width: 6px; height: 6px; background: #055744; border-radius: 50%; display: inline-block;"></span>
                                Events
                            </a>
                            <a href="/admin/users" style="padding: 0.75rem 1.25rem; display: flex; align-items: center; gap: 0.75rem; color: #333; text-decoration: none; font-size: 0.9rem; transition: background 0.15s;">
                                <span style="width: 6px; height: 6px; background: #055744; border-radius: 50%; display: inline-block;"></span>
                                Users
                            </a>
                            <a href="/admin/admins" style="padding: 0.75rem 1.25rem; display: flex; align-items: center; gap: 0.75rem; color: #333; text-decoration: none; font-size: 0.9rem; transition: background 0.15s;">
                                <span style="width: 6px; height: 6px; background: #055744; border-radius: 50%; display: inline-block;"></span>
                                Admins
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>


        <div class="flex items-center gap-4" style="display: flex; align-items: center; gap: 1rem;">
            <?php if (isset($_SESSION['user'])): ?>
                <div class="dropdown" style="position: relative;">
                                        <div class="user-avatar" style="background-color: #F1E39C; color: #055744; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: 700; cursor: pointer; border: 2px solid #055744; margin: 0.5rem 0; overflow:hidden; position:relative;">
                        <?php if ($navHasProfileImage): ?>
                            <img src="/profile/image" alt="Profile" style="width:100%;height:100%;object-fit:cover;display:block;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                            <span style="display:none;"><?= $navEscape($navInitial) ?></span>
                        <?php else: ?>
                            <span><?= $navEscape($navInitial) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="dropdown-content" style="right: 0; min-width: 200px; padding: 0.5rem 0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid rgba(0,0,0,0.05); background: white; top: 100%;">
                        <div style="padding: 1rem; border-bottom: 1px solid #eee;">
                            <p style="font-weight: 700; color: #055744; margin: 0; word-break: break-all;"><?= $navEscape($navEmail) ?></p>
                            <p style="font-size: 0.75rem; color: #666; margin: 0;"><?= $navEscape($navDisplayRole) ?></p>
                        </div>
                        <a href="/profile" style="padding: 0.75rem 1rem; display: block; color: #333; text-decoration: none;">Profile</a>
                        <?php if (in_array($_SESSION['user']['roleid'], ['ADMIN', 'SADMIN'])): ?>
                            <a href="/admin/dashboard" style="padding: 0.75rem 1rem; display: block; color: #333; text-decoration: none;">Admin Dashboard</a>
                        <?php endif; ?>
                        <a href="/logout" style="padding: 0.75rem 1rem; display: block; color: #EF4444; text-decoration: none; border-top: 1px solid #eee;">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/login" class="login-btn-premium">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Top Bar -->
<div class="header-top">
    <div class="container flex justify-between items-center" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="flex items-center gap-4" style="display: flex; align-items: center; gap: 1rem;">
            <img src="/assets/images/bp_logo_no_bg.png" alt="Bagong Pilipinas" style="height: 40px;">
            <img src="/assets/images/pca_logo_no_bg.png" alt="PCA" style="height: 40px;">
            <img src="/assets/images/IAD_logo.png" alt="IAD" style="height: 40px;">
            <div style="border-left: 1px solid #ddd; padding-left: 1rem; margin-left: 0.5rem;">
                <p style="font-size: 0.7rem; color: #666; margin: 0;">Department of Agriculture</p>
                <p style="font-size: 0.8rem; font-weight: 700; color: #055741; margin: 0;">PHILIPPINE COCONUT AUTHORITY</p>
                <p style="font-size: 0.7rem; color: #666; margin: 0;">Elliptical Road, Diliman, Quezon City</p>
            </div>
        </div>
        <div class="text-right" style="text-align: right;">
            <p style="font-size: 0.7rem; color: #666; margin: 0;">Philippine Standard Time</p>
            <p id="pst-time" style="font-size: 0.8rem; font-weight: 600; margin: 0;">Thursday, May 14, 2026 at 11:15:46 PM</p>
            <div class="flex gap-2 justify-end" style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 0.25rem;">
                <a href="#"><img src="/assets/images/facebook.png" alt="FB" style="height: 20px;"></a>
                <a href="#"><img src="/assets/images/yt.png" alt="YT" style="height: 20px;"></a>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/pst-clock.js"></script>
