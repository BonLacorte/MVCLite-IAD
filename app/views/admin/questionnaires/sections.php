<?php include __DIR__ . '/../../partials/public/head.php'; ?>
<?php include __DIR__ . '/../../partials/public/nav.php'; ?>
<?php
$sectionById = [];
foreach ($sections as $sectionRow) {
    $sectionById[(int)$sectionRow['id']] = $sectionRow;
}
$deleteCsrfToken = Csrf::generate();

$childrenByParent = [];
foreach ($sections as $sectionRow) {
    $sectionId = (int)$sectionRow['id'];
    $parentId = (int)($sectionRow['parent_section_id'] ?? 0);

    if ($parentId > 0 && isset($sectionById[$parentId])) {
        $childrenByParent[$parentId][] = $sectionRow;
    } else {
        $childrenByParent[0][] = $sectionRow;
    }
}

$orderedSections = [];
$appendSectionTree = function ($parentId) use (&$appendSectionTree, &$childrenByParent, &$orderedSections) {
    foreach ($childrenByParent[$parentId] ?? [] as $sectionRow) {
        $sectionId = (int)$sectionRow['id'];
        $orderedSections[] = $sectionRow;
        $appendSectionTree($sectionId);
    }
};
$appendSectionTree(0);

if (count($orderedSections) < count($sections)) {
    $orderedIds = array_column($orderedSections, 'id');
    foreach ($sections as $sectionRow) {
        if (!in_array($sectionRow['id'], $orderedIds, true)) {
            $orderedSections[] = $sectionRow;
        }
    }
}

$sectionDepth = function ($section) use (&$sectionById) {
    $depth = 0;
    $parentId = (int)($section['parent_section_id'] ?? 0);
    $visited = [];

    while ($parentId > 0 && isset($sectionById[$parentId]) && !isset($visited[$parentId])) {
        $visited[$parentId] = true;
        $depth++;
        $parentId = (int)($sectionById[$parentId]['parent_section_id'] ?? 0);
    }

    return $depth;
};
?>

<link rel="stylesheet" href="/assets/css/admin.css">

<style>
    .step-indicator {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .step {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #94a3b8;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .step.active { color: var(--admin-primary); }
    .step.completed { color: #16a34a; }
    .step-number {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        font-size: 0.8rem;
    }
    .step.active .step-number { background: var(--admin-primary); color: white; }
    .step.completed .step-number { background: #16a34a; color: white; }
    .step-divider { flex: 1; height: 2px; background: #f1f5f9; }
    
    .layout-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
    }
    .form-section {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        align-self: start;
    }
    .list-section {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #334155;
    }
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-family: inherit;
        font-size: 0.95rem;
        transition: all 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--admin-primary);
        box-shadow: 0 0 0 3px rgba(5, 87, 65, 0.1);
    }
    .section-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        background: #f8fafc;
    }
    .section-item-title {
        font-weight: 700;
        color: #1e293b;
    }
    .conditional-field[hidden] {
        display: none !important;
    }
    .preview-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
    }
    .preview-switch {
        display: inline-flex;
        padding: 0.25rem;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #f8fafc;
    }
    .preview-switch button {
        border: 0;
        border-radius: 999px;
        padding: 0.45rem 0.9rem;
        background: transparent;
        color: #64748b;
        font-weight: 800;
        cursor: pointer;
    }
    .preview-switch button.active {
        background: var(--admin-primary);
        color: white;
    }
    .preview-pane[hidden] {
        display: none !important;
    }
    .section-preview-card {
        border: 1px solid #dbe7e2;
        border-radius: 10px;
        background: #f8fafc;
        margin-bottom: 0.75rem;
        overflow: hidden;
    }
    .section-preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: white;
    }
    .section-preview-body {
        padding: 0.9rem 1rem;
        color: #64748b;
        font-size: 0.9rem;
        border-top: 1px solid #e2e8f0;
    }
    .icon-action {
        width: 32px;
        height: 32px;
        border: 0;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--admin-primary);
        color: white;
        cursor: pointer;
        font-weight: 800;
        text-decoration: none;
    }
    .icon-action.delete {
        background: #ef4444;
    }
    .builder-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 10000;
        background: rgba(15, 23, 42, 0.55);
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .builder-modal.open {
        display: flex;
    }
    .builder-modal-box {
        width: 100%;
        max-width: 440px;
        background: white;
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: 0 22px 50px rgba(15, 23, 42, 0.22);
    }
