<?php include __DIR__ . '/../partials/public/head.php'; ?>
<?php include __DIR__ . '/../partials/public/nav.php'; ?>
<?php
$e = static fn($value) => htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
$name = trim((string)($user['name'] ?? $user['email'] ?? 'User'));
$email = (string)($user['email'] ?? '');
$initials = '';
foreach (preg_split('/\s+/', $name) as $part) {
    if ($part !== '') {
        $initials .= strtoupper(substr($part, 0, 1));
    }
}
$initials = substr($initials ?: strtoupper(substr($email ?: 'U', 0, 1)), 0, 2);
$hasProfileImage = !empty($user['profile_image']);
?>

<style>
    .profile-edit-shell {
        min-height: 80vh;
        padding: 4rem 0;
        background: linear-gradient(180deg, #f8fafc 0%, #fafaf5 100%);
    }
    .profile-edit-card {
        background: rgba(255,255,255,0.94);
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 18px 55px rgba(15,23,42,0.08);
        overflow: hidden;
    }
    .profile-edit-header {
        padding: 2rem 2.25rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        background: #fff;
    }
    .profile-edit-header h1 {
        margin: 0;
        color: #055741;
        font-family: 'Montserrat', sans-serif;
        font-size: 2rem;
        line-height: 1.1;
    }
    .profile-edit-header p {
        margin: 0.35rem 0 0;
        color: #64748b;
    }
    .profile-edit-form {
        padding: 2.25rem;
        display: grid;
        gap: 2rem;
    }
    .profile-edit-grid {
        display: grid;
        grid-template-columns: minmax(260px, 330px) minmax(0, 1fr);
        gap: 2rem;
        align-items: start;
    }
    .profile-photo-panel {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #fbfdfc;
        padding: 1.5rem;
    }
    .profile-photo-preview {
        width: 148px;
        height: 148px;
        border-radius: 999px;
        margin: 0 auto 1.25rem;
        background: #055741;
        color: #f1e39c;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 900;
        border: 5px solid #f1e39c;
        box-shadow: 0 18px 42px rgba(5,87,65,0.15);
        overflow: hidden;
    }
    .profile-photo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .profile-upload-label {
        display: block;
        padding: 0.85rem 1rem;
        border: 1px dashed #9fb7af;
        border-radius: 12px;
        background: #fff;
        color: #055741;
        font-weight: 800;
        text-align: center;
        cursor: pointer;
        transition: background 0.18s ease, border-color 0.18s ease;
    }
    .profile-upload-label:hover {
        background: #f0fdf4;
        border-color: #055741;
    }
    .profile-edit-section {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        background: #fff;
        margin-bottom: 1.25rem;
    }
    .profile-edit-section h2 {
        margin: 0 0 1.25rem;
        color: #055741;
        font-size: 1.05rem;
        font-family: 'Montserrat', sans-serif;
    }
    .profile-field-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
    .profile-edit-field label {
        display: block;
        color: #334155;
        font-size: 0.86rem;
        font-weight: 800;
        margin-bottom: 0.45rem;
    }
    .profile-edit-field input {
        box-sizing: border-box;
        width: 100%;
        min-height: 46px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        background: #f8fafc;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }
    .profile-edit-field input:focus {
        outline: none;
        background: #fff;
        border-color: #055741;
        box-shadow: 0 0 0 4px rgba(5,87,65,0.1);
    }
    .profile-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.9rem;
        flex-wrap: wrap;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
    }
    .profile-btn {
        min-height: 44px;
        padding: 0 1.25rem;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-weight: 800;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }
    .profile-btn:hover { transform: translateY(-1px); box-shadow: 0 12px 28px rgba(15,23,42,0.12); }
    .profile-btn-primary { background: #055741; color: #fff; }
    .profile-btn-secondary { background: #fff; color: #055741; border-color: #cbd5e1; }
    .profile-help {
        color: #64748b;
        font-size: 0.82rem;
        line-height: 1.45;
        margin: 0.75rem 0 0;
    }
    .profile-alert {
        margin-bottom: 1.5rem;
        padding: 1rem 1.1rem;
        border-radius: 12px;
        font-weight: 700;
    }
    .profile-alert.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .profile-alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .profile-edit-field input[readonly] {
        background: #eef2f7;
        color: #64748b;
        cursor: not-allowed;
    }
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
        .profile-edit-grid { grid-template-columns: 1fr; }
        .profile-edit-header { align-items: flex-start; flex-direction: column; }
    }
    @media (max-width: 640px) {
        .profile-field-grid { grid-template-columns: 1fr; }
        .profile-edit-form, .profile-edit-header { padding: 1.5rem; }
    }
</style>

<main class="profile-edit-shell">
    <div class="container" style="max-width: 1160px;">
        <a href="/profile" style="display:inline-flex;align-items:center;gap:0.45rem;color:#055741;text-decoration:none;font-weight:800;margin-bottom:1.25rem;">
            <span aria-hidden="true">&lt;</span> Back to Profile
        </a>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="profile-alert <?= $e($_SESSION['flash_type'] ?? 'success') ?>">
                <?= $e($_SESSION['flash_message']) ?>
                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            </div>
        <?php endif; ?>

        <section class="profile-edit-card">
            <header class="profile-edit-header">
                <div>
                    <h1>Edit Profile</h1>
                    <p>Update your contact, work assignment, and private profile photo.</p>
                </div>
            </header>

            <form action="/profile/update" method="POST" enctype="multipart/form-data" class="profile-edit-form" id="profileEditForm" data-original-email="<?= $e($user['email'] ?? '') ?>">
                <div class="profile-edit-grid">
                    <aside class="profile-photo-panel">
                        <div class="profile-photo-preview" id="photoPreview">
                            <?php if ($hasProfileImage): ?>
                                <img src="/profile/image" alt="Current profile photo" onerror="this.remove(); document.getElementById('photoFallbackText').style.display='block';">
                                <span id="photoFallbackText" style="display:none;"><?= $e($initials) ?></span>
                            <?php else: ?>
                                <span id="photoFallbackText"><?= $e($initials) ?></span>
                            <?php endif; ?>
                        </div>
                        <label class="profile-upload-label" for="profile_image">Choose Profile Photo</label>
                        <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/webp" style="display:none;">
                        <p class="profile-help">JPG, PNG, or WebP only. Maximum 5MB. Stored privately under development `mnt/profile-pictures`.</p>
                        <?php if ($hasProfileImage): ?>
                            <label style="display:flex;align-items:center;gap:0.5rem;margin-top:1rem;color:#475569;font-weight:700;font-size:0.9rem;">
                                <input type="checkbox" name="remove_profile_image" value="1" style="width:16px;height:16px;accent-color:#055741;">
                                Remove current photo
                            </label>
                        <?php endif; ?>
                    </aside>

                    <div>
                        <section class="profile-edit-section">
                            <h2>Personal Information</h2>
                            <div class="profile-field-grid">
                                <div class="profile-edit-field">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name" value="<?= $e($user['name'] ?? '') ?>" required>
                                </div>
                                <div class="profile-edit-field">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" value="<?= $e($user['email'] ?? '') ?>" required>
                                </div>
                                <div class="profile-edit-field">
                                    <label for="cellphone_number">Cellphone Number</label>
                                    <input type="text" id="cellphone_number" name="cellphone_number" value="<?= $e($user['cellphone_number'] ?? '') ?>">
                                </div>
                                <div class="profile-edit-field">
                                    <label for="facebook_account">Facebook Account</label>
                                    <input type="text" id="facebook_account" name="facebook_account" value="<?= $e($user['facebook_account'] ?? '') ?>">
                                </div>
                            </div>
                        </section>

                        <section class="profile-edit-section">
                            <h2>Work Information</h2>
                            <div class="profile-field-grid">
                                <div class="profile-edit-field">
                                    <label for="position">Position</label>
                                    <input type="text" id="position" name="position" value="<?= $e($user['position'] ?? '') ?>">
                                </div>
                                <div class="profile-edit-field">
                                    <label for="department_office_unit">Dept / Office / Unit</label>
                                    <input type="text" id="department_office_unit" name="department_office_unit" value="<?= $e($user['department_office_unit'] ?? '') ?>" readonly>
                                </div>
                                <div class="profile-edit-field">
                                    <label for="for_central_office">Central Office</label>
                                    <input type="text" id="for_central_office" name="for_central_office" value="<?= $e($user['for_central_office'] ?? '') ?>" readonly>
                                </div>
                                <div class="profile-edit-field">
                                    <label for="for_region_center">Region Center</label>
                                    <input type="text" id="for_region_center" name="for_region_center" value="<?= $e($user['for_region_center'] ?? '') ?>" readonly>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="profile-form-actions">
                    <a href="/profile" class="profile-btn profile-btn-secondary">Cancel</a>
                    <button type="submit" class="profile-btn profile-btn-primary">Save Profile</button>
                </div>
            </form>
        </section>
    </div>
</main>

<div class="profile-confirm-modal" id="emailConfirmModal" aria-hidden="true">
    <div class="profile-confirm-card" role="dialog" aria-modal="true" aria-labelledby="emailConfirmTitle">
        <h3 id="emailConfirmTitle">Confirm email change</h3>
        <p>Changing your account email will save the new email address and automatically log you out. You will need to sign in again with the updated email.</p>
        <div class="profile-confirm-actions">
            <button type="button" class="profile-btn profile-btn-secondary" id="cancelEmailChange">No, cancel</button>
            <button type="button" class="profile-btn profile-btn-primary" id="confirmEmailChange">Yes, save and logout</button>
        </div>
    </div>
</div>

<script>
const uploadInput = document.getElementById('profile_image');
const preview = document.getElementById('photoPreview');
if (uploadInput && preview) {
    uploadInput.addEventListener('change', () => {
        const file = uploadInput.files && uploadInput.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        preview.replaceChildren();
        const img = document.createElement('img');
        img.src = url;
        img.alt = 'Selected profile photo preview';
        img.onload = () => URL.revokeObjectURL(url);
        preview.appendChild(img);
    });
}

const profileEditForm = document.getElementById('profileEditForm');
const emailInput = document.getElementById('email');
const emailConfirmModal = document.getElementById('emailConfirmModal');
const cancelEmailChange = document.getElementById('cancelEmailChange');
const confirmEmailChange = document.getElementById('confirmEmailChange');
let allowProfileSubmit = false;

if (profileEditForm && emailInput && emailConfirmModal) {
    profileEditForm.addEventListener('submit', (event) => {
        const originalEmail = (profileEditForm.dataset.originalEmail || '').trim().toLowerCase();
        const currentEmail = (emailInput.value || '').trim().toLowerCase();
        if (allowProfileSubmit || originalEmail === currentEmail) return;
        event.preventDefault();
        emailConfirmModal.classList.add('is-open');
        emailConfirmModal.setAttribute('aria-hidden', 'false');
    });
}

if (cancelEmailChange) {
    cancelEmailChange.addEventListener('click', () => {
        emailConfirmModal.classList.remove('is-open');
        emailConfirmModal.setAttribute('aria-hidden', 'true');
    });
}

if (confirmEmailChange) {
    confirmEmailChange.addEventListener('click', () => {
        allowProfileSubmit = true;
        if (typeof profileEditForm.requestSubmit === 'function') {
            profileEditForm.requestSubmit();
        } else {
            profileEditForm.submit();
        }
    });
}
</script>

<?php include __DIR__ . '/../partials/public/footer.php'; ?>
