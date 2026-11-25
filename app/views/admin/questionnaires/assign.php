<?php include __DIR__ . '/../../partials/public/head.php'; ?>
<?php include __DIR__ . '/../../partials/public/nav.php'; ?>

<link rel="stylesheet" href="/assets/css/admin.css">

<style>
    .assign-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2.5rem;
        margin-top: 2rem;
    }
    @media (max-width: 992px) {
        .assign-layout {
            grid-template-columns: 1fr;
        }
    }

    .pane-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        height: 650px;
    }

    .pane-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .pane-title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        font-family: 'Montserrat', sans-serif;
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .pane-badge {
        background: #e6f4f0;
        color: #055741;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.2rem 0.65rem;
        border-radius: 999px;
    }

    .assigned-badge {
        background: #fefce8;
        color: #854d0e;
    }

    .search-wrapper {
        position: relative;
        margin-bottom: 1.25rem;
    }

    .search-wrapper input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.9rem;
        outline: none;
        transition: all 0.2s;
    }

    .search-wrapper input:focus {
        border-color: #055741;
        box-shadow: 0 0 0 3px rgba(5, 87, 65, 0.12);
    }

    .search-wrapper svg {
        position: absolute;
        left: 0.9rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .bulk-actions-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fafbfb;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        border: 1px dashed #e2e8f0;
    }

    .user-list {
        flex-grow: 1;
        overflow-y: auto;
        padding-right: 0.25rem;
    }

    /* Custom Scrollbar for list */
    .user-list::-webkit-scrollbar {
        width: 6px;
    }
    .user-list::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 10px;
    }
    .user-list::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .user-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.85rem 1rem;
        border-radius: 12px;
        background: #f8fafc;
        margin-bottom: 0.75rem;
        border: 1px solid #f1f5f9;
        transition: all 0.2s ease-in-out;
    }

    .user-item:hover {
        background: white;
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        transform: translateY(-1px);
    }

    .user-item-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-checkbox {
        width: 18px;
        height: 18px;
        accent-color: #055741;
        cursor: pointer;
    }

    .user-info-text h4 {
        margin: 0;
        font-size: 0.9rem;
        color: #1e293b;
        font-weight: 700;
    }

    .user-info-text p {
        margin: 0;
        font-size: 0.75rem;
        color: #64748b;
    }

    .user-info-text span {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 700;
        color: #475569;
        background: #e2e8f0;
        padding: 0.1rem 0.4rem;
        border-radius: 4px;
        margin-top: 0.25rem;
    }

    .action-btn-small {
        border: none;
        outline: none;
        padding: 0.5rem 0.85rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.2s;
    }

    .btn-assign-single {
        background: #055741;
        color: white;
    }

    .btn-assign-single:hover {
        background: #044333;
    }

    .btn-remove-single {
        background: #fee2e2;
        color: #ef4444;
    }

    .btn-remove-single:hover {
        background: #fecaca;
    }

    .btn-bulk-action {
        padding: 0.45rem 1rem;
        font-size: 0.8rem;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
        border: none;
        transition: all 0.2s;
    }

    .btn-bulk-assign {
        background: #055741;
        color: white;
    }

    .btn-bulk-assign:hover {
        background: #044333;
    }

    .btn-bulk-remove {
        background: #ef4444;
        color: white;
    }

    .btn-bulk-remove:hover {
        background: #dc2626;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #94a3b8;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 80%;
    }

    .empty-state svg {
        opacity: 0.3;
        margin-bottom: 1rem;
    }

    /* Floating Toast Alerts */
    .toast-container {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 9999;
    }
    .toast-alert {
        background: #0f172a;
        color: white;
        padding: 0.85rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        transform: translateY(20px);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .toast-alert.show {
        transform: translateY(0);
        opacity: 1;
    }
    .toast-alert.success {
        border-left: 4px solid #10b981;
    }
    .toast-alert.error {
        border-left: 4px solid #ef4444;
    }
</style>

<main style="min-height: 80vh; padding: 3rem 0; background-color: var(--admin-bg);">
    <div class="container" style="max-width: 1300px;">
        
        <nav aria-label="breadcrumb" style="margin-bottom: 1.5rem;">
            <a href="/admin/questionnaire/show?questionnaire_id=<?= $selectedQuestionnaire['id'] ?>" style="color: var(--admin-primary); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; opacity: 0.7;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                Back to Questionnaire Details
            </a>
        </nav>

        <div style="background:white; border-radius:12px; padding:2rem; box-shadow:0 4px 15px rgba(0,0,0,0.03); margin-bottom:2rem;">
            <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--admin-primary); background: var(--admin-primary-soft); padding: 0.3rem 0.75rem; border-radius: 99px;">
                User Assignment Engine
            </span>
            <h1 style="margin: 0.75rem 0 0.5rem 0; font-family:'Montserrat', sans-serif; font-size:2rem; font-weight:800; color:#0f172a;">
                Assign Users to Questionnaire
            </h1>
            <p style="margin:0; color:#64748b; font-size:1rem; font-weight:500;">
                Questionnaire: <strong style="color: #0f172a;"><?= htmlspecialchars($selectedQuestionnaire['title']) ?></strong>
            </p>
        </div>

        <div class="assign-layout">
            <!-- Left Pane: Available Users (Unassigned) -->
            <div class="pane-card">
                <div class="pane-header">
                    <h3 class="pane-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Available Users
                    </h3>
                    <span class="pane-badge" id="unassignedCountBadge"><?= count($unassignedUsers) ?></span>
                </div>

                <div class="search-wrapper">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="unassignedSearch" placeholder="Search unassigned users...">
                </div>

                <div class="bulk-actions-bar">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="selectAllUnassigned" class="user-checkbox">
                        <span style="font-size: 0.8rem; font-weight: 700; color: #475569;">Select All</span>
                    </div>
                    <button class="btn-bulk-action btn-bulk-assign" id="bulkAssignBtn" disabled>
                        Assign Selected
                    </button>
                </div>

                <div class="user-list" id="unassignedList">
                    <?php if(empty($unassignedUsers)): ?>
                        <div class="empty-state">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                            <p style="margin: 0; font-weight:600;">All active users are currently assigned.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($unassignedUsers as $u): ?>
                            <div class="user-item" data-id="<?= $u['id'] ?>" data-search="<?= htmlspecialchars(strtolower($u['name'] . ' ' . $u['email'] . ' ' . $u['department_office_unit'])) ?>">
                                <div class="user-item-left">
                                    <input type="checkbox" class="user-checkbox unassigned-select" data-id="<?= $u['id'] ?>">
                                    <div class="user-info-text">
                                        <h4><?= htmlspecialchars($u['name']) ?></h4>
                                        <p><?= htmlspecialchars($u['email']) ?></p>
                                        <span><?= htmlspecialchars($u['department_office_unit'] ?: 'No Department') ?></span>
                                    </div>
                                </div>
                                <button class="action-btn-small btn-assign-single" onclick="assignSingle(<?= $u['id'] ?>)">
                                    + Assign
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Pane: Currently Assigned Users -->
            <div class="pane-card" style="border-top: 4px solid #055741;">
                <div class="pane-header">
                    <h3 class="pane-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                        Assigned Users
                    </h3>
                    <span class="pane-badge assigned-badge" id="assignedCountBadge"><?= count($assignedUsers) ?></span>
                </div>

                <div class="search-wrapper">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="assignedSearch" placeholder="Search assigned users...">
                </div>

                <div class="bulk-actions-bar">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="selectAllAssigned" class="user-checkbox">
                        <span style="font-size: 0.8rem; font-weight: 700; color: #475569;">Select All</span>
                    </div>
                    <button class="btn-bulk-action btn-bulk-remove" id="bulkRemoveBtn" disabled>
                        Remove Selected
                    </button>
                </div>

                <div class="user-list" id="assignedList">
                    <?php if(empty($assignedUsers)): ?>
                        <div class="empty-state" id="assignedEmptyState">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <p style="margin: 0; font-weight:600;">No users assigned to this questionnaire.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($assignedUsers as $u): ?>
                            <div class="user-item" data-id="<?= $u['id'] ?>" data-search="<?= htmlspecialchars(strtolower($u['name'] . ' ' . $u['email'] . ' ' . $u['department_office_unit'])) ?>">
                                <div class="user-item-left">
                                    <input type="checkbox" class="user-checkbox assigned-select" data-id="<?= $u['id'] ?>">
                                    <div class="user-info-text">
                                        <h4><?= htmlspecialchars($u['name']) ?></h4>
                                        <p><?= htmlspecialchars($u['email']) ?></p>
                                        <span><?= htmlspecialchars($u['department_office_unit'] ?: 'No Department') ?></span>
                                    </div>
                                </div>
                                <button class="action-btn-small btn-remove-single" onclick="removeSingle(<?= $u['id'] ?>)">
                                    ✕ Remove
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</main>