</style>

<main style="min-height: 80vh; padding: 3rem 0; background-color: var(--admin-bg);">
    <div class="container" style="max-width: 1200px;">
        
        <div id="flash-msg" style="display:none;" class="flash-msg"></div>

        <nav aria-label="breadcrumb" style="margin-bottom: 1.5rem;">
            <a href="/admin/questionnaires" style="color: var(--admin-primary); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; opacity: 0.7;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                Back to Questionnaires
            </a>
        </nav>

        <div style="margin-bottom: 2rem;">
            <h1 style="color: var(--admin-primary); font-family: 'Montserrat', sans-serif; font-size: 2rem; font-weight: 800; margin: 0; letter-spacing: -0.02em;">
                Manage Sections
            </h1>
            <p style="color: #64748b; margin-top: 0.25rem;">Step 2 of 3: Define sections for <strong><?= htmlspecialchars($questionnaire['title']) ?></strong></p>
        </div>

        <div class="step-indicator">
            <a href="/admin/questionnaire/edit?questionnaire_id=<?= $questionnaire['id'] ?>" class="step completed" style="text-decoration:none;">
                <div class="step-number"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                Basic Info
            </a>
            <div class="step-divider"></div>
            <div class="step active">
                <div class="step-number">2</div>
                Sections
            </div>
            <div class="step-divider"></div>
            <div class="step">
                <div class="step-number">3</div>
                Questions
            </div>
        </div>

        <div class="layout-grid">
            <!-- Left: Add/Edit Form -->
            <div class="form-section">
                <h3 id="formTitle" style="margin-top: 0; margin-bottom: 1.5rem; color: #1e293b;">Add New Section</h3>
                <form id="sectionForm">
                    <input type="hidden" name="questionnaire_id" value="<?= $questionnaire['id'] ?>">
                    <input type="hidden" name="section_id" id="section_id" value="">
                    
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Section Title <span style="color:#ef4444">*</span></label>
                        <input type="text" name="title" id="title" class="form-control" required placeholder="e.g. Demographics">
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="section_type">Type <span style="color:#ef4444">*</span></label>
                        <select name="section_type" id="section_type" class="form-control" required>
                            <option value="parent">Parent</option>
                            <option value="child">Child</option>
                        </select>
                    </div>

                    <div class="form-group conditional-field" id="parentSectionGroup" style="margin-bottom: 1.5rem;" hidden>
                        <label for="parent_section_id">Parent Section <span style="color:#ef4444">*</span></label>
                        <select name="parent_section_id" id="parent_section_id" class="form-control">
                            <option value="">Select Parent Section</option>
                            <?php foreach($orderedSections as $parentOption): ?>
                                <?php $optionDepth = $sectionDepth($parentOption); ?>
                                <option value="<?= (int)$parentOption['id'] ?>">
                                    <?= htmlspecialchars(str_repeat('-- ', $optionDepth) . $parentOption['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div id="sErrors" style="color:#ef4444; font-size:0.9rem; margin-bottom:1rem; display:none;"></div>
                    
                    <button type="submit" id="saveBtn" class="action-btn" style="width: 100%; background:var(--admin-primary); color:white; padding:0.75rem; border-radius:8px; font-weight:700;">
                        Add Section
                    </button>
                    <button type="button" id="cancelEditBtn" onclick="resetForm()" class="action-btn" style="width: 100%; margin-top: 0.5rem; background:#f1f5f9; color:#64748b; padding:0.75rem; border-radius:8px; font-weight:700; display:none;">
                        Cancel Edit
                    </button>
                </form>
            </div>

            <!-- Right: Sections List -->
            <div class="list-section">
                <div class="preview-toolbar">
                    <h3 style="margin: 0; color: #1e293b;">Existing Sections</h3>
                    <div class="preview-switch" aria-label="Section preview display">
                        <button type="button" class="section-preview-btn active" data-preview="old">Old View</button>
                        <button type="button" class="section-preview-btn" data-preview="new">New View</button>
                    </div>
                </div>

                <div id="sectionsListOld" class="preview-pane">
                    <?php if(empty($sections)): ?>
                        <div style="padding: 2rem; text-align: center; color: #94a3b8; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
                            No sections defined yet. Add your first section on the left.
                        </div>
                    <?php else: ?>
                        <?php foreach($orderedSections as $index => $section): ?>
                            <?php
                                $parentId = (int)($section['parent_section_id'] ?? 0);
                                $parentTitle = ($parentId && isset($sectionById[$parentId])) ? $sectionById[$parentId]['title'] : '';
                                $depth = $sectionDepth($section);
                                $sectionTypeLabel = $parentId ? 'Child of ' . $parentTitle : 'Parent Section';
                            ?>
                            <div class="section-item" style="margin-left: <?= min($depth, 4) * 1.25 ?>rem;">
                                <div>
                                    <div class="section-item-title"><?= htmlspecialchars($section['title']) ?></div>
                                    <div style="font-size: 0.8rem; color: #64748b;">
                                        <?= htmlspecialchars($sectionTypeLabel) ?>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button type="button" onclick='editSection(<?= (int)$section['id'] ?>, <?= htmlspecialchars(json_encode($section['title'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>, <?= $parentId ?>)' class="icon-action" title="Edit section" aria-label="Edit section">
                                        &#9998;
                                    </button>
                                    <button type="button" onclick='openDeleteModal("section", <?= (int)$section['id'] ?>, <?= htmlspecialchars(json_encode($section['title'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>)' class="icon-action delete" title="Delete section" aria-label="Delete section">
                                        &#128465;
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div id="sectionsListNew" class="preview-pane" hidden>
                    <?php if(empty($sections)): ?>
                        <div style="padding: 2rem; text-align: center; color: #94a3b8; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
                            No sections defined yet. Add your first section on the left.
                        </div>
                    <?php else: ?>
                        <?php foreach($orderedSections as $section): ?>
                            <?php
                                $parentId = (int)($section['parent_section_id'] ?? 0);
                                $parentTitle = ($parentId && isset($sectionById[$parentId])) ? $sectionById[$parentId]['title'] : '';
                                $depth = $sectionDepth($section);
                                $sectionTypeLabel = $parentId ? 'Child of ' . $parentTitle : 'Parent Section';
                            ?>
                            <div class="section-preview-card" style="margin-left: <?= min($depth, 4) * 1.25 ?>rem;">
                                <div class="section-preview-header">
                                    <div>
                                        <div class="section-item-title"><?= htmlspecialchars($section['title']) ?></div>
                                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.15rem;"><?= htmlspecialchars($sectionTypeLabel) ?></div>
                                    </div>
                                    <div style="display:flex; gap:0.5rem;">
                                        <button type="button" onclick='editSection(<?= (int)$section['id'] ?>, <?= htmlspecialchars(json_encode($section['title'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>, <?= $parentId ?>)' class="icon-action" title="Edit section" aria-label="Edit section">&#9998;</button>
                                        <button type="button" onclick='openDeleteModal("section", <?= (int)$section['id'] ?>, <?= htmlspecialchars(json_encode($section['title'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>)' class="icon-action delete" title="Delete section" aria-label="Delete section">&#128465;</button>
                                    </div>
                                </div>
                                <div class="section-preview-body">
                                    User view preview: this section will appear as an expandable group containing its questions and child sections.
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
                    <?php if(!empty($sections)): ?>
                        <a href="/admin/questionnaire/questions?questionnaire_id=<?= $questionnaire['id'] ?>&section_id=<?= $orderedSections[0]['id'] ?>" class="action-btn" style="text-decoration:none; background:var(--admin-primary); color:white; padding:0.75rem 2rem; border-radius:10px; font-weight:700; display:flex; align-items:center; gap:0.5rem;">
                            Next: Add Questions
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<div id="builderDeleteModal" class="builder-modal" aria-hidden="true">
    <div class="builder-modal-box" role="dialog" aria-modal="true" aria-labelledby="builderDeleteTitle">
        <h3 id="builderDeleteTitle" style="margin:0 0 0.75rem; color:#0f172a; font-size:1.25rem;">Delete Section</h3>
        <p style="margin:0; color:#475569; line-height:1.55;">Are you sure you want to delete <strong id="builderDeleteName"></strong>?</p>
        <p style="margin:0.75rem 0 0; color:#64748b; font-size:0.9rem;">This uses a safe soft delete and will be blocked if the section still has child sections or questions.</p>
        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.5rem;">
            <button type="button" class="action-btn" style="background:#f1f5f9; color:#475569; padding:0.6rem 1.1rem; border-radius:8px; font-weight:800;" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="action-btn" style="background:#ef4444; color:white; padding:0.6rem 1.1rem; border-radius:8px; font-weight:800;" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>

<script>
const builderDeleteCsrfToken = '<?= htmlspecialchars($deleteCsrfToken, ENT_QUOTES, 'UTF-8') ?>';
let pendingDelete = { type: '', id: 0 };

function showFlash(msg, type) {
    const el = document.getElementById('flash-msg');
    el.textContent = msg; el.className = 'flash-msg flash-' + type; el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 4000);
}

function setSectionPreview(mode) {
    document.getElementById('sectionsListOld').hidden = mode !== 'old';
    document.getElementById('sectionsListNew').hidden = mode !== 'new';
    document.querySelectorAll('.section-preview-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.preview === mode);
    });
}

