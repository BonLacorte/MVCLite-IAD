<?php include '../app/views/partials/public/head.php'; ?>
<?php include '../app/views/partials/public/nav.php'; ?>

<main style="background-color: #f8fafc; min-height: 100vh; font-family: 'Outfit', 'Inter', sans-serif;">
    <div class="questionnaire-container">
        <!-- Back Link -->
        <a href="/questionnaires" style="color: #64748b; text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem; font-weight: 600; transition: color 0.2s;" onmouseover="this.style.color='#055741'" onmouseout="this.style.color='#64748b'">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Back to Assigned Questionnaires
        </a>

        <!-- Metadata Header Card -->
        <div style="background: linear-gradient(135deg, #055741 0%, #033a2b 100%); border-radius: 1.5rem; padding: 2.5rem; color: white; margin-bottom: 2.5rem; box-shadow: 0 10px 25px -5px rgba(5,87,65,0.25); position: relative; overflow: hidden;">
            <div style="position: absolute; right: -50px; bottom: -50px; opacity: 0.08; color: #ffffff;">
                <svg width="300" height="300" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10H7v-2h10v2zm0-4H7V7h10v2zm0 8H7v-2h10v2z"/></svg>
            </div>
            <div style="position: relative; z-index: 1;">
                <span style="background: rgba(254, 252, 232, 0.2); color: #FEFCE8; padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; margin-bottom: 1rem;">
                    <?= htmlspecialchars(ucfirst($questionnaire['type'])) ?> Questionnaire
                </span>
                <h1 style="margin: 0; font-size: 2.25rem; font-weight: 800; line-height: 1.25; color: #FEFCE8; letter-spacing: -0.025em;"><?= htmlspecialchars($questionnaire['title']) ?></h1>
                <p style="margin: 1.25rem 0 0 0; color: #e2e8f0; font-size: 1.05rem; line-height: 1.6; max-width: 800px; font-weight: 500;">
                    <?= htmlspecialchars($questionnaire['description']) ?>
                </p>
            </div>
        </div>

        <!-- Premium Stats Cards -->
        <div class="stats-grid">
            <!-- Card 1: Completion Rate -->
            <div class="stat-card fade-in-up" style="animation-delay: 0s;">
                <span class="stat-card-title">Completion Rate</span>
                <div class="stat-card-value"><?= number_format($stats['completion_rate'], 1) ?>%</div>
                <div style="font-size: 0.8rem; color: #64748b; font-weight: 600; margin-bottom: 1rem;">Overall acceptance progress</div>
                <div class="stat-progress-bar">
                    <div class="stat-progress-fill" style="width: <?= $stats['completion_rate'] ?>%"></div>
                </div>
            </div>

            <!-- Card 2: Total Accepted -->
            <div class="stat-card fade-in-up" style="border-left: 4px solid #10b981; animation-delay: 0.1s;">
                <span class="stat-card-title">Total Accepted</span>
                <div class="stat-card-value" style="color: #055741;"><?= $stats['total_accepted'] ?></div>
                <div style="font-size: 0.8rem; color: #64748b; font-weight: 600; margin-bottom: 0.5rem;">Accepted questions</div>
                <div style="font-size: 0.85rem; color: #0284c7; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem; margin-top: auto;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Validated responses
                </div>
            </div>

            <!-- Card 3: User's Progress -->
            <div class="stat-card fade-in-up" style="animation-delay: 0.2s;">
                <span class="stat-card-title">User's Progress</span>
                <div class="stat-card-value"><?= $stats['total_accepted'] ?> <span style="font-size: 1.25rem; color: #94a3b8; font-weight: 500;">/ <?= $stats['total_questions'] ?></span></div>
                <div style="font-size: 0.8rem; color: #64748b; font-weight: 600; margin-bottom: 1rem;">Accepted vs Total questions</div>
                <div class="stat-progress-bar">
                    <div class="stat-progress-fill" style="width: <?= ($stats['total_questions'] > 0) ? ($stats['total_accepted'] / $stats['total_questions'] * 100) : 0 ?>%; background: linear-gradient(90deg, #10b981, #055741);"></div>
                </div>
            </div>
        </div>

        <!-- Interactive Questions Table Card -->
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 2rem; margin-bottom: 2.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0f172a;">Questions Status</h3>
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #64748b; font-weight: 600;">Status and validation summary of all questionnaire items</p>
                </div>
                <!-- Search bar -->
                <div style="position: relative; width: 100%; max-width: 320px;">
                    <input type="text" id="tableSearch" placeholder="Search questions..." style="width: 100%; padding: 0.65rem 1rem 0.65rem 2.5rem; border: 1px solid #cbd5e1; border-radius: 0.75rem; font-size: 0.875rem; outline: none; font-weight: 600; color: #334155; transition: border-color 0.2s;" onfocus="this.style.borderColor='#055741'" onblur="this.style.borderColor='#cbd5e1'">
                    <svg style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
            </div>

            <div class="premium-table-wrapper">
                <table class="premium-table" id="questionsTable">
                    <thead>
                        <tr>
                            <th style="width: 8%;" onclick="sortTable(0)">No. ▲▼</th>
                            <th style="width: 62%;" onclick="sortTable(1)">Question Text ▲▼</th>
                            <th style="width: 30%;" onclick="sortTable(2)">Answer Status ▲▼</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if (!empty($questions)): ?>
                            <?php foreach ($questions as $index => $question): ?>
                                <tr>
                                    <td style="font-weight: 700; color: #0f172a;"><?= ($index + 1) ?></td>
                                    <td style="font-weight: 600; line-height: 1.5; color: #334155;"><?= htmlspecialchars($question['question_text']) ?></td>
                                    <td>
                                        <?php 
                                        $status = strtolower($question['answer_status'] ?? 'no answer'); 
                                        $badgeClass = 'badge-no-answer';
                                        $statusLabel = 'No Answer';
                                        
                                        if ($status === 'draft') {
                                            $badgeClass = 'badge-draft';
                                            $statusLabel = 'Draft';
                                        } elseif ($status === 'pending') {
                                            $badgeClass = 'badge-pending';
                                            $statusLabel = 'Pending';
                                        } elseif ($status === 'for review') {
                                            $badgeClass = 'badge-review';
                                            $statusLabel = 'For Review';
                                        } elseif ($status === 'accepted') {
                                            $badgeClass = 'badge-accepted';
                                            $statusLabel = 'Accepted';
                                        } elseif ($status === 'returned') {
                                            $badgeClass = 'badge-rejected';
                                            $statusLabel = 'Returned';
                                        } elseif ($status === 'rejected') {
                                            $badgeClass = 'badge-rejected';
                                            $statusLabel = 'Rejected';
                                        }
                                        ?>
                                        <span class="badge-pill <?= $badgeClass ?>">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                                            <?= $statusLabel ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 3rem; color: #94a3b8; font-weight: 600;">No questions assigned to this questionnaire.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table Pagination Controls -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-top: 1rem;">
                <div style="font-size: 0.85rem; color: #64748b; font-weight: 600;" id="paginationInfo">
                    Showing 1 to 5 of 10 entries
                </div>
                <div style="display: flex; gap: 0.5rem;" id="paginationControls">
                    <!-- Dynamic Pagination Buttons -->
                </div>
            </div>
        </div>

        <!-- CTA Action Section -->
        <div style="background: #FEFCE8; border: 1px solid rgba(241, 227, 156, 0.5); border-radius: 1.5rem; padding: 2rem 2.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <div>
                <h4 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #055741;">Ready to Answer?</h4>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #055741; font-weight: 600; opacity: 0.85;">Start answering, saving drafts, and uploading PDF evidence files.</p>
            </div>
            <a href="/questionnaires/<?= $questionnaire['id'] ?>/answer" class="btn-primary" style="background: #055741; color: white; padding: 0.85rem 2rem; font-size: 0.95rem; font-weight: 700; border-radius: 0.75rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 15px rgba(5,87,65,0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                Answer Questionnaire
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </a>
        </div>
    </div>
