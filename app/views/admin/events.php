<?php include __DIR__ . '/../partials/public/head.php'; ?>
<?php include __DIR__ . '/../partials/public/nav.php'; ?>

<link rel="stylesheet" href="/assets/css/admin.css">

<style>
    /* Status Badge Overrides */
    .status-badge { padding: 0.35rem 0.85rem; border-radius: 999px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 0.4rem; }
    .status-active   { background: #dcfce7; color: #166534; }
    .status-active::before { content: ''; width: 6px; height: 6px; background: #16a34a; border-radius: 50%; }
    .status-finished { background: #e0f2fe; color: #0369a1; }
    .status-finished::before { content: ''; width: 6px; height: 6px; background: #0284c7; border-radius: 50%; }
    .status-inactive { background: #f3f4f6; color: #6b7280; }
    .status-inactive::before { content: ''; width: 6px; height: 6px; background: #94a3b8; border-radius: 50%; }
    
    .event-card-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; }
    .event-icon-box { width: 48px; height: 48px; background: var(--admin-primary-soft); color: var(--admin-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; }
</style>

<main style="min-height: 80vh; padding: 3rem 0; background-color: var(--admin-bg);">
    <div class="container" style="max-width: 1200px;">
        
        <div id="flash-msg" style="display:none;" class="flash-msg"></div>

        <nav aria-label="breadcrumb" style="margin-bottom: 1.5rem;">
            <a href="/dashboard" style="color: var(--admin-primary); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; opacity: 0.7;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                Back to Dashboard
            </a>
        </nav>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
            <div class="event-card-header">
                <div class="event-icon-box">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <h1 style="color: var(--admin-primary); font-family: 'Montserrat', sans-serif; font-size: 2.25rem; font-weight: 800; margin: 0; letter-spacing: -0.02em;">Event Management</h1>
                    <p style="color: #64748b; margin-top: 0.25rem; font-size: 0.95rem;">Organize and schedule institutional events and activities.</p>
                </div>
            </div>
            <a href="/admin/events/create" class="action-btn" style="width: auto; height: auto; padding: 0.65rem 1.5rem; background: var(--admin-primary); color: white; border-radius: 10px; font-weight: 700; gap: 0.5rem; text-decoration: none;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create New Event
            </a>
        </div>

        <div class="admin-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.9rem; color: #64748b;">
                    Show 
                    <select class="entries-select" id="entriesPerPage" onchange="renderTable()" style="padding: 0.4rem 0.75rem; border-radius: 8px;">
                        <option>10</option><option>25</option><option>50</option>
                    </select> 
                </div>
                <div style="position: relative;">
                    <svg style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="search-box" id="searchInput" oninput="filterTable()" placeholder="Search events..." style="padding-left: 2.5rem; width: 280px; border-radius: 10px;">
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="text-align:left;">Event Details</th>
                            <th>Status</th>
                            <th>Venue</th>
                            <th>Schedule</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; font-size: 0.85rem; color: #64748b;">
                <span id="tableInfo"></span>
                <div id="pagination" style="display: flex; gap: 0.5rem;"></div>
            </div>
        </div>
    </div>
</main>

<!-- CREATE / EDIT MODAL -->
<div id="eventModal" class="modal-overlay">
    <div class="modal-box glass-effect">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 id="modalTitle" style="margin: 0; color: var(--admin-primary); font-family: 'Montserrat', sans-serif; font-weight: 800;">Create Event</h2>
            <button onclick="closeModal()" style="background: rgba(0,0,0,0.05); border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer;">&times;</button>
        </div>
        <form id="eventForm">
            <input type="hidden" id="eventId" name="id">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Event Title *</label>
                <input type="text" name="title" id="ftitle" required placeholder="e.g. Annual General Assembly">
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Date *</label>
                    <input type="date" name="date" id="fdate" required>
                </div>
                <div class="form-group">
                    <label>Time *</label>
                    <input type="time" name="time" id="ftime" required>
                </div>
                <div class="form-group">
                    <label>Venue / Location</label>
                    <input type="text" name="venue" id="fvenue" placeholder="e.g. Grand Ballroom">
                </div>
                <div class="form-group">
                    <label>Publication Status</label>
                    <select name="status" id="fstatus">
                        <option value="active">Active / Upcoming</option>
                        <option value="finished">Completed</option>
                        <option value="inactive">Draft / Hidden</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Additional Details</label>
                <textarea name="description" id="fdescription" rows="4" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; background: #f8fafc;" placeholder="Describe the event..."></textarea>
            </div>
            <div id="formErrors" style="color: var(--admin-danger); font-size: 0.85rem; margin: 1rem 0; display: none;"></div>
            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
                <button type="button" onclick="closeModal()" class="action-btn" style="background: transparent; color: #64748b; width: auto; height: auto; padding: 0.65rem 1.5rem; box-shadow: none;">Cancel</button>
                <button type="submit" class="action-btn" id="submitBtn" style="background: var(--admin-primary); color: white; width: auto; height: auto; padding: 0.65rem 2rem; border-radius: 10px; font-weight: 700;">Save Event</button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE CONFIRM MODAL -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-box" style="max-width: 420px; text-align: center;">
        <div style="width: 64px; height: 64px; background: #fee2e2; color: #dc2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
        </div>
        <h3 style="margin: 0 0 0.75rem; color: #1e293b; font-size: 1.25rem; font-weight: 800;">Delete Event?</h3>
        <p id="deleteMsg" style="color: #64748b; margin-bottom: 2rem; font-size: 0.95rem;"></p>
        <div style="display: flex; justify-content: center; gap: 1rem;">
            <button onclick="document.getElementById('deleteModal').classList.remove('open')" class="action-btn" style="background: #f1f5f9; color: #64748b; width: auto; height: auto; padding: 0.65rem 1.5rem; box-shadow: none;">Cancel</button>
            <button id="confirmDeleteBtn" class="action-btn" style="background: #dc2626; color: white; width: auto; height: auto; padding: 0.65rem 2rem; border-radius: 10px; font-weight: 700;">Delete Permanently</button>
        </div>
    </div>
</div>

<script>
const rawEvents = <?= json_encode($events) ?>;
let allEvents   = [...rawEvents];
let editMode    = false;
let currentPage = 1;

const ICONS = {
    edit: `<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>`,
    delete: `<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>`
};

function getN() { return parseInt(document.getElementById('entriesPerPage').value); }

function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    allEvents = rawEvents.filter(e =>
        (e.title||'').toLowerCase().includes(q) ||
        (e.status||'').toLowerCase().includes(q) ||
        (e.venue||'').toLowerCase().includes(q)
    );
    currentPage = 1; renderTable();
}

function statusBadge(s) {
    const map = { active: 'status-active', finished: 'status-finished', inactive: 'status-inactive' };
    const labelMap = { active: 'Active', finished: 'Finished', inactive: 'Inactive' };
    return `<span class="status-badge ${map[s]||''}">${labelMap[s]||s}</span>`;
}

function formatDate(d) { 
    if (!d) return '-'; 
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(d).toLocaleDateString(undefined, options);
}

function formatTime(t) { 
    if (!t) return '-'; 
    const [h, m] = t.split(':'); 
    const hr = parseInt(h); 
    return `${hr > 12 ? hr - 12 : hr || 12}:${m} ${hr >= 12 ? 'PM' : 'AM'}`; 
}

function renderTable() {
    const n = getN(), total = allEvents.length;
    const totalPages = Math.ceil(total / n), start = (currentPage-1)*n;
    const slice = allEvents.slice(start, start+n);

    document.getElementById('tableBody').innerHTML = slice.length === 0
        ? '<tr><td colspan="5" style="padding:4rem;color:#94a3b8;text-align:center;">No events matching your criteria.</td></tr>'
        : slice.map(e => `<tr>
            <td>
                <div style="display: flex; flex-direction: column;">
                    <span style="font-weight: 700; color: #1e293b; font-size: 1rem;">${e.title}</span>
                    <span style="font-size: 0.75rem; color: #64748b; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; max-width: 300px;">${e.description || 'No description provided.'}</span>
                </div>
            </td>
            <td>${statusBadge(e.status)}</td>
            <td>
                <div style="display: flex; align-items: center; gap: 0.4rem; color: #475569;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>${e.venue||'TBA'}</span>
                </div>
            </td>
            <td>
                <div style="display: flex; flex-direction: column;">
                    <span style="font-weight: 600; color: #1e293b;">${formatDate(e.date)}</span>
                    <span style="font-size: 0.75rem; color: #64748b;">${formatTime(e.time)}</span>
                </div>
            </td>
            <td>
                <div style="display: flex; gap: 4px;">
                    <a class="action-btn btn-view" title="View Event" href="/admin/events/view?id=${e.id}" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></a><a class="action-btn btn-edit" title="Edit Event" href="/admin/events/edit?id=${e.id}" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">${ICONS.edit}</a>
                    <button class="action-btn btn-delete" title="Delete Event" onclick="confirmDelete(${e.id},'${(e.title||'').replace(/'/g,"\\'")}')">${ICONS.delete}</button>
                </div>
            </td>
        </tr>`).join('');

    document.getElementById('tableInfo').textContent = `Showing ${total===0?0:start+1}—${Math.min(start+n,total)} of ${total} events`;
    const pg = document.getElementById('pagination'); pg.innerHTML = '';
    const addBtn = (lbl, page, dis) => {
        const b = document.createElement('button'); b.className='page-btn'+(page===currentPage?' active':'');
        b.innerHTML=lbl; b.disabled=dis; b.onclick=()=>{currentPage=page;renderTable();}; pg.appendChild(b);
    };
    addBtn('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>', currentPage-1, currentPage===1);
    for(let i=1;i<=totalPages;i++) addBtn(i,i,false);
    addBtn('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>', currentPage+1, currentPage===totalPages||totalPages===0);
}

function openModal(mode, event = null) {
    editMode = mode === 'edit';
    document.getElementById('eventModal').classList.add('open');
    document.getElementById('modalTitle').textContent = editMode ? 'Update Event Details' : 'Register New Event';
    document.getElementById('submitBtn').textContent  = editMode ? 'Save Changes' : 'Create Event';
    const errEl = document.getElementById('formErrors');
    errEl.textContent = '';
    errEl.style.display = 'none';
    document.getElementById('eventForm').reset();
    if (editMode && event) {
        document.getElementById('eventId').value     = event.id;
        document.getElementById('ftitle').value      = event.title || '';
        document.getElementById('fdate').value       = event.date || '';
        document.getElementById('ftime').value       = event.time || '';
        document.getElementById('fvenue').value      = event.venue || '';
        document.getElementById('fstatus').value     = event.status || 'active';
        document.getElementById('fdescription').value= event.description || '';
    }
}

function closeModal() { document.getElementById('eventModal').classList.remove('open'); }

document.getElementById('eventForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Saving...';
    
    const url = editMode ? '/admin/events/update' : '/admin/events/store';
    fetch(url, { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(data => {
            if (data.success) { 
                showFlash(data.message, 'success'); 
                closeModal(); 
                setTimeout(() => location.reload(), 1000); 
            } else {
                btn.disabled = false;
                btn.textContent = editMode ? 'Save Changes' : 'Create Event';
                const errEl = document.getElementById('formErrors');
                errEl.textContent = data.message || 'Validation failed.';
                errEl.style.display = 'block';
            }
        });
});

let deleteId = null;
function confirmDelete(id, title) {
    deleteId = id;
    document.getElementById('deleteMsg').innerHTML = `Are you sure you want to permanently delete the event <strong>"${title}"</strong>? This action cannot be undone.`;
    document.getElementById('deleteModal').classList.add('open');
}
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    const fd = new FormData(); fd.append('id', deleteId);
    fetch('/admin/events/delete', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            document.getElementById('deleteModal').classList.remove('open');
            if (data.success) { showFlash('Event removed from records.', 'success'); setTimeout(() => location.reload(), 1000); }
            else showFlash(data.message, 'error');
        });
});

function showFlash(msg, type) {
    const el = document.getElementById('flash-msg');
    el.textContent = msg; el.className = 'flash-msg flash-' + type; el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 4000);
}

renderTable();
</script>

<?php include __DIR__ . '/../partials/public/footer.php'; ?>
