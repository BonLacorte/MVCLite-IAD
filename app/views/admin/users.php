<?php include __DIR__ . '/../partials/public/head.php'; ?>
<?php include __DIR__ . '/../partials/public/nav.php'; ?>
<?php
$usersJson = json_encode(
    $users ?? [],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE
);
if ($usersJson === false) {
    $usersJson = '[]';
}
?>

<link rel="stylesheet" href="/assets/css/admin.css">

<style>
    .users-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .users-page-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .btn-export {
        background: #16a34a;
        color: white;
        border: none;
        padding: 0.7rem 1.25rem;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        text-decoration: none;
        box-shadow: var(--admin-shadow-sm);
    }
    .btn-export:hover {
        background: #15803d;
        transform: translateY(-1px);
        box-shadow: var(--admin-shadow-md);
    }
    .users-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    .users-search-wrap {
        position: relative;
        min-width: 260px;
    }
    .users-search-wrap svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }
    .users-search-wrap input {
        padding-left: 2.5rem;
        width: 100%;
        height: 42px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .users-name-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 190px;
    }
    .users-avatar {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: var(--admin-primary-soft);
        color: var(--admin-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        flex: 0 0 auto;
    }
    .users-primary {
        display: block;
        color: #0f172a;
        font-weight: 800;
        line-height: 1.25;
    }
    .users-secondary {
        display: block;
        color: #64748b;
        font-size: 0.78rem;
        margin-top: 0.2rem;
    }
    .users-actions-cell {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        white-space: nowrap;
    }
    .users-actions-cell .action-btn {
        margin: 0;
    }
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        width: fit-content;
    }
    .users-empty {
        padding: 3rem 1rem;
        color: #94a3b8;
        text-align: center;
        font-style: italic;
    }
    .delete-user-name {
        font-weight: 800;
        color: #0f172a;
    }
    @media (max-width: 760px) {
        .users-page-header {
            align-items: stretch;
            flex-direction: column;
        }
        .users-page-actions {
            justify-content: flex-start;
        }
        .users-search-wrap {
            width: 100%;
        }
    }
</style>

<main style="min-height: 80vh; padding: 3rem 0; background-color: var(--admin-bg);">
    <div class="container" style="max-width: 1250px;">
        <div id="flash-msg" style="display:none;" class="flash-msg"></div>

        <nav aria-label="breadcrumb" style="margin-bottom: 1.5rem;">
            <a href="/dashboard" class="page-back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to Dashboard
            </a>
        </nav>

        <div class="users-page-header">
            <div>
                <h1 style="color: var(--admin-primary); font-family: 'Montserrat', sans-serif; font-size: 2.25rem; font-weight: 800; margin: 0; letter-spacing: -0.02em;">User Directory</h1>
                <p style="color: #64748b; margin-top: 0.25rem; font-size: 0.95rem;">Manage and monitor system users and access levels.</p>
            </div>
            <div class="users-page-actions">
                <a class="btn-export" href="/admin/users/export">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export Users
                </a>
                <a href="/admin/users/create" class="action-btn" style="width: auto; height: auto; padding: 0.7rem 1.35rem; background: var(--admin-primary); color: white; border-radius: 10px; font-weight: 800; gap: 0.5rem; text-decoration: none;">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add New User
                </a>
            </div>
        </div>

        <div class="admin-card">
            <div class="users-toolbar">
                <label style="display:flex; align-items:center; gap:0.75rem; color:#64748b; font-size:0.9rem; font-weight:600;">
                    Show
                    <select class="entries-select" id="entriesPerPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    records
                </label>
                <div class="users-search-wrap">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="search-box" id="searchInput" placeholder="Search users...">
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Central Office</th>
                            <th>Region Center</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-top:2rem; font-size:0.85rem; color:#64748b; font-weight:600; flex-wrap:wrap;">
                <span id="tableInfo"></span>
                <div id="pagination" style="display:flex; gap:0.5rem; flex-wrap:wrap;"></div>
            </div>
        </div>
    </div>