</main>

<script>
    // Client-side table interactive features: search, sort, and pagination.
    let tableRows = Array.from(document.querySelectorAll('#tableBody tr'));
    const rowsPerPage = 5;
    let currentPage = 1;
    let filteredRows = [...tableRows];
    let sortDirections = [true, true, true]; // true = ASC, false = DESC

    function displayPage(page) {
        currentPage = page;
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        
        tableRows.forEach(row => row.style.display = 'none');
        
        if (filteredRows.length === 0) {
            const tableBody = document.getElementById('tableBody');
            // If search has no results and no empty-row warning exists, add it
            if (!document.getElementById('noResultsRow')) {
                const noResults = document.createElement('tr');
                noResults.id = 'noResultsRow';
                noResults.innerHTML = `<td colspan="3" style="text-align: center; padding: 3rem; color: #94a3b8; font-weight: 600;">No matching questions found.</td>`;
                tableBody.appendChild(noResults);
            }
            document.getElementById('paginationInfo').innerText = 'Showing 0 to 0 of 0 entries';
            document.getElementById('paginationControls').innerHTML = '';
            return;
        }

        const noResults = document.getElementById('noResultsRow');
        if (noResults) noResults.remove();

        const pageRows = filteredRows.slice(start, end);
        pageRows.forEach(row => row.style.display = '');

        // Update info text
        const total = filteredRows.length;
        const shownStart = total > 0 ? start + 1 : 0;
        const shownEnd = Math.min(end, total);
        document.getElementById('paginationInfo').innerText = `Showing ${shownStart} to ${shownEnd} of ${total} entries`;

        renderPaginationButtons();
    }

    function renderPaginationButtons() {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        const container = document.getElementById('paginationControls');
        container.innerHTML = '';

        if (totalPages <= 1) return;

        // Prev Button
        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = '←';
        prevBtn.disabled = currentPage === 1;
        stylePaginationBtn(prevBtn);
        prevBtn.onclick = () => { if(currentPage > 1) displayPage(currentPage - 1); };
        container.appendChild(prevBtn);

        // Page Numbers
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.innerText = i;
            btn.disabled = currentPage === i;
            stylePaginationBtn(btn, currentPage === i);
            btn.onclick = () => displayPage(i);
            container.appendChild(btn);
        }

        // Next Button
        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = '→';
        nextBtn.disabled = currentPage === totalPages;
        stylePaginationBtn(nextBtn);
        nextBtn.onclick = () => { if(currentPage < totalPages) displayPage(currentPage + 1); };
        container.appendChild(nextBtn);
    }

    function stylePaginationBtn(btn, isActive = false) {
        btn.style.padding = '0.4rem 0.8rem';
        btn.style.fontSize = '0.85rem';
        btn.style.fontWeight = '700';
        btn.style.borderRadius = '0.5rem';
        btn.style.border = '1px solid #e2e8f0';
        btn.style.cursor = btn.disabled && !isActive ? 'not-allowed' : 'pointer';
        btn.style.transition = 'all 0.2s';
        
        if (isActive) {
            btn.style.backgroundColor = '#055741';
            btn.style.color = '#ffffff';
            btn.style.borderColor = '#055741';
        } else {
            btn.style.backgroundColor = '#ffffff';
            btn.style.color = '#475569';
            if (!btn.disabled) {
                btn.onmouseover = () => { btn.style.backgroundColor = '#f1f5f9'; };
                btn.onmouseout = () => { btn.style.backgroundColor = '#ffffff'; };
            } else {
                btn.style.opacity = '0.5';
            }
        }
    }

    // Search Filtering
    document.getElementById('tableSearch').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        filteredRows = tableRows.filter(row => {
            const text = row.cells[1].innerText.toLowerCase();
            return text.includes(query);
        });
        displayPage(1);
    });

    // Column Sorting
    function sortTable(colIndex) {
        const asc = sortDirections[colIndex];
        sortDirections[colIndex] = !asc;

        // Reset other headers indicators in DOM
        const headers = document.querySelectorAll('#questionsTable th');
        headers.forEach((th, idx) => {
            const text = th.innerText.replace(/[▲▼]/g, '').trim();
            if (idx === colIndex) {
                th.innerText = `${text} ${asc ? '▲' : '▼'}`;
            } else {
                th.innerText = `${text} ▲▼`;
            }
        });

        filteredRows.sort((a, b) => {
            let valA = a.cells[colIndex].innerText.trim();
            let valB = b.cells[colIndex].innerText.trim();

            if (colIndex === 0) {
                return asc ? (parseInt(valA) - parseInt(valB)) : (parseInt(valB) - parseInt(valA));
            }

            return asc ? valA.localeCompare(valB) : valB.localeCompare(valA);
        });

        displayPage(1);
    }

    // Initialize Page
    document.addEventListener('DOMContentLoaded', () => {
        displayPage(1);
    });
</script>

<?php include '../app/views/partials/public/footer.php'; ?>
