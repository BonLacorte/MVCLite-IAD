<?php include '../app/views/partials/public/head.php'; ?>
<?php include '../app/views/partials/public/nav.php'; ?>

<main id="answerPageShell" class="answer-view-old" style="background-color: #f8fafc; min-height: 100vh; font-family: 'Outfit', 'Inter', sans-serif; padding-bottom: 5rem;">
    <!-- Toast Notification Container -->
    <div class="toast-container" id="toastContainer"></div>

    <div class="questionnaire-container">
                <?php
        $answerStatusCounts = [
            'total' => 0,
            'no answer' => 0,
            'draft' => 0,
            'for review' => 0,
            'returned' => 0,
            'pending' => 0,
            'rejected' => 0,
            'accepted' => 0,
        ];
        $collectAnswerStatusCounts = function ($sectionList) use (&$collectAnswerStatusCounts, &$answerStatusCounts) {
            foreach ($sectionList as $section) {
                foreach (($section['questions'] ?? []) as $question) {
                    $status = strtolower((string)($question['latest_answer_status'] ?? 'no answer'));
                    if (!array_key_exists($status, $answerStatusCounts)) {
                        $status = 'no answer';
                    }
                    $answerStatusCounts['total']++;
                    $answerStatusCounts[$status]++;
                }
                if (!empty($section['subsections'])) {
                    $collectAnswerStatusCounts($section['subsections']);
                }
            }
        };
        $collectAnswerStatusCounts($sections ?? []);
        $answerCountCards = [
            ['key' => 'total', 'filter' => 'all', 'label' => 'Total Questions', 'color' => '#475569'],
            ['key' => 'no answer', 'filter' => 'no answer', 'label' => 'No Answer', 'color' => '#94a3b8'],
            ['key' => 'draft', 'filter' => 'draft', 'label' => 'Draft', 'color' => '#8b5cf6'],
            ['key' => 'for review', 'filter' => 'for review', 'label' => 'For Review', 'color' => '#2563eb'],
            ['key' => 'returned', 'filter' => 'returned', 'label' => 'Returned', 'color' => '#dc2626'],
            ['key' => 'pending', 'filter' => 'pending', 'label' => 'Pending', 'color' => '#eab308'],
            ['key' => 'rejected', 'filter' => 'rejected', 'label' => 'Rejected', 'color' => '#dc2626'],
            ['key' => 'accepted', 'filter' => 'accepted', 'label' => 'Accepted', 'color' => '#16a34a'],
        ];
        ?>
        <!-- Back and Title Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <a href="/questionnaires/<?= $questionnaire['id'] ?>" style="color: #64748b; text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600; transition: color 0.2s;" onmouseover="this.style.color='#055741'" onmouseout="this.style.color='#64748b'">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Back to Questionnaire Details
            </a>

            <!-- Mini Progress Indicator -->
            <div style="background: white; border: 1px solid #e2e8f0; padding: 0.5rem 1rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="font-size: 0.8rem; font-weight: 700; color: #475569;">Progress:</div>
                <div style="width: 120px; height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                    <div style="height: 100%; width: <?= ($stats['total_questions'] > 0) ? ($stats['total_accepted'] / $stats['total_questions'] * 100) : 0 ?>%; background: #055741; transition: width 0.3s ease;"></div>
                </div>
                <div style="font-size: 0.8rem; font-weight: 800; color: #055741;"><?= $stats['total_accepted'] ?> / <?= $stats['total_questions'] ?> Accepted</div>
            </div>
        </div>

        <!-- Questionnaire Main Header -->
        <div style="margin-bottom: 2rem;">
            <h1 style="margin: 0; font-size: 1.75rem; font-weight: 800; color: #055741;"><?= htmlspecialchars($questionnaire['title']) ?></h1>
            <p style="margin: 0.5rem 0 1.5rem 0; color: #64748b; font-size: 0.95rem; font-weight: 500;"><?= htmlspecialchars($questionnaire['description']) ?></p>

            <!-- Top Actions -->
            <div class="answer-action-row">
                <a href="/questionnaires/<?= (int)$questionnaire['id'] ?>/download" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; padding: 0.5rem 1.25rem; border-radius: 0.5rem; background: #f1f5f9; border: 1px solid #cbd5e1; font-weight: 700; color: #334155; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Download Questionnaire
                </a>
                <a href="/questionnaires/<?= (int)$questionnaire['id'] ?>/dh-verification-form" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; padding: 0.5rem 1.25rem; border-radius: 0.5rem; background: #f1f5f9; border: 1px solid #cbd5e1; font-weight: 700; color: #334155; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Download DH Verification Form
                </a>
                <button type="button" class="btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; padding: 0.5rem 1.25rem; border-radius: 0.5rem; background: #055741; color: white; border: none; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#033a2b'" onmouseout="this.style.background='#055741'" onclick="openDhVerificationModal()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Upload DH Verification Form
                </button>
                <button type="button" id="statusColorToggle" class="status-color-toggle" aria-pressed="false">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5"></circle><circle cx="17.5" cy="10.5" r=".5"></circle><circle cx="8.5" cy="7.5" r=".5"></circle><circle cx="6.5" cy="12.5" r=".5"></circle><path d="M12 2C6.5 2 2 6 2 11.5S6.5 21 12 21c.9 0 1.5-.7 1.5-1.5 0-.4-.2-.8-.4-1.1-.3-.3-.4-.7-.4-1.1 0-.8.7-1.5 1.5-1.5H16c3.3 0 6-2.7 6-6C22 5.5 17.5 2 12 2z"></path></svg>
                    <span id="statusColorToggleText">Status Colors: Off</span>
                </button>
                            <div class="question-filter-control">
                    <button type="button" id="questionFilterToggle" class="question-filter-toggle" aria-expanded="false" aria-controls="questionFilterMenu">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                        <span id="questionFilterLabel">Filter: All</span>
                    </button>
                    <div id="questionFilterMenu" class="question-filter-menu" role="menu" aria-hidden="true">
                        <button type="button" data-filter-status="all">All</button>
                        <button type="button" data-filter-status="no answer">No Answer</button>
                        <button type="button" data-filter-status="draft">Draft</button>
                        <button type="button" data-filter-status="for review">For Review</button>
                        <button type="button" data-filter-status="returned">Returned</button>
                        <button type="button" data-filter-status="pending">Pending</button>
                        <button type="button" data-filter-status="rejected">Rejected</button>
                        <button type="button" data-filter-status="accepted">Accepted</button>
                    </div>
                </div>
                <div class="answer-view-switch" aria-label="Answer page display mode">
                    <button type="button" class="answer-view-btn" data-answer-view-target="old">Old View</button>
                    <button type="button" class="answer-view-btn" data-answer-view-target="new">New View</button>
                </div>
            </div>
        </div>

        <div class="answer-status-summary-grid" id="answerStatusSummary">
            <?php foreach ($answerCountCards as $card): ?>
                <?php $slug = str_replace(' ', '-', $card['key']); ?>
                <button type="button" class="answer-count-card" data-filter-status="<?= htmlspecialchars($card['filter']) ?>" style="border-top-color: <?= htmlspecialchars($card['color']) ?>;">
                    <span class="answer-count-value" id="answerCount-<?= htmlspecialchars($slug) ?>" style="color: <?= htmlspecialchars($card['color']) ?>;"><?= (int)$answerStatusCounts[$card['key']] ?></span>
                    <span class="answer-count-label"><?= htmlspecialchars($card['label']) ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Tabbed Navigation for Sub-Questionnaires -->
        <?php if (count($tabQuestionnaires) > 1): ?>
            <div class="q-tabs">
                <?php foreach ($tabQuestionnaires as $tabQ): ?>
                    <a href="/questionnaires/<?= $tabQ['id'] ?>/answer" class="q-tab-item <?= $tabQ['id'] == $questionnaire['id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($tabQ['title']) ?>
                        <span style="font-size: 0.7rem; font-weight: 600; opacity: 0.7; margin-left: 0.25rem;">
                            (<?= ucfirst($tabQ['type']) ?>)
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div id="questionFilterEmptyState" class="question-filter-empty" style="display:none;">
            No questions match the selected filter.
        </div>

        <div id="answerOldViewPane" class="answer-old-view-pane" style="display:none;">
            <div class="answer-old-table-scroll">
                <table class="answer-old-table">
                    <thead>
                        <tr>
                            <th>Area</th>
                            <th>Criteria</th>
                            <th>Ref. No.</th>
                            <th>Questions</th>
                            <th>Auditee Self-Assessment</th>
                        </tr>
                    </thead>
                    <tbody id="answerOldTableBody"></tbody>
                </table>
            </div>
        </div>

        <!-- Collapsible Hierarchy of Sections & Questions -->
        <div id="answerNewViewPane" class="answer-new-view-pane" style="margin-top: 1rem;">
            <?php
            if (!empty($sections)) {
                // Define recursive helper inline to avoid global pollution
                function renderSectionsTree($sections, $questionnaire, $level = 0) {
                    foreach ($sections as $section) {
                        $paddingLeft = $level * 1.25;
                        ?>
                        <div class="section-accordion <?= $level === 0 ? 'open' : '' ?>" style="margin-left: <?= $paddingLeft ?>rem; border-left: <?= $level > 0 ? '4px solid #f1f5f9' : 'none' ?>;">
                            <div class="section-header" onclick="toggleSection(this)">
                                <h3 class="section-title">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: #055741;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                                    <?= htmlspecialchars($section['title']) ?>
                                </h3>
                                <svg class="section-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            
                            <div class="section-body" style="display: <?= $level === 0 ? 'block' : 'none' ?>;">
                                <!-- Questions in this section -->
                                <?php if (!empty($section['questions'])): ?>
                                    <?php foreach ($section['questions'] as $question): ?>
                                        <?php 
                                        $status = strtolower($question['latest_answer_status'] ?? 'no answer');
                                        $isLocked = in_array($status, ['for review', 'pending', 'accepted']);
                                        
                                        // Retrieve latest answer values
                                        $latestAnswer = $question['answers'][0] ?? null;
                                        $currentAnswerValue = '';
                                        $currentExplanationValue = '';
                                        if ($latestAnswer) {
                                            $currentAnswerValue = strtolower($latestAnswer['answer'] ?? '');
                                            $currentExplanationValue = $latestAnswer['explanation'] ?? '';
                                        }
                                        $adminRemark = trim((string)($latestAnswer['remark'] ?? ''));
                                        $adminRemarkStatus = strtolower((string)($latestAnswer['status'] ?? ''));
                                        $adminRemarkLabel = in_array($adminRemarkStatus, ['rejected', 'returned'], true)
                                            ? 'Admin Feedback / Reason for Return'
                                            : 'Admin Remarks';
                                        $historyEntries = array_values(array_filter($question['answers'] ?? [], function ($entry) {
                                            return strtolower((string)($entry['status'] ?? '')) !== 'draft';
                                        }));
                                        $historyCount = count($historyEntries);
                                        ?>
                                        <div class="question-item-card" id="card-<?= $question['id'] ?>" data-status="<?= htmlspecialchars($status) ?>" data-area="<?= htmlspecialchars((string)($question['area'] ?? ''), ENT_QUOTES) ?>" data-criteria="<?= htmlspecialchars((string)($question['criteria'] ?? ''), ENT_QUOTES) ?>" data-ref-no="<?= htmlspecialchars((string)($question['ref_no'] ?? ''), ENT_QUOTES) ?>" data-question-text="<?= htmlspecialchars((string)($question['question_text'] ?? ''), ENT_QUOTES) ?>">
                                            <!-- Top metadata & status badge -->
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                                                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: #64748b; font-weight: 700;">
                                                    <?php if ($question['ref_no']): ?>
                                                        <span style="background: #e2e8f0; padding: 0.2rem 0.5rem; border-radius: 0.375rem;">REF: <?= htmlspecialchars($question['ref_no']) ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($question['area']): ?>
                                                        <span>AREA: <?= htmlspecialchars($question['area']) ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($question['criteria']): ?>
                                                        <span>CRITERIA: <?= htmlspecialchars($question['criteria']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <span class="badge-pill badge-<?= str_replace(' ', '-', in_array($status, ['returned', 'rejected'], true) ? 'rejected' : $status) ?>" id="badge-<?= $question['id'] ?>">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                                                    <?= ucfirst($status === 'returned' ? 'Returned' : $status) ?>
                                                </span>
                                            </div>
                                            
                                            <!-- Question Text -->
                                            <div class="question-text"><?= htmlspecialchars($question['question_text']) ?></div>
                                            
                                            <!-- Answer Form -->
                                            <form class="ajax-answer-form" id="form-<?= $question['id'] ?>" onsubmit="return false;">
                                                <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                                                
                                                <!-- Yes / No Radio buttons. N/A is intentionally disabled for now. -->
                                                <div class="option-group">
                                                    <label style="position: relative; cursor: pointer;">
                                                        <input type="radio" name="answer" value="yes" class="option-input" <?= $currentAnswerValue === 'yes' ? 'checked' : '' ?> <?= $isLocked ? 'disabled' : '' ?>>
                                                        <span class="option-btn">Yes</span>
                                                    </label>
                                                    <label style="position: relative; cursor: pointer;">
                                                        <input type="radio" name="answer" value="no" class="option-input" <?= $currentAnswerValue === 'no' ? 'checked' : '' ?> <?= $isLocked ? 'disabled' : '' ?>>
                                                        <span class="option-btn">No</span>
                                                    </label>
                                                    <?php /* N/A is intentionally disabled for now.
                                                    <label style="position: relative; cursor: pointer;">
                                                        <input type="radio" name="answer" value="n/a" class="option-input" <?= $currentAnswerValue === 'n/a' ? 'checked' : '' ?> <?= $isLocked ? 'disabled' : '' ?>>
                                                        <span class="option-btn">N/A</span>
                                                    </label>
                                                    */ ?>
                                                </div>
                                                
                                                <!-- Explanation Area -->
                                                <div style="margin-bottom: 1.25rem;">
                                                    <?php
                                                        $currentExplanationLength = function_exists('mb_strlen')
                                                            ? mb_strlen((string)$currentExplanationValue, 'UTF-8')
                                                            : strlen((string)$currentExplanationValue);
                                                    ?>
                                                    <label class="answer-explanation-label">
                                                        <span>
                                                            Explanation / Justification
                                                            <?php if (!empty($latestAnswer['submitter_name'])): ?>
                                                                <span style="color:#055741; display:inline-block; padding:0.15rem 0.5rem; background:#e6f4f0; border-radius:4px; font-size:0.75rem; font-weight:700; margin-left:0.5rem;">Last edited by <?= htmlspecialchars($latestAnswer['submitter_name']) ?></span>
                                                            <?php endif; ?>
                                                        </span>
                                                        <span class="explanation-counter <?= $currentExplanationLength > 750 ? 'is-over' : '' ?>" id="explanation-counter-<?= (int)$question['id'] ?>"><?= (int)$currentExplanationLength ?> / 750</span>
                                                    </label>
                                                    <textarea name="explanation" rows="3" maxlength="750" data-counter-id="explanation-counter-<?= (int)$question['id'] ?>" oninput="updateExplanationCounter(this)" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0.75rem; font-size: 0.9rem; outline: none; font-weight: 600; font-family: inherit; color: #334155; transition: border-color 0.2s;" placeholder="Provide explanation here..." <?= $isLocked ? 'disabled' : '' ?>><?= htmlspecialchars($currentExplanationValue) ?></textarea>
                                                </div>

                                                <?php if ($adminRemark !== ''): ?>
                                                    <div class="admin-remark-box" style="margin-bottom: 1.25rem; border: 1px solid <?= in_array($adminRemarkStatus, ['rejected', 'returned'], true) ? '#fecaca' : '#bbf7d0' ?>; background: <?= in_array($adminRemarkStatus, ['rejected', 'returned'], true) ? '#fef2f2' : '#f0fdf4' ?>; border-radius: 0.5rem; padding: 0.85rem 1rem;">
                                                        <div style="font-size: 0.78rem; font-weight: 800; text-transform: uppercase; color: <?= in_array($adminRemarkStatus, ['rejected', 'returned'], true) ? '#991b1b' : '#166534' ?>; margin-bottom: 0.35rem;">
                                                            <?= htmlspecialchars($adminRemarkLabel) ?>
                                                        </div>
                                                        <div style="font-size: 0.9rem; font-weight: 600; color: #334155; line-height: 1.5;">
                                                            <?= nl2br(htmlspecialchars($adminRemark)) ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- Drag-and-drop File upload zone -->
                                                <?php if (!$isLocked): ?>
                                                    <div class="upload-dropzone" id="dropzone-<?= $question['id'] ?>" onclick="triggerFileInput(<?= $question['id'] ?>)">
                                                        <div class="upload-icon">
                                                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                                        </div>
                                                        <div class="upload-text" id="dropzone-text-<?= $question['id'] ?>">Drag & drop PDF evidence files here, or click to upload</div>
                                                        <div class="upload-subtext">PDF files only (Max size: 10MB each)</div>
                                                        <input type="file" name="files[]" id="file-input-<?= $question['id'] ?>" accept="application/pdf" multiple style="display: none;" onchange="handleFilesSelected(<?= $question['id'] ?>, this.files)">
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- List of uploaded PDF files -->
                                                <div class="uploaded-files-list" id="files-list-<?= $question['id'] ?>">
                                                    <?php if (!empty($question['uploaded_files'])): ?>
                                                        <?php foreach ($question['uploaded_files'] as $file): ?>
                                                            <?php
                                                            $fileBelongsToViewer = (int)($file['user_id'] ?? 0) === (int)($_SESSION['user']['userid'] ?? 0);
                                                            $fileStatus = strtolower((string)($file['status'] ?? ''));
                                                            if ($fileStatus !== 'draft') {
                                                                continue;
                                                            }
                                                            $canDeleteFile = $fileBelongsToViewer && !$isLocked;
                                                            ?>
                                                            <div class="uploaded-file-item" id="file-item-<?= (int)$file['id'] ?>" data-file-id="<?= (int)$file['id'] ?>" data-file-name="<?= htmlspecialchars(basename((string)$file['file_path']), ENT_QUOTES) ?>" data-review-file="<?= $fileBelongsToViewer ? '1' : '0' ?>">
                                                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: #ef4444;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                                    <?php if ($fileBelongsToViewer): ?>
                                                                        <?php
                                                                        $safeFileName = basename((string)$file['file_path']);
                                                                        $pdfUrl = '/questionnaires/file?file=' . rawurlencode((string)$file['file_path']);
                                                                        ?>
                                                                        <span style="font-weight: 700; color: #055741;"><?= htmlspecialchars(basename($file['file_path'])) ?></span>
                                                                        <button type="button" class="pdf-view-btn" data-pdf-url="<?= htmlspecialchars($pdfUrl, ENT_QUOTES) ?>" data-pdf-title="<?= htmlspecialchars($safeFileName, ENT_QUOTES) ?>">View PDF</button>
                                                                    <?php else: ?>
                                                                        <span style="font-weight: 700; color: #64748b;"><?= htmlspecialchars(basename($file['file_path'])) ?></span>
                                                                        <span style="font-size: 0.7rem; background: #f1f5f9; padding: 0.15rem 0.35rem; border-radius: 0.25rem; font-weight: 700; color: #64748b;">Restricted</span>
                                                                    <?php endif; ?>
                                                                    <span style="font-size: 0.7rem; background: #e2e8f0; padding: 0.15rem 0.35rem; border-radius: 0.25rem; font-weight: 700; color: #475569;"><?= htmlspecialchars(strtoupper($file['status'])) ?></span>
                                                                    <?php if (!empty($file['submitter_name'])): ?>
                                                                        <span style="color:#055741; display:inline-block; margin-left:auto; padding:0.15rem 0.5rem; background:#e6f4f0; border-radius:4px; font-size:0.75rem; font-weight:700;">by <?= htmlspecialchars($file['submitter_name']) ?></span>
                                                                    <?php endif; ?>
                                                                    <?php if ($canDeleteFile): ?>
                                                                        <button type="button" class="file-delete-btn" onclick="markFileForDeletion(<?= (int)$question['id'] ?>, <?= (int)$file['id'] ?>)">Delete</button>
                                                                        <button type="button" class="file-undo-delete-btn" onclick="undoFileDeletion(<?= (int)$question['id'] ?>, <?= (int)$file['id'] ?>)" style="display:none;">Undo</button>
                                                                        <span class="file-delete-note" style="display:none;">Will be removed when you save draft</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if ($historyCount > 0): ?>
                                                    <div class="submission-history-shell">
                                                        <button type="button" class="submission-history-toggle" aria-expanded="false" aria-controls="submission-history-<?= $question['id'] ?>" onclick="toggleSubmissionHistory(<?= $question['id'] ?>, this)">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v5h5"></path><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"></path><path d="M12 7v5l3 2"></path></svg>
                                                            <?= $historyCount === 1 ? 'View Submission' : 'View Submissions (' . $historyCount . ')' ?>
                                                        </button>

                                                        <div class="submission-history-panel" id="submission-history-<?= $question['id'] ?>" style="display: none;">
                                                            <?php foreach ($historyEntries as $historyEntry): ?>
                                                                <?php
                                                                $historyStatus = strtolower((string)($historyEntry['status'] ?? 'no answer'));
                                                                $historyBadgeStatus = in_array($historyStatus, ['returned', 'rejected'], true) ? 'rejected' : $historyStatus;
                                                                $historyStatusLabel = $historyStatus === 'returned'
                                                                    ? 'Returned'
                                                                    : ucwords($historyStatus);
                                                                $historyAnswer = strtoupper((string)($historyEntry['answer'] ?? 'N/A'));
                                                                $historySubmittedAt = $historyEntry['submitted_at'] ?? $historyEntry['created_at'] ?? '';
                                                                $historyCheckedAt = $historyEntry['checked_at'] ?? '';
                                                                $historyRemark = trim((string)($historyEntry['remark'] ?? ''));
                                                                $historyFiles = $historyEntry['uploaded_files'] ?? [];
                                                                if (in_array($historyStatus, ['pending', 'accepted', 'rejected'], true)) {
                                                                    $dhResult = 'Accepted by Department Head / forwarded to admin review';
                                                                } elseif ($historyStatus === 'returned') {
                                                                    $dhResult = 'Returned by Department Head';
                                                                } else {
                                                                    $dhResult = 'No Department Head result yet';
                                                                }
                                                                ?>
                                                                <div class="submission-history-entry">
                                                                    <div class="submission-history-entry-head">
                                                                        <div>
                                                                            <div class="submission-history-meta">
                                                                                Submission #<?= (int)($historyEntry['id'] ?? 0) ?>
                                                                                <?php if (!empty($historyEntry['submitter_name'])): ?>
                                                                                    <span>by <?= htmlspecialchars($historyEntry['submitter_name']) ?></span>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                            <div class="submission-history-date">
                                                                                Submitted: <?= htmlspecialchars($historySubmittedAt ?: 'Not recorded') ?>
                                                                                <?php if (!empty($historyCheckedAt)): ?>
                                                                                    <span>Checked: <?= htmlspecialchars($historyCheckedAt) ?></span>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                        <span class="badge-pill badge-<?= htmlspecialchars(str_replace(' ', '-', $historyBadgeStatus)) ?>">
                                                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                                                                            <?= htmlspecialchars($historyStatusLabel) ?>
                                                                        </span>
                                                                    </div>

                                                                    <div class="submission-history-grid">
                                                                        <div class="submission-history-field">
                                                                            <strong>Answer</strong>
                                                                            <span><?= htmlspecialchars($historyAnswer) ?></span>
                                                                        </div>
                                                                        <div class="submission-history-field">
                                                                            <strong>Department Head Result</strong>
                                                                            <span><?= htmlspecialchars($dhResult) ?></span>
                                                                        </div>
                                                                    </div>

                                                                    <?php if (trim((string)($historyEntry['explanation'] ?? '')) !== ''): ?>
                                                                        <div class="submission-history-text">
                                                                            <strong>Explanation / Comment</strong>
                                                                            <div><?= nl2br(htmlspecialchars((string)$historyEntry['explanation'])) ?></div>
                                                                        </div>
                                                                    <?php endif; ?>

                                                                    <?php if ($historyRemark !== ''): ?>
                                                                        <div class="submission-history-text submission-history-remark">
                                                                            <strong><?= in_array($historyStatus, ['rejected', 'returned'], true) ? 'Admin Feedback / Reason for Return' : 'Admin Remarks' ?></strong>
                                                                            <div><?= nl2br(htmlspecialchars($historyRemark)) ?></div>
                                                                        </div>
                                                                    <?php endif; ?>

                                                                    <?php if (!empty($historyFiles)): ?>
                                                                        <div class="submission-history-files">
                                                                            <strong>Supporting Files</strong>
                                                                            <?php foreach ($historyFiles as $historyFile): ?>
                                                                                <?php
                                                                                $historyFileBelongsToViewer = (int)($historyFile['user_id'] ?? 0) === (int)($_SESSION['user']['userid'] ?? 0);
                                                                                $historySafeFileName = basename((string)$historyFile['file_path']);
                                                                                $historyPdfUrl = '/questionnaires/file?file=' . rawurlencode((string)$historyFile['file_path']);
                                                                                ?>
                                                                                <div class="uploaded-file-item">
                                                                                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: #ef4444;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                                                        <span style="font-weight: 700; color: <?= $historyFileBelongsToViewer ? '#055741' : '#64748b' ?>;"><?= htmlspecialchars($historySafeFileName) ?></span>
                                                                                        <?php if ($historyFileBelongsToViewer): ?>
                                                                                            <button type="button" class="pdf-view-btn" data-pdf-url="<?= htmlspecialchars($historyPdfUrl, ENT_QUOTES) ?>" data-pdf-title="<?= htmlspecialchars($historySafeFileName, ENT_QUOTES) ?>">View PDF</button>
                                                                                        <?php else: ?>
                                                                                            <span style="font-size: 0.7rem; background: #f1f5f9; padding: 0.15rem 0.35rem; border-radius: 0.25rem; font-weight: 700; color: #64748b;">Restricted</span>
                                                                                        <?php endif; ?>
                                                                                        <span style="font-size: 0.7rem; background: #e2e8f0; padding: 0.15rem 0.35rem; border-radius: 0.25rem; font-weight: 700; color: #475569;"><?= htmlspecialchars(strtoupper((string)$historyFile['status'])) ?></span>
                                                                                        <?php if (!empty($historyFile['submitter_name'])): ?>
                                                                                            <span style="color:#055741; display:inline-block; padding:0.15rem 0.5rem; background:#e6f4f0; border-radius:4px; font-size:0.75rem; font-weight:700;">by <?= htmlspecialchars($historyFile['submitter_name']) ?></span>
                                                                                        <?php endif; ?>
                                                                                    </div>
                                                                                </div>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- Action bar and saving status -->
                                                <div class="card-actions">
                                                    <div style="margin-right: auto; display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; font-weight: 700; color: #64748b;" id="save-status-<?= $question['id'] ?>">
                                                        <!-- Inline saving indicator goes here -->
                                                    </div>
                                                    
                                                    <?php if (!$isLocked): ?>
                                                        <button type="button" class="btn-secondary" style="font-size: 0.85rem; padding: 0.5rem 1.25rem; border-radius: 0.5rem; background: #f1f5f9; border: 1px solid #cbd5e1; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'" onclick="saveAnswer(<?= $question['id'] ?>, 'draft')">Save Draft</button>
                                                        <button type="button" class="btn-primary" style="font-size: 0.85rem; padding: 0.5rem 1.25rem; border-radius: 0.5rem; background: #055741; color: white; border: none; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#033a2b'" onmouseout="this.style.background='#055741'" onclick="submitForReview(<?= $question['id'] ?>)">Submit for Review</button>
                                                    <?php else: ?>
                                                        <span style="font-size: 0.85rem; font-weight: 700; color: #94a3b8; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                                            Answer Locked
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <!-- Render subsections recursively -->
                                <?php if (!empty($section['subsections'])): ?>
                                    <?php renderSectionsTree($section['subsections'], $questionnaire, $level + 1); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
                }
                
                renderSectionsTree($sections, $questionnaire, 0);
            } else {
                ?>
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 1.5rem; padding: 3rem; text-align: center; color: #64748b; font-weight: 600;">
                    No sections or questions are assigned to this questionnaire.
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</main>

<!-- Modal Confirmation dialog for submitting -->
<div id="confirmModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 1rem; width: 100%; max-width: 640px; padding: 2rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
        <h3 style="margin-top: 0; color: #0f172a; font-weight: 800; font-size: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: #055741;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            Confirm Submission
        </h3>
        <p style="color: #475569; line-height: 1.6; font-size: 0.95rem; margin: 1rem 0;">
            Are you sure you want to submit this response? Once submitted, it will be locked and forwarded to the Department Head for verification.
        </p>
        <div id="confirmReviewSummary" class="confirm-review-summary" aria-live="polite"></div>
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
            <button class="btn-secondary" style="font-size: 0.9rem; padding: 0.6rem 1.25rem; border-radius: 0.5rem; background: #f1f5f9; border: 1px solid #cbd5e1; font-weight: 700; cursor: pointer;" onclick="closeConfirmModal()">Cancel</button>
            <button class="btn-primary" id="confirmSubmitBtn" style="font-size: 0.9rem; padding: 0.6rem 1.25rem; border-radius: 0.5rem; background: #055741; color: white; border: none; font-weight: 700; cursor: pointer;">Submit Response</button>
        </div>
    </div>
</div>

<!-- PDF Viewer Modal -->
<div id="pdfViewerModal" class="pdf-viewer-modal" aria-hidden="true">
    <div class="pdf-viewer-dialog" role="dialog" aria-modal="true" aria-labelledby="pdfViewerTitle">
        <div class="pdf-viewer-header">
            <div>
                <div class="pdf-viewer-label">PDF Preview</div>
                <h3 id="pdfViewerTitle">Supporting File</h3>
            </div>
            <div class="pdf-viewer-actions">
                <a id="pdfViewerNewTab" href="#" target="_blank" rel="noopener" class="pdf-open-new-tab">Open in new tab</a>
                <button type="button" class="pdf-close-btn" onclick="closePdfViewer()" aria-label="Close PDF preview">Close</button>
            </div>
        </div>
        <iframe id="pdfViewerFrame" title="PDF preview"></iframe>
    </div>
</div>

<?php
// Flatten sections to find all 'for review' questions
$forReviewQuestions = [];
if (!function_exists('flattenForReviewQuestions')) {
    function flattenForReviewQuestions($sections, &$result) {
        foreach ($sections as $section) {
            if (!empty($section['questions'])) {
                foreach ($section['questions'] as $q) {
                    $status = strtolower($q['latest_answer_status'] ?? 'no answer');
                    if ($status === 'for review') {
                        $result[] = $q;
                    }
                }
            }
            if (!empty($section['subsections'])) {
                flattenForReviewQuestions($section['subsections'], $result);
            }
        }
    }
}
flattenForReviewQuestions($sections, $forReviewQuestions);
?>

<!-- DH Verification Modal -->
<div id="dhVerificationModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); z-index: 10000; align-items: center; justify-content: center; overflow-y: auto; padding: 2rem;">
    <div style="background: white; border-radius: 1rem; width: 100%; max-width: 900px; padding: 2rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; max-height: 90vh;">
        <h3 style="margin-top: 0; color: #0f172a; font-weight: 800; font-size: 1.5rem; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" style="color: #055741;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><polyline points="16 13 8 13"></polyline><polyline points="16 17 8 17"></polyline><polyline points="10 9 9 9 8 9"></polyline></svg>
            Upload Department Head Verification
        </h3>
        
        <div style="flex-grow: 1; overflow-y: auto; padding-right: 0.5rem; margin-bottom: 1.5rem;">
            <?php if (empty($forReviewQuestions)): ?>
                <div style="background: #f8fafc; padding: 2rem; text-align: center; border-radius: 0.75rem; border: 1px dashed #cbd5e1; color: #64748b; font-weight: 600;">
                    No questions are currently 'For Review'.
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 0.75rem; font-weight: 800; color: #334155;">Reference No.</th>
                            <th style="padding: 0.75rem; font-weight: 800; color: #334155;">Question / Answers</th>
                            <th style="padding: 0.75rem; font-weight: 800; color: #334155; text-align: center;">Accepted</th>
                            <th style="padding: 0.75rem; font-weight: 800; color: #334155; text-align: center;">Returned</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($forReviewQuestions as $q): ?>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 1rem 0.75rem; color: #475569; font-weight: 600; vertical-align: top;">
                                    <?= htmlspecialchars($q['ref_no']) ?>
                                </td>
                                <td style="padding: 1rem 0.75rem; vertical-align: top;">
                                    <div style="font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;"><?= htmlspecialchars($q['question_text']) ?></div>
                                    <div style="font-size: 0.8rem; color: #64748b;">
                                        <strong>Answer:</strong> <?= htmlspecialchars(ucfirst($q['answers'][0]['answer'] ?? 'N/A')) ?><br>
                                        <strong>Explanation:</strong> <?= htmlspecialchars($q['answers'][0]['explanation'] ?? '') ?>
                                    </div>
                                    <?php if (!empty($q['for_review_uploaded_files'])): ?>
                                        <div style="margin-top: 0.5rem; font-size: 0.8rem;">
                                            <strong>Files:</strong>
                                            <ul style="margin: 0; padding-left: 1.25rem;">
                                                <?php foreach ($q['for_review_uploaded_files'] as $f): ?>
                                                    <li><?= htmlspecialchars(basename($f['file_path'])) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem 0.75rem; text-align: center; vertical-align: top;">
                                    <input type="radio" name="dh_eval_<?= $q['id'] ?>" value="pending" class="dh-eval-radio" data-qid="<?= $q['id'] ?>" style="width: 1.2rem; height: 1.2rem; accent-color: #055741; cursor: pointer;">
                                </td>
                                <td style="padding: 1rem 0.75rem; text-align: center; vertical-align: top;">
                                    <input type="radio" name="dh_eval_<?= $q['id'] ?>" value="returned" class="dh-eval-radio" data-qid="<?= $q['id'] ?>" style="width: 1.2rem; height: 1.2rem; accent-color: #ef4444; cursor: pointer;">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (!empty($forReviewQuestions)): ?>
                <div style="margin-top: 1.5rem;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 700; color: #475569; margin-bottom: 0.5rem;">Upload Verified Form (PDF)</label>
                    <div class="upload-dropzone" id="dhvf-dropzone" onclick="document.getElementById('dhvf-file-input').click()">
                        <div class="upload-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        </div>
                        <div class="upload-text" id="dhvf-dropzone-text">Drag & drop verified PDF form here, or click to upload</div>
                        <div class="upload-subtext">PDF files only (Max size: 10MB)</div>
                        <input type="file" id="dhvf-file-input" accept="application/pdf" style="display: none;">
                    </div>
                </div>

                <div style="margin-top: 1.5rem;">
                    <label style="display: block; font-size: 0.9rem; font-weight: 700; color: #475569; margin-bottom: 0.5rem;">Type "confirm" to verify</label>
                    <input type="text" id="dhvf-confirm-input" placeholder="confirm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0.75rem; font-size: 0.9rem; outline: none; font-weight: 600; font-family: inherit; color: #334155; transition: border-color 0.2s;" autocomplete="off">
                </div>
            <?php endif; ?>
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
            <button class="btn-secondary" style="font-size: 0.95rem; padding: 0.6rem 1.25rem; border-radius: 0.5rem; background: #f1f5f9; border: 1px solid #cbd5e1; font-weight: 700; cursor: pointer;" onclick="closeDhVerificationModal()">Cancel</button>
            <?php if (!empty($forReviewQuestions)): ?>
                <button class="btn-primary" id="dhvfSubmitBtn" style="font-size: 0.95rem; padding: 0.6rem 1.25rem; border-radius: 0.5rem; background: #055741; color: white; border: none; font-weight: 700; cursor: pointer;" onclick="submitDhVerification()">Submit Verification</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // In-memory cache of dragged files per question
    const selectedFiles = {};
    const pendingDeletedFiles = {};
    let pendingSubmitQuestionId = null;
    const statusColorStorageKey = 'iad_questionnaire_status_colors';

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function escapeAttribute(value) {
        return escapeHtml(value).replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    const submittedQuestionFocusKey = 'iad_submitted_question_focus';
    const currentQuestionnaireId = <?= (int)$questionnaire['id'] ?>;
    const answerViewStorageKey = `iad_answer_view_mode_${currentQuestionnaireId}`;
    let currentAnswerViewMode = 'old';

    function ensureAnswerQuestionPlaceholders() {
        document.querySelectorAll('.question-item-card').forEach(card => {
            if (card.dataset.placeholderReady === '1') return;
            const placeholder = document.createElement('span');
            placeholder.className = 'answer-question-placeholder';
            placeholder.dataset.questionId = card.id.replace('card-', '');
            card.parentNode.insertBefore(placeholder, card);
            card.dataset.placeholderReady = '1';
        });
    }

    function restoreQuestionCardsToNewView() {
        document.querySelectorAll('.answer-question-placeholder').forEach(placeholder => {
            const card = document.getElementById(`card-${placeholder.dataset.questionId}`);
            if (card && card.parentNode !== placeholder.parentNode) {
                placeholder.parentNode.insertBefore(card, placeholder.nextSibling);
            }
        });
    }

    function getDirectSectionBody(section) {
        return Array.from(section.children).find(child => child.classList && child.classList.contains('section-body')) || null;
    }

    function getSectionTitle(section) {
        return section.querySelector(':scope > .section-header .section-title')?.textContent?.trim() || 'Section';
    }

    function appendOldSectionRow(tbody, title, level) {
        const row = document.createElement('tr');
        row.className = 'answer-old-section-row';
        row.dataset.level = String(level);
        const cell = document.createElement('td');
        cell.colSpan = 5;
        cell.style.setProperty('--section-indent', `${Math.min(level, 6) * 1.25}rem`);
        cell.textContent = title;
        row.appendChild(cell);
        tbody.appendChild(row);
    }

    function appendOldQuestionRow(tbody, card) {
        const row = document.createElement('tr');
        row.className = 'answer-old-question-row';
        row.dataset.status = card.dataset.status || 'no answer';
        row.dataset.questionId = card.id.replace('card-', '');

        ['area', 'criteria', 'refNo', 'questionText'].forEach(key => {
            const cell = document.createElement('td');
            cell.className = `answer-old-${key.replace(/[A-Z]/g, match => '-' + match.toLowerCase())}-cell`;
            cell.textContent = card.dataset[key] || (key === 'questionText' ? 'Untitled question' : '');
            row.appendChild(cell);
        });

        const assessmentCell = document.createElement('td');
        assessmentCell.className = 'answer-old-assessment-cell';

        const statusBar = document.createElement('div');
        statusBar.className = 'answer-old-status-bar';
        const statusBadge = document.createElement('span');
        statusBadge.className = card.querySelector('.badge-pill')?.className || 'badge-pill badge-no-answer';
        statusBadge.dataset.oldStatusBadge = row.dataset.questionId;
        statusBadge.innerHTML = card.querySelector('.badge-pill')?.innerHTML || '<span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span> No answer';
        statusBar.appendChild(statusBadge);
        assessmentCell.appendChild(statusBar);

        assessmentCell.appendChild(card);
        row.appendChild(assessmentCell);
        tbody.appendChild(row);
    }

    function appendOldSection(tbody, section, level = 0) {
        appendOldSectionRow(tbody, getSectionTitle(section), level);
        const body = getDirectSectionBody(section);
        if (!body) return;

        Array.from(body.children).forEach(child => {
            if (child.classList?.contains('question-item-card')) {
                appendOldQuestionRow(tbody, child);
            } else if (child.classList?.contains('section-accordion')) {
                appendOldSection(tbody, child, level + 1);
            }
        });
    }

    function buildOldAnswerTable() {
        ensureAnswerQuestionPlaceholders();
        restoreQuestionCardsToNewView();
        const tbody = document.getElementById('answerOldTableBody');
        const newPane = document.getElementById('answerNewViewPane');
        if (!tbody || !newPane) return;

        tbody.textContent = '';
        Array.from(newPane.children).forEach(child => {
            if (child.classList?.contains('section-accordion')) {
                appendOldSection(tbody, child, 0);
            }
        });
    }

    function setAnswerViewMode(mode, persist = true) {
        const normalizedMode = mode === 'new' ? 'new' : 'old';
        currentAnswerViewMode = normalizedMode;

        if (normalizedMode === 'old') {
            buildOldAnswerTable();
        } else {
            restoreQuestionCardsToNewView();
        }

        const shell = document.getElementById('answerPageShell');
        const oldPane = document.getElementById('answerOldViewPane');
        const newPane = document.getElementById('answerNewViewPane');
        if (shell) {
            shell.classList.toggle('answer-view-old', normalizedMode === 'old');
            shell.classList.toggle('answer-view-new', normalizedMode === 'new');
        }
        if (oldPane) oldPane.style.display = normalizedMode === 'old' ? 'block' : 'none';
        if (newPane) newPane.style.display = normalizedMode === 'new' ? 'block' : 'none';

        document.querySelectorAll('.answer-view-btn').forEach(button => {
            const isActive = button.dataset.answerViewTarget === normalizedMode;
            button.classList.toggle('active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        applyQuestionStatusFilter(currentQuestionStatusFilter || 'all');

        if (persist) {
            try {
                localStorage.setItem(answerViewStorageKey, normalizedMode);
            } catch (error) {
                // Local storage may be blocked; the page still works with the default old view.
            }
        }
    }

    function initializeAnswerViewMode() {
        let savedMode = 'old';
        try {
            savedMode = localStorage.getItem(answerViewStorageKey) || 'old';
        } catch (error) {
            savedMode = 'old';
        }
        setAnswerViewMode(savedMode, false);

        document.querySelectorAll('.answer-view-btn').forEach(button => {
            button.addEventListener('click', () => setAnswerViewMode(button.dataset.answerViewTarget || 'old'));
        });
    }

    function rememberSubmittedQuestionFocus(qid) {
        try {
            sessionStorage.setItem(submittedQuestionFocusKey, JSON.stringify({
                questionnaireId: currentQuestionnaireId,
                questionId: Number(qid),
            }));
        } catch (error) {
            // Session storage may be unavailable in private or locked-down browsers.
        }
    }

    function applySubmittedQuestionFocus() {
        let focus = null;
        try {
            focus = JSON.parse(sessionStorage.getItem(submittedQuestionFocusKey) || 'null');
        } catch (error) {
            focus = null;
        }

        if (!focus || Number(focus.questionnaireId) !== currentQuestionnaireId || !focus.questionId) {
            return;
        }

        try {
            sessionStorage.removeItem(submittedQuestionFocusKey);
        } catch (error) {
            // Ignore cleanup failures.
        }

        document.querySelectorAll('.section-accordion').forEach(accordion => {
            accordion.classList.add('open');
            const body = accordion.querySelector('.section-body');
            if (body) {
                body.style.display = 'block';
            }
        });

        const card = document.getElementById(`card-${Number(focus.questionId)}`);
        if (!card) return;

        setTimeout(() => {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            card.classList.add('submitted-question-focus');
            setTimeout(() => card.classList.remove('submitted-question-focus'), 3600);
        }, 250);
    }
    function setStatusColorMode(enabled) {
        const isEnabled = Boolean(enabled);
        document.body.classList.toggle('questionnaire-status-colors', isEnabled);

        const toggle = document.getElementById('statusColorToggle');
        const toggleText = document.getElementById('statusColorToggleText');
        if (toggle) {
            toggle.classList.toggle('is-on', isEnabled);
            toggle.setAttribute('aria-pressed', isEnabled ? 'true' : 'false');
        }
        if (toggleText) {
            toggleText.textContent = `Status Colors: ${isEnabled ? 'On' : 'Off'}`;
        }

        try {
            localStorage.setItem(statusColorStorageKey, isEnabled ? 'on' : 'off');
        } catch (error) {
            // Local storage can be unavailable in some locked-down browsers.
        }
    }

    const questionFilterLabels = {
        all: 'All',
        'no answer': 'No Answer',
        draft: 'Draft',
        'for review': 'For Review',
        returned: 'Returned',
        pending: 'Pending',
        rejected: 'Rejected',
        accepted: 'Accepted'
    };
    let currentQuestionStatusFilter = 'all';

    function statusSlug(status) {
        return String(status || 'no answer').toLowerCase().replace(/\s+/g, '-');
    }

    function refreshQuestionStatusCounts() {
        const counts = {
            total: 0,
            'no answer': 0,
            draft: 0,
            'for review': 0,
            returned: 0,
            pending: 0,
            rejected: 0,
            accepted: 0
        };

        document.querySelectorAll('.question-item-card').forEach(card => {
            const status = questionFilterLabels[card.dataset.status] ? card.dataset.status : 'no answer';
            counts.total += 1;
            counts[status] = (counts[status] || 0) + 1;
        });

        Object.entries(counts).forEach(([status, value]) => {
            const el = document.getElementById(`answerCount-${statusSlug(status)}`);
            if (el) el.textContent = String(value);
        });
    }

    function setQuestionFilterMenuOpen(open) {
        const menu = document.getElementById('questionFilterMenu');
        const toggle = document.getElementById('questionFilterToggle');
        if (!menu || !toggle) return;
        if (!open && menu.contains(document.activeElement)) {
            document.activeElement.blur();
        }
        menu.classList.toggle('is-open', open);
        menu.setAttribute('aria-hidden', open ? 'false' : 'true');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function setActiveFilterUi(status) {
        const label = document.getElementById('questionFilterLabel');
        if (label) label.textContent = `Filter: ${questionFilterLabels[status] || 'All'}`;

        document.querySelectorAll('[data-filter-status]').forEach(button => {
            button.classList.toggle('is-active', button.dataset.filterStatus === status);
        });
    }

    function applyQuestionStatusFilter(status = 'all') {
        currentQuestionStatusFilter = questionFilterLabels[status] ? status : 'all';
        let visibleCards = 0;

        document.querySelectorAll('.question-item-card').forEach(card => {
            const matches = currentQuestionStatusFilter === 'all' || card.dataset.status === currentQuestionStatusFilter;
            card.classList.toggle('question-filter-hidden', !matches);
            const oldRow = card.closest('.answer-old-question-row');
            if (oldRow) {
                oldRow.classList.toggle('question-filter-hidden', !matches);
            }
            if (matches) visibleCards += 1;
        });

        if (currentAnswerViewMode === 'old') {
            document.querySelectorAll('.answer-old-section-row').forEach(sectionRow => {
                const level = Number(sectionRow.dataset.level || 0);
                let sibling = sectionRow.nextElementSibling;
                let hasVisibleQuestion = false;
                while (sibling) {
                    if (sibling.classList.contains('answer-old-section-row') && Number(sibling.dataset.level || 0) <= level) {
                        break;
                    }
                    if (sibling.classList.contains('answer-old-question-row') && !sibling.classList.contains('question-filter-hidden')) {
                        hasVisibleQuestion = true;
                        break;
                    }
                    sibling = sibling.nextElementSibling;
                }
                sectionRow.classList.toggle('question-filter-hidden', currentQuestionStatusFilter !== 'all' && !hasVisibleQuestion);
            });
        }

        const filtered = currentQuestionStatusFilter !== 'all';
        const sections = Array.from(document.querySelectorAll('.section-accordion')).reverse();
        sections.forEach(section => {
            const hasVisibleCard = Boolean(section.querySelector('.question-item-card:not(.question-filter-hidden)'));
            section.classList.toggle('question-filter-hidden', filtered && !hasVisibleCard);
            if (filtered && hasVisibleCard) {
                section.classList.add('open');
                const body = section.querySelector('.section-body');
                if (body) body.style.display = 'block';
            }
        });

        const emptyState = document.getElementById('questionFilterEmptyState');
        if (emptyState) emptyState.style.display = visibleCards === 0 ? 'block' : 'none';
        setActiveFilterUi(currentQuestionStatusFilter);
        setQuestionFilterMenuOpen(false);
    }

    function updateExplanationCounter(textarea) {
        const counter = document.getElementById(textarea.dataset.counterId || '');
        if (!counter) return;
        const length = textarea.value.length;
        counter.textContent = `${length} / 750`;
        counter.classList.toggle('is-over', length > 750);
    }

    function initializeExplanationCounters() {
        document.querySelectorAll('textarea[name="explanation"][data-counter-id]').forEach(updateExplanationCounter);
    }
    function openPdfViewer(url, title) {
        const modal = document.getElementById('pdfViewerModal');
        const frame = document.getElementById('pdfViewerFrame');
        const titleEl = document.getElementById('pdfViewerTitle');
        const newTabLink = document.getElementById('pdfViewerNewTab');

        if (!modal || !frame || !newTabLink) return;

        if (titleEl) {
            titleEl.textContent = title || 'Supporting File';
        }
        newTabLink.href = url;
        frame.src = url;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closePdfViewer() {
        const modal = document.getElementById('pdfViewerModal');
        const frame = document.getElementById('pdfViewerFrame');
        const newTabLink = document.getElementById('pdfViewerNewTab');

        if (frame) {
            frame.removeAttribute('src');
        }
        if (newTabLink) {
            newTabLink.href = '#';
        }
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
        document.body.style.overflow = '';
    }

    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('statusColorToggle');
        let initialStatusColors = false;
        try {
            initialStatusColors = localStorage.getItem(statusColorStorageKey) === 'on';
        } catch (error) {
            initialStatusColors = false;
        }
        setStatusColorMode(initialStatusColors);
        initializeAnswerViewMode();
        applySubmittedQuestionFocus();

        if (toggle) {
            toggle.addEventListener('click', () => {
                setStatusColorMode(toggle.getAttribute('aria-pressed') !== 'true');
            });
        }

        document.addEventListener('click', (event) => {
            const pdfButton = event.target.closest('.pdf-view-btn');
            if (!pdfButton) return;

            event.preventDefault();
            openPdfViewer(pdfButton.dataset.pdfUrl, pdfButton.dataset.pdfTitle);
        });

        const pdfModal = document.getElementById('pdfViewerModal');
        if (pdfModal) {
            pdfModal.addEventListener('click', (event) => {
                if (event.target === pdfModal) {
                    closePdfViewer();
                }
            });
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closePdfViewer();
            }
        });
    });

    // Toggle Section collapsing
    function toggleSection(header) {
        const accordion = header.closest('.section-accordion');
        const body = accordion.querySelector('.section-body');
        
        if (accordion.classList.contains('open')) {
            accordion.classList.remove('open');
            body.style.display = 'none';
        } else {
            accordion.classList.add('open');
            body.style.display = 'block';
        }
    }

    function toggleSubmissionHistory(qid, button) {
        const panel = document.getElementById(`submission-history-${qid}`);
        if (!panel) return;

        const isOpen = panel.style.display !== 'none';
        panel.style.display = isOpen ? 'none' : 'block';
        if (button) {
            button.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            button.classList.toggle('is-open', !isOpen);
        }
    }

    // Trigger local file input click
    function triggerFileInput(qid) {
        document.getElementById(`file-input-${qid}`).click();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const filterToggle = document.getElementById('questionFilterToggle');
        if (filterToggle) {
            filterToggle.addEventListener('click', (event) => {
                event.stopPropagation();
                setQuestionFilterMenuOpen(filterToggle.getAttribute('aria-expanded') !== 'true');
            });
        }

        document.querySelectorAll('#questionFilterMenu [data-filter-status], .answer-count-card[data-filter-status]').forEach(button => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                applyQuestionStatusFilter(button.dataset.filterStatus || 'all');
            });
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.question-filter-control')) {
                setQuestionFilterMenuOpen(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setQuestionFilterMenuOpen(false);
            }
        });

        refreshQuestionStatusCounts();
        initializeExplanationCounters();
        applyQuestionStatusFilter('all');
    });
    // Drag and drop events setup
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.upload-dropzone').forEach(dropzone => {
            if (!dropzone.id.startsWith('dropzone-')) return;
            const qid = dropzone.id.split('-')[1];
            
            // Dragover / Dragenter
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropzone.classList.add('dragover');
                }, false);
            });

            // Dragleave / Dragend
            ['dragleave', 'dragend'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropzone.classList.remove('dragover');
                }, false);
            });

            // Drop
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('dragover');
                if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    handleFilesSelected(qid, e.dataTransfer.files);
                }
            }, false);
        });
    });

    function handleFilesSelected(qid, fileList) {
        const files = Array.from(fileList || []);
        if (files.length === 0) return;

        for (const file of files) {
            if (file.size > 10 * 1024 * 1024) {
                showToast(`${file.name} exceeds the 10MB limit.`, 'error');
                resetFileInput(qid);
                return;
            }

            if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
                showToast(`${file.name} is not a PDF evidence file.`, 'error');
                resetFileInput(qid);
                return;
            }
        }

        selectedFiles[qid] = files;

        const textEl = document.getElementById(`dropzone-text-${qid}`);
        if (textEl) {
            const safeNames = files.map(file => escapeHtml(file.name));
            const fileLabel = files.length === 1 ? 'Selected PDF' : `Selected ${files.length} PDFs`;
            textEl.innerHTML = `<span style="color: #055741; font-weight: 800;">${fileLabel}:</span> ${safeNames.join(', ')}`;
        }

        showToast(`${files.length} PDF file${files.length === 1 ? '' : 's'} selected successfully.`, 'success');
    }

    function resetFileInput(qid) {
        const fileInput = document.getElementById(`file-input-${qid}`);
        if (fileInput) fileInput.value = '';

        const textEl = document.getElementById(`dropzone-text-${qid}`);
        if (textEl) {
            textEl.innerHTML = 'Drag & drop PDF evidence files here, or click to upload';
        }

        delete selectedFiles[qid];
    }

    // Handle File Selection
    function handleFileSelected(qid, file) {
        if (!file) return;

        // Size check (10MB limit)
        if (file.size > 10 * 1024 * 1024) {
            showToast('File size exceeds the 10MB limit.', 'error');
            return;
        }

        // Mime check
        if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
            showToast('Only PDF evidence files are allowed.', 'error');
            return;
        }

        // Cache it in client state
        selectedFiles[qid] = file;

        // Update dropzone styling/text
        const textEl = document.getElementById(`dropzone-text-${qid}`);
        textEl.innerHTML = `<span style="color: #055741; font-weight: 800;">✓ Selected PDF:</span> ${file.name}`;
        
        showToast('PDF file selected successfully.', 'success');
    }

    // Save Draft AJAX call
    function saveAnswer(qid, action = 'draft') {
        const form = document.getElementById(`form-${qid}`);
        const statusEl = document.getElementById(`save-status-${qid}`);
        
        // Validation check
        const answerVal = form.querySelector('input[name="answer"]:checked')?.value;
        const explanationVal = form.querySelector('textarea[name="explanation"]').value.trim();

        if (!['yes', 'no'].includes(answerVal)) {
            showToast('Please select Yes or No before saving.', 'error');
            return;
        }

        if (explanationVal.length > 750) {
            showToast('Explanation / Justification is limited to 750 characters.', 'error');
            return;
        }

        statusEl.innerHTML = `
            <svg class="animate-spin" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke="rgba(0,0,0,0.1)"></circle><path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" fill="currentColor"></path></svg>
            Saving...
        `;

        const formData = new FormData();
        formData.append('question_id', qid);
        formData.append('action', action);
        formData.append('answer', answerVal);
        formData.append('explanation', explanationVal);

        // Include files if selected
        if (selectedFiles[qid] && selectedFiles[qid].length > 0) {
            selectedFiles[qid].forEach(file => {
                formData.append('files[]', file);
            });
        }

        if (action === 'draft' && pendingDeletedFiles[qid] && pendingDeletedFiles[qid].size > 0) {
            pendingDeletedFiles[qid].forEach(fileId => {
                formData.append('delete_file_ids[]', fileId);
            });
        }

        fetch(window.location.pathname, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusEl.innerHTML = `
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color: #10b981;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span style="color: #10b981;">Saved!</span>
                `;
                
                // Update badge and class
                updateQuestionBadge(qid, data.status);
                
                // If action is review submit, reload page to lock inputs, or dynamically lock them
                if (action === 'for-review') {
                    showToast('Response submitted successfully! Locking inputs...', 'success');
                    rememberSubmittedQuestionFocus(qid);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast('Draft saved successfully.', 'success');
                    removePendingDeletedFileItems(qid);
                    // Add filenames to uploaded files list if they were uploaded
                    if (selectedFiles[qid] && selectedFiles[qid].length > 0) {
                        const uploadedRows = Array.isArray(data.files) ? data.files : [];
                        const uploadedPaths = Array.isArray(data.file_paths)
                            ? data.file_paths
                            : (data.file_path ? [data.file_path] : []);

                        selectedFiles[qid].forEach((file, index) => {
                            const uploadedRow = uploadedRows[index] || {};
                            addFileToList(qid, file.name, uploadedRow.path || uploadedPaths[index] || null, uploadedRow.id || null, uploadedRow.status || 'draft');
                        });

                        resetFileInput(qid);
                    }
                }
            } else {
                statusEl.innerHTML = `<span style="color: #ef4444;">Failed to save</span>`;
                showToast(data.error || 'Failed to save changes.', 'error');
            }
        })
        .catch(err => {
            statusEl.innerHTML = `<span style="color: #ef4444;">Connection error</span>`;
            showToast('A network connection error occurred.', 'error');
            console.error(err);
        });
    }

    function ensureDeleteSet(qid) {
        if (!pendingDeletedFiles[qid]) {
            pendingDeletedFiles[qid] = new Set();
        }
        return pendingDeletedFiles[qid];
    }

    function markFileForDeletion(qid, fileId) {
        const id = Number(fileId);
        if (!id) return;

        ensureDeleteSet(qid).add(id);
        const item = document.getElementById(`file-item-${id}`);
        if (item) {
            item.classList.add('pending-file-delete');
            const deleteBtn = item.querySelector('.file-delete-btn');
            const undoBtn = item.querySelector('.file-undo-delete-btn');
            const note = item.querySelector('.file-delete-note');
            if (deleteBtn) deleteBtn.style.display = 'none';
            if (undoBtn) undoBtn.style.display = 'inline-flex';
            if (note) note.style.display = 'inline-flex';
        }

        showToast('File marked for removal. Click Save Draft to apply the change.', 'success');
    }

    function undoFileDeletion(qid, fileId) {
        const id = Number(fileId);
        if (!id || !pendingDeletedFiles[qid]) return;

        pendingDeletedFiles[qid].delete(id);
        const item = document.getElementById(`file-item-${id}`);
        if (item) {
            item.classList.remove('pending-file-delete');
            const deleteBtn = item.querySelector('.file-delete-btn');
            const undoBtn = item.querySelector('.file-undo-delete-btn');
            const note = item.querySelector('.file-delete-note');
            if (deleteBtn) deleteBtn.style.display = 'inline-flex';
            if (undoBtn) undoBtn.style.display = 'none';
            if (note) note.style.display = 'none';
        }
    }

    function removePendingDeletedFileItems(qid) {
        if (!pendingDeletedFiles[qid] || pendingDeletedFiles[qid].size === 0) return;

        pendingDeletedFiles[qid].forEach(fileId => {
            document.getElementById(`file-item-${fileId}`)?.remove();
        });
        pendingDeletedFiles[qid].clear();
    }

    function getReviewFileNames(qid) {
        const files = [];
        const list = document.getElementById(`files-list-${qid}`);

        if (list) {
            list.querySelectorAll('.uploaded-file-item').forEach(item => {
                if (item.classList.contains('pending-file-delete')) return;
                if (item.dataset.reviewFile === '0') return;
                const name = item.dataset.fileName || item.querySelector('[data-file-name]')?.dataset.fileName || item.querySelector('span')?.textContent?.trim() || '';
                if (name) files.push({ name, state: 'Saved' });
            });
        }

        if (selectedFiles[qid] && selectedFiles[qid].length > 0) {
            selectedFiles[qid].forEach(file => {
                files.push({ name: file.name, state: 'Selected for upload' });
            });
        }

        return files;
    }

    function appendSummaryField(container, label, value) {
        const field = document.createElement('div');
        field.className = 'confirm-review-field';

        const strong = document.createElement('strong');
        strong.textContent = label;

        const body = document.createElement('div');
        body.textContent = value || 'None provided';

        field.appendChild(strong);
        field.appendChild(body);
        container.appendChild(field);
    }

    function populateSubmissionReview(qid) {
        const summary = document.getElementById('confirmReviewSummary');
        const form = document.getElementById(`form-${qid}`);
        const card = document.getElementById(`card-${qid}`);
        if (!summary || !form) return;

        summary.textContent = '';

        const heading = document.createElement('div');
        heading.className = 'confirm-review-heading';
        heading.textContent = 'Review your response';
        summary.appendChild(heading);

        const questionText = card?.querySelector('.question-text')?.textContent?.trim() || `Question ${qid}`;
        const answerVal = form.querySelector('input[name="answer"]:checked')?.value || '';
        const explanationVal = form.querySelector('textarea[name="explanation"]')?.value?.trim() || '';

        appendSummaryField(summary, 'Question', questionText);
        appendSummaryField(summary, 'Answer', answerVal ? answerVal.toUpperCase() : 'No answer selected');
        appendSummaryField(summary, 'Explanation', explanationVal);

        const filesField = document.createElement('div');
        filesField.className = 'confirm-review-field';
        const filesLabel = document.createElement('strong');
        filesLabel.textContent = 'Uploaded files';
        filesField.appendChild(filesLabel);

        const files = getReviewFileNames(qid);
        if (files.length === 0) {
            const empty = document.createElement('div');
            empty.textContent = 'No files attached';
            filesField.appendChild(empty);
        } else {
            const list = document.createElement('ul');
            list.className = 'confirm-review-file-list';
            files.forEach(file => {
                const item = document.createElement('li');
                const name = document.createElement('span');
                name.textContent = file.name;
                const state = document.createElement('em');
                state.textContent = file.state;
                item.appendChild(name);
                item.appendChild(state);
                list.appendChild(item);
            });
            filesField.appendChild(list);
        }

        summary.appendChild(filesField);
    }
    // Trigger Submit for Review confirmation
    function submitForReview(qid) {
        // Validation check
        const form = document.getElementById(`form-${qid}`);
        const answerVal = form.querySelector('input[name="answer"]:checked')?.value;
        if (!['yes', 'no'].includes(answerVal)) {
            showToast('Please select Yes or No before submitting.', 'error');
            return;
        }

        const explanationVal = form.querySelector('textarea[name="explanation"]')?.value || '';
        if (explanationVal.length > 750) {
            showToast('Explanation / Justification is limited to 750 characters.', 'error');
            return;
        }

        if (pendingDeletedFiles[qid] && pendingDeletedFiles[qid].size > 0) {
            showToast('Please click Save Draft to apply file deletions before submitting for review.', 'error');
            return;
        }

        pendingSubmitQuestionId = qid;
        populateSubmissionReview(qid);
        document.getElementById('confirmModal').style.display = 'flex';
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').style.display = 'none';
        pendingSubmitQuestionId = null;
    }

    // Modal submit confirmation
    document.getElementById('confirmSubmitBtn').addEventListener('click', () => {
        if (pendingSubmitQuestionId) {
            const qid = pendingSubmitQuestionId;
            closeConfirmModal();
            saveAnswer(qid, 'for-review');
        }
    });

    // Update question badge
    function updateQuestionBadge(qid, status) {
        const normalizedStatus = String(status || 'no answer').toLowerCase();
        const badge = document.getElementById(`badge-${qid}`);
        const card = document.getElementById(`card-${qid}`);

        const oldRow = card?.closest('.answer-old-question-row') || document.querySelector(`.answer-old-question-row[data-question-id="${qid}"]`);
        if (card) {
            card.dataset.status = normalizedStatus;
        }
        if (oldRow) {
            oldRow.dataset.status = normalizedStatus;
        }

        const badgeStatus = ['returned', 'rejected'].includes(normalizedStatus) ? 'rejected' : normalizedStatus;
        const label = normalizedStatus === 'returned'
            ? 'Returned'
            : normalizedStatus.charAt(0).toUpperCase() + normalizedStatus.slice(1);
        const badgeMarkup = `<span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span> ${label}`;

        if (badge) {
            badge.className = `badge-pill badge-${badgeStatus.replace(' ', '-')}`;
            badge.innerHTML = badgeMarkup;
        }
        document.querySelectorAll(`[data-old-status-badge="${qid}"]`).forEach(oldBadge => {
            oldBadge.className = `badge-pill badge-${badgeStatus.replace(' ', '-')}`;
            oldBadge.innerHTML = badgeMarkup;
        });
        refreshQuestionStatusCounts();
        applyQuestionStatusFilter(currentQuestionStatusFilter);
    }

    // Add file to display list dynamically
    function addFileToList(qid, fileName, filePath = null, fileId = null, status = 'draft') {
        const list = document.getElementById(`files-list-${qid}`);
        if (!list) return;

        const numericFileId = Number(fileId || 0);
        const safeFileName = escapeHtml(fileName);
        const safeStatus = escapeHtml(String(status || 'draft').toUpperCase());
        const viewButton = filePath
            ? `<button type="button" class="pdf-view-btn" data-pdf-url="/questionnaires/file?file=${encodeURIComponent(filePath)}" data-pdf-title="${escapeAttribute(filePath.split(/[\\/]/).pop())}">View PDF</button>`
            : '';
        const deleteControls = numericFileId > 0
            ? `<button type="button" class="file-delete-btn" onclick="markFileForDeletion(${Number(qid)}, ${numericFileId})">Delete</button>
               <button type="button" class="file-undo-delete-btn" onclick="undoFileDeletion(${Number(qid)}, ${numericFileId})" style="display:none;">Undo</button>
               <span class="file-delete-note" style="display:none;">Will be removed when you save draft</span>`
            : '';

        const item = document.createElement('div');
        item.className = 'uploaded-file-item';
        item.dataset.fileName = fileName;
        item.dataset.reviewFile = '1';
        if (numericFileId > 0) {
            item.id = `file-item-${numericFileId}`;
            item.dataset.fileId = String(numericFileId);
        }
        item.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: #ef4444;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span style="font-weight: 700; color: #055741;">${safeFileName}</span>
                ${viewButton}
                <span style="font-size: 0.7rem; background: #e2e8f0; padding: 0.15rem 0.35rem; border-radius: 0.25rem; font-weight: 700; color: #475569;">${safeStatus}</span>
                ${deleteControls}
            </div>
        `;
        list.appendChild(item);
    }

    // Toast alerts helper
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const checkIcon = `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
        const errIcon = `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`;
        
        toast.innerHTML = `
            ${type === 'success' ? checkIcon : errIcon}
            <div>${message}</div>
        `;
        container.appendChild(toast);

        // Auto remove
        setTimeout(() => {
            toast.style.animation = 'slideIn 0.3s reverse';
            setTimeout(() => toast.remove(), 280);
        }, 3500);
    }

    // DH Verification Modal JS
    let dhvfSelectedFile = null;

    function openDhVerificationModal() {
        document.getElementById('dhVerificationModal').style.display = 'flex';
        dhvfSelectedFile = null;
        const fileInput = document.getElementById('dhvf-file-input');
        if (fileInput) fileInput.value = '';
        const textEl = document.getElementById('dhvf-dropzone-text');
        if (textEl) textEl.innerHTML = 'Drag & drop verified PDF form here, or click to upload';
        const confirmInput = document.getElementById('dhvf-confirm-input');
        if (confirmInput) confirmInput.value = '';
        
        // Clear all radios
        document.querySelectorAll('.dh-eval-radio').forEach(r => r.checked = false);
    }

    function closeDhVerificationModal() {
        document.getElementById('dhVerificationModal').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', () => {
        const dropzone = document.getElementById('dhvf-dropzone');
        if (!dropzone) return;

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'dragend'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.remove('dragover');
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                handleDhvfFile(e.dataTransfer.files[0]);
            }
        }, false);

        document.getElementById('dhvf-file-input').addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                handleDhvfFile(e.target.files[0]);
            }
        });
    });

    function handleDhvfFile(file) {
        if (!file) return;
        if (file.size > 10 * 1024 * 1024) {
            showToast('File size exceeds the 10MB limit.', 'error');
            return;
        }
        if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
            showToast('Only PDF evidence files are allowed.', 'error');
            return;
        }

        dhvfSelectedFile = file;
        document.getElementById('dhvf-dropzone-text').innerHTML = `<span style="color: #055741; font-weight: 800;">✓ Selected:</span> ${file.name}`;
        showToast('DH Verification PDF selected.', 'success');
    }

    function submitDhVerification() {
        // Collect evaluations
        const radios = document.querySelectorAll('.dh-eval-radio');
        const evaluations = {};
        const qids = new Set();
        
        radios.forEach(r => qids.add(r.dataset.qid));
        
        let allSelected = true;
        qids.forEach(qid => {
            const checked = document.querySelector(`input[name="dh_eval_${qid}"]:checked`);
            if (checked) {
                evaluations[qid] = checked.value;
            } else {
                allSelected = false;
            }
        });

        if (!allSelected) {
            showToast('You must select Accepted or Returned for every question in the list.', 'error');
            return;
        }

        if (!dhvfSelectedFile) {
            showToast('You must upload the Department Head Verification Form PDF.', 'error');
            return;
        }

        const confirmText = document.getElementById('dhvf-confirm-input').value.trim().toLowerCase();
        if (confirmText !== 'confirm') {
            showToast('Please type "confirm" in the text box to verify.', 'error');
            return;
        }

        const btn = document.getElementById('dhvfSubmitBtn');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Submitting...';

        const formData = new FormData();
        formData.append('evaluations', JSON.stringify(evaluations));
        formData.append('verification_file', dhvfSelectedFile);
        formData.append('confirm_text', 'confirm');

        fetch(`/questionnaires/<?= $questionnaire['id'] ?>/verify-dh`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Department Head Verification processed successfully!', 'success');
                closeDhVerificationModal();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showToast(data.error || 'Failed to process verification.', 'error');
                btn.disabled = false;
                btn.innerHTML = origText;
            }
        })
        .catch(err => {
            console.error(err);
            showToast('A network error occurred.', 'error');
            btn.disabled = false;
            btn.innerHTML = origText;
        });
    }