<div class="toast-container" id="toastContainer"></div>

<script>
    const questionnaireId = <?= $selectedQuestionnaire['id'] ?>;

    // Toast utility
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast-alert ${type}`;
        
        const icon = type === 'success' 
            ? `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`
            : `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;
            
        toast.innerHTML = `${icon} <span>${message}</span>`;
        container.appendChild(toast);
        
        setTimeout(() => toast.classList.add('show'), 50);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Client-side search filters
    document.getElementById('unassignedSearch').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        const items = document.querySelectorAll('#unassignedList .user-item');
        items.forEach(item => {
            const searchContent = item.getAttribute('data-search') || '';
            item.style.display = searchContent.includes(query) ? '' : 'none';
        });
    });

    document.getElementById('assignedSearch').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        const items = document.querySelectorAll('#assignedList .user-item');
        items.forEach(item => {
            const searchContent = item.getAttribute('data-search') || '';
            item.style.display = searchContent.includes(query) ? '' : 'none';
        });
    });

    // Checkbox bulk action togglers
    const selectAllUnassigned = document.getElementById('selectAllUnassigned');
    selectAllUnassigned.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('#unassignedList .unassigned-select');
        checkboxes.forEach(cb => {
            if(cb.closest('.user-item').style.display !== 'none') {
                cb.checked = selectAllUnassigned.checked;
            }
        });
        toggleBulkButton('unassigned');
    });

    const selectAllAssigned = document.getElementById('selectAllAssigned');
    selectAllAssigned.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('#assignedList .assigned-select');
        checkboxes.forEach(cb => {
            if(cb.closest('.user-item').style.display !== 'none') {
                cb.checked = selectAllAssigned.checked;
            }
        });
        toggleBulkButton('assigned');
    });

    document.getElementById('unassignedList').addEventListener('change', function(e) {
        if(e.target.classList.contains('unassigned-select')) {
            toggleBulkButton('unassigned');
        }
    });

    document.getElementById('assignedList').addEventListener('change', function(e) {
        if(e.target.classList.contains('assigned-select')) {
            toggleBulkButton('assigned');
        }
    });

    function toggleBulkButton(pane) {
        if(pane === 'unassigned') {
            const checkedCount = document.querySelectorAll('#unassignedList .unassigned-select:checked').length;
            document.getElementById('bulkAssignBtn').disabled = checkedCount === 0;
        } else {
            const checkedCount = document.querySelectorAll('#assignedList .assigned-select:checked').length;
            document.getElementById('bulkRemoveBtn').disabled = checkedCount === 0;
        }
    }

    // Ajax assignments handler
    async function postAssignment(action, userIds) {
        const url = action === 'assign' ? '/admin/questionnaire/assign-create' : '/admin/questionnaire/assign-delete';
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    questionnaire_id: questionnaireId,
                    user_ids: userIds
                })
            });
            const data = await response.json();
            if(data.success) {
                showToast(action === 'assign' ? 'Users assigned successfully!' : 'Users unassigned successfully!');
                // Wait slightly and reload to keep lists perfectly updated and pristine
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message || 'Operation failed.', 'error');
            }
        } catch (error) {
            showToast('A connection error occurred.', 'error');
        }
    }

    function assignSingle(userId) {
        postAssignment('assign', [userId]);
    }

    function removeSingle(userId) {
        postAssignment('unassign', [userId]);
    }

    document.getElementById('bulkAssignBtn').addEventListener('click', function() {
        const checked = document.querySelectorAll('#unassignedList .unassigned-select:checked');
        const ids = Array.from(checked).map(cb => parseInt(cb.getAttribute('data-id')));
        postAssignment('assign', ids);
    });

    document.getElementById('bulkRemoveBtn').addEventListener('click', function() {
        const checked = document.querySelectorAll('#assignedList .assigned-select:checked');
        const ids = Array.from(checked).map(cb => parseInt(cb.getAttribute('data-id')));
        postAssignment('unassign', ids);
    });
</script>

<?php include __DIR__ . '/../../partials/public/footer.php'; ?>
