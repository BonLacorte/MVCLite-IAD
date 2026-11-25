<?php include __DIR__ . '/../../partials/public/head.php'; ?>
<?php include __DIR__ . '/../../partials/public/nav.php'; ?>
<?php
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$deleteCsrfToken = Csrf::generate();

$sectionById = [];
foreach ($sections as $sectionRow) {
    $sectionById[(int)$sectionRow['id']] = $sectionRow;
}

$childrenByParent = [];
foreach ($sections as $sectionRow) {
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

$questionsBySection = [];
foreach ($questions as $questionRow) {
    $questionsBySection[(int)$questionRow['section_id']][] = $questionRow;
}

$selectedSectionId = (int)($section['id'] ?? ($orderedSections[0]['id'] ?? 0));

$criteriaFileUrl = static function ($path): string {
    $filePath = (string)$path;
    $fileName = basename(str_replace('\\', '/', $filePath));
    return $fileName !== '' ? '/admin/questionnaire/criteria-file?file=' . rawurlencode($filePath) : '#';
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
        grid-template-columns: minmax(320px, 0.95fr) minmax(640px, 1.9fr);
        gap: 1.5rem;
        align-items: start;
    }
    .form-section,
    .list-section {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        border: 1px solid rgba(5, 87, 65, 0.18);
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .form-group label {
        display: block;
        margin-bottom: 0.4rem;
        font-weight: 700;
        color: #064e3b;
        font-size: 0.78rem;
    }
    .form-control {
        width: 100%;
        padding: 0.7rem 0.85rem;
        border: 1px solid rgba(5, 87, 65, 0.7);
        border-radius: 6px;
        font-family: inherit;
        font-size: 0.9rem;
        transition: all 0.2s;
        background: #fff;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--admin-primary);
        box-shadow: 0 0 0 3px rgba(5, 87, 65, 0.1);
    }
    .radio-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
        margin-top: 0.25rem;
    }
    .radio-row label {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin: 0;
        color: #334155;
        font-weight: 600;
        font-size: 0.82rem;
    }
    .conditional-field[hidden] { display: none !important; }
    .questions-table-wrap {
        overflow-x: auto;
        border: 1px solid var(--admin-primary);
        border-radius: 8px;
    }
    .questions-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
        color: #064e3b;
        font-size: 0.86rem;
    }
    .questions-table th,
    .questions-table td {
        border-bottom: 1px solid rgba(5, 87, 65, 0.75);
        border-right: 1px solid rgba(5, 87, 65, 0.75);
        padding: 0.9rem;
        vertical-align: top;
        text-align: left;
    }
    .questions-table th:last-child,
    .questions-table td:last-child { border-right: 0; }
    .questions-table tr:last-child td { border-bottom: 0; }
    .questions-table th {
        text-align: center;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        font-weight: 800;
    }
    .section-row td {
        background: #f8fafc;
        font-weight: 800;
        color: #064e3b;
    }
    .criteria-link {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        margin-top: 0.35rem;
        color: #2563eb;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
    }
    .icon-action {
        width: 30px;
        height: 30px;
        border: 0;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--admin-primary);
        color: white;
        cursor: pointer;
        font-weight: 800;
    }
    .icon-action.delete {
        background: #ef4444;
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
    .preview-question-card {
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1rem;
        margin-bottom: 0.85rem;
        background: #f8fafc;
    }
    .preview-section-card {
        border: 1px solid rgba(5, 87, 65, 0.2);
        border-radius: 1rem;
        padding: 1rem;
        background: white;
        margin-bottom: 1rem;
    }
    .disabled-option-row {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 0.85rem;
    }
    .disabled-option-row span {
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        padding: 0.35rem 0.75rem;
        color: #64748b;
        font-weight: 800;
        background: white;
        font-size: 0.8rem;
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
    .question-empty {
        padding: 2rem;
        text-align: center;
        color: #94a3b8;
        background: #f8fafc;
    }
    @media (max-width: 980px) {
        .layout-grid { grid-template-columns: 1fr; }
    }
</style>

<main style="min-height: 80vh; padding: 3rem 0; background-color: var(--admin-bg);">
    <div class="container" style="max-width: 1500px;">
        <div id="flash-msg" style="display:none;" class="flash-msg"></div>

        <nav aria-label="breadcrumb" style="margin-bottom: 1.5rem;">
            <a href="/admin/questionnaire/sections?questionnaire_id=<?= (int)$questionnaire['id'] ?>" style="color: var(--admin-primary); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; opacity: 0.7;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                Back to Sections
            </a>
        </nav>

        <div style="margin-bottom: 2rem;">
            <h1 style="color: var(--admin-primary); font-family: 'Montserrat', sans-serif; font-size: 2rem; font-weight: 800; margin: 0; letter-spacing: -0.02em;">
                Manage Questions
            </h1>
            <p style="color: #64748b; margin-top: 0.25rem;">Step 3 of 3: Add questions for <strong><?= $escape($questionnaire['title'] ?? '') ?></strong></p>
        </div>

        <div class="step-indicator">
            <a href="/admin/questionnaire/edit?questionnaire_id=<?= (int)$questionnaire['id'] ?>" class="step completed" style="text-decoration:none;">
                <div class="step-number"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                Basic Info
            </a>
            <div class="step-divider"></div>
            <a href="/admin/questionnaire/sections?questionnaire_id=<?= (int)$questionnaire['id'] ?>" class="step completed" style="text-decoration:none;">
                <div class="step-number"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                Sections
            </a>
            <div class="step-divider"></div>
            <div class="step active">
                <div class="step-number">3</div>
                Questions
            </div>
        </div>

        <div class="layout-grid">
            <div class="form-section">
                <h3 id="formTitle" style="margin-top: 0; margin-bottom: 1.25rem; color: var(--admin-primary); font-size: 1.35rem;">Create Question</h3>
                <form id="questionForm" enctype="multipart/form-data">
                    <input type="hidden" name="questionnaire_id" value="<?= (int)$questionnaire['id'] ?>">
                    <input type="hidden" name="question_id" id="question_id" value="">
                    <input type="hidden" name="type" id="type" value="yes_no">

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="question_text">Question <span style="color:#ef4444">*</span></label>
                        <textarea name="question_text" id="question_text" class="form-control" rows="3" required placeholder="Enter questionnaire title"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="ref_no">Reference Number</label>
                        <input type="text" name="ref_no" id="ref_no" class="form-control" placeholder="Enter reference number">
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="area">Area</label>
                        <textarea name="area" id="area" class="form-control" rows="2" placeholder="Enter area"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="criteria">Criteria Title</label>
                        <textarea name="criteria" id="criteria" class="form-control" rows="2" placeholder="Enter criteria title"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>Criteria Type</label>
                        <div class="radio-row">
                            <label><input type="radio" name="criteria_type" value="none" checked> None</label>
                            <label><input type="radio" name="criteria_type" value="link"> Link</label>
                            <label><input type="radio" name="criteria_type" value="file"> Upload File</label>
                        </div>
                    </div>

                    <div class="form-group conditional-field" id="criteriaLinkGroup" style="margin-bottom: 1rem;" hidden>
                        <label for="criteria_link">Criteria Link <span style="color:#ef4444">*</span></label>
                        <input type="url" name="criteria_link" id="criteria_link" class="form-control" placeholder="https://example.com/reference.pdf">
                    </div>

                    <div class="form-group conditional-field" id="criteriaFileGroup" style="margin-bottom: 1rem;" hidden>
                        <label for="criteria_file">Criteria File <span style="color:#ef4444">*</span></label>
                        <input type="file" name="criteria_file" id="criteria_file" class="form-control" accept="application/pdf">
                        <div id="existingCriteriaFile" style="font-size: 0.78rem; color:#64748b; margin-top:0.35rem;"></div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="section_id">Parent Section <span style="color:#ef4444">*</span></label>
                        <select name="section_id" id="section_id" class="form-control" required <?= empty($orderedSections) ? 'disabled' : '' ?>>
                            <option value="">Select Parent Section</option>
                            <?php foreach($orderedSections as $sectionOption): ?>
                                <?php $optionDepth = $sectionDepth($sectionOption); ?>
                                <option value="<?= (int)$sectionOption['id'] ?>" <?= (int)$sectionOption['id'] === $selectedSectionId ? 'selected' : '' ?>>
                                    <?= $escape(str_repeat('-- ', $optionDepth) . $sectionOption['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="qErrors" style="color:#ef4444; font-size:0.9rem; margin-bottom:1rem; display:none;"></div>

                    <button type="submit" id="saveBtn" class="action-btn" style="width: 100%; background:var(--admin-primary); color:white; padding:0.75rem; border-radius:8px; font-weight:700;" <?= empty($orderedSections) ? 'disabled' : '' ?>>
                        Create
                    </button>
                    <button type="button" id="cancelEditBtn" onclick="resetForm()" class="action-btn" style="width: 100%; margin-top: 0.5rem; background:#f1f5f9; color:#64748b; padding:0.75rem; border-radius:8px; font-weight:700; display:none;">
                        Cancel Edit
                    </button>
                </form>
            </div>

            <div class="list-section">
                <div class="preview-toolbar">
                    <h3 style="margin: 0; color: var(--admin-primary); font-size: 1.25rem;">
                        <?= $escape($questionnaire['title'] ?? 'Questions') ?>
                    </h3>
                    <div class="preview-switch" aria-label="Question preview display">
                        <button type="button" class="question-preview-btn active" data-preview="old">Old View</button>
                        <button type="button" class="question-preview-btn" data-preview="new">New View</button>
                    </div>
                </div>

                <?php if(empty($orderedSections)): ?>
                    <div class="question-empty">No sections exist yet. Create sections before adding questions.</div>
                <?php else: ?>
                    <div id="questionsPreviewOld" class="questions-table-wrap preview-pane">
                        <table class="questions-table">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Area</th>
                                    <th style="width: 24%;">Criteria</th>
                                    <th style="width: 12%;">Ref. No.</th>
                                    <th>Questions</th>
                                    <th style="width: 90px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orderedSections as $sectionRow): ?>
                                    <?php $sectionId = (int)$sectionRow['id']; ?>
                                    <tr class="section-row">
                                        <td colspan="5" style="padding-left: <?= 0.9 + min($sectionDepth($sectionRow), 4) * 1.25 ?>rem;">
                                            <?= $escape($sectionRow['title']) ?>
                                        </td>
                                    </tr>
                                    <?php foreach(($questionsBySection[$sectionId] ?? []) as $questionRow): ?>
                                        <?php
                                            $criteriaType = $questionRow['criteria_type'] ?? 'none';
                                            $criteriaFilePath = $questionRow['criteria_file_path'] ?? '';
                                            $questionPayload = [
                                                'id' => (int)$questionRow['id'],
                                                'section_id' => (int)$questionRow['section_id'],
                                                'question_text' => $questionRow['question_text'] ?? '',
                                                'type' => $questionRow['type'] ?? 'yes_no',
                                                'criteria' => $questionRow['criteria'] ?? '',
                                                'criteria_type' => $criteriaType,
                                                'criteria_link' => $questionRow['criteria_link'] ?? '',
                                                'criteria_file_path' => $criteriaFilePath,
                                                'ref_no' => $questionRow['ref_no'] ?? '',
                                                'area' => $questionRow['area'] ?? ''
                                            ];
                                        ?>
                                        <tr>
                                            <td><?= nl2br($escape($questionRow['area'] ?? '')) ?></td>
                                            <td>
                                                <?= nl2br($escape($questionRow['criteria'] ?? '')) ?>
                                                <?php if($criteriaType === 'link' && !empty($questionRow['criteria_link'])): ?>
                                                    <a class="criteria-link" href="<?= $escape($questionRow['criteria_link']) ?>" target="_blank" rel="noopener noreferrer">View Link</a>
                                                <?php elseif($criteriaType === 'file' && !empty($criteriaFilePath)): ?>
                                                    <a class="criteria-link" href="<?= $escape($criteriaFileUrl($criteriaFilePath)) ?>" target="_blank" rel="noopener noreferrer">View File</a>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= nl2br($escape($questionRow['ref_no'] ?? '')) ?></td>
                                            <td><?= nl2br($escape($questionRow['question_text'] ?? '')) ?></td>
                                            <td style="text-align:center;">
                                                <div style="display:inline-flex; gap:0.45rem;">
                                                <button type="button" class="icon-action edit-question-btn" title="Edit question" data-question="<?= $escape(json_encode($questionPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>">
                                                    &#9998;
                                                </button>
                                                <button type="button" class="icon-action delete" title="Delete question" onclick='openDeleteModal(<?= (int)$questionRow['id'] ?>, <?= $escape(json_encode($questionRow['question_text'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>)'>
                                                    &#128465;
                                                </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="questionsPreviewNew" class="preview-pane" hidden>
                        <?php foreach($orderedSections as $sectionRow): ?>
                            <?php $sectionId = (int)$sectionRow['id']; ?>
                            <div class="preview-section-card">
                                <h4 style="margin:0 0 0.85rem; color:var(--admin-primary); padding-left:<?= min($sectionDepth($sectionRow), 4) * 1.25 ?>rem;"><?= $escape($sectionRow['title']) ?></h4>
                                <?php if(empty($questionsBySection[$sectionId] ?? [])): ?>
                                    <div style="color:#94a3b8; font-weight:700; font-size:0.9rem;">No questions in this section yet.</div>
                                <?php endif; ?>
                                <?php foreach(($questionsBySection[$sectionId] ?? []) as $questionRow): ?>
                                    <?php
                                        $criteriaType = $questionRow['criteria_type'] ?? 'none';
                                        $criteriaFilePath = $questionRow['criteria_file_path'] ?? '';
                                        $questionPayload = [
                                            'id' => (int)$questionRow['id'],
                                            'section_id' => (int)$questionRow['section_id'],
                                            'question_text' => $questionRow['question_text'] ?? '',
                                            'type' => $questionRow['type'] ?? 'yes_no',
                                            'criteria' => $questionRow['criteria'] ?? '',
                                            'criteria_type' => $criteriaType,
                                            'criteria_link' => $questionRow['criteria_link'] ?? '',
                                            'criteria_file_path' => $criteriaFilePath,
                                            'ref_no' => $questionRow['ref_no'] ?? '',
                                            'area' => $questionRow['area'] ?? ''
                                        ];
                                    ?>
                                    <div class="preview-question-card">
                                        <div style="display:flex; justify-content:space-between; gap:1rem; align-items:flex-start;">
                                            <div style="min-width:0;">
                                                <div style="display:flex; gap:0.5rem; flex-wrap:wrap; color:#64748b; font-size:0.75rem; font-weight:800; margin-bottom:0.6rem;">
                                                    <?php if(!empty($questionRow['ref_no'])): ?><span>REF: <?= $escape($questionRow['ref_no']) ?></span><?php endif; ?>
                                                    <?php if(!empty($questionRow['area'])): ?><span>AREA: <?= $escape($questionRow['area']) ?></span><?php endif; ?>
                                                </div>
                                                <div style="font-weight:800; color:#1e293b; line-height:1.45;"><?= nl2br($escape($questionRow['question_text'] ?? '')) ?></div>
                                                <?php if(!empty($questionRow['criteria'])): ?>
                                                    <div style="margin-top:0.6rem; color:#475569; font-weight:700; font-size:0.85rem;">Criteria: <?= nl2br($escape($questionRow['criteria'])) ?></div>
                                                <?php endif; ?>
                                                <?php if($criteriaType === 'link' && !empty($questionRow['criteria_link'])): ?>
                                                    <a class="criteria-link" href="<?= $escape($questionRow['criteria_link']) ?>" target="_blank" rel="noopener noreferrer">View Link</a>
                                                <?php elseif($criteriaType === 'file' && !empty($criteriaFilePath)): ?>
                                                    <a class="criteria-link" href="<?= $escape($criteriaFileUrl($criteriaFilePath)) ?>" target="_blank" rel="noopener noreferrer">View File</a>
                                                <?php endif; ?>
                                                <div class="disabled-option-row" aria-label="Disabled user answer preview">
                                                    <span>Yes</span><span>No</span><span>N/A</span>
                                                </div>
                                            </div>
                                            <div style="display:inline-flex; gap:0.45rem; flex-shrink:0;">
                                                <button type="button" class="icon-action edit-question-btn" title="Edit question" data-question="<?= $escape(json_encode($questionPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>">&#9998;</button>
                                                <button type="button" class="icon-action delete" title="Delete question" onclick='openDeleteModal(<?= (int)$questionRow['id'] ?>, <?= $escape(json_encode($questionRow['question_text'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>)'>&#128465;</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 1.25rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <a href="/admin/questionnaire/sections?questionnaire_id=<?= (int)$questionnaire['id'] ?>" class="action-btn" style="text-decoration:none; background:#f7e58f; color:#064e3b; padding:0.7rem 1.5rem; border-radius:8px; font-weight:700;">Back</a>
                    <a href="/admin/questionnaire/show?questionnaire_id=<?= (int)$questionnaire['id'] ?>" class="action-btn" style="text-decoration:none; background:var(--admin-primary); color:white; padding:0.7rem 1.5rem; border-radius:8px; font-weight:700;">Finish</a>
                </div>
            </div>
        </div>
    </div>
</main>

<div id="builderDeleteModal" class="builder-modal" aria-hidden="true">
    <div class="builder-modal-box" role="dialog" aria-modal="true" aria-labelledby="builderDeleteTitle">
        <h3 id="builderDeleteTitle" style="margin:0 0 0.75rem; color:#0f172a; font-size:1.25rem;">Delete Question</h3>
        <p style="margin:0; color:#475569; line-height:1.55;">Are you sure you want to delete <strong id="builderDeleteName"></strong>?</p>
        <p style="margin:0.75rem 0 0; color:#64748b; font-size:0.9rem;">This uses a safe soft delete and will be blocked if the question already has answer records.</p>
        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.5rem;">
            <button type="button" class="action-btn" style="background:#f1f5f9; color:#475569; padding:0.6rem 1.1rem; border-radius:8px; font-weight:800;" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="action-btn" style="background:#ef4444; color:white; padding:0.6rem 1.1rem; border-radius:8px; font-weight:800;" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>

<script>
const builderDeleteCsrfToken = '<?= htmlspecialchars($deleteCsrfToken, ENT_QUOTES, 'UTF-8') ?>';
let pendingDeleteQuestionId = 0;

function showFlash(msg, type) {
    const el = document.getElementById('flash-msg');
    el.textContent = msg;
    el.className = 'flash-msg flash-' + type;
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 4000);
}

function setQuestionPreview(mode) {
    const oldPreview = document.getElementById('questionsPreviewOld');
    const newPreview = document.getElementById('questionsPreviewNew');
    if (oldPreview) oldPreview.hidden = mode !== 'old';
    if (newPreview) newPreview.hidden = mode !== 'new';
    document.querySelectorAll('.question-preview-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.preview === mode);
    });
}

document.querySelectorAll('.question-preview-btn').forEach(btn => {
    btn.addEventListener('click', () => setQuestionPreview(btn.dataset.preview));
});

function openDeleteModal(id, name) {
    pendingDeleteQuestionId = id;
    document.getElementById('builderDeleteName').textContent = name;
    document.getElementById('builderDeleteModal').classList.add('open');
    document.getElementById('builderDeleteModal').setAttribute('aria-hidden', 'false');
}

function closeDeleteModal() {
    pendingDeleteQuestionId = 0;
    document.getElementById('builderDeleteModal').classList.remove('open');
    document.getElementById('builderDeleteModal').setAttribute('aria-hidden', 'true');
}

document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
    if (!pendingDeleteQuestionId) return;
    const fd = new FormData();
    fd.append('csrf_token', builderDeleteCsrfToken);
    fd.append('question_id', pendingDeleteQuestionId);

    fetch('/admin/questionnaire/questions/delete', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeDeleteModal();
                showFlash(data.message || 'Question deleted.', 'success');
                setTimeout(() => location.reload(), 800);
                return;
            }
            showFlash(data.message || 'Failed to delete question.', 'error');
            closeDeleteModal();
        })
        .catch(err => {
            console.error(err);
            showFlash('Server error occurred.', 'error');
            closeDeleteModal();
        });
});