</script>

<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .answer-action-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .answer-view-switch {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    }

    .answer-view-btn {
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: #64748b;
        padding: 0.45rem 0.95rem;
        font-size: 0.8rem;
        font-weight: 900;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }

    .answer-view-btn:hover,
    .answer-view-btn.active {
        background: #055741;
        color: #ffffff;
        transform: translateY(-1px);
    }

    .answer-old-table-head {
        display: grid;
        grid-template-columns: 1.1fr 1.4fr 0.9fr 1.7fr 1.1fr;
        gap: 0;
        margin-top: 1rem;
        border: 1px solid #0f6b52;
        border-bottom: 0;
        border-radius: 0.9rem 0.9rem 0 0;
        overflow: hidden;
        background: #f8fafc;
    }

    .answer-old-table-head span {
        padding: 0.85rem 0.9rem;
        color: #055741;
        font-size: 0.72rem;
        font-weight: 900;
        text-transform: uppercase;
        text-align: center;
        border-right: 1px solid #0f6b52;
    }

    .answer-old-table-head span:last-child {
        border-right: 0;
    }

    .answer-view-new .answer-old-table-head {
        display: none;
    }

    .answer-view-old .section-accordion {
        margin-left: 0 !important;
        border-left: 0 !important;
        border-radius: 0;
        box-shadow: none;
        border-color: #0f6b52;
        background: #ffffff;
    }

    .answer-view-old .section-accordion:first-of-type {
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }

    .answer-view-old .section-header {
        min-height: 3rem;
        border-radius: 0;
        background: #f3f8f6;
        border-bottom: 1px solid #0f6b52;
        cursor: pointer;
    }

    .answer-view-old .section-title {
        color: #055741;
        font-size: 0.95rem;
        font-weight: 900;
    }

    .answer-view-old .section-body {
        padding: 0;
        background: #ffffff;
    }

    .answer-view-old .question-item-card {
        display: grid;
        grid-template-columns: 1.1fr 1.4fr 3.7fr;
        gap: 0;
        padding: 0;
        border: 0;
        border-bottom: 1px solid #0f6b52;
        border-radius: 0;
        box-shadow: none;
        background: #ffffff;
        overflow: visible;
        scroll-margin-top: 6rem;
    }

    .answer-view-old .question-item-card > div:first-child {
        display: block !important;
        margin: 0 !important;
        padding: 0.85rem;
        border-right: 1px solid #0f6b52;
        background: #fbfefc;
    }

    .answer-view-old .question-item-card > div:first-child > div:first-child {
        display: grid !important;
        align-items: start !important;
        gap: 0.45rem !important;
        font-size: 0.74rem !important;
        line-height: 1.35;
    }

    .answer-view-old .question-item-card > div:first-child .badge-pill {
        margin-top: 0.75rem;
    }

    .answer-view-old .question-text {
        margin: 0;
        padding: 0.85rem;
        border-right: 1px solid #0f6b52;
        color: #0f172a;
        font-size: 0.88rem;
        font-weight: 800;
        line-height: 1.45;
    }

    .answer-view-old .ajax-answer-form {
        display: grid;
        grid-template-columns: 0.85fr 1.5fr 1.25fr;
        align-items: stretch;
        gap: 0;
        min-width: 0;
    }

    .answer-view-old .ajax-answer-form > input[type="hidden"] {
        display: none;
    }

    .answer-view-old .option-group {
        margin: 0;
        padding: 0.85rem;
        border-right: 1px solid #0f6b52;
        align-content: start;
        justify-content: center;
        gap: 0.45rem;
    }

    .answer-view-old .ajax-answer-form > div:nth-of-type(2) {
        margin: 0 !important;
        padding: 0.85rem;
        border-right: 1px solid #0f6b52;
    }

    .answer-view-old textarea[name="explanation"] {
        min-height: 7rem;
        resize: vertical;
    }

    .answer-view-old .admin-remark-box,
    .answer-view-old .upload-dropzone,
    .answer-view-old .uploaded-files-list,
    .answer-view-old .submission-history-shell {
        grid-column: 2 / 4;
        margin: 0 !important;
        padding: 0.85rem;
        border-top: 1px solid #d7ebe3;
        border-right: 0;
    }

    .answer-view-old .card-actions {
        grid-column: 3;
        align-self: stretch;
        margin: 0;
        padding: 0.85rem;
        border-top: 1px solid #d7ebe3;
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        align-items: center;
        justify-content: flex-end;
    }

    .answer-view-old .submission-history-panel {
        max-height: 430px;
        overflow: auto;
    }

    @media (max-width: 1200px) {
        .answer-old-table-head { display: none; }
        .answer-view-old .question-item-card,
        .answer-view-old .ajax-answer-form {
            display: block;
        }
        .answer-view-old .question-item-card > div:first-child,
        .answer-view-old .question-text,
        .answer-view-old .option-group,
        .answer-view-old .ajax-answer-form > div:nth-of-type(2) {
            border-right: 0;
            border-bottom: 1px solid #d7ebe3;
        }
        .answer-view-old .admin-remark-box,
        .answer-view-old .upload-dropzone,
        .answer-view-old .uploaded-files-list,
        .answer-view-old .submission-history-shell,
        .answer-view-old .card-actions {
            grid-column: auto;
        }
    }

    .answer-view-old .questionnaire-container {
        width: min(100% - 2rem, 1800px);
        max-width: none;
    }

    .answer-view-old .answer-new-view-pane {
        display: none !important;
    }

    .answer-view-new .answer-old-view-pane {
        display: none !important;
    }

    .answer-old-view-pane {
        margin-top: 1rem;
    }

    .answer-old-table-scroll {
        width: 100%;
        overflow-x: auto;
        border: 1px solid #0f6b52;
        border-radius: 0.85rem;
        background: #ffffff;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.07);
    }

    .answer-old-table {
        width: 100%;
        min-width: 1320px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .answer-old-table th {
        position: sticky;
        top: 0;
        z-index: 4;
        background: #f6fbf9;
        color: #055741;
        border-bottom: 1px solid #0f6b52;
        border-right: 1px solid #0f6b52;
        padding: 0.9rem 0.75rem;
        text-align: center;
        font-size: 0.72rem;
        font-weight: 900;
        text-transform: uppercase;
    }

    .answer-old-table th:nth-child(1) { width: 14%; }
    .answer-old-table th:nth-child(2) { width: 16%; }
    .answer-old-table th:nth-child(3) { width: 9%; }
    .answer-old-table th:nth-child(4) { width: 20%; }
    .answer-old-table th:nth-child(5) { width: 41%; }

    .answer-old-table th:last-child,
    .answer-old-table td:last-child {
        border-right: 0;
    }

    .answer-old-table td {
        border-right: 1px solid #0f6b52;
        border-bottom: 1px solid #0f6b52;
        padding: 0.8rem;
        vertical-align: top;
        color: #0f172a;
        font-size: 0.84rem;
        line-height: 1.45;
        background: #ffffff;
    }

    .answer-old-section-row td {
        padding: 0.8rem 1rem 0.8rem calc(1rem + var(--section-indent, 0rem));
        background: #ffffff;
        color: #055741;
        font-size: 0.92rem;
        font-weight: 900;
        border-right: 0;
    }

    .answer-old-area-cell,
    .answer-old-criteria-cell,
    .answer-old-ref-no-cell {
        font-weight: 700;
        color: #334155;
    }

    .answer-old-ref-no-cell {
        text-align: center;
        color: #055741;
        font-weight: 900;
    }

    .answer-old-question-text-cell {
        font-weight: 800;
    }

    .answer-old-assessment-cell {
        padding: 0 !important;
        background: #ffffff !important;
    }

    .answer-old-status-bar {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding: 0.8rem 1rem 0;
    }

    .answer-old-assessment-cell .question-item-card {
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        background: transparent !important;
        overflow: visible;
    }

    .answer-old-assessment-cell .question-item-card > div:first-child,
    .answer-old-assessment-cell .question-text {
        display: none !important;
    }

    .answer-old-assessment-cell .ajax-answer-form {
        display: block !important;
        min-width: 0;
    }

    .answer-old-assessment-cell .option-group {
        margin: 0 !important;
        padding: 0.75rem 1.25rem 0.5rem !important;
        border: 0 !important;
        display: flex;
        justify-content: flex-start;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .answer-old-assessment-cell .option-group::before {
        content: 'Answer';
        flex: 0 0 100%;
        color: #334155;
        font-size: 0.78rem;
        font-weight: 900;
        text-transform: uppercase;
    }

    .answer-old-assessment-cell .ajax-answer-form > div:nth-of-type(2) {
        margin: 0 !important;
        padding: 0.5rem 1.25rem 1rem !important;
        border: 0 !important;
    }

    .answer-old-assessment-cell textarea[name="explanation"] {
        width: 100% !important;
        box-sizing: border-box;
        min-height: 5.5rem;
    }

    .answer-old-assessment-cell .admin-remark-box,
    .answer-old-assessment-cell .upload-dropzone,
    .answer-old-assessment-cell .uploaded-files-list,
    .answer-old-assessment-cell .submission-history-shell,
    .answer-old-assessment-cell .card-actions {
        margin: 0 !important;
        padding: 1rem 1.25rem !important;
        border-top: 1px solid #d7ebe3;
        border-right: 0 !important;
    }

    .answer-old-assessment-cell .upload-dropzone {
        border: 1px dashed #cbd5e1 !important;
        margin: 0.25rem 1.25rem 1rem !important;
        width: auto !important;
    }

    .answer-old-assessment-cell .card-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: center;
        gap: 0.65rem;
    }

    .answer-old-assessment-cell .card-actions > div:first-child {
        margin-right: auto !important;
    }

    .answer-old-assessment-cell .submission-history-panel {
        max-height: 460px;
        overflow: auto;
    }

    .questionnaire-status-colors .answer-old-question-row[data-status="draft"] td {
        background: #faf5ff;
    }

    .questionnaire-status-colors .answer-old-question-row[data-status="for review"] td {
        background: #eff6ff;
    }

    .questionnaire-status-colors .answer-old-question-row[data-status="returned"] td,
    .questionnaire-status-colors .answer-old-question-row[data-status="rejected"] td {
        background: #fef2f2;
    }

    .questionnaire-status-colors .answer-old-question-row[data-status="pending"] td {
        background: #fefce8;
    }

    .questionnaire-status-colors .answer-old-question-row[data-status="accepted"] td {
        background: #f0fdf4;
    }

    @media (max-width: 900px) {
        .answer-view-old .questionnaire-container {
            width: min(100% - 1rem, 1800px);
        }

        .answer-old-table {
            min-width: 1120px;
        }
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
        transition: all 0.2s ease;
    }

    .status-color-toggle:hover,
    .status-color-toggle.is-on {
        border-color: #055741;
        background: #e6f4f0;
        color: #055741;
    }

    .answer-status-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.85rem;
        margin: 0 0 1.5rem 0;
    }

    @media (max-width: 1100px) {
        .answer-status-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 640px) {
        .answer-status-summary-grid { grid-template-columns: 1fr; }
    }

    .answer-count-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-top: 3px solid #64748b;
        border-radius: 0.75rem;
        padding: 1rem;
        min-height: 84px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
        transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    }

    .answer-count-card:hover,
    .answer-count-card.is-active {
        transform: translateY(-1px);
        border-color: #055741;
        background: #f8fafc;
    }

    .answer-count-value {
        font-size: 1.45rem;
        font-weight: 900;
        line-height: 1;
    }

    .answer-count-label {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        text-align: center;
    }

    .question-filter-control {
        position: relative;
        display: inline-flex;
    }

    .question-filter-toggle {
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

    .question-filter-toggle:hover,
    .question-filter-toggle[aria-expanded="true"] {
        border-color: #055741;
        background: #e6f4f0;
        color: #055741;
    }

    .question-filter-menu {
        display: none;
        position: absolute;
        top: calc(100% + 0.4rem);
        left: 0;
        z-index: 30;
        min-width: 190px;
        padding: 0.35rem;
        border-radius: 0.65rem;
        border: 1px solid #dbe7e2;
        background: #ffffff;
        box-shadow: 0 16px 35px rgba(15, 23, 42, 0.14);
    }

    .question-filter-menu.is-open {
        display: grid;
        gap: 0.2rem;
    }

    .question-filter-menu button {
        border: 0;
        background: transparent;
        color: #334155;
        text-align: left;
        border-radius: 0.45rem;
        padding: 0.55rem 0.65rem;
        font-size: 0.83rem;
        font-weight: 800;
        cursor: pointer;
    }

    .question-filter-menu button:hover,
    .question-filter-menu button.is-active {
        background: #e6f4f0;
        color: #055741;
    }

    .question-filter-hidden {
        display: none !important;
    }

    .question-filter-empty {
        margin: 1rem 0;
        border: 1px dashed #cbd5e1;
        background: #ffffff;
        color: #64748b;
        border-radius: 0.75rem;
        padding: 1.25rem;
        text-align: center;
        font-size: 0.9rem;
        font-weight: 800;
    }

    .answer-explanation-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        font-size: 0.85rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 0.5rem;
        flex-wrap: wrap;
    }

    .explanation-counter {
        font-size: 0.76rem;
        font-weight: 900;
        color: #64748b;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        padding: 0.18rem 0.55rem;
        white-space: nowrap;
    }

    .explanation-counter.is-over {
        background: #fef2f2;
        border-color: #fecaca;
        color: #b91c1c;
    }
    .questionnaire-status-colors .question-item-card {
        border-left: 5px solid transparent;
        transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .submitted-question-focus {
        outline: 3px solid #facc15;
        box-shadow: 0 0 0 7px rgba(250, 204, 21, 0.25), 0 18px 38px rgba(15, 23, 42, 0.16);
        transition: outline 0.25s ease, box-shadow 0.25s ease;
    }
    .questionnaire-status-colors .question-item-card[data-status="draft"] {
        background: #faf5ff;
        border-color: #ddd6fe;
        border-left-color: #8b5cf6;
    }

    .questionnaire-status-colors .question-item-card[data-status="for review"] {
        background: #eff6ff;
        border-color: #bfdbfe;
        border-left-color: #2563eb;
    }

    .questionnaire-status-colors .question-item-card[data-status="returned"] {
        background: #fef2f2;
        border-color: #fecaca;
        border-left-color: #dc2626;
    }

    .questionnaire-status-colors .question-item-card[data-status="pending"] {
        background: #fefce8;
        border-color: #fde68a;
        border-left-color: #eab308;
    }

    .questionnaire-status-colors .question-item-card[data-status="accepted"] {
        background: #f0fdf4;
        border-color: #bbf7d0;
        border-left-color: #16a34a;
    }

    .questionnaire-status-colors .question-item-card[data-status="rejected"] {
        background: #fef2f2;
        border-color: #fecaca;
        border-left-color: #dc2626;
    }

    .pdf-view-btn {
        font-size: 0.7rem;
        background: #e6f4f0;
        padding: 0.18rem 0.45rem;
        border-radius: 0.25rem;
        border: 1px solid #b7dfd3;
        font-weight: 800;
        color: #055741;
        cursor: pointer;
    }

    .pdf-view-btn:hover {
        background: #d3ebe4;
    }

    .file-delete-btn,
    .file-undo-delete-btn {
        display: inline-flex;
        align-items: center;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #b91c1c;
        border-radius: 0.25rem;
        padding: 0.18rem 0.45rem;
        font-size: 0.7rem;
        font-weight: 800;
        cursor: pointer;
    }

    .file-undo-delete-btn {
        border-color: #bbf7d0;
        background: #f0fdf4;
        color: #166534;
    }

    .uploaded-file-item.pending-file-delete {
        opacity: 0.68;
        background: #fef2f2;
        border-color: #fecaca;
    }

    .uploaded-file-item.pending-file-delete span:first-of-type {
        text-decoration: line-through;
    }

    .file-delete-note {
        border-radius: 0.25rem;
        background: #ffedd5;
        color: #9a3412;
        padding: 0.15rem 0.35rem;
        font-size: 0.7rem;
        font-weight: 800;
    }

    .confirm-review-summary {
        border: 1px solid #dbe7e2;
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1rem;
        margin: 1rem 0;
        max-height: 42vh;
        overflow: auto;
    }

    .confirm-review-heading {
        color: #055741;
        font-size: 0.85rem;
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 0.75rem;
    }

    .confirm-review-field {
        border-top: 1px solid #e2e8f0;
        padding-top: 0.7rem;
        margin-top: 0.7rem;
        color: #334155;
        font-size: 0.88rem;
        line-height: 1.45;
    }

    .confirm-review-field:first-of-type {
        border-top: 0;
        padding-top: 0;
        margin-top: 0;
    }

    .confirm-review-field strong {
        display: block;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 0.25rem;
    }

    .confirm-review-file-list {
        list-style: none;
        padding: 0;
        margin: 0.35rem 0 0;
        display: grid;
        gap: 0.4rem;
    }

    .confirm-review-file-list li {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        border-radius: 0.45rem;
        padding: 0.45rem 0.55rem;
        font-weight: 800;
    }

    .confirm-review-file-list em {
        color: #64748b;
        font-size: 0.72rem;
        font-style: normal;
        white-space: nowrap;
    }
    .submission-history-shell {
        margin-top: 1rem;
        margin-bottom: 1rem;
    }

    .submission-history-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        border-radius: 0.5rem;
        padding: 0.45rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .submission-history-toggle:hover,
    .submission-history-toggle.is-open {
        border-color: #055741;
        background: #e6f4f0;
        color: #055741;
    }

    .submission-history-panel {
        margin-top: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #f8fafc;
        padding: 0.75rem;
    }

    .submission-history-entry {
        border: 1px solid #e2e8f0;
        border-left: 4px solid #94a3b8;
        border-radius: 0.65rem;
        background: #ffffff;
        padding: 1rem;
        margin-bottom: 0.75rem;
    }

    .submission-history-entry:last-child {
        margin-bottom: 0;
    }

    .submission-history-entry-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }

    .submission-history-meta {
        font-size: 0.82rem;
        font-weight: 800;
        color: #0f172a;
    }

    .submission-history-meta span,
    .submission-history-date span {
        margin-left: 0.45rem;
    }

    .submission-history-date {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        margin-top: 0.2rem;
    }

    .submission-history-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.65rem;
        margin-bottom: 0.75rem;
    }

    .submission-history-field,
    .submission-history-text,
    .submission-history-files {
        border-radius: 0.5rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 0.65rem 0.75rem;
        font-size: 0.82rem;
        color: #334155;
    }

    .submission-history-field strong,
    .submission-history-text strong,
    .submission-history-files strong {
        display: block;
        font-size: 0.72rem;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.25rem;
    }

    .submission-history-field span,
    .submission-history-text div {
        font-weight: 700;
        line-height: 1.45;
    }

    .submission-history-text,
    .submission-history-files {
        margin-top: 0.65rem;
    }

    .submission-history-remark {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .pdf-viewer-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 10050;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        background: rgba(15, 23, 42, 0.68);
    }

    .pdf-viewer-dialog {
        width: min(1100px, 96vw);
        height: min(820px, 92vh);
        background: #ffffff;
        border-radius: 0.75rem;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .pdf-viewer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .pdf-viewer-label {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.15rem;
    }

    .pdf-viewer-header h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        word-break: break-word;
    }

    .pdf-viewer-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
    }

    .pdf-open-new-tab,
    .pdf-close-btn {
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        background: #ffffff;
        color: #334155;
        font-size: 0.82rem;
        font-weight: 800;
        padding: 0.45rem 0.75rem;
        text-decoration: none;
        cursor: pointer;
    }

    .pdf-close-btn {
        background: #055741;
        border-color: #055741;
        color: #ffffff;
    }

    #pdfViewerFrame {
        width: 100%;
        flex: 1;
        border: 0;
        background: #f8fafc;
    }

    @media (max-width: 640px) {
        .pdf-viewer-modal {
            padding: 0.5rem;
        }

        .pdf-viewer-dialog {
            width: 100%;
            height: 94vh;
        }

        .pdf-viewer-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .pdf-viewer-actions {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>

<?php include '../app/views/partials/public/footer.php'; ?>












