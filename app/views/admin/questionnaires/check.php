<?php include __DIR__ . '/../../partials/public/head.php'; ?>
<?php include __DIR__ . '/../../partials/public/nav.php'; ?>

<link rel="stylesheet" href="/assets/css/admin.css">

<style>
    .check-header {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        margin-bottom: 2rem;
        border: 1px solid #f1f5f9;
    }
    
    .check-meta-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    @media (max-width: 992px) {
        .check-meta-grid {
            grid-template-columns: 1fr;
        }
    }

    .meta-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        border: 1px solid #f1f5f9;
    }

    .stats-six-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 1rem;
        margin-bottom: 2.5rem;
    }
    @media (max-width: 1200px) {
        .stats-six-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media (max-width: 768px) {
        .stats-six-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .mini-stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        border: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: transform 0.2s;
    }
    .mini-stat-card:hover {
        transform: translateY(-2px);
    }
    .mini-stat-val {
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
    }
    .mini-stat-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Hierarchy Tree Styles */
    .tree-wrapper {
        background: white;
        border-radius: 16px;
        padding: 2.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
    }

    .section-block {
        margin-bottom: 2rem;
        border-left: 3px solid #055741;
        padding-left: 1.5rem;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 1rem 0;
        font-family: 'Montserrat', sans-serif;
    }

    .subsection-block {
        margin-left: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 2px dashed #cbd5e1;
        padding-left: 1.25rem;
    }

    .subsection-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #334155;
        margin: 0 0 0.75rem 0;
    }

    .question-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.25rem;
        transition: all 0.2s;
    }

    .question-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .question-text {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
    }

    .question-meta {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
        background: #e2e8f0;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
    }

    /* Answers and Files Layout */
    .answer-item {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1.25rem;
        margin-top: 0.75rem;
    }

    .answer-text-val {
        font-size: 0.9rem;
        color: #334155;
        line-height: 1.6;
        white-space: pre-line;
        background: #fafbfb;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1px solid #f1f5f9;
    }

    .file-attachment {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.85rem;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #475569;
        text-decoration: none;
        margin-top: 0.5rem;
        transition: all 0.2s;
    }

    .file-attachment:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* Evaluation UI */
    .eval-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.75rem;
        border-radius: 99px;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .eval-pending { background: #fef3c7; color: #d97706; }
    .eval-accepted { background: #dcfce7; color: #15803d; }
    .eval-rejected { background: #fee2e2; color: #b91c1c; }
    .eval-returned { background: #fee2e2; color: #b91c1c; }
    .eval-for-review { background: #dbeafe; color: #2563eb; }
    .eval-draft { background: #f3e8ff; color: #7c3aed; }

    .check-toolbar {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .status-color-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        padding: 0.5rem 1.25rem;
        border-radius: 0.5rem;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        font-weight: 800;
        cursor: pointer;
    }

    .status-color-toggle:hover,
    .status-color-toggle.is-on {
        border-color: #055741;
        background: #e6f4f0;
        color: #055741;
    }

    .admin-question-filter-wrap { position: relative; }
    .admin-question-filter-menu {
        position: absolute;
        right: 0;
        top: calc(100% + 0.5rem);
        min-width: 180px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
        padding: 0.35rem;
        z-index: 30;
    }
    .admin-question-filter-menu button {
        width: 100%;
        border: 0;
        background: transparent;
        padding: 0.65rem 0.75rem;
        border-radius: 0.55rem;
        font-size: 0.84rem;
        font-weight: 800;
        color: #334155;
        text-align: left;
        cursor: pointer;
    }
    .admin-question-filter-menu button:hover,
    .admin-question-filter-menu button.is-active { background: #e6f4f0; color: #055741; }
    .admin-question-filter-hidden { display: none !important; }
    .admin-question-filter-empty {
        display: none;
        margin: 0 0 1.5rem;
        padding: 1.25rem;
        border: 1px dashed #cbd5e1;
        border-radius: 0.85rem;
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-align: center;
    }

    .admin-check-status-colors .question-card {
        border-left: 5px solid transparent;
    }
    .admin-check-status-colors .question-card[data-status="pending"] {
        background: #fefce8;
        border-color: #fde68a;
        border-left-color: #eab308;
    }
    .admin-check-status-colors .question-card[data-status="accepted"] {
        background: #f0fdf4;
        border-color: #bbf7d0;
        border-left-color: #16a34a;
    }
    .admin-check-status-colors .question-card[data-status="rejected"],
    .admin-check-status-colors .question-card[data-status="returned"] {
        background: #fef2f2;
        border-color: #fecaca;
        border-left-color: #dc2626;
    }
    .admin-check-status-colors .question-card[data-status="for review"] {
        background: #eff6ff;
        border-color: #bfdbfe;
        border-left-color: #2563eb;
    }
    .admin-check-status-colors .question-card[data-status="draft"] {
        background: #faf5ff;
        border-color: #ddd6fe;
        border-left-color: #8b5cf6;
    }

    .answer-review-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem;
        margin-top: 0.75rem;
    }
    .answer-review-field {
        background: #fafbfb;
        border: 1px solid #f1f5f9;
        border-radius: 8px;
        padding: 0.75rem 1rem;
    }
    .answer-review-field strong,
    .submission-history-field strong,
    .submission-history-text strong,
    .submission-history-files strong {
        display: block;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 0.25rem;
    }
    .answer-review-field div,
    .submission-history-field span,
    .submission-history-text div {
        font-size: 0.9rem;
        color: #334155;
        font-weight: 650;
        line-height: 1.55;
        white-space: pre-wrap;
    }
    .answer-review-field.is-answer div {
        font-size: 1rem;
        font-weight: 850;
        color: #0f172a;
    }
    .answer-review-field.is-explanation div {
        max-height: 12rem;
        overflow-y: auto;
        padding-right: 0.35rem;
    }
    .submission-history-shell { margin-top: 0.9rem; }
    .submission-history-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        border-radius: 0.5rem;
        padding: 0.45rem 0.75rem;
        font-size: 0.78rem;
        font-weight: 850;
        cursor: pointer;
    }
    .submission-history-toggle:hover,
    .submission-history-toggle.is-open { background: #f8fafc; border-color: #94a3b8; }
    .submission-history-panel {
        margin-top: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #ffffff;
        overflow: hidden;
    }
    .submission-history-entry { padding: 1rem; border-bottom: 1px solid #e2e8f0; }
    .submission-history-entry:last-child { border-bottom: 0; }
    .submission-history-entry-head {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
        margin-bottom: 0.75rem;
    }
    .submission-history-meta { font-size: 0.78rem; font-weight: 900; color: #0f172a; text-transform: uppercase; }
    .submission-history-date { font-size: 0.75rem; color: #64748b; margin-top: 0.2rem; font-weight: 650; }
    .submission-history-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem; }
    @media (max-width: 768px) { .submission-history-grid { grid-template-columns: 1fr; } }
    .submission-history-field,
    .submission-history-text,
    .submission-history-files { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.6rem; padding: 0.7rem 0.85rem; margin-top: 0.6rem; }

    .pdf-view-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.75rem;
        background: #e6f4f0;
        border: 1px solid #b7dfd3;
        border-radius: 0.45rem;
        font-size: 0.76rem;
        font-weight: 850;
        color: #055741;
        cursor: pointer;
        margin-top: 0.5rem;
    }
    .pdf-view-btn:hover { background: #d3ebe4; }

    .pdf-viewer-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 10000;
        background: rgba(15, 23, 42, 0.72);
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    .pdf-viewer-dialog {
        width: min(1100px, 96vw);
        height: min(820px, 92vh);
        background: #ffffff;
        border-radius: 1rem;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 22px 60px rgba(15, 23, 42, 0.3);
    }
    .pdf-viewer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.9rem 1rem;
        border-bottom: 1px solid #e2e8f0;
        color: #0f172a;
        font-weight: 900;
    }
    .pdf-viewer-actions { display: flex; align-items: center; gap: 0.5rem; }
    .pdf-viewer-actions a,
    .pdf-viewer-actions button {
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #334155;
        border-radius: 0.45rem;
        padding: 0.45rem 0.7rem;
        font-size: 0.78rem;
        font-weight: 850;
        text-decoration: none;
        cursor: pointer;
    }
    .pdf-viewer-dialog iframe { flex: 1; border: 0; width: 100%; background: #f8fafc; }

    .eval-section {
        margin-top: 1.25rem;
        padding-top: 1.25rem;
        border-top: 1px solid #f1f5f9;
    }

    .eval-form-box {
        background: #fafbfb;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
        padding: 1.25rem;
        margin-top: 0.75rem;
    }

    .form-group-inline {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 1rem;
    }

    .radio-pill {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        padding: 0.4rem 0.85rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 700;
        transition: all 0.2s;
    }

    .radio-pill input {
        accent-color: #055741;
    }

    .radio-pill.accept:hover, .radio-pill.accept.checked {
        border-color: #10b981;
        background: #ecfdf5;
        color: #047857;
    }

    .radio-pill.reject:hover, .radio-pill.reject.checked {
        border-color: #ef4444;
        background: #fef2f2;
        color: #b91c1c;
    }

    .eval-textarea {
        width: 100%;
        min-height: 80px;
        padding: 0.75rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.85rem;
        resize: vertical;
        outline: none;
        transition: border 0.2s;
        margin-bottom: 0.75rem;
    }

    .eval-textarea:focus {
        border-color: #055741;
    }

    .btn-submit-eval {
        background: #055741;
        color: #FEFCE8;
        border: none;
        outline: none;
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-submit-eval:hover {
        background: #044333;
    }

    .btn-edit-eval-inline {
        color: #854d0e;
        background: #fefce8;
        border: 1px solid #fef08a;
        padding: 0.25rem 0.65rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        transition: all 0.2s;
    }

    .btn-edit-eval-inline:hover {
        background: #fef9c3;
    }
</style>

<main style="min-height: 80vh; padding: 3rem 0; background-color: var(--admin-bg);">
    <div class="container" style="max-width: 1300px;">
        
        <nav aria-label="breadcrumb" style="margin-bottom: 1.5rem;">
            <a href="/admin/questionnaire/show?questionnaire_id=<?= $questionnaire['id'] ?>" style="color: var(--admin-primary); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; opacity: 0.7;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                Back to Questionnaire Details
            </a>
        </nav>

        <!-- Questionnaire & User Header -->
        <div class="check-header">
            <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--admin-primary); background: var(--admin-primary-soft); padding: 0.3rem 0.75rem; border-radius: 99px;">
                Evaluation & Checking Dashboard
            </span>
            <h1 style="margin: 0.75rem 0 0.5rem 0; font-family:'Montserrat', sans-serif; font-size:2rem; font-weight:800; color:#0f172a;">
                Evaluate Submissions
            </h1>
            <p style="margin: 0; color:#64748b; font-size:1.1rem; font-weight:600;">
                Questionnaire: <strong style="color: #0f172a;"><?= htmlspecialchars($questionnaire['title']) ?></strong>
            </p>
        </div>

        <div class="check-meta-grid">
            <!-- Left Card: Selected User Details -->
            <div class="meta-card">
                <h3 style="margin:0 0 1rem 0; font-size:1.05rem; font-weight:800; color:#0f172a; text-transform:uppercase; letter-spacing:0.02em; border-bottom:1px solid #f1f5f9; padding-bottom:0.5rem;">
                    Respondent Metadata
                </h3>
                <?php if($user): ?>
                    <div style="display: flex; align-items: center; gap: 1.5rem;">
                        <div style="width:55px; height:55px; border-radius:50%; background:#e6f4f0; color:#055741; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.35rem; border:1px solid #cce8e0;">
                            <?php 
                                $initials = '';
                                $parts = explode(' ', $user['name']);
                                foreach($parts as $p) {
                                    if(!empty($p)) $initials .= strtoupper($p[0]);
                                }
                                echo substr($initials, 0, 2);
                            ?>
                        </div>
                        <div>
                            <h4 style="margin:0; font-size:1.15rem; font-weight:800; color:#0f172a;"><?= htmlspecialchars($user['name']) ?></h4>
                            <p style="margin:0.15rem 0; font-size:0.85rem; color:#64748b; font-weight:500;"><?= htmlspecialchars($user['email']) ?></p>
                            <div style="display:flex; gap:0.5rem; margin-top:0.25rem;">
                                <span class="dept-badge" style="background:#e6f4f0; color:#055741; font-weight:700;"><?= htmlspecialchars($user['department_office_unit'] ?: 'No Department') ?></span>
                                <span class="dept-badge" style="background:#f1f5f9; color:#475569; font-weight:600;"><?= htmlspecialchars($user['position'] ?: 'No Position') ?></span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php if (!empty($usersAssigned)): ?>
                        <div style="font-size:0.78rem; font-weight:800; text-transform:uppercase; color:#055741; background:#e6f4f0; display:inline-block; padding:0.2rem 0.6rem; border-radius:99px; margin-bottom:0.75rem;">Pooled Group &mdash; <?= count($usersAssigned) ?> Respondent<?= count($usersAssigned) !== 1 ? 's' : '' ?></div>
                        <div style="display:flex; flex-direction:column; gap:0.5rem; max-height:200px; overflow-y:auto;">
                            <?php foreach($usersAssigned as $au): ?>
                                <?php
                                    $auInitials = '';
                                    foreach(explode(' ', $au['name']) as $p) if(!empty($p)) $auInitials .= strtoupper($p[0]);
                                    $auInitials = substr($auInitials, 0, 2);
                                ?>
                                <div style="display:flex; align-items:center; gap:0.65rem; padding:0.5rem 0.6rem; background:#f8fafc; border-radius:8px; border:1px solid #f1f5f9;">
                                    <div style="width:32px; height:32px; border-radius:50%; background:#e6f4f0; color:#055741; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.8rem; flex-shrink:0;"><?= $auInitials ?></div>
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-size:0.85rem; font-weight:700; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($au['name']) ?></div>
                                        <div style="font-size:0.74rem; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($au['department_office_unit'] ?: 'No Department') ?></div>
                                    </div>
                                    <div style="flex-shrink:0; text-align:right;">
                                        <div style="font-size:0.8rem; font-weight:800; color:#055741;"><?= $au['user_completion'] ?>%</div>
                                        <div style="font-size:0.7rem; color:#94a3b8;">accepted</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 1rem; color: #94a3b8;">
                            No users assigned to this questionnaire yet.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Right Card: Form Mode / Context info -->
            <div class="meta-card" style="display:flex; align-items:center; justify-content:center;">
                <div style="text-align: center;">
                    <span style="font-size:0.8rem; font-weight:800; color:#94a3b8; text-transform:uppercase;">Evaluation Mode</span>
                    <h3 style="margin:0.25rem 0 0 0; color:#055741; font-size:1.4rem; font-weight:800;">Instant AJAX Save</h3>
                </div>
            </div>
        </div>

        <!-- Six Stats Summary Cards Grid -->
        <div class="stats-six-grid">
            <div class="mini-stat-card" style="border-top: 3px solid #055741;">
                <div class="mini-stat-val" style="color: #055741;"><?= $questionnaire['user_completion'] ?>%</div>
                <div class="mini-stat-label">Completion</div>
            </div>
            <div class="mini-stat-card" style="border-top: 3px solid #64748b;">
                <div class="mini-stat-val" style="color: #1e293b;"><?= $questionnaire['total_questions'] ?? 0 ?></div>
                <div class="mini-stat-label">Questions</div>
            </div>
            <div class="mini-stat-card" style="border-top: 3px solid #10b981;">
                <div class="mini-stat-val" style="color: #10b981;" id="statsAcceptedCount"><?= $acceptedCount ?></div>
                <div class="mini-stat-label">Accepted</div>
            </div>
            <div class="mini-stat-card" style="border-top: 3px solid #ef4444;">
                <div class="mini-stat-val" style="color: #ef4444;" id="statsRejectedCount"><?= $rejectedCount ?></div>
                <div class="mini-stat-label">Rejected</div>
            </div>
            <div class="mini-stat-card" style="border-top: 3px solid #f59e0b;">
                <div class="mini-stat-val" style="color: #f59e0b;" id="statsPendingCount"><?= $pendingCount ?></div>
                <div class="mini-stat-label">Pending</div>
            </div>
            <div class="mini-stat-card" style="border-top: 3px solid #94a3b8;">
                <div class="mini-stat-val" style="color: #94a3b8;" id="statsNoAnswerCount"><?= $noAnswerCount ?></div>
                <div class="mini-stat-label">No Answer</div>
            </div>
        </div>

        <!-- Structured Nested Tree of Sections, Subsections & Questions -->
        <div class="tree-wrapper">
            <div class="check-toolbar">
                <div class="admin-question-filter-wrap">
                    <button type="button" id="adminQuestionFilterToggle" class="status-color-toggle" aria-expanded="false" onclick="toggleAdminQuestionFilterMenu()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5h18"></path><path d="M7 12h10"></path><path d="M10 19h4"></path></svg>
                        Filter: <span id="adminQuestionFilterText">All</span>
                    </button>
                    <div id="adminQuestionFilterMenu" class="admin-question-filter-menu" style="display:none;" aria-hidden="true">
                        <button type="button" data-admin-question-filter="all" class="is-active">All</button>
                        <button type="button" data-admin-question-filter="pending">Pending</button>
                        <button type="button" data-admin-question-filter="accepted">Accepted</button>
                        <button type="button" data-admin-question-filter="rejected">Rejected</button>
                    </div>
                </div>
                <button type="button" id="adminStatusColorToggle" class="status-color-toggle" aria-pressed="false" onclick="toggleAdminStatusColors()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5"></circle><circle cx="17.5" cy="10.5" r=".5"></circle><circle cx="8.5" cy="7.5" r=".5"></circle><circle cx="6.5" cy="12.5" r=".5"></circle><path d="M12 2C6.5 2 2 6 2 11.5S6.5 21 12 21c.9 0 1.7-.1 2.5-.4.8-.3 1.5-1.1 1.5-2 0-.6-.2-1.1-.5-1.6-.3-.5-.5-1-.5-1.6 0-1.4 1.1-2.4 2.5-2.4H19c1.7 0 3-1.3 3-3C22 5.6 17.5 2 12 2z"></path></svg>
                    Status Colors: <span id="adminStatusColorToggleText">On</span>
                </button>
            </div>
            <h2 style="margin: 0 0 2rem 0; font-family:'Montserrat', sans-serif; font-size:1.5rem; font-weight:800; color:#0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 0.75rem; display:flex; align-items:center; gap:0.5rem;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#055741" stroke-width="2.5"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                Hierarchical Questionnaire Structure
            </h2>

            <div id="adminQuestionFilterEmpty" class="admin-question-filter-empty">No questions match this filter.</div>

            <?php if(empty($sections)): ?>
                <div style="padding: 3rem; text-align: center; color: #94a3b8; background: #f8fafc; border-radius: 12px;">
                    No sections or sub-sections are defined for this questionnaire.
                </div>
            <?php else: ?>
                <?php
                /**
                 * Recursively renders a section (and all its nested subsections + questions)
                 * at the correct visual depth.
                 */
                function renderSection($sec, $questionnaireId, $userId, $depth = 0) {
                    $tagMap   = ['h3', 'h4', 'h5', 'h6'];
                    $classMap = ['section-title', 'subsection-title', 'subsection-title', 'subsection-title'];
                    $tag      = $tagMap[min($depth, 3)];
                    $cls      = $classMap[min($depth, 3)];
                    $wrapCls  = $depth === 0 ? 'section-block' : 'subsection-block';
                    ?>
                    <div class="<?= $wrapCls ?>">
                        <<?= $tag ?> class="<?= $cls ?>"><?= htmlspecialchars($sec['title']) ?></<?= $tag ?>>

                        <?php if (!empty($sec['subsections'])): ?>
                            <?php foreach ($sec['subsections'] as $sub): ?>
                                <?php renderSection($sub, $questionnaireId, $userId, $depth + 1); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($sec['questions'])): ?>
                            <?php foreach ($sec['questions'] as $q): ?>
                                <?php renderQuestionCard($q, $questionnaireId, $userId); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php
                }
                ?>

                <?php foreach($sections as $sec): ?>
                    <?php renderSection($sec, $questionnaire['id'], $user ? $user['id'] : null, 0); ?>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>

    </div>
</main>

<div class="toast-container" id="toastContainer"></div>

<?php
// PHP helper functions to render admin review cards
function adminStatusLabel($status) {
    $status = strtolower((string)$status);
    if ($status === 'returned') return 'Returned';
    if ($status === 'for review') return 'For Review';
    if ($status === 'no answer') return 'No Answer';
    return ucwords($status);
}

function adminStatusClass($status) {
    $status = strtolower((string)$status);
    return str_replace(' ', '-', in_array($status, ['returned', 'rejected'], true) ? 'rejected' : $status);
}

function renderAdminFileButton($file, $wide = false) {
    $fileLabel = $file['original_name'] ?? $file['safe_file_name'] ?? 'uploaded_document.pdf';
    if (!empty($file['file_available'])): ?>
        <button type="button" class="pdf-view-btn" data-pdf-url="/admin/questionnaire/file?file_id=<?= (int)$file['id'] ?>" data-pdf-title="<?= htmlspecialchars($fileLabel, ENT_QUOTES) ?>" style="<?= $wide ? 'width:100%; justify-content:flex-start; box-sizing:border-box;' : '' ?>" title="Preview uploaded file">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            <span><strong>View PDF</strong><small style="display:block; font-weight:600; color:#64748b; margin-top:0.1rem;"><?= htmlspecialchars($fileLabel) ?></small></span>
        </button>
    <?php else: ?>
        <div class="file-attachment" style="<?= $wide ? 'width:100%; box-sizing:border-box;' : '' ?> background:#f8fafc; border-color:#cbd5e1; color:#64748b; cursor:not-allowed;" title="File record exists, but the physical file is not available in this environment.">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>
                <strong>Missing local file</strong>
                <small style="display:block; font-weight:600; color:#94a3b8; margin-top:0.15rem;"><?= htmlspecialchars($fileLabel) ?> - File record exists, but the physical file is not available in this environment.</small>
            </span>
        </div>
    <?php endif;
}

function renderAdminSubmissionHistory($answers, $questionId) {
    $answers = array_values(array_filter($answers, fn($ans) => strtolower((string)($ans['status'] ?? '')) !== 'draft'));
    if (empty($answers)) return;
    $historyCount = count($answers);
    ?>
    <div class="submission-history-shell">
        <button type="button" class="submission-history-toggle" aria-expanded="false" aria-controls="admin-submission-history-<?= (int)$questionId ?>" onclick="toggleSubmissionHistory(<?= (int)$questionId ?>, this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v5h5"></path><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"></path><path d="M12 7v5l3 2"></path></svg>
            <?= $historyCount === 1 ? 'View Submission' : 'View Submissions (' . $historyCount . ')' ?>
        </button>
        <div class="submission-history-panel" id="admin-submission-history-<?= (int)$questionId ?>" style="display:none;">
            <?php foreach ($answers as $historyEntry): ?>
                <?php
                    $historyStatus = strtolower((string)($historyEntry['status'] ?? 'no answer'));
                    $historyBadgeStatus = adminStatusClass($historyStatus);
                    $historySubmittedAt = $historyEntry['submitted_at'] ?? $historyEntry['created_at'] ?? '';
                    $historyCheckedAt = $historyEntry['checked_at'] ?? '';
                    $historyFiles = $historyEntry['uploaded_files'] ?? [];
                    $historyRemark = trim((string)($historyEntry['remark'] ?? ''));
                    $historyExplanation = trim((string)($historyEntry['explanation'] ?? ''));
                ?>
                <div class="submission-history-entry">
                    <div class="submission-history-entry-head">
                        <div>
                            <div class="submission-history-meta">Submission #<?= (int)($historyEntry['id'] ?? 0) ?><?php if (!empty($historyEntry['submitter_name'])): ?> <span>by <?= htmlspecialchars($historyEntry['submitter_name']) ?></span><?php endif; ?></div>
                            <div class="submission-history-date">Submitted: <?= htmlspecialchars($historySubmittedAt ?: 'Not recorded') ?><?php if ($historyCheckedAt): ?> <span>Checked: <?= htmlspecialchars($historyCheckedAt) ?></span><?php endif; ?></div>
                        </div>
                        <span class="eval-badge eval-<?= htmlspecialchars($historyBadgeStatus) ?>"><?= htmlspecialchars(adminStatusLabel($historyStatus)) ?></span>
                    </div>
                    <div class="submission-history-grid">
                        <div class="submission-history-field"><strong>Answer</strong><span><?= htmlspecialchars(strtoupper((string)($historyEntry['answer'] ?? 'N/A'))) ?></span></div>
                        <div class="submission-history-field"><strong>Evaluation</strong><span><?= htmlspecialchars(adminStatusLabel($historyStatus)) ?></span></div>
                    </div>
                    <div class="submission-history-text"><strong>Explanation / Justification</strong><div><?= $historyExplanation !== '' ? nl2br(htmlspecialchars($historyExplanation)) : 'No explanation provided.' ?></div></div>
                    <?php if ($historyRemark !== ''): ?>
                        <div class="submission-history-text"><strong>Admin Remarks</strong><div><?= nl2br(htmlspecialchars($historyRemark)) ?></div></div>
                    <?php endif; ?>
                    <?php if (!empty($historyFiles)): ?>
                        <div class="submission-history-files">
                            <strong>Supporting Files</strong>
                            <?php foreach ($historyFiles as $historyFile): ?>
                                <?php renderAdminFileButton($historyFile); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function renderAdminEvaluationArea($ans, $q, $questionnaireId, $userId) {
    ?>
    <div class="eval-section" id="eval-section-<?= (int)$ans['id'] ?>">
        <?php if($ans['status'] === 'pending'): ?>
            <div style="font-weight: 700; font-size: 0.85rem; color: #1e293b; margin-bottom: 0.5rem;">Evaluate Submission:</div>
            <form onsubmit="submitEvaluation(event, 'answer', <?= (int)$ans['id'] ?>, <?= (int)$q['id'] ?>, <?= (int)$questionnaireId ?>, <?= $userId ?? 'null' ?>)" class="eval-form-box">
                <div class="form-group-inline">
                    <label class="radio-pill accept" onclick="toggleRadioPill(this)"><input type="radio" name="status" value="accepted" required>Accept Answer</label>
                    <label class="radio-pill reject" onclick="toggleRadioPill(this)"><input type="radio" name="status" value="rejected" required>Reject Answer</label>
                </div>
                <textarea name="remarks" class="eval-textarea" placeholder="Add checked remarks/evaluation feedback for the respondent... (optional)"></textarea>
                <button type="submit" class="btn-submit-eval">Save Check Results</button>
            </form>
        <?php else: ?>
            <div style="display:flex; justify-content:space-between; align-items:center; background:#f1f5f9; padding:0.75rem 1rem; border-radius:8px; gap:0.75rem; flex-wrap:wrap;">
                <div style="font-size:0.8rem; color:#475569; font-weight:600;">Checked Remark: <strong style="color:#1e293b; font-weight:700;"><?= htmlspecialchars($ans['remark'] ?: 'No evaluation remarks provided.') ?></strong></div>
                <button class="btn-edit-eval-inline" onclick="revealEditForm(<?= (int)$ans['id'] ?>)">Edit Evaluation</button>
            </div>
            <div id="edit-form-box-<?= (int)$ans['id'] ?>" style="display:none; margin-top: 0.75rem;">
                <form onsubmit="submitEditEvaluation(event, 'answer', <?= (int)$ans['id'] ?>, <?= (int)$q['id'] ?>, <?= (int)$questionnaireId ?>, <?= $userId ?? 'null' ?>)" class="eval-form-box" style="background:#fffbeb; border-color:#fef08a;">
                    <div class="form-group-inline">
                        <label class="radio-pill accept <?= $ans['status'] === 'accepted' ? 'checked' : '' ?>" onclick="toggleRadioPill(this)"><input type="radio" name="edit_status" value="accepted" <?= $ans['status'] === 'accepted' ? 'checked' : '' ?> required>Accept Answer</label>
                        <label class="radio-pill reject <?= $ans['status'] === 'rejected' ? 'checked' : '' ?>" onclick="toggleRadioPill(this)"><input type="radio" name="edit_status" value="rejected" <?= $ans['status'] === 'rejected' ? 'checked' : '' ?> required>Reject Answer</label>
                    </div>
                    <textarea name="edit_remarks" class="eval-textarea" placeholder="Modify evaluation feedback remarks..."><?= htmlspecialchars($ans['remark'] ?? '') ?></textarea>
                    <div style="display:flex; gap:0.5rem;"><button type="submit" class="btn-submit-eval" style="background:#854d0e;">Update Check</button><button type="button" class="btn-submit-eval" style="background:#cbd5e1; color:#334155;" onclick="hideEditForm(<?= (int)$ans['id'] ?>)">Cancel</button></div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

function renderQuestionCard($q, $questionnaireId, $userId) {
    $answers = array_values(array_filter($q['answers'] ?? [], fn($ans) => strtolower((string)($ans['status'] ?? '')) !== 'draft'));
    $standaloneFiles = array_values(array_filter($q['uploaded_files'] ?? [], fn($file) => empty($file['user_answer_id']) && strtolower((string)($file['status'] ?? '')) !== 'draft'));
    $latestAnswer = $answers[0] ?? null;
    $cardStatus = strtolower((string)($latestAnswer['status'] ?? 'no answer'));
    if (!$latestAnswer || !in_array($cardStatus, ['pending', 'accepted', 'rejected'], true)) {
        return;
    }
    ?>
    <div class="question-card" id="question-card-<?= (int)$q['id'] ?>" data-status="<?= htmlspecialchars(strtolower((string)$cardStatus), ENT_QUOTES) ?>">
        <div class="question-text">
            <span><?= htmlspecialchars($q['question_text']) ?></span>
        </div>

        <?php if(empty($answers) && empty($standaloneFiles)): ?>
            <div style="padding: 1rem; background: white; border-radius: 8px; border: 1px dashed #cbd5e1; color:#94a3b8; font-size: 0.85rem; font-weight:600; display:flex; align-items:center; gap:0.5rem;">
                No response has been submitted yet for this item.
            </div>
        <?php else: ?>
            <?php if($latestAnswer): ?>
                <?php $latestExplanation = trim((string)($latestAnswer['explanation'] ?? '')); ?>
                <div class="answer-item" id="answer-item-<?= (int)$latestAnswer['id'] ?>">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; gap:0.75rem; flex-wrap:wrap;">
                        <span style="font-size:0.8rem; font-weight:800; color:#475569; text-transform:uppercase;">Latest User Answer Submission<?php if (!empty($latestAnswer['submitter_name'])): ?><span style="color:#055741; display:inline-block; margin-left:0.5rem; padding:0.15rem 0.5rem; background:#e6f4f0; border-radius:4px; font-size:0.75rem; font-weight:700; text-transform:none;">by <?= htmlspecialchars($latestAnswer['submitter_name']) ?></span><?php endif; ?></span>
                        <span class="eval-badge eval-<?= htmlspecialchars(adminStatusClass($latestAnswer['status'])) ?>" id="status-badge-<?= (int)$latestAnswer['id'] ?>"><?= htmlspecialchars(adminStatusLabel($latestAnswer['status'])) ?></span>
                    </div>
                    <div class="answer-review-grid">
                        <div class="answer-review-field is-answer"><strong>Answer</strong><div><?= htmlspecialchars(strtoupper((string)$latestAnswer['answer'])) ?></div></div>
                        <div class="answer-review-field is-explanation"><strong>Explanation / Justification</strong><div><?= $latestExplanation !== '' ? nl2br(htmlspecialchars($latestExplanation)) : 'No explanation provided.' ?></div></div>
                    </div>
                    <?php if(!empty($latestAnswer['uploaded_files'])): ?>
                        <div class="submission-history-files" style="margin-top:0.75rem;"><strong>Attached Files</strong><?php foreach($latestAnswer['uploaded_files'] as $f): ?><?php renderAdminFileButton($f); ?><?php endforeach; ?></div>
                    <?php endif; ?>
                    <?php renderAdminEvaluationArea($latestAnswer, $q, $questionnaireId, $userId); ?>
                </div>
                <?php renderAdminSubmissionHistory($answers, (int)$q['id']); ?>
            <?php endif; ?>

            <?php foreach($standaloneFiles as $f): ?>
                <div class="answer-item" id="file-item-<?= (int)$f['id'] ?>">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; gap:0.75rem; flex-wrap:wrap;">
                        <span style="font-size:0.8rem; font-weight:800; color:#475569; text-transform:uppercase;">Isolated File Upload<?php if (!empty($f['submitter_name'])): ?><span style="color:#055741; display:inline-block; margin-left:0.5rem; padding:0.15rem 0.5rem; background:#e6f4f0; border-radius:4px; font-size:0.75rem; font-weight:700; text-transform:none;">by <?= htmlspecialchars($f['submitter_name']) ?></span><?php endif; ?></span>
                        <span class="eval-badge eval-<?= htmlspecialchars(adminStatusClass($f['status'])) ?>" id="file-status-badge-<?= (int)$f['id'] ?>"><?= htmlspecialchars(adminStatusLabel($f['status'])) ?></span>
                    </div>
                    <?php renderAdminFileButton($f, true); ?>
                    <div class="eval-section" id="file-eval-section-<?= (int)$f['id'] ?>">
                        <?php if($f['status'] === 'pending'): ?>
                            <form onsubmit="submitEvaluation(event, 'file', <?= (int)$f['id'] ?>, <?= (int)$q['id'] ?>, <?= (int)$questionnaireId ?>, <?= $userId ?? 'null' ?>)" class="eval-form-box">
                                <div class="form-group-inline"><label class="radio-pill accept" onclick="toggleRadioPill(this)"><input type="radio" name="status" value="accepted" required>Accept File</label><label class="radio-pill reject" onclick="toggleRadioPill(this)"><input type="radio" name="status" value="rejected" required>Reject File</label></div>
                                <textarea name="remarks" class="eval-textarea" placeholder="Add checking remarks/evaluation feedback for this file upload... (optional)"></textarea>
                                <button type="submit" class="btn-submit-eval">Save Check Results</button>
                            </form>
                        <?php else: ?>
                            <div style="font-size:0.8rem; color:#475569; font-weight:600; background:#f1f5f9; padding:0.75rem 1rem; border-radius:8px;">Checked Remark: <strong style="color:#1e293b; font-weight:700;"><?= htmlspecialchars($f['remark'] ?: 'No evaluation remarks provided.') ?></strong></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
}
?>
<div id="adminPdfViewerModal" class="pdf-viewer-modal" aria-hidden="true">
    <div class="pdf-viewer-dialog" role="dialog" aria-modal="true" aria-labelledby="adminPdfViewerTitle">
        <div class="pdf-viewer-header">
            <span id="adminPdfViewerTitle">Supporting File</span>
            <div class="pdf-viewer-actions">
                <a id="adminPdfViewerNewTab" href="#" target="_blank" rel="noopener">Open in new tab</a>
                <button type="button" onclick="closeAdminPdfViewer()">Close</button>
            </div>
        </div>
        <iframe id="adminPdfViewerFrame" title="PDF preview"></iframe>
    </div>
</div>
<script>
    // Toggle styles of accepted/rejected radio selectors dynamically
    function toggleRadioPill(label) {
        const row = label.parentNode;
        row.querySelectorAll('.radio-pill').forEach(l => l.classList.remove('checked'));
        label.classList.add('checked');
        const input = label.querySelector('input');
        if (input) input.checked = true;
    }

    // Toggle inline form reveals
    function revealEditForm(id) {
        document.getElementById(`edit-form-box-${id}`).style.display = 'block';
    }
    function hideEditForm(id) {
        document.getElementById(`edit-form-box-${id}`).style.display = 'none';
    }
    function revealFileEditForm(id) {
        document.getElementById(`file-edit-form-box-${id}`).style.display = 'block';
    }
    function hideFileEditForm(id) {
        document.getElementById(`file-edit-form-box-${id}`).style.display = 'none';
    }

    // Toast alerts helper
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

    function setAdminStatusColorMode(enabled) {
        document.body.classList.toggle('admin-check-status-colors', enabled);
        const toggle = document.getElementById('adminStatusColorToggle');
        const toggleText = document.getElementById('adminStatusColorToggleText');
        if (toggle) {
            toggle.classList.toggle('is-on', enabled);
            toggle.setAttribute('aria-pressed', enabled ? 'true' : 'false');
        }
        if (toggleText) toggleText.textContent = enabled ? 'On' : 'Off';
        localStorage.setItem('iad_admin_check_status_colors', enabled ? '1' : '0');
    }

    function toggleAdminStatusColors() {
        setAdminStatusColorMode(!document.body.classList.contains('admin-check-status-colors'));
    }

    const adminQuestionFilterLabels = {
        all: 'All',
        pending: 'Pending',
        accepted: 'Accepted',
        rejected: 'Rejected'
    };
    let currentAdminQuestionFilter = 'all';

    function toggleAdminQuestionFilterMenu() {
        const menu = document.getElementById('adminQuestionFilterMenu');
        const toggle = document.getElementById('adminQuestionFilterToggle');
        if (!menu) return;
        const willOpen = menu.style.display === 'none';
        menu.style.display = willOpen ? 'block' : 'none';
        menu.setAttribute('aria-hidden', willOpen ? 'false' : 'true');
        if (toggle) toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    }

    function closeAdminQuestionFilterMenu() {
        const menu = document.getElementById('adminQuestionFilterMenu');
        const toggle = document.getElementById('adminQuestionFilterToggle');
        if (menu) {
            menu.style.display = 'none';
            menu.setAttribute('aria-hidden', 'true');
        }
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
    }

    function applyAdminQuestionFilter(status) {
        currentAdminQuestionFilter = status || 'all';
        const label = adminQuestionFilterLabels[currentAdminQuestionFilter] || 'All';
        const labelEl = document.getElementById('adminQuestionFilterText');
        if (labelEl) labelEl.textContent = label;

        document.querySelectorAll('[data-admin-question-filter]').forEach(button => {
            button.classList.toggle('is-active', button.dataset.adminQuestionFilter === currentAdminQuestionFilter);
        });

        let visibleCards = 0;
        document.querySelectorAll('.question-card[data-status]').forEach(card => {
            const cardStatus = (card.dataset.status || '').toLowerCase();
            const visible = currentAdminQuestionFilter === 'all' || cardStatus === currentAdminQuestionFilter;
            card.classList.toggle('admin-question-filter-hidden', !visible);
            if (visible) visibleCards++;
        });

        document.querySelectorAll('.subsection-block, .section-block').forEach(section => {
            const hasVisibleCard = Boolean(section.querySelector('.question-card[data-status]:not(.admin-question-filter-hidden)'));
            section.classList.toggle('admin-question-filter-hidden', !hasVisibleCard && currentAdminQuestionFilter !== 'all');
        });

        const empty = document.getElementById('adminQuestionFilterEmpty');
        if (empty) empty.style.display = visibleCards === 0 ? 'block' : 'none';
        closeAdminQuestionFilterMenu();
    }

    function toggleSubmissionHistory(questionId, button) {
        const panel = document.getElementById(`admin-submission-history-${questionId}`);
        if (!panel) return;
        const isOpen = panel.style.display !== 'none';
        panel.style.display = isOpen ? 'none' : 'block';
        if (button) button.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    }

    function openAdminPdfViewer(url, title) {
        const modal = document.getElementById('adminPdfViewerModal');
        const frame = document.getElementById('adminPdfViewerFrame');
        const titleEl = document.getElementById('adminPdfViewerTitle');
        const newTab = document.getElementById('adminPdfViewerNewTab');
        if (!modal || !frame) return;
        frame.src = url;
        if (titleEl) titleEl.textContent = title || 'Supporting File';
        if (newTab) newTab.href = url;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeAdminPdfViewer() {
        const modal = document.getElementById('adminPdfViewerModal');
        const frame = document.getElementById('adminPdfViewerFrame');
        if (frame) frame.src = 'about:blank';
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
    }

    document.addEventListener('click', function(event) {
        const filterButton = event.target.closest('[data-admin-question-filter]');
        if (filterButton) {
            event.preventDefault();
            applyAdminQuestionFilter(filterButton.dataset.adminQuestionFilter || 'all');
            return;
        }

        if (!event.target.closest('.admin-question-filter-wrap')) {
            closeAdminQuestionFilterMenu();
        }

        const fileButton = event.target.closest('.pdf-view-btn');
        if (fileButton) {
            event.preventDefault();
            openAdminPdfViewer(fileButton.dataset.pdfUrl, fileButton.dataset.pdfTitle);
            return;
        }

        const modal = document.getElementById('adminPdfViewerModal');
        if (modal && event.target === modal) closeAdminPdfViewer();
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') closeAdminPdfViewer();
    });

    document.addEventListener('DOMContentLoaded', function() {
        setAdminStatusColorMode(localStorage.getItem('iad_admin_check_status_colors') !== '0');
        applyAdminQuestionFilter('all');
    });

    // Submit new evaluations instantly via AJAX
    async function submitEvaluation(event, answerType, rowId, questionId, questionnaireId, userId) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        formData.append('questionnaire_id', questionnaireId);
        formData.append('question_id', questionId);
        formData.append('answer_type', answerType);
        formData.append('answer_files_id', rowId);
        formData.append('user_id', userId);

        try {
            const response = await fetch('/admin/questionnaire/checked', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                showToast(data.message || 'Evaluation saved successfully!');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message || 'Failed to save evaluation.', 'error');
            }
        } catch (error) {
            showToast('A network error occurred.', 'error');
        }
    }

    // Submit evaluation edits instantly via AJAX
    async function submitEditEvaluation(event, type, rowId, questionId, questionnaireId, userId) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        formData.append('edit_questionnaire_id', questionnaireId);
        formData.append('edit_question_id', questionId);
        formData.append('edit_user_id', userId);
        formData.append('edit_type', type);
        formData.append('edit_answer_file_id', rowId);

        try {
            const response = await fetch('/admin/questionnaire/checked-update', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                showToast(data.message || 'Evaluation updated successfully!');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message || 'Failed to update evaluation.', 'error');
            }
        } catch (error) {
            showToast('A network error occurred.', 'error');
        }
    }
</script>

<?php include __DIR__ . '/../../partials/public/footer.php'; ?>


