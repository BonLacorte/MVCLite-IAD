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
            <h1 class="page-title">Create New User</h1>

            <form id="createUserForm">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; margin-bottom: 2rem;">
                    <!-- LEFT: Profile Information -->
                    <div>
                        <div class="section-divider">
                            <h3>Profile Information</h3>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                            <div class="form-group">
                                <label>Name <span style="color: var(--admin-danger);">*</span></label>
                                <input type="text" name="name" id="fname" required placeholder="Full Name">
                            </div>

                            <div class="form-group">
                                <label>Email <span style="color: var(--admin-danger);">*</span></label>
                                <input type="email" name="email" id="femail" required placeholder="email@example.com">
                            </div>

                            <div class="form-group">
                                <label>Password <span style="color: var(--admin-danger);">*</span></label>
                                <div class="input-with-icon">
                                    <input type="password" name="password" id="fpassword" required placeholder="Enter new password" minlength="8" maxlength="20">
                                    <button type="button" class="toggle-password" onclick="togglePw('fpassword', this)" title="Toggle visibility">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                                <span class="field-hint">
                                    <a href="#" id="useDefaultPw" style="color: var(--admin-primary); font-size:0.78rem; font-weight:600;">Use Default Password (PCApca2025)</a><br>
                                    Must be 8-20 characters with at least 1 uppercase, 1 lowercase, and 1 number
                                </span>
                            </div>

                            <div class="form-group">
                                <label>Confirm Password <span style="color: var(--admin-danger);">*</span></label>
                                <div class="input-with-icon">
                                    <input type="password" name="confirm_password" id="fconfirm" required placeholder="Confirm new password">
                                    <button type="button" class="toggle-password" onclick="togglePw('fconfirm', this)" title="Toggle visibility">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Cellphone Number</label>
                                <input type="text" name="cellphone_number" id="fcellphone" placeholder="09XXXXXXXXX" maxlength="11">
                                <span class="field-hint">Must be 11 digits and start with 09</span>
                            </div>

                            <div class="form-group">
                                <label>Facebook Account</label>
                                <input type="text" name="facebook_account" id="ffacebook" placeholder="Profile link or username">
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: Contact Information -->
                    <div>
                        <div class="section-divider">
                            <h3>Contact Information</h3>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                            <div class="form-group">
                                <label>Position <span style="color: var(--admin-danger);">*</span></label>
                                <input type="text" name="position" id="fposition" required placeholder="e.g. Info System Analyst III">
                            </div>

                            <div class="form-group">
                                <label>Role <span style="color: var(--admin-danger);">*</span></label>
                                <select name="role" id="frole">
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>

                            <?php
                            $officeRecord = [];
                            $officePrefix = 'f';
                            include __DIR__ . '/_office_assignment_fields.php';
                            ?>
                        </div>
                    </div>
                </div>

                <div class="form-error-box" id="formErrors"></div>

                <div class="form-actions">
                    <a href="/admin/users" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
function togglePw(fieldId, btn) {
    const input = document.getElementById(fieldId);
    input.type = input.type === 'password' ? 'text' : 'password';
}

document.getElementById('useDefaultPw').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('fpassword').value = 'PCApca2025';
    document.getElementById('fconfirm').value  = 'PCApca2025';
});

document.getElementById('createUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    const errEl = document.getElementById('formErrors');
    errEl.style.display = 'none';

    // Client-side validation
    const pw  = document.getElementById('fpassword').value;
    const cpw = document.getElementById('fconfirm').value;
    if (pw !== cpw) {
        errEl.textContent = 'Passwords do not match.';
        errEl.style.display = 'block';
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Creating...';

    fetch('/admin/users/store', { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showFlash(data.message || 'User created successfully!', 'success');
                setTimeout(() => window.location.href = '/admin/users', 1000);
            } else {
                btn.disabled = false;
                btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg> Create User';
                const errs = data.errors ? Object.values(data.errors).join('<br>') : (data.message || 'An error occurred.');
                errEl.innerHTML = errs;
                errEl.style.display = 'block';
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg> Create User';
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
