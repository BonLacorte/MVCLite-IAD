<?php include __DIR__ . '/../partials/public/head.php'; ?>
<?php include __DIR__ . '/../partials/public/nav.php'; ?>
<?php
$e = static fn($value) => htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
$name = trim((string)($user['name'] ?? $user['email'] ?? 'User'));
$email = (string)($user['email'] ?? '');
$internalRole = strtoupper((string)($_SESSION['user']['roleid'] ?? $user['role'] ?? 'USER'));
$role = in_array($internalRole, ['ADMIN', 'SADMIN'], true) ? 'ADMIN' : 'USER';
$position = trim((string)($user['position'] ?? 'Position not set'));
$initials = '';
foreach (preg_split('/\s+/', $name) as $part) {
    if ($part !== '') {
        $initials .= strtoupper(substr($part, 0, 1));
    }
}
$initials = substr($initials ?: strtoupper(substr($email ?: 'U', 0, 1)), 0, 2);
$hasProfileImage = !empty($user['profile_image']);
$joined = !empty($user['createdTime']) ? date('F j, Y', strtotime($user['createdTime'])) : 'Not recorded';
$office = trim((string)($user['for_central_office'] ?: $user['for_region_center'] ?: 'No office assignment'));
?>

<style>
    .profile-shell {
        min-height: 80vh;
        padding: 4rem 0;
        background: linear-gradient(180deg, #f8fafc 0%, #fafaf5 100%);
    }
    .profile-layout {
        display: grid;
        grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
        gap: 2rem;
        align-items: start;
    }
    .profile-card-premium {
        background: rgba(255,255,255,0.92);
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 18px 55px rgba(15,23,42,0.08);
        overflow: hidden;
    }
    .profile-side {
        padding: 2rem;
        position: sticky;
        top: 6rem;
    }
    .profile-avatar-xl {
        width: 152px;
        height: 152px;
        border-radius: 999px;
        margin: 0 auto 1.5rem;
        background: #055741;
        color: #f1e39c;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.25rem;
        font-weight: 900;
        border: 5px solid #f1e39c;
        box-shadow: 0 20px 45px rgba(5,87,65,0.18);
        overflow: hidden;
    }
    .profile-avatar-xl img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .profile-name-title {
        margin: 0;
        color: #055741;
        font-family: 'Montserrat', sans-serif;
        font-size: 1.55rem;
        line-height: 1.15;
        text-align: center;
    }
    .profile-muted {
        color: #64748b;
        font-size: 0.92rem;
        line-height: 1.45;
    }
    .profile-actions {
        display: grid;
        gap: 0.75rem;
        margin: 1.75rem 0 2rem;
    }
    .profile-btn {
        min-height: 44px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-weight: 800;
        text-decoration: none;
        border: 1px solid transparent;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }
    .profile-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 28px rgba(15,23,42,0.12);
    }
    .profile-btn-primary { background: #055741; color: #fff; }
    .profile-btn-secondary { background: #fff; color: #055741; border-color: #cbd5e1; }
    .profile-meta-card {
        border-top: 1px solid #e2e8f0;
        padding-top: 1.5rem;
        display: grid;
        gap: 0.9rem;
    }
    .profile-meta-row {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        color: #334155;
        font-size: 0.9rem;
    }
    .profile-meta-row span:first-child {
        color: #94a3b8;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.05em;
    }
    .profile-main {
        padding: 0;
    }
    .profile-tabs {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem;
        background: rgba(255, 255, 255, 0.68);
        border-bottom: 1px solid #e2e8f0;
    }
    .profile-tab-btn {
        min-height: 48px;
        border: 0;
        background: transparent;
        color: #475569;
        font-weight: 800;
        cursor: pointer;
        border-radius: 999px;
        padding: 0.75rem 1.35rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
    }
    .profile-tab-btn.active {
        background: #055741;
        color: #fff;
        box-shadow: 0 10px 20px rgba(5, 87, 65, 0.2);
    }
    .profile-tab-btn:hover { transform: translateY(-1px); }
    .profile-tab-panel {
        display: none;
        padding: 2.5rem;
    }
    .profile-tab-panel.active { display: block; }
    .profile-section-title {
        margin: 0 0 1.5rem;
        color: #055741;
        font-family: 'Montserrat', sans-serif;
        font-size: 1.35rem;
    }
    .profile-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
    }
    .profile-info-box {
        min-height: 96px;
        border: 1px solid #e2e8f0;
        background: #fbfdfc;
        border-radius: 14px;
        padding: 1rem;
        transition: border-color 0.18s ease, transform 0.18s ease;
    }
    .profile-info-box:hover {
        border-color: rgba(5,87,65,0.35);
        transform: translateY(-1px);
    }
    .profile-info-box label {
        display: block;
        color: #94a3b8;
        font-size: 0.73rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.055em;
        margin-bottom: 0.45rem;
    }
    .profile-info-box p {
        margin: 0;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 700;
        word-break: break-word;
    }
    .profile-security-form {
        max-width: 560px;
        display: grid;
        gap: 1.1rem;
    }
    .profile-field label {
        display: block;
        color: #334155;
        font-weight: 800;
        font-size: 0.9rem;
        margin-bottom: 0.45rem;
    }
    .profile-field input {
        box-sizing: border-box;
        width: 100%;
        min-height: 46px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        background: #fff;
    }
    .profile-alert {
        margin-bottom: 1.5rem;
        padding: 1rem 1.1rem;
        border-radius: 12px;
        font-weight: 700;
    }
    .profile-alert.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .profile-alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .profile-confirm-modal {
        position: fixed;
        inset: 0;
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        background: rgba(15, 23, 42, 0.48);
        backdrop-filter: blur(4px);
    }
    .profile-confirm-modal.is-open { display: flex; }
    .profile-confirm-card {
        width: min(460px, 100%);
        background: #fff;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 24px 70px rgba(15,23,42,0.22);
        padding: 1.5rem;
    }
    .profile-confirm-card h3 {
        margin: 0 0 0.65rem;
        color: #055741;
        font-family: 'Montserrat', sans-serif;
        font-size: 1.2rem;
    }
    .profile-confirm-card p {
        margin: 0 0 1.25rem;
        color: #475569;
        line-height: 1.55;
    }
    .profile-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    @media (max-width: 900px) {
        .profile-layout { grid-template-columns: 1fr; }
        .profile-side { position: static; }
    }
    @media (max-width: 640px) {
        .profile-info-grid { grid-template-columns: 1fr; }
        .profile-tabs { align-items: stretch; }
        .profile-tab-btn { width: 100%; }
        .profile-tab-panel { padding: 1.5rem; }
    }
</style>

<main class="profile-shell">
    <div class="container" style="max-width: 1220px;">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="profile-alert <?= $e($_SESSION['flash_type'] ?? 'success') ?>">
                <?= $e($_SESSION['flash_message']) ?>
                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            </div>
        <?php endif; ?>

        <div class="profile-layout">
            <aside class="profile-card-premium profile-side">
                <div class="profile-avatar-xl" id="profileAvatarFallback">
                    <?php if ($hasProfileImage): ?>
                        <img src="/profile/image" alt="Profile photo" onerror="this.remove(); document.getElementById('profileAvatarText').style.display='block';">
                        <span id="profileAvatarText" style="display:none;"><?= $e($initials) ?></span>
                    <?php else: ?>
                        <span id="profileAvatarText"><?= $e($initials) ?></span>
                    <?php endif; ?>
                </div>
                <h1 class="profile-name-title"><?= $e($name) ?></h1>
                <p class="profile-muted" style="text-align:center; margin:0.5rem 0 0;"><?= $e($position) ?></p>
                <p class="profile-muted" style="text-align:center; margin:0.2rem 0 0;"><?= $e($office) ?></p>

                <div class="profile-actions">
                    <a href="/profile/edit" class="profile-btn profile-btn-primary">Edit Profile</a>
                </div>

                <div class="profile-meta-card">
                    <div class="profile-meta-row"><span>Role</span><strong><?= $e($role) ?></strong></div>
                    <div class="profile-meta-row"><span>Joined</span><strong><?= $e($joined) ?></strong></div>
                    <div class="profile-meta-row"><span>Email</span><strong style="word-break:break-all;"><?= $e($email) ?></strong></div>
                </div>
            </aside>

            <section class="profile-card-premium profile-main">
                <div class="profile-tabs" role="tablist">
                    <button class="profile-tab-btn active" type="button" data-profile-tab="personal">Personal Information</button>
                    <button class="profile-tab-btn" type="button" data-profile-tab="work">Work Information</button>
                    <button class="profile-tab-btn" type="button" data-profile-tab="security">Account Security</button>
                </div>

                <div id="profile-tab-personal" class="profile-tab-panel active">
                    <h2 class="profile-section-title">Personal Details</h2>
                    <div class="profile-info-grid">
                        <div class="profile-info-box"><label>Full Name</label><p><?= $e($name ?: 'N/A') ?></p></div>
                        <div class="profile-info-box"><label>Email Address</label><p><?= $e($email ?: 'N/A') ?></p></div>
                        <div class="profile-info-box"><label>Cellphone No.</label><p><?= $e($user['cellphone_number'] ?: 'N/A') ?></p></div>
                        <div class="profile-info-box"><label>Facebook Account</label><p><?= $e($user['facebook_account'] ?: 'N/A') ?></p></div>
                    </div>
                </div>

                <div id="profile-tab-work" class="profile-tab-panel">
                    <h2 class="profile-section-title">Work Details</h2>
                    <div class="profile-info-grid">
                        <div class="profile-info-box"><label>Position</label><p><?= $e($user['position'] ?: 'N/A') ?></p></div>
                        <div class="profile-info-box"><label>Dept / Office / Unit</label><p><?= $e($user['department_office_unit'] ?: 'N/A') ?></p></div>
                        <div class="profile-info-box"><label>Central Office</label><p><?= $e($user['for_central_office'] ?: 'N/A') ?></p></div>
                        <div class="profile-info-box"><label>Region Center</label><p><?= $e($user['for_region_center'] ?: 'N/A') ?></p></div>
                    </div>
                </div>

                <div id="profile-tab-security" class="profile-tab-panel">
                    <h2 class="profile-section-title">Account Security</h2>
                    <p class="profile-muted" style="margin-top:-0.75rem; margin-bottom:1.5rem;">Manage your password and protect your account.</p>
                    <form action="/profile/change-password" method="POST" class="profile-security-form" id="passwordChangeForm">
                        <div class="profile-field">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        <div class="profile-field">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                            <div id="password-strength-bar" style="height:4px;width:0;background:#ddd;margin-top:0.55rem;border-radius:999px;transition:all 0.25s;"></div>
                            <p id="password-strength-text" class="profile-muted" style="font-size:0.78rem;margin:0.35rem 0 0;">Enter a strong password.</p>
                        </div>
                        <div class="profile-field">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="profile-btn profile-btn-primary" style="width:max-content; padding:0 1.25rem;">Update Password</button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</main>

<div class="profile-confirm-modal" id="passwordConfirmModal" aria-hidden="true">
    <div class="profile-confirm-card" role="dialog" aria-modal="true" aria-labelledby="passwordConfirmTitle">
        <h3 id="passwordConfirmTitle">Confirm password change</h3>
        <p>Changing your account password will save the new password and automatically log you out. You will need to sign in again with the new password.</p>
        <div class="profile-confirm-actions">
            <button type="button" class="profile-btn profile-btn-secondary" id="cancelPasswordChange">No, cancel</button>
            <button type="button" class="profile-btn profile-btn-primary" id="confirmPasswordChange">Yes, save and logout</button>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('[data-profile-tab]').forEach((button) => {
    button.addEventListener('click', () => {
        const tab = button.dataset.profileTab;
        document.querySelectorAll('[data-profile-tab]').forEach((btn) => btn.classList.toggle('active', btn === button));
        document.querySelectorAll('.profile-tab-panel').forEach((panel) => panel.classList.toggle('active', panel.id === `profile-tab-${tab}`));
    });
});

const passwordInput = document.getElementById('new_password');
if (passwordInput) {
    passwordInput.addEventListener('input', () => {
        const password = passwordInput.value;
        const bar = document.getElementById('password-strength-bar');
        const text = document.getElementById('password-strength-text');
        let strength = 0;
        if (password.length >= 8) strength += 25;
        if (/[A-Z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 25;
        if (/[^A-Za-z0-9]/.test(password)) strength += 25;
        bar.style.width = strength + '%';
        if (strength <= 25) {
            bar.style.background = '#ef4444';
            text.textContent = 'Weak password';
        } else if (strength <= 75) {
            bar.style.background = '#eab308';
            text.textContent = 'Moderate password';
        } else {
            bar.style.background = '#16a34a';
            text.textContent = 'Strong password';
        }
    });
}

const passwordChangeForm = document.getElementById('passwordChangeForm');
const passwordConfirmModal = document.getElementById('passwordConfirmModal');
const cancelPasswordChange = document.getElementById('cancelPasswordChange');
const confirmPasswordChange = document.getElementById('confirmPasswordChange');
let allowPasswordSubmit = false;

if (passwordChangeForm && passwordConfirmModal) {
    passwordChangeForm.addEventListener('submit', (event) => {
        if (allowPasswordSubmit) return;
        event.preventDefault();
        passwordConfirmModal.classList.add('is-open');
        passwordConfirmModal.setAttribute('aria-hidden', 'false');
    });
}

if (cancelPasswordChange) {
    cancelPasswordChange.addEventListener('click', () => {
        passwordConfirmModal.classList.remove('is-open');
        passwordConfirmModal.setAttribute('aria-hidden', 'true');
    });
}

if (confirmPasswordChange) {
    confirmPasswordChange.addEventListener('click', () => {
        allowPasswordSubmit = true;
        if (typeof passwordChangeForm.requestSubmit === 'function') {
            passwordChangeForm.requestSubmit();
        } else {
            passwordChangeForm.submit();
        }
    });
}
</script>

<?php include __DIR__ . '/../partials/public/footer.php'; ?>