function selectedCriteriaType() {
    return document.querySelector('input[name="criteria_type"]:checked')?.value || 'none';
}

function setCriteriaType(type) {
    const field = document.querySelector(`input[name="criteria_type"][value="${type}"]`);
    if (field) {
        field.checked = true;
    }
    toggleCriteriaFields();
}

function toggleCriteriaFields() {
    const type = selectedCriteriaType();
    const linkGroup = document.getElementById('criteriaLinkGroup');
    const fileGroup = document.getElementById('criteriaFileGroup');
    const linkInput = document.getElementById('criteria_link');
    const fileInput = document.getElementById('criteria_file');

    linkGroup.hidden = type !== 'link';
    fileGroup.hidden = type !== 'file';
    linkInput.required = type === 'link';
    fileInput.required = type === 'file' && !document.getElementById('existingCriteriaFile').textContent.trim();

    if (type !== 'link') {
        linkInput.value = '';
    }
    if (type !== 'file') {
        fileInput.value = '';
        document.getElementById('existingCriteriaFile').textContent = '';
    }
}

function editQuestion(question) {
    document.getElementById('formTitle').textContent = 'Edit Question';
    document.getElementById('question_id').value = question.id || '';
    document.getElementById('question_text').value = question.question_text || '';
    document.getElementById('type').value = question.type || 'yes_no';
    document.getElementById('ref_no').value = question.ref_no || '';
    document.getElementById('area').value = question.area || '';
    document.getElementById('criteria').value = question.criteria || '';
    document.getElementById('criteria_link').value = question.criteria_link || '';
    document.getElementById('criteria_file').value = '';
    document.getElementById('section_id').value = question.section_id || '';
    document.getElementById('saveBtn').textContent = 'Edit';
    document.getElementById('cancelEditBtn').style.display = 'block';

    const existingFile = document.getElementById('existingCriteriaFile');
    existingFile.textContent = question.criteria_file_path ? `Current file: ${question.criteria_file_path.split(/[\\/]/).pop()}` : '';
    setCriteriaType(question.criteria_type || 'none');
}

