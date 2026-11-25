<?php include __DIR__ . '/../../partials/public/head.php'; ?>
<?php include __DIR__ . '/../../partials/public/nav.php'; ?>

<link rel="stylesheet" href="/assets/css/admin.css">

<style>
    /* Questionnaire Specific Styles */
    .status-badge { padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
    .status-active   { background: #dcfce7; color: #166534; }
    .status-draft    { background: #fef9c3; color: #854d0e; }
    .status-inactive { background: #f3f4f6; color: #6b7280; }
    .status-archived { background: #ede9fe; color: #6d28d9; }
    
    .progress-pill { display: inline-flex; align-items: center; padding: 0.35rem 0.7rem; border-radius: 999px; background: #f1f5f9; color: #64748b; font-size: 0.72rem; font-weight: 700; white-space: nowrap; }
    .progress-pill.has-progress { background: #dcfce7; color: #166534; }
    
    .section-title-wrap { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem; }
    .section-icon { width: 40px; height: 40px; background: var(--admin-primary-soft); color: var(--admin-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    .questionnaire-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 1rem; flex-wrap: wrap; }
    .questionnaire-table-controls { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap; }
    .questionnaire-search-wrap { position: relative; width: min(100%, 320px); flex: 0 1 320px; }
    .questionnaire-search-wrap .search-box { box-sizing: border-box; width: 100%; }
</style>

<main style="min-height: 80vh; padding: 3rem 0; background-color: var(--admin-bg);">
    <div class="container" style="max-width: 1250px;">
        
        <div id="flash-msg" style="display:none;" class="flash-msg"></div>

        <nav aria-label="breadcrumb" style="margin-bottom: 1.5rem;">
            <a href="/dashboard" style="color: var(--admin-primary); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; opacity: 0.7;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                Back to Dashboard
            </a>
        </nav>

        <div style="margin-bottom: 3rem; display:flex; justify-content:space-between; align-items:flex-end; gap:1.5rem; flex-wrap:wrap;">
            <div>
                <h1 style="color: var(--admin-primary); font-family: 'Montserrat', sans-serif; font-size: 2.25rem; font-weight: 800; margin: 0; letter-spacing: -0.02em;">BAICS Questionnaires</h1>
                <p style="color: #64748b; margin-top: 0.25rem;">Create and manage survey instruments and response data.</p>
            </div>
            <a href="/admin/questionnaires/export" class="btn-export" style="min-height:44px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export Questionnaires
            </a>
        </div>

        <!-- ===== GENERAL QUESTIONNAIRES ===== -->
        <div class="admin-card" style="margin-bottom: 2.5rem;">
            <div class="questionnaire-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </div>
                    <h2 style="margin: 0; font-size: 1.4rem; color: #1e293b; font-weight: 800;">General Questionnaires</h2>
                </div>
                <a href="/admin/questionnaire/create?type=general" class="action-btn" style="text-decoration: none; width: auto; height: auto; padding: 0.6rem 1.25rem; background: var(--admin-primary); color: white; border-radius: 10px; font-weight: 700; gap: 0.5rem; display: flex; align-items: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New Survey
                </a>
            </div>

            <div class="questionnaire-table-controls">
                <div style="display:flex;align-items:center;gap:0.75rem;font-size:0.9rem;color:#64748b;">
                    Show <select class="entries-select" id="gEntries" onchange="renderGeneral()"><option>10</option><option>25</option></select>
                </div>
                <div class="questionnaire-search-wrap">
                    <svg style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="search-box" id="gSearch" oninput="filterGeneral()" placeholder="Search..." style="padding-left: 2.5rem; border-radius: 10px;">
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead><tr>
                        <th style="text-align:left;">Questionnaire Title</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Created</th>
                        <th>Responses</th>
                        <th>Actions</th>
                    </tr></thead>
                    <tbody id="gTableBody"></tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:2rem;font-size:0.85rem;color:#64748b;">
                <span id="gTableInfo"></span>
                <div id="gPagination" style="display:flex;gap:0.5rem;"></div>
            </div>
        </div>

        <!-- ===== SUB QUESTIONNAIRES ===== -->
        <div class="admin-card">
            <div class="questionnaire-card-header">
                <div class="section-title-wrap">
                    <div class="section-icon" style="background: #f0f9ff; color: #0369a1;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2H2v10h10V2z"/><path d="M22 12H12v10h10V12z"/><path d="M12 22V12"/><path d="M2 12h10"/></svg>
                    </div>
                    <h2 style="margin: 0; font-size: 1.4rem; color: #1e293b; font-weight: 800;">Sub Questionnaires</h2>
                </div>
                <a href="/admin/questionnaire/create?type=sub" class="action-btn" style="text-decoration: none; width: auto; height: auto; padding: 0.6rem 1.25rem; background: #0369a1; color: white; border-radius: 10px; font-weight: 700; gap: 0.5rem; display: flex; align-items: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New Component
                </a>
            </div>

            <div class="questionnaire-table-controls">
                <div style="display:flex;align-items:center;gap:0.75rem;font-size:0.9rem;color:#64748b;">
                    Show <select class="entries-select" id="sEntries" onchange="renderSub()"><option>10</option><option>25</option></select>
                </div>
                <div class="questionnaire-search-wrap">
                    <svg style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" class="search-box" id="sSearch" oninput="filterSub()" placeholder="Search..." style="padding-left: 2.5rem; border-radius: 10px;">
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead><tr>
                        <th style="text-align:left;">Title</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Created</th>
                        <th>Responses</th>
                        <th>Actions</th>
                    </tr></thead>
                    <tbody id="sTableBody"></tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:2rem;font-size:0.85rem;color:#64748b;">
                <span id="sTableInfo"></span>
                <div id="sPagination" style="display:flex;gap:0.5rem;"></div>
            </div>
        </div>
    </div>
</main>


<!-- DELETE MODAL -->
<div id="qDeleteModal" class="modal-overlay">
    <div class="modal-box" style="max-width:420px; text-align:center;">
        <div style="width:64px; height:64px; background:#fee2e2; color:#dc2626; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
        </div>
        <h3 style="margin: 0 0 0.75rem; color: #1e293b; font-size: 1.25rem; font-weight: 800;">Remove Instrument?</h3>
        <p id="qDeleteMsg" style="color:#64748b; margin-bottom:2rem; font-size:0.95rem;"></p>
        <div style="display:flex;justify-content:center;gap:1rem;">
            <button onclick="document.getElementById('qDeleteModal').classList.remove('open')" class="action-btn" style="background:#f1f5f9; color:#64748b; width:auto; height:auto; padding:0.65rem 1.5rem; box-shadow:none;">Cancel</button>
            <button id="qConfirmDeleteBtn" class="action-btn" style="background:#dc2626; color:white; width:auto; height:auto; padding:0.65rem 2rem; border-radius:10px; font-weight:700;">Hide Questionnaire</button>
        </div>
    </div>
</div>

<script>
const rawGeneral = <?= json_encode($general, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const rawSub     = <?= json_encode($sub, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const deleteCsrfToken = <?= json_encode(Csrf::generate(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
let allGeneral   = [...rawGeneral], allSub = [...rawSub];
let gPage = 1, sPage = 1;
let qEditMode = false, currentType = 'general';

const ICONS = {
    view: `<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>`,
    edit: `<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>`,
    delete: `<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>`
};

function statusBadge(s) {
    const map = { active:'status-active', draft:'status-draft', inactive:'status-inactive', archived:'status-archived' };
    const badge = document.createElement('span');
    const status = String(s || '');
    badge.className = `status-badge ${map[status] || ''}`.trim();
    badge.textContent = status;
    return badge;
}

function progressStatus(q) {
    const label = document.createElement('span');
    const totalQuestions = parseInt(q.total_questions, 10) || 0;
    const totalRespondents = parseInt(q.total_respondents, 10) || 0;
    const expectedSlots = parseInt(q.expected_answer_slots, 10) || 0;
    const submittedSlots = parseInt(q.submitted_answer_slots, 10) || 0;
    const completionPercent = Number.parseFloat(q.completion_percent);

    label.className = 'progress-pill';

    if (totalQuestions === 0) {
        label.textContent = 'No questions yet';
        label.title = 'This questionnaire has no active questions to measure.';
    } else if (totalRespondents === 0) {
        label.textContent = 'No respondents assigned';
        label.title = `${totalQuestions} active question${totalQuestions === 1 ? '' : 's'}, but no respondents are assigned.`;
    } else if (submittedSlots === 0) {
        label.textContent = 'Awaiting submissions';
        label.title = `0 of ${expectedSlots} expected answer slots submitted`;
    } else {
        label.classList.add('has-progress');
        label.textContent = `${completionPercent.toFixed(1)}% submitted`;
        label.title = `${submittedSlots} of ${expectedSlots} expected answer slots submitted`;
    }

    return label;
}

function formatDate(d) { 
    if (!d) return '-'; 
    return new Date(d).toLocaleDateString(undefined, {year:'numeric',month:'short',day:'numeric'}); 
}

function createQuestionnaireRow(q) {
    const row = document.createElement('tr');
    const id = parseInt(q.id, 10) || 0;
    const title = String(q.title || '');
    const description = q.description ? String(q.description).substring(0, 40) + '...' : 'No description';

    const titleCell = document.createElement('td');
    const titleWrap = document.createElement('div');
    titleWrap.style.display = 'flex';
    titleWrap.style.flexDirection = 'column';

    const titleEl = document.createElement('span');
    titleEl.style.fontWeight = '700';
    titleEl.style.color = '#1e293b';
    titleEl.textContent = title;

    const descEl = document.createElement('span');
    descEl.style.fontSize = '0.75rem';
    descEl.style.color = '#94a3b8';
    descEl.textContent = description;

    titleWrap.appendChild(titleEl);
    titleWrap.appendChild(descEl);
    titleCell.appendChild(titleWrap);
    row.appendChild(titleCell);

    const statusCell = document.createElement('td');
    statusCell.appendChild(statusBadge(q.status));
    row.appendChild(statusCell);

    const progressCell = document.createElement('td');
    progressCell.appendChild(progressStatus(q));
    row.appendChild(progressCell);

    const createdCell = document.createElement('td');
    const created = document.createElement('span');
    created.style.fontWeight = '600';
    created.style.color = '#475569';
    created.textContent = formatDate(q.created_at);
    createdCell.appendChild(created);
    row.appendChild(createdCell);

    const responsesCell = document.createElement('td');
    const responses = document.createElement('span');
    responses.style.fontWeight = '700';
    responses.style.color = 'var(--admin-primary)';
    responses.textContent = q.total_respondents || 0;
    responsesCell.appendChild(responses);
    row.appendChild(responsesCell);

    const actionsCell = document.createElement('td');
    const actions = document.createElement('div');
    actions.style.display = 'flex';
    actions.style.gap = '4px';

    const viewLink = document.createElement('a');
    viewLink.href = `/admin/questionnaire/show?questionnaire_id=${id}`;
    viewLink.className = 'action-btn btn-view';
    viewLink.title = 'View Details';
    viewLink.style.textDecoration = 'none';
    viewLink.innerHTML = ICONS.view;

    const editLink = document.createElement('a');
    editLink.href = `/admin/questionnaire/edit?questionnaire_id=${id}`;
    editLink.className = 'action-btn btn-edit';
    editLink.title = 'Edit Structure';
    editLink.style.textDecoration = 'none';
    editLink.innerHTML = ICONS.edit;

    const deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.className = 'action-btn btn-delete';
    deleteButton.title = 'Delete Instrument';
    deleteButton.innerHTML = ICONS.delete;
    deleteButton.addEventListener('click', () => confirmQDelete(id, title));

    actions.appendChild(viewLink);
    actions.appendChild(editLink);
    actions.appendChild(deleteButton);
    actionsCell.appendChild(actions);
    row.appendChild(actionsCell);

    return row;
}

function createEmptyRow(message) {
    const row = document.createElement('tr');
    const cell = document.createElement('td');
    cell.colSpan = 6;
    cell.style.padding = '4rem';
    cell.style.color = '#94a3b8';
    cell.style.textAlign = 'center';
    cell.textContent = message;
    row.appendChild(cell);
    return row;
}

function renderRows(tbodyId, data, emptyMessage) {
    const tbody = document.getElementById(tbodyId);
    tbody.replaceChildren();

    if (!data.length) {
        tbody.appendChild(createEmptyRow(emptyMessage));
        return;
    }

    data.forEach(q => tbody.appendChild(createQuestionnaireRow(q)));
}

function renderPagination(containerId, total, page, setter, renderFn, n) {
    const pg = document.getElementById(containerId); pg.innerHTML = '';
    if (total === 0) {
        pg.style.display = 'none';
        return;
    }
    pg.style.display = 'flex';
    const totalPages = Math.ceil(total/n)||1;
    const addBtn = (lbl, p, dis, isStaticSvg = false) => {
        const b = document.createElement('button'); b.className='page-btn'+(p===page?' active':'');
        if (isStaticSvg) {
            b.innerHTML = lbl;
        } else {
            b.textContent = String(lbl);
        }
        b.disabled=dis; b.onclick=()=>{setter(p);renderFn();}; pg.appendChild(b);
    };
    addBtn('<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>', page-1, page===1, true);
    for(let i=1;i<=totalPages;i++) addBtn(i,i,false);
    addBtn('<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>', page+1, page===totalPages, true);
}

function renderGeneral() {
    const n = parseInt(document.getElementById('gEntries').value);
    const total = allGeneral.length;
    const totalPages = Math.max(1, Math.ceil(total / n));
    if (gPage > totalPages) gPage = totalPages;
    const start = (gPage-1)*n;
    const slice = allGeneral.slice(start, start+n);
    renderRows('gTableBody', slice, 'No questionnaires available.');
    document.getElementById('gTableInfo').textContent = `${total} general surveys found`;
    renderPagination('gPagination', total, gPage, p=>gPage=p, renderGeneral, n);
}

function renderSub() {
    const n = parseInt(document.getElementById('sEntries').value);
    const total = allSub.length;
    const totalPages = Math.max(1, Math.ceil(total / n));
    if (sPage > totalPages) sPage = totalPages;
    const start = (sPage-1)*n;
    const slice = allSub.slice(start, start+n);
    renderRows('sTableBody', slice, 'No sub-questionnaires available.');
    document.getElementById('sTableInfo').textContent = `${total} components found`;
    renderPagination('sPagination', total, sPage, p=>sPage=p, renderSub, n);
}

function filterGeneral() {
    const q = document.getElementById('gSearch').value.toLowerCase();
    allGeneral = rawGeneral.filter(e => (e.title||'').toLowerCase().includes(q) || (e.status||'').toLowerCase().includes(q));
    gPage = 1; renderGeneral();
}

function filterSub() {
    const q = document.getElementById('sSearch').value.toLowerCase();
    allSub = rawSub.filter(e => (e.title||'').toLowerCase().includes(q) || (e.status||'').toLowerCase().includes(q));
    sPage = 1; renderSub();
}



let qDeleteId = null;
function confirmQDelete(id, title) {
    qDeleteId = id;
    const msg = document.getElementById('qDeleteMsg');
    const titleEl = document.createElement('strong');
    titleEl.textContent = `"${title}"`;
    msg.replaceChildren(
        document.createTextNode('Are you sure you want to delete '),
        titleEl,
        document.createTextNode('? This questionnaire will be hidden from active lists. Existing response records are not permanently deleted by this action.')
    );
    document.getElementById('qDeleteModal').classList.add('open');
}
document.getElementById('qConfirmDeleteBtn').addEventListener('click', function() {
    const fd = new FormData();
    fd.append('id', qDeleteId);
    fd.append('csrf_token', deleteCsrfToken);
    fetch('/admin/questionnaire/delete', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            document.getElementById('qDeleteModal').classList.remove('open');
            if (data.success) { showFlash('Instrument deleted.', 'success'); setTimeout(() => location.reload(), 1000); }
            else showFlash(data.message, 'error');
        });
});

function showFlash(msg, type) {
    const el = document.getElementById('flash-msg');
    el.textContent = msg; el.className = 'flash-msg flash-' + type; el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 4000);
}

renderGeneral(); renderSub();
</script>

<?php include __DIR__ . '/../../partials/public/footer.php'; ?>
