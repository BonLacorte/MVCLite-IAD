<?php include '../app/views/partials/public/head.php'; ?>
<?php include '../app/views/partials/public/nav.php'; ?>

<main class="flex flex-col min-h-screen" style="background-color: var(--secondary);">

    <section class="hero" style="padding: 4rem 0;">
        <div class="container">
            <h1 class="hero-title">About the Internal Audit Department</h1>
            <p class="hero-description">
                The Internal Audit Department (IAD) of the Philippine Coconut Authority is committed to enhancing and protecting organizational value through risk-based and objective assurance, advice, and insight.
            </p>
        </div>
    </section>

    <section style="padding: 4rem 0 2rem 0;">
        <div class="container">

            <div style="display: flex; justify-content: center; align-items: center;">
                <div class="tabs-container" style="justify-content: center; display: flex;">
                    <button class="tab-button active" onclick="openTab(event, 'overview')">
                        <span class="material-symbols-rounded">info</span>
                        Overview
                    </button>
                    <button class="tab-button" onclick="openTab(event, 'team')">
                        <span class="material-symbols-rounded">groups</span>
                        Our Team
                    </button>
                    <button class="tab-button" onclick="openTab(event, 'structure')">
                        <span class="material-symbols-rounded">account_tree</span>
                        Structure
                    </button>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="premium-card">
                    
                    <!-- Overview Tab -->
                    <div id="overview" class="tab-pane">
                        <!-- Preamble -->
                        <div style="margin-bottom: 3rem; padding-bottom: 2.5rem; border-bottom: 1px solid #eaeaea;">
                            <h5 style="color: #cda85f; font-size: 0.75rem; letter-spacing: 2.5px; font-weight: 800; text-transform: uppercase; margin-bottom: 1rem;">RPGIAM</h5>
                            <h2 class="event-title" style="font-size: 1.5rem; font-weight: 700; line-height: 1.5; margin-bottom: 1.5rem;">
                                "The role of IAD is not about fault-finding; neither it is investigative nor punitive. As a component of the performance management framework of PCA, IAD assesses the levels of performance against agreed measures, targets, and objectives."
                            </h2>
                            <p style="font-style: italic; color: #666; font-size: 0.9rem; line-height: 1.6;">
                                Revised Philippine Government Internal Audit Manual (RPGIAM) 2020, page 5<br>
                                Revised Internal Audit Manual, BR#035-2023
                            </p>
                        </div>

                        <!-- Official Mission -->
                        <div style="margin-bottom: 2.5rem; border-left: 3px solid #055741; padding-left: 1.5rem;">
                            <h3 class="event-title" style="font-weight: 700; font-size: 1.1rem; margin-bottom: 0.75rem;">Official Mission</h3>
                            <p style="color: #555; font-size: 0.95rem; line-height: 1.6;">
                                To provide quality, objective, and relevant internal audit functions and assistance to the PCA Board through the Audit Committee and Management in achieving its objectives effectively, efficiently, ethically, and economically.
                            </p>
                        </div>

                        <!-- Institutional Vision -->
                        <div style="margin-bottom: 2.5rem; border-left: 3px solid #055741; padding-left: 1.5rem;">
                            <h3 class="event-title" style="font-weight: 700; font-size: 1.1rem; margin-bottom: 0.75rem;">Institutional Vision</h3>
                            <p style="color: #555; font-size: 0.95rem; line-height: 1.6;">
                                An independent, competent, and empowered Internal Audit Department providing the Philippine Coconut Authority with quality internal audit services.
                            </p>
                        </div>

                        <!-- Legal Mandate -->
                        <div style="margin-bottom: 3.5rem; border-left: 3px solid #055741; padding-left: 1.5rem;">
                            <h3 class="event-title" style="font-weight: 700; font-size: 1.1rem; margin-bottom: 0.75rem;">Legal Mandate</h3>
                            <p style="color: #555; font-size: 0.95rem; line-height: 1.6;">
                                The Internal Audit Department is mandated by various laws, rules, and regulations cited in the Revised Philippine Government Internal Audit Manual (RPGIAM) 2020 and as per PCA's Rationalization Plan of 2013.
                            </p>
                        </div>

                        <!-- Core Values -->
                        <h3 class="event-title" style="font-weight: 800; font-size: 1.5rem; margin-bottom: 0.75rem;">Core Values</h3>
                        <div style="width: 60px; height: 2px; background: #cda85f; margin-bottom: 2rem;"></div>
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                            <!-- Value 1 -->
                            <!-- <div class="value-card"> -->
                            <div class="event-card">
                                <h4 class="event-title">Integrity</h4>
                                <p style="color: #555; font-size: 0.9rem; line-height: 1.6;">Uncompromising adherence to moral and ethical principles; soundness of moral character; honesty.</p>
                            </div>
                            <!-- Value 2 -->
                            <div class="event-card">
                                <h4 class="event-title">Adaptable</h4>
                                <p style="color: #555; font-size: 0.9rem; line-height: 1.6;">Flexible and versatile in responding to organizational changes and professional demands.</p>
                            </div>
                            <!-- Value 3 -->
                            <div class="event-card">
                                <h4 class="event-title">Discreet</h4>
                                <p style="color: #555; font-size: 0.9rem; line-height: 1.6;">Maintaining the highest levels of confidentiality and professional sensitivity.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Our Team Tab -->
                    <div id="team" class="tab-pane" style="display: none;">
                        <h3 class="event-title" style="font-weight: 800; font-size: 1.5rem; margin-bottom: 0.75rem;">IAD Department Members</h2>
                        <!-- <h3 class="event-title" style="font-weight: 800; font-size: 1.5rem; margin-bottom: 0.75rem;">Core Values</h3> -->
                        <div style="width: 60px; height: 2px; background: #cda85f; margin-bottom: 2rem;"></div>
                        <p style="color: #666; margin-bottom: 3rem; border-bottom: 1px solid #eaeaea; padding-bottom: 2rem; font-size: 1.05rem;">
                            Meet the dedicated professionals ensuring transparency and accountability within the PCA.
                        </p>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                            <?php
                            $teamMembers = [
                                ['name' => 'Engr. Annalyn E. Mansiliohan', 'position' => 'OIC - DM', 'image' => '/assets/images/2.png'],
                                ['name' => 'Marilou B. Mora', 'position' => 'INTERNAL AUDITOR III', 'image' => '/assets/images/3.png'],
                                ['name' => 'Raul Jacob K. Firme', 'position' => 'INTERNAL AUDITOR I', 'image' => '/assets/images/8.jpg'],
                                ['name' => 'Loueda N. Deona', 'position' => 'TECHNICAL STAFF', 'image' => '/assets/images/4.png'],
                                ['name' => 'Elgin Kate N. Monding', 'position' => 'TECHNICAL STAFF', 'image' => '/assets/images/5.png'],
                                ['name' => 'Florence Bon D. Lacorte', 'position' => 'TECHNICAL STAFF', 'image' => '/assets/images/7.png'],
                                ['name' => 'Mark Joel P. Padilla', 'position' => 'ADMINISTRATIVE ASSISTANT', 'image' => '/assets/images/6.png'],
                            ];
                            foreach ($teamMembers as $member): ?>
                                <div style="background: #fbfbfb; border-radius: 8px; padding: 2rem 1.5rem; border: 1px solid #f0f0f0; border-top: 4px solid #055741;; display: flex; flex-direction: column; align-items: center; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                                    <div style="width: 90px; height: 90px; border-radius: 50%; overflow: hidden; margin-bottom: 1.25rem; border: 2px solid #055741; padding: 2px; background: white;">
                                        <img src="<?= $member['image'] ?>" alt="<?= $member['name'] ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                    </div>
                                    <h4 style="color: #222; font-weight: 700; font-size: 1rem; margin: 0 0 0.5rem 0;"><?= $member['name'] ?></h4>
                                    <p style="color: #055741; font-weight: 700; font-size: 0.7rem; letter-spacing: 0.5px; margin: 0; text-transform: uppercase;"><?= $member['position'] ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Structure Tab -->
                    <div id="structure" class="tab-pane" style="display: none;">
                        <h3 class="event-title" style="font-weight: 800; font-size: 1.5rem; margin-bottom: 0.75rem;">Organizational Structure</h2>
                        <!-- <h3 class="event-title" style="font-weight: 800; font-size: 1.5rem; margin-bottom: 0.75rem;">Core Values</h3> -->
                        <div style="width: 60px; height: 2px; background: #cda85f; margin-bottom: 2rem;"></div>
                        <p style="color: #666; margin-bottom: 3rem; border-bottom: 1px solid #eaeaea; padding-bottom: 2rem; font-size: 1.05rem;">
                            The hierarchy and reporting lines of the Internal Audit Department.
                        </p>
                        <div style="padding: 1rem; background: #fbfbfb; border: 1px solid #eee; border-radius: 8px; border-top: 4px solid #055741;">
                            <img src="/assets/images/PCA_Organizational_Structure.png" alt="PCA Organizational Structure" style="width: 100%; height: auto; border-radius: 4px;">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</main>

<script>
function openTab(evt, tabName) {
    // Hide all tab content
    var tabContent = document.getElementsByClassName("tab-pane");
    for (var i = 0; i < tabContent.length; i++) {
        tabContent[i].style.display = "none";
    }
    
    // Remove active class from all buttons
    var tabButtons = document.getElementsByClassName("tab-button");
    for (var i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove("active");
    }
    
    // Show the current tab and add active class
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.classList.add("active");
}
</script>

<?php include '../app/views/partials/public/footer.php'; ?>