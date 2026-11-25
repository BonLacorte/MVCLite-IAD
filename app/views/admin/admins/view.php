<?php include __DIR__ . '/../../partials/public/head.php'; ?>
<?php include __DIR__ . '/../../partials/public/nav.php'; ?>
<?php
$escape = static fn($value): string => htmlspecialchars((string)($value ?? 'N/A'), ENT_QUOTES, 'UTF-8');
$name = $admin['name'] ?? 'Administrator';
$initials = implode('', array_map(static fn($part) => strtoupper(substr($part, 0, 1)), array_slice(preg_split('/\s+/', trim($name)), 0, 2))) ?: 'A';
$office = trim((string)($admin['for_central_office'] ?? '')) ?: trim((string)($admin['for_region_center'] ?? ''));
?>

<link rel="stylesheet" href="/assets/css/admin.css">

<style>
    .profile-shell {
        max-width: 880px;
        margin: 0 auto;
    }
    .profile-card {
        background: white;
        border: 1px solid var(--admin-border);
        border-radius: 18px;
        box-shadow: var(--admin-shadow-md);
        overflow: hidden;
    }
    .profile-hero {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 1.25rem;
        align-items: center;
        padding: 2rem;
        background: linear-gradient(135deg, rgba(5, 87, 68, 0.08), rgba(22, 163, 74, 0.08));
        border-bottom: 1px solid var(--admin-border);
    }
    .profile-avatar {
        width: 88px;
        height: 88px;
        border-radius: 24px;
        background: var(--admin-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        font-weight: 900;
        letter-spacing: -0.03em;
        box-shadow: var(--admin-shadow-md);
    }
    .profile-name {
        margin: 0;
        color: var(--admin-primary);
        font-family: 'Montserrat', sans-serif;
        font-size: 1.8rem;
        font-weight: 900;
        line-height: 1.1;
    }
    .profile-meta {
        margin: 0.35rem 0 0;
        color: #64748b;
        font-weight: 600;
    }
    .profile-role {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.4rem 0.85rem;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-size: 0.76rem;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .profile-body {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
        padding: 1.5rem;
    }
    .profile-section {
        border: 1px solid var(--admin-border);
        border-radius: 14px;
        padding: 1.35rem;
        background: #f8fafc;
    }
    .profile-section h2 {
        margin: 0 0 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--admin-primary);
        font-size: 0.95rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .profile-field {
        display: grid;
        gap: 0.25rem;
        padding: 0.8rem 0;
        border-top: 1px solid #e2e8f0;
    }
    .profile-field:first-of-type {
        border-top: none;
        padding-top: 0;
    }
    .profile-field label {
        color: #64748b;
        font-size: 0.74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .profile-field span {
        color: #0f172a;
        font-size: 0.96rem;
        font-weight: 650;
        overflow-wrap: anywhere;
    }
    .profile-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.8rem;
        padding: 1.25rem 1.5rem 1.5rem;
        border-top: 1px solid var(--admin-border);
    }
    @media (max-width: 760px) {
        .profile-hero {
            grid-template-columns: 1fr;
            text-align: center;
        }
        .profile-avatar {
            margin: 0 auto;
        }
        .profile-body {
            grid-template-columns: 1fr;
        }
        .profile-footer {
            justify-content: stretch;
            flex-direction: column;
        }
    }
</style>

<main style="min-height: 80vh; padding: 3rem 0; background-color: var(--admin-bg);">
    <div class="container profile-shell">
        <a href="/admin/admins" class="page-back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Admins
        </a>

        <article class="profile-card">
            <header class="profile-hero">
                <div class="profile-avatar"><?= $escape($initials) ?></div>
                <div>
                    <h1 class="profile-name"><?= $escape($name) ?></h1>
                    <p class="profile-meta"><?= $escape($admin['position'] ?? 'N/A') ?></p>
                    <p class="profile-meta"><?= $escape($office ?: 'No office assignment') ?></p>
                </div>
                <span class="profile-role">System Admin</span>
            </header>

            <div class="profile-body">
                <section class="profile-section">
                    <h2>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Personal Information
                    </h2>
                    <div class="profile-field">
                        <label>Full Name</label>
                        <span><?= $escape($admin['name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="profile-field">
                        <label>Email Address</label>
                        <span><?= $escape($admin['email'] ?? 'N/A') ?></span>
                    </div>
                    <div class="profile-field">
                        <label>Cellphone Number</label>
                        <span><?= $escape($admin['cellphone_number'] ?? 'N/A') ?></span>
                    </div>
                    <div class="profile-field">
                        <label>Facebook Account</label>
                        <span><?= $escape($admin['facebook_account'] ?? 'N/A') ?></span>
                    </div>
                </section>

                <section class="profile-section">
                    <h2>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                        Work Information
                    </h2>
                    <div class="profile-field">
                        <label>Position</label>
                        <span><?= $escape($admin['position'] ?? 'N/A') ?></span>
                    </div>
                    <div class="profile-field">
                        <label>Department / Office / Unit</label>
                        <span><?= $escape($admin['department_office_unit'] ?? 'N/A') ?></span>
                    </div>
                    <div class="profile-field">
                        <label>Central Office</label>
                        <span><?= $escape($admin['for_central_office'] ?? 'N/A') ?></span>
                    </div>
                    <div class="profile-field">
                        <label>Region Center</label>
                        <span><?= $escape($admin['for_region_center'] ?? 'N/A') ?></span>
                    </div>
                </section>
            </div>

            <footer class="profile-footer">
                <a href="/admin/admins" class="btn-cancel">Back to Administrators</a>
                <a href="/admin/admins/edit?id=<?= (int)($admin['id'] ?? 0) ?>" class="btn-submit" style="text-decoration:none;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                    Edit Profile
                </a>
            </footer>
        </article>
    </div>
</main>

<?php include __DIR__ . '/../../partials/public/footer.php'; ?>