function resetForm() {
    document.getElementById('formTitle').textContent = 'Create Question';
    document.getElementById('question_id').value = '';
    document.getElementById('question_text').value = '';
    document.getElementById('type').value = 'yes_no';
    document.getElementById('ref_no').value = '';
    document.getElementById('area').value = '';
    document.getElementById('criteria').value = '';
    document.getElementById('criteria_link').value = '';
    document.getElementById('criteria_file').value = '';
    document.getElementById('existingCriteriaFile').textContent = '';
    document.getElementById('saveBtn').textContent = 'Create';
    document.getElementById('cancelEditBtn').style.display = 'none';
    setCriteriaType('none');
}

document.querySelectorAll('input[name="criteria_type"]').forEach(field => {
    field.addEventListener('change', toggleCriteriaFields);
});
document.querySelectorAll('.edit-question-btn').forEach(button => {
    button.addEventListener('click', () => {
        editQuestion(JSON.parse(button.dataset.question));
        window.scrollTo({ top: document.getElementById('questionForm').offsetTop - 120, behavior: 'smooth' });
    });
});
toggleCriteriaFields();

document.getElementById('questionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('saveBtn');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const id = document.getElementById('question_id').value;
    const url = id ? '/admin/questionnaire/questions/edit' : '/admin/questionnaire/questions';

    fetch(url, { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showFlash('Question saved successfully.', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                btn.disabled = false;
                btn.textContent = originalText;
                const errEl = document.getElementById('qErrors');
                errEl.textContent = data.message || 'Error occurred.';
                errEl.style.display = 'block';
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.textContent = originalText;
            const errEl = document.getElementById('qErrors');
            errEl.textContent = 'Server error occurred.';
            errEl.style.display = 'block';
        });
});
</script>

<?php include __DIR__ . '/../../partials/public/footer.php'; ?>