</main>

<div id="deleteModal" class="modal-overlay">
    <div class="modal-box" style="max-width: 430px; text-align: center;">
        <div style="width:64px; height:64px; background:#fee2e2; color:#dc2626; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
        </div>
        <h3 style="margin:0 0 0.75rem; color:#1e293b; font-size:1.25rem; font-weight:800;">Delete User?</h3>
        <p style="color:#64748b; margin-bottom:2rem; font-size:0.95rem; line-height:1.5;">
            This will hide <span id="deleteUserName" class="delete-user-name"></span> from active user lists.
        </p>
        <div style="display:flex; justify-content:center; gap:1rem;">
            <button type="button" id="cancelDeleteBtn" class="action-btn" style="background:#f1f5f9; color:#64748b; width:auto; height:auto; padding:0.7rem 1.5rem; box-shadow:none;">Cancel</button>
            <button type="button" id="confirmDeleteBtn" class="action-btn" style="background:#dc2626; color:white; width:auto; height:auto; padding:0.7rem 1.75rem; border-radius:10px; font-weight:800;">Delete User</button>
        </div>
    </div>
</div>

<script>
const rawUsers = <?= $usersJson ?>;
let filteredUsers = [...rawUsers];
let currentPage = 1;
let deleteId = null;

const icons = {
    view: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>',
    edit: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>',
    delete: '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>',
    previous: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>',
    next: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>'
};

function valueOrDash(value) {
    const normalized = String(value ?? '').trim();
    return normalized || '-';
}

function initials(name) {
    return String(name || 'U').trim().split(/\s+/).slice(0, 2).map(part => part.charAt(0).toUpperCase()).join('') || 'U';
}

function setText(parent, selector, text) {
    parent.querySelector(selector).textContent = text;
}

function createActionButton(type, user) {
    const id = encodeURIComponent(user.id);
    if (type === 'delete') {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'action-btn btn-delete';
        button.title = 'Delete User';
        button.innerHTML = icons.delete;
        button.addEventListener('click', () => confirmDelete(user));
        return button;
    }

    const link = document.createElement('a');
    link.className = `action-btn btn-${type}`;
    link.title = type === 'view' ? 'View User' : 'Edit User';
    link.href = `/admin/users/${type}?id=${id}`;
    link.style.textDecoration = 'none';
    link.innerHTML = icons[type];
    return link;
}

function createUserRow(user) {
    const row = document.createElement('tr');

    const nameCell = document.createElement('td');
    const nameWrap = document.createElement('div');
    nameWrap.className = 'users-name-cell';
    const avatar = document.createElement('span');
    avatar.className = 'users-avatar';
    avatar.textContent = initials(user.name);
    const nameText = document.createElement('span');
    const primary = document.createElement('span');
    primary.className = 'users-primary';
    primary.textContent = valueOrDash(user.name);
    const secondary = document.createElement('span');
    secondary.className = 'users-secondary';
    secondary.textContent = `ID #${user.id}`;
    nameText.append(primary, secondary);
    nameWrap.append(avatar, nameText);
    nameCell.appendChild(nameWrap);
    row.appendChild(nameCell);

    [
        user.email,
        user.position,
        user.department_office_unit,
        user.for_central_office,
        user.for_region_center
    ].forEach(value => {
        const cell = document.createElement('td');
        cell.textContent = valueOrDash(value);
        row.appendChild(cell);
    });

    const actionCell = document.createElement('td');
    const actions = document.createElement('span');
    actions.className = 'users-actions-cell';
    actions.appendChild(createActionButton('view', user));
    actions.appendChild(createActionButton('edit', user));
    actions.appendChild(createActionButton('delete', user));
    actionCell.appendChild(actions);
    row.appendChild(actionCell);
    return row;
}

