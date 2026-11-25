<?php include '../app/views/partials/public/head.php'; ?>
<?php include '../app/views/partials/public/nav.php'; ?>

<?php
$p = $pagination ?? [];
$currentPage = max(1, (int)($p['current_page'] ?? 1));
$limit = (int)($p['limit'] ?? 10);
$totalRows = (int)($p['total_rows'] ?? 0);
$totalPages = max(1, (int)($p['total_pages'] ?? 1));
$search = (string)($search ?? '');
$sort = (string)($p['sort'] ?? 'created_at');
$order = (string)($p['order'] ?? 'DESC');
$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');

function questionnaireIndexUrl(array $overrides = []): string {
    global $limit, $search, $sort, $order, $currentPage;
    $query = array_merge([
        'limit' => $limit,
        'page' => $currentPage,
        'sort' => $sort,
        'order' => $order,
        'search' => $search,
    ], $overrides);
    $query = array_filter($query, static fn($value) => $value !== '' && $value !== null);
    return '/questionnaires?' . http_build_query($query);
}

function questionnaireProgressLabel(array $q): array {
    $progress = round((float)($q['completion_percent'] ?? 0), 0);
    if ($progress >= 100) return ['Completed', 'badge-accepted', $progress];
    if ($progress <= 0) return ['Not Started', 'badge-no-answer', 0];
    return ['In Progress', 'badge-pending', $progress];
}
?>

<style>
    .questionnaires-shell {
        min-height: 80vh;
        background: #f8fafc;
        padding: 3rem 0 5rem;
    }
    .questionnaire-page-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .questionnaire-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .questionnaire-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .questionnaire-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 35px rgba(15, 23, 42, 0.08);
    }
    .questionnaire-stat-label {
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 0.45rem;
    }
    .questionnaire-stat-value {
        color: #055741;
        font-size: 2rem;
        font-weight: 900;
        line-height: 1;
    }
    .questionnaire-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 14px 35px rgba(15, 23, 42, 0.05);
    }
    .questionnaire-table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .questionnaire-search-wrap {
        position: relative;
        width: min(100%, 320px);
        flex: 0 1 320px;
    }
    .questionnaire-search-wrap svg {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }
    .questionnaire-search-wrap input,
    .questionnaire-limit-select {
        height: 2.65rem;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        border-radius: 0.75rem;
        color: #334155;
        font-weight: 700;
        outline: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }
    .questionnaire-search-wrap input {
        width: 100%;
        padding: 0 0.9rem 0 2.65rem;
    }
    .questionnaire-search-wrap input:focus,
    .questionnaire-limit-select:focus {
        border-color: #055741;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(5, 87, 65, 0.08);
    }
    .questionnaire-limit-select {
        padding: 0 0.8rem;
    }
    .questionnaire-table-wrap { overflow-x: auto; }
    .questionnaire-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 820px;
    }
    .questionnaire-table th {
        background: #f8fafc;
        color: #055741;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.05em;
        padding: 0.95rem 1rem;
        text-align: left;
        text-transform: uppercase;
        border-bottom: 1px solid #e2e8f0;
    }
    .questionnaire-table td {
        padding: 1rem;
        border-bottom: 1px solid #edf2f7;
        color: #475569;
        vertical-align: middle;
    }
    .questionnaire-table tr:hover td { background: #f8fafc; }
    .questionnaire-title-text {
        display: block;
        color: #0f172a;
        font-weight: 900;
        line-height: 1.25;
    }
    .questionnaire-desc-text {
        display: block;
        color: #64748b;
        font-size: 0.8rem;
        margin-top: 0.25rem;
        max-width: 420px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .questionnaire-progress-track {
        width: 130px;
        height: 0.45rem;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
        margin-top: 0.4rem;
    }
    .questionnaire-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #055741, #16a34a);
        border-radius: inherit;
    }
    .questionnaire-row-actions {
        display: inline-flex;
        gap: 0.45rem;
        align-items: center;
    }
    .questionnaire-icon-btn {
        width: 2.3rem;
        height: 2.3rem;
        border-radius: 0.7rem;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: transform 0.16s ease, box-shadow 0.16s ease;
    }
    .questionnaire-icon-btn.is-start {
        border-color: #bbf7d0;
        background: #dcfce7;
        color: #166534;
    }
    .questionnaire-icon-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    }
    .questionnaire-table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 1.25rem;
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 700;
    }
    .questionnaire-pagination {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
    }
    .questionnaire-page-btn {
        min-width: 2.25rem;
        height: 2.25rem;
        padding: 0 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.65rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        text-decoration: none;
        background: #ffffff;
        font-weight: 800;
    }
    .questionnaire-page-btn.active {
        background: #055741;
        border-color: #055741;
        color: #ffffff;
    }
    .questionnaire-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: #94a3b8;
        font-weight: 700;
    }
    @media (max-width: 820px) {
        .questionnaire-page-head { flex-direction: column; align-items: stretch; }
        .questionnaire-stats-grid { grid-template-columns: 1fr; }
        .questionnaire-search-wrap { width: 100%; flex-basis: 100%; }
    }
</style>