document.querySelectorAll('.section-preview-btn').forEach(btn => {
    btn.addEventListener('click', () => setSectionPreview(btn.dataset.preview));
});

function openDeleteModal(type, id, name) {
    pendingDelete = { type, id };
    document.getElementById('builderDeleteTitle').textContent = type === 'section' ? 'Delete Section' : 'Delete Item';
    document.getElementById('builderDeleteName').textContent = name;
    document.getElementById('builderDeleteModal').classList.add('open');
    document.getElementById('builderDeleteModal').setAttribute('aria-hidden', 'false');
}

function closeDeleteModal() {
    pendingDelete = { type: '', id: 0 };
    document.getElementById('builderDeleteModal').classList.remove('open');
    document.getElementById('builderDeleteModal').setAttribute('aria-hidden', 'true');
}

document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
    if (!pendingDelete.id) return;
    const fd = new FormData();
    fd.append('csrf_token', builderDeleteCsrfToken);
    fd.append('section_id', pendingDelete.id);

    fetch('/admin/questionnaire/sections/delete', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeDeleteModal();
                showFlash(data.message || 'Section deleted.', 'success');
                setTimeout(() => location.reload(), 800);
                return;
            }
            showFlash(data.message || 'Failed to delete section.', 'error');
            closeDeleteModal();
        })
        .catch(err => {
            console.error(err);
            showFlash('Server error occurred.', 'error');
            closeDeleteModal();
        });
});