function filterUsers() {
    const query = document.getElementById('searchInput').value.trim().toLowerCase();
    filteredUsers = rawUsers.filter(user => [
        user.name,
        user.email,
        user.position,
        user.department_office_unit,
        user.for_central_office,
        user.for_region_center
    ].some(value => String(value ?? '').toLowerCase().includes(query)));
    currentPage = 1;
    renderTable();
}

function renderTable() {
    const perPage = Number(document.getElementById('entriesPerPage').value) || 10;
    const total = filteredUsers.length;
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    currentPage = Math.min(currentPage, totalPages);
    const start = (currentPage - 1) * perPage;
    const pageUsers = filteredUsers.slice(start, start + perPage);
    const tbody = document.getElementById('tableBody');
    tbody.textContent = '';

    if (pageUsers.length === 0) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 7;
        cell.className = 'users-empty';
        cell.textContent = 'No users found.';
        row.appendChild(cell);
        tbody.appendChild(row);
    } else {
        pageUsers.forEach(user => tbody.appendChild(createUserRow(user)));
    }

    const end = total === 0 ? 0 : Math.min(start + perPage, total);
    document.getElementById('tableInfo').textContent = `Showing ${total === 0 ? 0 : start + 1} to ${end} of ${total} active users`;
    renderPagination(totalPages);
}

function renderPagination(totalPages) {
    const pagination = document.getElementById('pagination');
    pagination.textContent = '';

    const addButton = (content, page, disabled = false, active = false) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `page-btn${active ? ' active' : ''}`;
        button.innerHTML = content;
        button.disabled = disabled;
        button.addEventListener('click', () => {
            currentPage = page;
            renderTable();
        });
        pagination.appendChild(button);
    };

    addButton(icons.previous, Math.max(1, currentPage - 1), currentPage === 1);
    for (let page = 1; page <= totalPages; page++) {
        if (page === 1 || page === totalPages || Math.abs(page - currentPage) <= 1) {
            addButton(String(page), page, false, page === currentPage);
        } else if (Math.abs(page - currentPage) === 2) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.style.padding = '0 0.25rem';
            pagination.appendChild(dots);
        }
    }
    addButton(icons.next, Math.min(totalPages, currentPage + 1), currentPage === totalPages);
}

function confirmDelete(user) {
    deleteId = user.id;
    document.getElementById('deleteUserName').textContent = valueOrDash(user.name);
    document.getElementById('deleteModal').classList.add('open');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('open');
    deleteId = null;
}

document.getElementById('entriesPerPage').addEventListener('change', () => {
    currentPage = 1;
    renderTable();
});
document.getElementById('searchInput').addEventListener('input', filterUsers);
document.getElementById('cancelDeleteBtn').addEventListener('click', closeDeleteModal);
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (!deleteId) return;
    const button = this;
    button.disabled = true;
    button.textContent = 'Deleting...';

    const form = new FormData();
    form.append('id', deleteId);

    fetch('/admin/users/delete', { method: 'POST', body: form })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showFlash('User has been removed from the active list.', 'success');
                setTimeout(() => location.reload(), 900);
            } else {
                showFlash(data.message || 'Deletion failed.', 'error');
                button.disabled = false;
                button.textContent = 'Delete User';
            }
            closeDeleteModal();
        })
        .catch(() => {
            showFlash('Deletion failed.', 'error');
            button.disabled = false;
            button.textContent = 'Delete User';
            closeDeleteModal();
        });
});

function showFlash(message, type) {
    const element = document.getElementById('flash-msg');
    element.textContent = message;
    element.className = 'flash-msg flash-' + type;
    element.style.opacity = '1';
    element.style.display = 'block';
    setTimeout(() => {
        element.style.opacity = '0';
        element.style.transition = 'opacity 0.35s ease';
        setTimeout(() => element.style.display = 'none', 400);
    }, 3500);
}

renderTable();
</script>

<?php include __DIR__ . '/../partials/public/footer.php'; ?>
