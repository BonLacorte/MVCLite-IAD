<?php include __DIR__ . '/../../partials/public/head.php'; ?>
<?php include __DIR__ . '/../../partials/public/nav.php'; ?>

<link rel="stylesheet" href="/assets/css/admin.css">

<style>
    .status-badge { padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; display: inline-block; }
    .status-active   { background: #dcfce7; color: #166534; }
    .status-draft    { background: #fef9c3; color: #854d0e; }
    .status-inactive { background: #f3f4f6; color: #6b7280; }
    .status-archived { background: #ede9fe; color: #6d28d9; }

    .header-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .header-details h1 {
        color: var(--admin-primary);
        font-family: 'Montserrat', sans-serif;
        font-size: 2.25rem;
        font-weight: 800;
        margin: 0 0 0.5rem 0;
        letter-spacing: -0.02em;
    }
    .header-details p {
        color: #64748b;
        margin: 0 0 1rem 0;
        font-size: 1.05rem;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .stat-icon.primary { background: var(--admin-primary-soft); color: var(--admin-primary); }
    .stat-icon.success { background: #dcfce7; color: #16a34a; }
    .stat-icon.info { background: #e0f2fe; color: #0284c7; }
    .stat-info h3 { margin: 0; font-size: 1.8rem; font-weight: 800; color: #1e293b; }
    .stat-info p { margin: 0; color: #64748b; font-size: 0.9rem; font-weight: 600; }

    .content-grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 2rem;
    }
    @media (max-width: 992px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }
    .content-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .content-card h3 {
        margin-top: 0;
        color: #1e293b;
        font-size: 1.25rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Tree View Styles */
    .tree-section {
        margin-bottom: 1rem;
    }
    .tree-section-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-radius: 8px;
        font-weight: 700;
        color: #334155;
        border: 1px solid #e2e8f0;
    }
    .tree-questions {
        padding-left: 2.5rem;
        margin-top: 0.5rem;
        border-left: 2px solid #e2e8f0;
        margin-left: 1.25rem;
    }
    .tree-question {
        padding: 0.5rem 0;
        color: #475569;
        font-size: 0.9rem;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
    }
    .tree-question::before {
        content: '';
        display: inline-block;
        width: 12px;
        height: 2px;
        background: #e2e8f0;
        margin-top: 10px;
    }

    /* Premium Assigned User Table Styles */
    .search-input-wrapper {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .search-input-wrapper input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.9rem;
        outline: none;
        transition: all 0.2s ease-in-out;
    }
    .search-input-wrapper input:focus {
        border-color: #055741;
        box-shadow: 0 0 0 3px rgba(5, 87, 65, 0.15);
    }
    .search-input-wrapper svg {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }
    .user-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1.5rem;
    }
    .user-table th {
        text-align: left;
        padding: 0.75rem;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        border-bottom: 2px solid #f1f5f9;
    }
    .user-table td {
        padding: 1rem 0.75rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .user-profile-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .user-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #e6f4f0;
        color: #055741;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.95rem;
        border: 1px solid #cce8e0;
    }
    .user-name-wrapper h4 {
        margin: 0;
        font-size: 0.9rem;
        color: #1e293b;
        font-weight: 700;
    }
    .user-name-wrapper p {
        margin: 0;
        font-size: 0.75rem;
        color: #64748b;
    }
    .dept-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        background: #f1f5f9;
        color: #475569;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .progress-bar-container {
        width: 100%;
        background: #e2e8f0;
        border-radius: 999px;
        height: 6px;
        position: relative;
        overflow: hidden;
        margin-top: 0.25rem;
    }
    .progress-bar-fill {
        background: linear-gradient(90deg, #055741, #10b981);
        height: 100%;
        border-radius: 999px;
        transition: width 0.4s ease-out;
    }
    .progress-percent-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #0f172a;
    }
    .btn-action-check {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0.4rem 0.85rem;
        background: #055741;
        color: #FEFCE8;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        box-shadow: 0 2px 4px rgba(5,87,65,0.15);
    }
    .btn-action-check:hover {
        background: #044333;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(5,87,65,0.25);
    }
    
    .dept-summary-card {
        background: #fafbfb;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
        padding: 1.25rem;
        margin-top: 2rem;
    }
    .dept-summary-card h4 {
        margin: 0 0 1rem 0;
        color: #1e293b;
        font-size: 0.95rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 0.5rem;
    }
    .dept-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px dashed #f1f5f9;
        font-size: 0.85rem;
    }
    .dept-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
</style>

<main style="min-height: 80vh; padding: 3rem 0; background-color: var(--admin-bg);">
    <div class="container" style="max-width: 1300px;">
        
        <nav aria-label="breadcrumb" style="margin-bottom: 1.5rem;">
            <a href="/admin/questionnaires" style="color: var(--admin-primary); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; opacity: 0.7;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                Back to Questionnaires
            </a>
        </nav>

        <div class="header-card">
            <div class="header-details">
                <h1><?= htmlspecialchars($selectedQuestionnaire['title']) ?></h1>
                <p><?= htmlspecialchars($selectedQuestionnaire['description'] ?? 'No description provided.') ?></p>
                <div style="display:flex; gap:1rem; align-items:center;">
                    <?php 
                        $statusClass = 'status-draft';
                        if ($selectedQuestionnaire['status'] === 'active') $statusClass = 'status-active';
                        else if ($selectedQuestionnaire['status'] === 'inactive') $statusClass = 'status-inactive';
                        else if ($selectedQuestionnaire['status'] === 'archived') $statusClass = 'status-archived';
                    ?>
                    <span class="status-badge <?= $statusClass ?>"><?= $selectedQuestionnaire['status'] ?></span>
                    <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 600; text-transform: uppercase;">
                        Type: <?= $selectedQuestionnaire['type'] ?>
                    </span>
                </div>
            </div>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; justify-content:flex-end;">
                <a href="/admin/questionnaire/export?questionnaire_id=<?= (int)$selectedQuestionnaire['id'] ?>" class="btn-export" style="min-height:44px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export Details
                </a>
                <a href="/admin/questionnaire/edit?questionnaire_id=<?= (int)$selectedQuestionnaire['id'] ?>" class="action-btn" style="text-decoration:none; background:#f8fafc; color:#0f172a; width:auto; height:auto; min-height:44px; padding:0.75rem 1.5rem; border-radius:10px; font-weight:700; display:flex; align-items:center; gap:0.5rem; border:1px solid #cbd5e1;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                    Edit Structure
                </a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <div class="stat-info">
                    <h3><?= $selectedQuestionnaire['total_questions'] ?></h3>
                    <p>Total Questions</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                </div>
                <div class="stat-info">
                    <h3><?= $selectedQuestionnaire['completion_percentage'] ?>%</h3>
                    <p>Avg. Completion Rate</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon info">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="stat-info">
                    <h3 id="assignedUsersCount"><?= $totalUsers ?></h3>
                    <p>Assigned Users</p>
                </div>
            </div>
        </div>

        <div class="content-grid">
            <!-- Left: Structure Tree (Now Dominant & Bigger) -->
            <div class="content-card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid #f1f5f9;">
                    <h3 style="margin:0;border:none;padding:0;">Structure</h3>
                    <a href="/admin/questionnaire/sections?questionnaire_id=<?= $selectedQuestionnaire['id'] ?>" style="color:var(--admin-primary);font-size:0.85rem;font-weight:600;text-decoration:none;">Manage</a>
                </div>

                <?php if(empty($sections)): ?>
                    <div style="padding: 2rem; text-align: center; color: #94a3b8; background: #f8fafc; border-radius: 8px;">
                        No sections defined.
                    </div>
                <?php else: ?>
                    <?php foreach($sections as $index => $section): ?>
                        <div class="tree-section">
                            <div class="tree-section-header">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:#94a3b8;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                <?= htmlspecialchars($section['title']) ?>
                            </div>
                            <?php if(!empty($questionsBySection[$section['id']])): ?>
                                <div class="tree-questions">
                                    <?php foreach($questionsBySection[$section['id']] as $q): ?>
                                        <div class="tree-question">
                                            <span><?= htmlspecialchars($q['question_text']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="tree-questions">
                                    <div class="tree-question" style="color:#cbd5e1; font-style:italic;">No questions in this section.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Right: Assigned Users Card -->
            <div class="content-card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid #f1f5f9;">
                    <h3 style="margin:0;border:none;padding:0;">Assigned Users</h3>
                    <div style="display:flex; gap: 0.5rem;">
                        <?php if($selectedQuestionnaire['type'] === 'general' && !empty($usersAssigned)): ?>
                            <a href="/admin/questionnaire/check?questionnaire_id=<?= $selectedQuestionnaire['id'] ?>&questionnaire_type=general" class="btn-action-check" style="background:#10b981; color:#FEFCE8; border-color:#10b981; padding:0.5rem 1rem;">
                                Evaluate General Submission
                            </a>
                        <?php endif; ?>
                        <a href="/admin/questionnaire/assign?questionnaire_id=<?= $selectedQuestionnaire['id'] ?>" class="btn-action-check" style="background:#055741; color:#FEFCE8; border-color:#055741; padding:0.5rem 1rem;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
                            Assign
                        </a>
                    </div>
                </div>

                <div class="search-input-wrapper">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="userSearchInput" placeholder="Search assigned users by name, email or department...">
                </div>

                <?php if(empty($usersAssigned)): ?>
                    <div style="padding: 3rem; text-align: center; color: #94a3b8; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;" id="emptyUsersBlock">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem;opacity:0.5;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <p style="margin:0;">No users assigned yet.</p>
                        <a href="/admin/questionnaire/assign?questionnaire_id=<?= $selectedQuestionnaire['id'] ?>" style="color:#055741; font-weight:700; text-decoration:none; display:inline-block; margin-top:0.5rem; font-size:0.9rem;">Assign one now →</a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="user-table" id="assignedUsersTable">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Progress</th>
                                    <?php if($selectedQuestionnaire['type'] !== 'general'): ?>
                                        <th style="width: 10px;">Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($usersAssigned as $u): ?>
                                    <?php 
                                        $initials = '';
                                        $parts = explode(' ', $u['name']);
                                        foreach($parts as $p) {
                                            if(!empty($p)) $initials .= strtoupper($p[0]);
                                        }
                                        $initials = substr($initials, 0, 2);
                                    ?>
                                    <tr class="user-row" data-searchable="<?= htmlspecialchars(strtolower($u['name'] . ' ' . $u['email'] . ' ' . $u['department_office_unit'])) ?>">
                                        <td>
                                            <div class="user-profile-info">
                                                <div class="user-avatar"><?= $initials ?></div>
                                                <div class="user-name-wrapper">
                                                    <h4><?= htmlspecialchars($u['name']) ?></h4>
                                                    <span class="dept-badge"><?= htmlspecialchars($u['department_office_unit'] ?: 'No Department') ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                                                <div style="flex-grow: 1;">
                                                    <div class="progress-bar-container">
                                                        <div class="progress-bar-fill" style="width: <?= $u['user_completion'] ?>%;"></div>
                                                    </div>
                                                </div>
                                                <span class="progress-percent-label"><?= $u['user_completion'] ?>%</span>
                                            </div>
                                        </td>
                                        <?php if($selectedQuestionnaire['type'] !== 'general'): ?>
                                            <td>
                                                <a href="/admin/questionnaire/check?questionnaire_id=<?= $selectedQuestionnaire['id'] ?>&user_id=<?= $u['id'] ?>" class="btn-action-check">
                                                    Check
                                                </a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Departmental Average Summary Card -->
                <?php if(!empty($departmentProgress)): ?>
                    <div class="dept-summary-card">
                        <h4>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                            Departmental Averages
                        </h4>
                        <?php foreach($departmentProgress as $dp): ?>
                            <div class="dept-row">
                                <span style="font-weight: 600; color: #475569;"><?= htmlspecialchars($dp['department'] ?: 'Unknown Department') ?></span>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 600;"><?= $dp['user_count'] ?> assigned</span>
                                    <span style="font-weight: 700; color: #055741;"><?= $dp['average_completion'] ?>%</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('userSearchInput');
        const userRows = document.querySelectorAll('.user-row');
        const emptyBlock = document.getElementById('emptyUsersBlock');
        const table = document.getElementById('assignedUsersTable');

        if(searchInput) {
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase().trim();
                let visibleCount = 0;

                userRows.forEach(row => {
                    const searchContent = row.getAttribute('data-searchable') || '';
                    if(searchContent.includes(query)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show empty block or hide table if no matching row
                if(table) {
                    if(visibleCount === 0) {
                        table.style.display = 'none';
                        if(!document.getElementById('noResultsMessage')) {
                            const noRes = document.createElement('div');
                            noRes.id = 'noResultsMessage';
                            noRes.style.padding = '2rem';
                            noRes.style.textAlign = 'center';
                            noRes.style.color = '#94a3b8';
                            noRes.style.background = '#f8fafc';
                            noRes.style.borderRadius = '8px';
                            noRes.style.marginTop = '1rem';
                            noRes.style.fontWeight = '500';
                            noRes.textContent = 'No matching users found.';
                            table.parentNode.insertBefore(noRes, table.nextSibling);
                        }
                    } else {
                        table.style.display = '';
                        const msg = document.getElementById('noResultsMessage');
                        if(msg) msg.remove();
                    }
                }
            });
        }
    });
</script>

<?php include __DIR__ . '/../../partials/public/footer.php'; ?>