<main class="questionnaires-shell">
    <div class="container" style="max-width: 1180px;">
        <div class="questionnaire-page-head">
            <div>
                <h1 style="margin:0; color:#055741; font-family:'Montserrat', sans-serif; font-size:2.15rem; font-weight:900;">Assigned Questionnaires</h1>
                <p style="margin:0.35rem 0 0; color:#64748b;">Review your assigned BAICS instruments and continue your submissions.</p>
            </div>
        </div>

        <div class="questionnaire-stats-grid">
            <article class="questionnaire-stat-card">
                <div class="questionnaire-stat-label">Average Completion</div>
                <div class="questionnaire-stat-value"><?= $e($stats['average_completion'] ?? 0) ?>%</div>
                <p class="stat-meta">Across active assignments</p>
            </article>
            <article class="questionnaire-stat-card">
                <div class="questionnaire-stat-label">Active Assessments</div>
                <div class="questionnaire-stat-value"><?= $e($stats['total_active'] ?? 0) ?></div>
                <p class="stat-meta">Assigned to your profile</p>
            </article>
            <article class="questionnaire-stat-card">
                <div class="questionnaire-stat-label">In Progress</div>
                <div class="questionnaire-stat-value"><?= $e($stats['in_progress'] ?? 0) ?></div>
                <p class="stat-meta"><?= $e($stats['completed'] ?? 0) ?> completed</p>
            </article>
        </div>

        <section class="questionnaire-table-card">
            <form id="questionnaireFilterForm" class="questionnaire-table-toolbar" method="GET" action="/questionnaires">
                <label style="display:flex; align-items:center; gap:0.75rem; color:#64748b; font-weight:800; font-size:0.9rem;">
                    Show
                    <select name="limit" class="questionnaire-limit-select" onchange="this.form.submit()">
                        <?php foreach ([5, 10, 25] as $option): ?>
                            <option value="<?= $option ?>" <?= $limit === $option ? 'selected' : '' ?>><?= $option ?></option>
                        <?php endforeach; ?>
                    </select>
                    entries
                </label>
                <input type="hidden" name="sort" value="<?= $e($sort) ?>">
                <input type="hidden" name="order" value="<?= $e($order) ?>">
                <input type="hidden" name="page" value="1">
                <div class="questionnaire-search-wrap">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="search" name="search" value="<?= $e($search) ?>" placeholder="Search questionnaires..." autocomplete="off">
                </div>
            </form>

            <div class="questionnaire-table-wrap">
                <table class="questionnaire-table">
                    <thead>
                        <tr>
                            <th>Questionnaire</th>
                            <th>Type</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($questionnaires)): ?>
                            <?php foreach ($questionnaires as $q): ?>
                                <?php [$statusText, $statusClass, $progress] = questionnaireProgressLabel($q); ?>
                                <tr>
                                    <td>
                                        <span class="questionnaire-title-text"><?= $e($q['title'] ?? 'Untitled Questionnaire') ?></span>
                                        <span class="questionnaire-desc-text"><?= $e($q['description'] ?? 'No description') ?></span>
                                    </td>
                                    <td><span class="badge-pill badge-no-answer"><?= $e($q['type'] ?? 'General') ?></span></td>
                                    <td>
                                        <strong><?= $progress ?>%</strong>
                                        <div class="questionnaire-progress-track"><div class="questionnaire-progress-fill" style="width: <?= $progress ?>%;"></div></div>
                                    </td>
                                    <td><span class="badge-pill <?= $statusClass ?>"><?= $statusText ?></span></td>
                                    <td><?= !empty($q['created_at']) ? $e(date('M d, Y', strtotime($q['created_at']))) : '-' ?></td>
                                    <td>
                                        <div class="questionnaire-row-actions">
                                            <a class="questionnaire-icon-btn" href="/questionnaires/<?= (int)$q['id'] ?>" title="View details" aria-label="View details">
                                                <span class="material-symbols-rounded" style="font-size:18px;">visibility</span>
                                            </a>
                                            <a class="questionnaire-icon-btn is-start" href="/questionnaires/<?= (int)$q['id'] ?>/answer" title="Continue answer" aria-label="Continue answer">
                                                <span class="material-symbols-rounded" style="font-size:18px;">play_arrow</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="questionnaire-empty">No assigned active questionnaires found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="questionnaire-table-footer">
                <?php
                    $start = $totalRows === 0 ? 0 : (($currentPage - 1) * $limit) + 1;
                    $end = min($totalRows, $currentPage * $limit);
                ?>
                <span>Showing <?= $start ?> to <?= $end ?> of <?= $totalRows ?> entries</span>
                <nav class="questionnaire-pagination" aria-label="Questionnaire pagination">
                    <?php if ($currentPage > 1): ?>
                        <a class="questionnaire-page-btn" href="<?= $e(questionnaireIndexUrl(['page' => $currentPage - 1])) ?>">Prev</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a class="questionnaire-page-btn <?= $i === $currentPage ? 'active' : '' ?>" href="<?= $e(questionnaireIndexUrl(['page' => $i])) ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a class="questionnaire-page-btn" href="<?= $e(questionnaireIndexUrl(['page' => $currentPage + 1])) ?>">Next</a>
                    <?php endif; ?>
                </nav>
            </div>
        </section>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('questionnaireFilterForm');
        const search = form ? form.querySelector('input[name="search"]') : null;
        let timer;
        if (search) {
            search.addEventListener('input', function() {
                clearTimeout(timer);
                timer = setTimeout(() => form.submit(), 450);
            });
        }
    });
</script>

<?php include '../app/views/partials/public/footer.php'; ?>