function toggleParentSection() {
    const type = document.getElementById('section_type').value;
    const group = document.getElementById('parentSectionGroup');
    const parentSelect = document.getElementById('parent_section_id');
    const currentId = document.getElementById('section_id').value;

    group.hidden = type !== 'child';
    parentSelect.required = type === 'child';

    Array.from(parentSelect.options).forEach(option => {
        option.disabled = currentId && option.value === currentId;
    });

    if (type !== 'child') {
        parentSelect.value = '';
    }
}

function editSection(id, title, parentSectionId) {
    document.getElementById('formTitle').textContent = 'Edit Section';
    document.getElementById('section_id').value = id;
    document.getElementById('title').value = title;
    document.getElementById('section_type').value = parentSectionId ? 'child' : 'parent';
    document.getElementById('parent_section_id').value = parentSectionId || '';
    document.getElementById('saveBtn').textContent = 'Save Changes';
    document.getElementById('cancelEditBtn').style.display = 'block';
    toggleParentSection();
}

function resetForm() {
    document.getElementById('formTitle').textContent = 'Add New Section';
    document.getElementById('section_id').value = '';
    document.getElementById('title').value = '';
    document.getElementById('section_type').value = 'parent';
    document.getElementById('parent_section_id').value = '';
    document.getElementById('saveBtn').textContent = 'Add Section';
    document.getElementById('cancelEditBtn').style.display = 'none';
    toggleParentSection();
}

document.getElementById('section_type').addEventListener('change', toggleParentSection);
toggleParentSection();

document.getElementById('sectionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('saveBtn');
    const originalText = btn.textContent;
    btn.disabled = true; 
    btn.textContent = 'Saving...';
    
    const id = document.getElementById('section_id').value;
    const url = id ? '/admin/questionnaire/sections/edit' : '/admin/questionnaire/sections';
    
    fetch(url, { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showFlash('Section saved successfully.', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                btn.disabled = false;
                btn.textContent = originalText;
                const errEl = document.getElementById('sErrors');
                errEl.textContent = data.message || 'Error occurred.';
                errEl.style.display = 'block';
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.textContent = originalText;
            const errEl = document.getElementById('sErrors');
            errEl.textContent = 'Server error occurred.';
            errEl.style.display = 'block';
        });
});
</script>

<?php include __DIR__ . '/../../partials/public/footer.php'; ?>
