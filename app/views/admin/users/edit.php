<?php include __DIR__ . '/../../partials/public/head.php'; ?>
<?php include __DIR__ . '/../../partials/public/nav.php'; ?>
<link rel="stylesheet" href="/assets/css/admin.css">

<div id="flash-msg" class="flash-msg" style="display:none;"></div>

<main style="min-height: 80vh; padding: 3rem 0; background-color: var(--admin-bg);">
    <div class="container" style="max-width: 960px;">

        <a href="/admin/users" class="page-back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Users
        </a>

        <div class="page-card">

            <!-- User Header -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <div class="avatar-circle" id="avatarEl"><?= strtoupper(substr($user['name'] ?? 'U', 0, 4)) ?></div>
                <h2 style="margin: 0; color: var(--admin-primary); font-family: 'Montserrat', sans-serif; font-weight: 800; letter-spacing: -0.02em;"><?= htmlspecialchars($user['name'] ?? '') ?></h2>
                <p style="color: #64748b; margin: 0.25rem 0 1rem; font-size: 0.9rem;"><?= htmlspecialchars($user['position'] ?? '') ?></p>
                <button type="button" class="btn-change-pw" onclick="document.getElementById('pwModal').classList.add('open')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Change Password
                </button>
            </div>

            <h1 class="page-title" style="margin-bottom: 1.5rem;">Edit User</h1>

            <form id="editUserForm">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; margin-bottom: 2rem;">
                    <!-- LEFT: Profile Information -->
                    <div>
                        <div class="section-divider"><h3>Profile Information</h3></div>
                        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" id="fname" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" id="femail" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Cellphone Number</label>
                                <input type="text" name="cellphone_number" id="fcellphone" value="<?= htmlspecialchars($user['cellphone_number'] ?? '') ?>" placeholder="09XXXXXXXXX">
                            </div>
                            <div class="form-group">
                                <label>Facebook Account</label>
                                <input type="text" name="facebook_account" id="ffacebook" value="<?= htmlspecialchars($user['facebook_account'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: Work Information -->
                    <div>
                        <div class="section-divider"><h3>Work Information</h3></div>
                        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                            <div class="form-group">
                                <label>Role <span style="color: var(--admin-danger);">*</span></label>
                                <select name="role" id="frole">
                                    <option value="user"  <?= ($user['role'] ?? '') === 'user'  ? 'selected' : '' ?>>User</option>
                                    <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Position <span style="color: var(--admin-danger);">*</span></label>
                                <input type="text" name="position" id="fposition" value="<?= htmlspecialchars($user['position'] ?? '') ?>">
                            </div>
                            <?php
                            $officeRecord = $user;
                            $officePrefix = 'f';
                            include __DIR__ . '/_office_assignment_fields.php';
                            ?>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" id="fstatus">
                                    <option value="active"   <?= ($user['is_deleted'] ?? 0) == 0 ? 'selected' : '' ?>>Active</option>
                                    <option value="deleted"  <?= ($user['is_deleted'] ?? 0) == 1 ? 'selected' : '' ?>>Deleted</option>
                                </select>
                                <span class="field-hint">Setting a user to "Deleted" will prevent them from logging in</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-error-box" id="formErrors"></div>

                <div class="form-actions">
                    <a href="/admin/users" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<!-- Change Password Modal -->
<div id="pwModal" class="modal-overlay">
    <div class="modal-box" style="max-width: 440px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0; color: var(--admin-primary); font-weight: 800;">Change Password</h3>
            <button onclick="document.getElementById('pwModal').classList.remove('open')" style="background: rgba(0,0,0,0.05); border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer;">&times;</button>
        </div>
        <form id="pwForm">
            <input type="hidden" name="id" value="<?= $user['id'] ?>">
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label>New Password</label>
                <div class="input-with-icon">
                    <input type="password" name="password" id="pwNew" placeholder="••••••••" minlength="7">
                    <button type="button" class="toggle-password" onclick="togglePw('pwNew', this)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>
            <div class="form-error-box" id="pwErrors"></div>
            <div class="form-actions" style="margin-top: 1rem; padding-top: 1rem;">
                <button type="button" onclick="document.getElementById('pwModal').classList.remove('open')" class="btn-cancel">Cancel</button>
                <button type="submit" class="btn-submit" id="pwSubmitBtn">Update Password</button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePw(fieldId, btn) {
    const input = document.getElementById(fieldId);
    input.type = input.type === 'password' ? 'text' : 'password';
}

document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    const errEl = document.getElementById('formErrors');
    errEl.style.display = 'none';
    btn.disabled = true;
    btn.textContent = 'Saving...';

    fetch('/admin/users/update', { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showFlash(data.message || 'User updated successfully!', 'success');
                setTimeout(() => window.location.href = '/admin/users', 1000);
            } else {
                btn.disabled = false;
                btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg> Update User';
                const errs = data.errors ? Object.values(data.errors).join('<br>') : (data.message || 'An error occurred.');
                errEl.innerHTML = errs;
                errEl.style.display = 'block';
            }
        });
});

document.getElementById('pwForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('pwSubmitBtn');
    const errEl = document.getElementById('pwErrors');
    errEl.style.display = 'none';
    btn.disabled = true;
    btn.textContent = 'Updating...';

    fetch('/admin/users/change-password', { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(data => {
            document.getElementById('pwModal').classList.remove('open');
            if (data.success) {
                showFlash('Password updated successfully!', 'success');
            } else {
                errEl.textContent = data.message || 'Failed to update password.';
                errEl.style.display = 'block';
            }
            btn.disabled = false;
            btn.textContent = 'Update Password';
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
