<?php include __DIR__ . '/../../partials/public/head.php'; ?>
<?php include __DIR__ . '/../../partials/public/nav.php'; ?>

<?php
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$isEditMode = !empty($selectedQuestionnaire);
$formType = $isEditMode
    ? ($selectedQuestionnaire['type'] ?? 'general')
    : ($createType ?? 'general');
if (!in_array($formType, ['general', 'sub'], true)) {
    $formType = 'general';
}
$createHeading = $formType === 'sub'
    ? 'Create New Sub Questionnaire / Component'
    : 'Create New General Questionnaire / Survey';
$typeHelper = $formType === 'sub'
    ? 'You are creating a sub questionnaire/component. Link it to a General Questionnaire below.'
    : 'You are creating a general questionnaire/survey.';
$questionnaireId = $isEditMode ? (int)($selectedQuestionnaire['id'] ?? 0) : 0;
$questionnaireTitle = $escape($selectedQuestionnaire['title'] ?? '');
$questionnaireDescription = $escape($selectedQuestionnaire['description'] ?? '');
$selectedParentQuestionnaireId = (int)($selectedQuestionnaire['parent_questionnaire_id'] ?? 0);
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
    .step.active {
        color: var(--admin-primary);
    }
    .step.completed {
        color: #16a34a;
    }
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
    .step.active .step-number {
        background: var(--admin-primary);
        color: white;
    }
    .step.completed .step-number {
        background: #16a34a;
        color: white;
    }
    .step-divider {
        flex: 1;
        height: 2px;
        background: #f1f5f9;
    }
    .form-section {
        background: white;
        padding: 2.5rem;
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
    .preview-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
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
    .builder-preview {
        margin-top: 1.5rem;
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #e2e8f0;
    }
    .old-preview-table {
        width: 100%;
        border-collapse: collapse;
        color: #064e3b;
    }
    .old-preview-table th,
    .old-preview-table td {
        border: 1px solid rgba(5, 87, 65, 0.55);
        padding: 0.85rem;
        text-align: left;
    }
    .new-preview-card {
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
        background: #f8fafc;
    }
</style>

<main style="min-height: 80vh; padding: 3rem 0; background-color: var(--admin-bg);">
    <div class="container" style="max-width: 900px;">
        
        <div id="flash-msg" style="display:none;" class="flash-msg"></div>

        <nav aria-label="breadcrumb" style="margin-bottom: 1.5rem;">
            <a href="/admin/questionnaires" style="color: var(--admin-primary); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; opacity: 0.7;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                Back to Questionnaires
            </a>
        </nav>

        <div style="margin-bottom: 2rem;">
            <h1 style="color: var(--admin-primary); font-family: 'Montserrat', sans-serif; font-size: 2rem; font-weight: 800; margin: 0; letter-spacing: -0.02em;">
                <?= $isEditMode ? 'Edit Questionnaire' : htmlspecialchars($createHeading) ?>
            </h1>
            <p style="color: #64748b; margin-top: 0.25rem;">Step 1 of 3: Define basic information</p>
        </div>

        <div class="step-indicator">
            <div class="step active">
                <div class="step-number">1</div>
                Basic Info
            </div>
            <div class="step-divider"></div>
            <div class="step">
                <div class="step-number">2</div>
                Sections
            </div>
            <div class="step-divider"></div>
            <div class="step">
                <div class="step-number">3</div>
                Questions
            </div>
        </div>

        <div class="form-section">
            <form id="qForm">
                <?= Csrf::input(); ?>
                <?php if ($isEditMode): ?>
                    <input type="hidden" name="questionnaire_id" id="qId" value="<?= $questionnaireId ?>">
                <?php endif; ?>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label>Questionnaire Title <span style="color:#ef4444">*</span></label>
                    <input type="text" name="title" id="title" class="form-control" required 
                           value="<?= $questionnaireTitle ?>"
                           placeholder="e.g. Annual Community Satisfaction Survey">
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label>Description / Briefing</label>
                    <textarea name="description" id="description" class="form-control" rows="4" 
                              placeholder="Describe the purpose of this questionnaire..."><?= $questionnaireDescription ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label>Questionnaire Type</label>
                        <select name="type" id="type" class="form-control" onchange="toggleLinkedGeneral()">
                            <option value="general" <?= $formType === 'general' ? 'selected' : '' ?>>General</option>
                            <option value="sub" <?= $formType === 'sub' ? 'selected' : '' ?>>Sub-Component</option>
                        </select>
                        <?php if (!$isEditMode): ?>
                            <p id="typeHelper" style="font-size: 0.8rem; color: #64748b; margin: 0.35rem 0 0;"><?= htmlspecialchars($typeHelper) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Visibility Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="draft" <?= ($selectedQuestionnaire['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft - Private</option>
                            <option value="active" <?= ($selectedQuestionnaire['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active - Public</option>
                            <option value="inactive" <?= ($selectedQuestionnaire['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive - Closed</option>
                            <option value="archived" <?= ($selectedQuestionnaire['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="linkedGeneralDiv" style="margin-bottom: 1.5rem; display: <?= $formType === 'sub' ? 'block' : 'none' ?>;">
                    <label>Link to General Questionnaire</label>
                    <select name="linked_general_questionnaire_id" id="linked_general_questionnaire_id" class="form-control">
                        <option value="">-- Select General Questionnaire --</option>
                        <?php foreach($generalQuestionnaires as $gq): ?>
                            <?php $generalQuestionnaireId = (int)($gq['id'] ?? 0); ?>
                            <option value="<?= $generalQuestionnaireId ?>" <?= $selectedParentQuestionnaireId === $generalQuestionnaireId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($gq['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p style="font-size: 0.8rem; color: #64748b; margin-top: 0.25rem;">Required if this is a sub-component of a larger assessment.</p>
                </div>

                <div id="qErrors" style="color:#ef4444; font-size:0.9rem; margin-bottom:1.5rem; display:none;"></div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 1.5rem; margin-top: 1rem;">
                    <a href="/admin/questionnaires" class="action-btn admin-text-btn" style="background:transparent; color:#64748b; text-decoration:none; box-shadow:none;">Cancel</a>
                    
                    <button type="submit" id="saveBtn" class="action-btn admin-text-btn" style="background:var(--admin-primary); color:white; padding-left:1.5rem; padding-right:1.5rem;">
                        Save & Continue
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                    </button>
                </div>
            </form>
        </div>

        <div class="builder-preview">
            <div class="preview-toolbar">
                <h3 style="margin:0; color:#1e293b;">Questionnaire Preview</h3>
                <div class="preview-switch" aria-label="Questionnaire preview display">
                    <button type="button" class="edit-preview-btn active" data-preview="old">Old View</button>
                    <button type="button" class="edit-preview-btn" data-preview="new">New View</button>
                </div>
            </div>

            <div id="editPreviewOld" class="preview-pane">
                <table class="old-preview-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td id="oldPreviewTitle"><?= $questionnaireTitle ?: 'Untitled Questionnaire' ?></td>
                            <td id="oldPreviewType"><?= htmlspecialchars(ucfirst($formType)) ?></td>
                            <td id="oldPreviewStatus"><?= htmlspecialchars(ucfirst($selectedQuestionnaire['status'] ?? 'draft')) ?></td>
                            <td id="oldPreviewDescription"><?= $questionnaireDescription ?: 'No description yet.' ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="editPreviewNew" class="preview-pane" hidden>
                <div class="new-preview-card">
                    <div style="display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; flex-wrap:wrap;">
                        <div>
                            <h3 id="newPreviewTitle" style="margin:0; color:var(--admin-primary); font-size:1.35rem;"><?= $questionnaireTitle ?: 'Untitled Questionnaire' ?></h3>
                            <p id="newPreviewDescription" style="margin:0.45rem 0 0; color:#64748b; font-weight:600;"><?= $questionnaireDescription ?: 'No description yet.' ?></p>
                        </div>
                        <span id="newPreviewStatus" style="background:#e6f4f0; color:#055741; padding:0.35rem 0.7rem; border-radius:999px; font-weight:800; font-size:0.8rem;"><?= htmlspecialchars(ucfirst($selectedQuestionnaire['status'] ?? 'draft')) ?></span>
                    </div>
                    <div id="newPreviewType" style="margin-top:1rem; color:#475569; font-weight:800; font-size:0.85rem;"><?= htmlspecialchars(ucfirst($formType)) ?> questionnaire builder preview</div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function toggleLinkedGeneral() {
    const type = document.getElementById('type').value;
    document.getElementById('linkedGeneralDiv').style.display = type === 'sub' ? 'block' : 'none';
}

function showFlash(msg, type) {
    const el = document.getElementById('flash-msg');
    el.textContent = msg; el.className = 'flash-msg flash-' + type; el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 4000);
}

function setEditPreview(mode) {
    document.getElementById('editPreviewOld').hidden = mode !== 'old';
    document.getElementById('editPreviewNew').hidden = mode !== 'new';
    document.querySelectorAll('.edit-preview-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.preview === mode);
    });
}

function updateBuilderPreview() {
    const title = document.getElementById('title').value.trim() || 'Untitled Questionnaire';
    const description = document.getElementById('description').value.trim() || 'No description yet.';
    const type = document.getElementById('type').value || 'general';
    const status = document.getElementById('status').value || 'draft';
    const typeLabel = type === 'sub' ? 'Sub-Component' : 'General';
    const statusLabel = status.charAt(0).toUpperCase() + status.slice(1);

    document.getElementById('oldPreviewTitle').textContent = title;
    document.getElementById('oldPreviewDescription').textContent = description;
    document.getElementById('oldPreviewType').textContent = typeLabel;
    document.getElementById('oldPreviewStatus').textContent = statusLabel;
    document.getElementById('newPreviewTitle').textContent = title;
    document.getElementById('newPreviewDescription').textContent = description;
    document.getElementById('newPreviewType').textContent = typeLabel + ' questionnaire builder preview';
    document.getElementById('newPreviewStatus').textContent = statusLabel;
}

document.querySelectorAll('.edit-preview-btn').forEach(btn => {
    btn.addEventListener('click', () => setEditPreview(btn.dataset.preview));
});
['title', 'description', 'type', 'status'].forEach(id => {
    document.getElementById(id).addEventListener('input', updateBuilderPreview);
    document.getElementById(id).addEventListener('change', updateBuilderPreview);
});
updateBuilderPreview();

document.getElementById('qForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('saveBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true; 
    btn.innerHTML = 'Saving...';
    
    const isEdit = !!document.getElementById('qId');
    const url = isEdit ? '/admin/questionnaire/edit' : '/admin/questionnaire';
    
    fetch(url, { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const qId = isEdit ? document.getElementById('qId').value : data.questionnaire_id;
                showFlash('Basic info saved! Proceeding to sections...', 'success');
                setTimeout(() => {
                    window.location.href = `/admin/questionnaire/sections?questionnaire_id=${qId}`;
                }, 1000);
            } else {
                btn.disabled = false;
                btn.innerHTML = originalText;
                const errEl = document.getElementById('qErrors');
                errEl.textContent = data.message || 'Error occurred.';
                errEl.style.display = 'block';
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = originalText;
            const errEl = document.getElementById('qErrors');
            errEl.textContent = 'Server error occurred.';
            errEl.style.display = 'block';
        });
});
</script>

<?php include __DIR__ . '/../../partials/public/footer.php'; ?>
