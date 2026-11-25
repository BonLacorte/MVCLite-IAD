<?php

require_once __DIR__ . '/../models/QuestionnaireModel.php';
require_once __DIR__ . '/../models/SectionModel.php';
require_once __DIR__ . '/../models/QuestionModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../services/SimpleXlsxBuilder.php';

class AdminQuestionnaireController extends Controller
{
    protected $questionnaireModel;
    protected $sectionModel;
    protected $questionModel;
    protected $userModel;

    public function __construct()
    {
        // Require ADMIN or SADMIN role
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['roleid'], ['ADMIN', 'SADMIN'])) {
            header("Location: /login");
            exit;
        }

        $this->questionnaireModel = new QuestionnaireModel();
        $this->sectionModel = new SectionModel();
        $this->questionModel = new QuestionModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $general = $this->questionnaireModel->getAllByType('general');
        $sub     = $this->questionnaireModel->getAllByType('sub');

        $this->view('admin/questionnaires/index', [
            'title'   => 'Admin: BAICS Questionnaires',
            'general' => $general,
            'sub'     => $sub
        ]);
    }

    public function create()
    {
        $generalQuestionnaires = $this->questionnaireModel->getAllByType('general');
        $createType = $_GET['type'] ?? 'general';
        if (!in_array($createType, ['general', 'sub'], true)) {
            $createType = 'general';
        }
        
        $this->view('admin/questionnaires/edit', [
            'title' => 'Create Questionnaire',
            'selectedQuestionnaire' => null,
            'generalQuestionnaires' => $generalQuestionnaires,
            'createType' => $createType
        ]);
    }

    public function store()
    {
        header('Content-Type: application/json');

        if (!$this->validateCsrfToken()) {
            $this->rejectInvalidCsrfToken();
        }

        $type = $this->normalizeQuestionnaireType($_POST['type'] ?? 'general');
        $parentQuestionnaireId = $this->resolveParentQuestionnaireId($type, $_POST['linked_general_questionnaire_id'] ?? null);
        if ($parentQuestionnaireId === false) {
            echo json_encode(['success' => false, 'message' => 'Please select a valid General Questionnaire for this Sub-Component.']);
            exit;
        }

        $data = [
            'title'       => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'type'        => $type,
            'status'      => 'draft', // New questionnaires start as draft
            'created_by'  => $_SESSION['user']['userid'],
            'parent_questionnaire_id' => $parentQuestionnaireId
        ];

        if (empty($data['title'])) {
            echo json_encode(['success' => false, 'message' => 'Title is required.']);
            exit;
        }

        if ($this->questionnaireModel->create($data)) {
            $id = $this->questionnaireModel->getLastInsertId();
            echo json_encode([
                'success' => true, 
                'message' => 'Questionnaire created.',
                'questionnaire_id' => $id,
                'title' => $data['title'],
                'description' => $data['description']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create questionnaire.']);
        }
        exit;
    }

    public function show()
    {
        $id = (int)($_GET['questionnaire_id'] ?? 0);
        $questionnaire = $this->questionnaireModel->getById($id);

        if (!$questionnaire) {
            header("Location: /admin/questionnaires");
            exit;
        }

        $sections = $this->sectionModel->getByQuestionnaireId($id);
        $questions = $this->questionModel->getByQuestionnaireId($id);

        // Group questions by section
        $questionsBySection = [];
        foreach ($questions as $q) {
            $questionsBySection[$q['section_id']][] = $q;
        }

        // Fetch actual assigned users and department progress
        $usersAssigned = $this->questionnaireModel->getAssignedUsers($id);
        $totalUsers = count($usersAssigned);
        $departmentProgress = $this->questionnaireModel->getDepartmentProgress($usersAssigned);

        $questionnaire['total_questions'] = count($questions);
        $acceptedAnswers = $this->questionnaireModel->countAcceptedAnswersForQuestionnaire($id);
        $totalQCount = count($questions);

        if ($questionnaire['type'] === 'general') {
            // General: pooled — accepted answers / total questions
            $questionnaire['completion_percentage'] = $totalQCount > 0
                ? round(($acceptedAnswers / $totalQCount) * 100, 1)
                : 0;
        } else {
            // Sub: average completion across all assigned users
            $totalAccepted = 0;
            foreach ($usersAssigned as $u) {
                $totalAccepted += $u['answered_questions'];
            }
            $questionnaire['completion_percentage'] = ($totalQCount > 0 && $totalUsers > 0)
                ? round(($totalAccepted / ($totalQCount * $totalUsers)) * 100, 1)
                : 0;
        }

        $this->view('admin/questionnaires/show', [
            'title' => 'View Questionnaire',
            'selectedQuestionnaire' => $questionnaire,
            'sections' => $sections,
            'questionsBySection' => $questionsBySection,
            'usersAssigned' => $usersAssigned,
            'totalUsers' => $totalUsers,
            'departmentProgress' => $departmentProgress
        ]);
    }

    public function exportIndex()
    {
        $questionnaires = array_merge(
            $this->questionnaireModel->getAllByType('general'),
            $this->questionnaireModel->getAllByType('sub')
        );

        $headers = [
            'ID',
            'Title',
            'Type',
            'Status',
            'Total Questions',
            'Total Respondents',
            'Expected Answer Slots',
            'Submitted Answer Slots',
            'Completion Percent',
            'Created Date',
        ];

        $rows = array_map(static function ($questionnaire) {
            return [
                $questionnaire['id'] ?? '',
                $questionnaire['title'] ?? '',
                $questionnaire['type'] ?? '',
                $questionnaire['status'] ?? '',
                $questionnaire['total_questions'] ?? 0,
                $questionnaire['total_respondents'] ?? 0,
                $questionnaire['expected_answer_slots'] ?? 0,
                $questionnaire['submitted_answer_slots'] ?? 0,
                $questionnaire['completion_percent'] !== null ? $questionnaire['completion_percent'] . '%' : '',
                $questionnaire['created_at'] ?? '',
            ];
        }, $questionnaires);

        $this->downloadXlsx($headers, $rows, 'questionnaires-' . date('Y-m-d-His') . '.xlsx', 'Questionnaires', [10, 42, 14, 14, 18, 20, 22, 24, 20, 22]);
    }

    public function exportShow()
    {
        $id = (int)($_GET['questionnaire_id'] ?? 0);
        $questionnaire = $this->questionnaireModel->getById($id);

        if (!$questionnaire) {
            header('Location: /admin/questionnaires');
            exit;
        }

        $sections = $this->sectionModel->getByQuestionnaireId($id);
        $questions = $this->questionModel->getByQuestionnaireId($id);
        $sectionNames = [];
        foreach ($sections as $section) {
            $sectionNames[(int)$section['id']] = $section['title'] ?? '';
        }

        $statusCounts = $this->questionnaireModel->getQuestionStatusCountsForExport($id);
        $headers = [
            'Questionnaire ID',
            'Questionnaire Title',
            'Questionnaire Type',
            'Questionnaire Status',
            'Section',
            'Ref No',
            'Area',
            'Criteria',
            'Question',
            'Question Type',
            'No Answer',
            'Draft',
            'For Review',
            'Returned',
            'Pending',
            'Rejected',
            'Accepted',
        ];

        $rows = [];
        foreach ($questions as $question) {
            $questionId = (int)$question['id'];
            $counts = $statusCounts[$questionId] ?? [];
            $rows[] = [
                $questionnaire['id'] ?? '',
                $questionnaire['title'] ?? '',
                $questionnaire['type'] ?? '',
                $questionnaire['status'] ?? '',
                $sectionNames[(int)($question['section_id'] ?? 0)] ?? '',
                $question['ref_no'] ?? '',
                $question['area'] ?? '',
                $question['criteria'] ?? '',
                $question['question_text'] ?? '',
                $question['type'] ?? '',
                $counts['no_answer'] ?? 0,
                $counts['draft'] ?? 0,
                $counts['for_review'] ?? 0,
                $counts['returned'] ?? 0,
                $counts['pending'] ?? 0,
                $counts['rejected'] ?? 0,
                $counts['accepted'] ?? 0,
            ];
        }

        if (empty($rows)) {
            $rows[] = [$questionnaire['id'], $questionnaire['title'], $questionnaire['type'], $questionnaire['status'], '', '', '', '', 'No active questions', '', 0, 0, 0, 0, 0, 0, 0];
        }

        $safeTitle = preg_replace('/[^a-zA-Z0-9_-]+/', '-', strtolower($questionnaire['title'] ?? 'questionnaire')) ?: 'questionnaire';
        $this->downloadXlsx($headers, $rows, 'questionnaire-' . $id . '-' . substr($safeTitle, 0, 40) . '-' . date('Y-m-d-His') . '.xlsx', 'Questionnaire Detail', [16, 42, 18, 18, 28, 14, 24, 34, 56, 18, 14, 12, 14, 14, 12, 12, 12]);
    }

    private function downloadXlsx(array $headers, array $rows, string $filename, string $sheetName, array $columnWidths): void
    {
        $xlsx = (new SimpleXlsxBuilder())->build($headers, $rows, $sheetName, $columnWidths);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($xlsx));
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        echo $xlsx;
        exit;
    }

    public function edit()
    {
        $id = (int)($_GET['questionnaire_id'] ?? 0);
        $questionnaire = $this->questionnaireModel->getById($id);

        if (!$questionnaire) {
            header("Location: /admin/questionnaires");
            exit;
        }

        $generalQuestionnaires = $this->questionnaireModel->getAllByType('general');

        $this->view('admin/questionnaires/edit', [
            'title' => 'Edit Questionnaire',
            'selectedQuestionnaire' => $questionnaire,
            'generalQuestionnaires' => $generalQuestionnaires
        ]);
    }

    public function update()
    {
        header('Content-Type: application/json');

        if (!$this->validateCsrfToken()) {
            $this->rejectInvalidCsrfToken();
        }

        $id   = (int)($_POST['questionnaire_id'] ?? 0);
        $type = $this->normalizeQuestionnaireType($_POST['type'] ?? 'general');
        $parentQuestionnaireId = $this->resolveParentQuestionnaireId($type, $_POST['linked_general_questionnaire_id'] ?? null, $id);
        if ($parentQuestionnaireId === false) {
            echo json_encode(['success' => false, 'message' => 'Please select a valid General Questionnaire for this Sub-Component.']);
            exit;
        }

        $data = [
            'title'       => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'type'        => $type,
            'status'      => $_POST['status'] ?? 'draft',
            'parent_questionnaire_id' => $parentQuestionnaireId
        ];

        if (empty($data['title'])) {
            echo json_encode(['success' => false, 'message' => 'Title is required.']);
            exit;
        }

        if ($id && $this->questionnaireModel->update($id, $data)) {
            echo json_encode([
                'success' => true, 
                'message' => 'Questionnaire updated.',
                'title' => $data['title'],
                'description' => $data['description'],
                'type' => $data['type'],
                'status' => $data['status']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update questionnaire.']);
        }
        exit;
    }

    public function delete()
    {
        header('Content-Type: application/json');
        $csrfToken = (string)($_POST['csrf_token'] ?? '');
        if (!Csrf::validate($csrfToken, 'keep')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id && $this->questionnaireModel->softDelete($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete questionnaire.']);
        }
        exit;
    }

    // ==========================================
    // SECTIONS
    // ==========================================

    public function sections()
    {
        $id = (int)($_GET['questionnaire_id'] ?? 0);
        $questionnaire = $this->questionnaireModel->getById($id);

        if (!$questionnaire) {
            header("Location: /admin/questionnaires");
            exit;
        }

        $sections = $this->sectionModel->getByQuestionnaireId($id);

        $this->view('admin/questionnaires/sections', [
            'title' => 'Manage Sections',
            'questionnaire' => $questionnaire,
            'sections' => $sections
        ]);
    }

    public function storeSection()
    {
        header('Content-Type: application/json');

        $questionnaireId = (int)($_POST['questionnaire_id'] ?? 0);
        $parentSectionId = $this->resolveParentSectionId(
            $questionnaireId,
            $_POST['section_type'] ?? 'parent',
            $_POST['parent_section_id'] ?? null
        );

        if ($parentSectionId === false) {
            echo json_encode(['success' => false, 'message' => 'Please select a valid parent section.']);
            exit;
        }

        $data = [
            'questionnaire_id' => $questionnaireId,
            'parent_section_id' => $parentSectionId,
            'title' => trim($_POST['title'] ?? '')
        ];

        if (empty($data['title'])) {
            echo json_encode(['success' => false, 'message' => 'Section title is required.']);
            exit;
        }

        if ($this->sectionModel->create($data)) {
            echo json_encode(['success' => true, 'message' => 'Section created.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create section.']);
        }
        exit;
    }

    public function updateSection()
    {
        header('Content-Type: application/json');

        $id = (int)($_POST['section_id'] ?? 0);
        $existingSection = $id ? $this->sectionModel->getById($id) : null;
        if (!$existingSection) {
            echo json_encode(['success' => false, 'message' => 'Section not found.']);
            exit;
        }

        $parentSectionId = $this->resolveParentSectionId(
            (int)$existingSection['questionnaire_id'],
            $_POST['section_type'] ?? 'parent',
            $_POST['parent_section_id'] ?? null,
            $id
        );

        if ($parentSectionId === false) {
            echo json_encode(['success' => false, 'message' => 'Please select a valid parent section.']);
            exit;
        }

        $data = [
            'parent_section_id' => $parentSectionId,
            'title' => trim($_POST['title'] ?? '')
        ];

        if (empty($data['title'])) {
            echo json_encode(['success' => false, 'message' => 'Section title is required.']);
            exit;
        }

        if ($id && $this->sectionModel->update($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Section updated.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update section.']);
        }
        exit;
    }

    public function deleteSection()
    {
        header('Content-Type: application/json');

        if (!$this->validateCsrfToken()) {
            $this->rejectInvalidCsrfToken();
        }

        $id = (int)($_POST['section_id'] ?? 0);
        $section = $id ? $this->sectionModel->getById($id) : null;
        if (!$section) {
            echo json_encode(['success' => false, 'message' => 'Section not found.']);
            exit;
        }

        if ($this->sectionModel->countActiveChildren($id) > 0) {
            echo json_encode(['success' => false, 'message' => 'Delete child sections first before deleting this parent section.']);
            exit;
        }

        if ($this->sectionModel->countActiveQuestions($id) > 0) {
            echo json_encode(['success' => false, 'message' => 'Delete or move questions first before deleting this section.']);
            exit;
        }

        if ($this->sectionModel->softDelete($id)) {
            echo json_encode(['success' => true, 'message' => 'Section deleted.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete section.']);
        }
        exit;
    }

    // ==========================================
    // QUESTIONS
    // ==========================================

    public function questions()
    {
        $qId = (int)($_GET['questionnaire_id'] ?? 0);
        $sId = (int)($_GET['section_id'] ?? 0);
        
        $questionnaire = $this->questionnaireModel->getById($qId);
        $sections = $this->sectionModel->getByQuestionnaireId($qId);
        $section = $sId ? $this->sectionModel->getById($sId) : ($sections[0] ?? null);

        if (!$questionnaire) {
            header("Location: /admin/questionnaires");
            exit;
        }

        if ($section && (int)$section['questionnaire_id'] !== $qId) {
            header("Location: /admin/questionnaire/sections?questionnaire_id={$qId}");
            exit;
        }

        $questions = $this->questionModel->getByQuestionnaireId($qId);

        $this->view('admin/questionnaires/questions', [
            'title' => 'Manage Questions',
            'questionnaire' => $questionnaire,
            'section' => $section,
            'sections' => $sections,
            'questions' => $questions
        ]);
    }

    public function storeQuestion()
    {
        header('Content-Type: application/json');

        $questionnaireId = (int)($_POST['questionnaire_id'] ?? 0);
        $sectionId = (int)($_POST['section_id'] ?? 0);
        if (!$this->sectionBelongsToQuestionnaire($sectionId, $questionnaireId)) {
            echo json_encode(['success' => false, 'message' => 'Please select a valid parent section.']);
            exit;
        }

        $criteriaPayload = $this->prepareCriteriaPayload('create');
        if (isset($criteriaPayload['error'])) {
            echo json_encode(['success' => false, 'message' => $criteriaPayload['error']]);
            exit;
        }

        $data = [
            'questionnaire_id' => $questionnaireId,
            'section_id' => $sectionId,
            'question_text' => trim($_POST['question_text'] ?? ''),
            'type' => $this->normalizeQuestionResponseType($_POST['type'] ?? 'yes_no'),
            'criteria' => trim($_POST['criteria'] ?? ''),
            'ref_no' => trim($_POST['ref_no'] ?? ''),
            'area' => trim($_POST['area'] ?? ''),
            'criteria_type' => $criteriaPayload['criteria_type'],
            'criteria_link' => $criteriaPayload['criteria_link'],
            'criteria_file_path' => $criteriaPayload['criteria_file_path'],
            'created_by' => $_SESSION['user']['userid']
        ];

        if (empty($data['question_text'])) {
            echo json_encode(['success' => false, 'message' => 'Question text is required.']);
            exit;
        }

        if ($this->questionModel->create($data)) {
            echo json_encode(['success' => true, 'message' => 'Question added.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add question.']);
        }
        exit;
    }

    public function updateQuestion()
    {
        header('Content-Type: application/json');

        $id = (int)($_POST['question_id'] ?? 0);
        $existingQuestion = $id ? $this->questionModel->getById($id) : null;
        if (!$existingQuestion) {
            echo json_encode(['success' => false, 'message' => 'Question not found.']);
            exit;
        }

        $questionnaireId = (int)($_POST['questionnaire_id'] ?? $existingQuestion['questionnaire_id']);
        $sectionId = (int)($_POST['section_id'] ?? $existingQuestion['section_id']);
        if ((int)$existingQuestion['questionnaire_id'] !== $questionnaireId || !$this->sectionBelongsToQuestionnaire($sectionId, $questionnaireId)) {
            echo json_encode(['success' => false, 'message' => 'Please select a valid parent section.']);
            exit;
        }

        $criteriaPayload = $this->prepareCriteriaPayload('edit', $existingQuestion);
        if (isset($criteriaPayload['error'])) {
            echo json_encode(['success' => false, 'message' => $criteriaPayload['error']]);
            exit;
        }

        $data = [
            'section_id' => $sectionId,
            'question_text' => trim($_POST['question_text'] ?? ''),
            'type' => $this->normalizeQuestionResponseType($_POST['type'] ?? ($existingQuestion['type'] ?? 'yes_no')),
            'criteria' => trim($_POST['criteria'] ?? ''),
            'ref_no' => trim($_POST['ref_no'] ?? ''),
            'area' => trim($_POST['area'] ?? ''),
            'criteria_type' => $criteriaPayload['criteria_type'],
            'criteria_link' => $criteriaPayload['criteria_link'],
            'criteria_file_path' => $criteriaPayload['criteria_file_path'],
            'edited_by' => $_SESSION['user']['userid']
        ];

        if (empty($data['question_text'])) {
            echo json_encode(['success' => false, 'message' => 'Question text is required.']);
            exit;
        }

        if ($id && $this->questionModel->update($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Question updated.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update question.']);
        }
        exit;
    }

    public function deleteQuestion()
    {
        header('Content-Type: application/json');

        if (!$this->validateCsrfToken()) {
            $this->rejectInvalidCsrfToken();
        }

        $id = (int)($_POST['question_id'] ?? 0);
        $question = $id ? $this->questionModel->getById($id) : null;
        if (!$question) {
            echo json_encode(['success' => false, 'message' => 'Question not found.']);
            exit;
        }

        if ($this->questionModel->countActiveAnswers($id) > 0) {
            echo json_encode(['success' => false, 'message' => 'This question has submitted answers and cannot be deleted from the builder.']);
            exit;
        }

        if ($this->questionModel->softDelete($id)) {
            echo json_encode(['success' => true, 'message' => 'Question deleted.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete question.']);
        }
        exit;
    }

    public function assign()
    {
        $id = (int)($_GET['questionnaire_id'] ?? 0);
        $questionnaire = $this->questionnaireModel->getById($id);

        if (!$questionnaire) {
            header("Location: /admin/questionnaires");
            exit;
        }

        $assignedUsers = $this->questionnaireModel->getAssignedUsers($id);
        $unassignedUsers = $this->questionnaireModel->getUnassignedUsers($id);

        $this->view('admin/questionnaires/assign', [
            'title' => 'Assign Users to Questionnaire',
            'selectedQuestionnaire' => $questionnaire,
            'assignedUsers' => $assignedUsers,
            'unassignedUsers' => $unassignedUsers
        ]);
    }

    public function assignCreate()
    {
        header('Content-Type: application/json');

        // Allow both JSON payload and standard POST
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }

        $questionnaireId = (int)($data['questionnaire_id'] ?? 0);
        $userIds = $data['user_ids'] ?? [];

        if (is_scalar($userIds)) {
            $userIds = [$userIds];
        }

        if (!$questionnaireId || empty($userIds)) {
            echo json_encode(['success' => false, 'message' => 'Missing questionnaire ID or user IDs.']);
            exit;
        }

        $assignedBy = $_SESSION['user']['userid'];
        $result = $this->questionnaireModel->assignUsers($questionnaireId, $userIds, $assignedBy);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Users successfully assigned.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to assign users.']);
        }
        exit;
    }

    public function assignDelete()
    {
        header('Content-Type: application/json');

        // Allow both JSON payload and standard POST
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }

        $questionnaireId = (int)($data['questionnaire_id'] ?? 0);
        $userIds = $data['user_ids'] ?? [];

        if (is_scalar($userIds)) {
            $userIds = [$userIds];
        }

        if (!$questionnaireId || empty($userIds)) {
            echo json_encode(['success' => false, 'message' => 'Missing questionnaire ID or user IDs.']);
            exit;
        }

        $result = $this->questionnaireModel->unassignUsers($questionnaireId, $userIds);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Users successfully unassigned.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to unassign users.']);
        }
        exit;
    }

    public function check()
    {
        $questionnaireId = (int)($_GET['questionnaire_id'] ?? 0);
        $userId = (isset($_GET['user_id']) && $_GET['user_id'] !== '') ? (int)$_GET['user_id'] : null;
        $questionnaireType = $_GET['questionnaire_type'] ?? 'general';

        $questionnaire = $this->questionnaireModel->getById($questionnaireId);
        if (!$questionnaire) {
            header("Location: /admin/questionnaires");
            exit;
        }

        if ($questionnaire['type'] === 'general') {
            $userId = null;
        }

        $user = null;
        if ($userId) {
            $user = $this->userModel->getUserById($userId);
        }

        // Calculate progress bars safely
        $totalItems = count($this->questionModel->getByQuestionnaireId($questionnaireId));
        $totalRespondents = $this->questionnaireModel->countRespondents($questionnaireId);
        $questionnaire['respondents'] = $totalRespondents;
        $questionnaire['total_questions'] = $totalItems;

        // Overall progress
        $overallAnswered = $this->questionnaireModel->countAcceptedAnswersForQuestionnaire($questionnaireId);
        if ($questionnaire['type'] === 'general') {
            if ($totalItems > 0) {
                $questionnaire['questionnaire_completion'] = round(($overallAnswered / $totalItems) * 100, 2);
            } else {
                $questionnaire['questionnaire_completion'] = 0;
            }
            $questionnaire['user_completion'] = $questionnaire['questionnaire_completion'];
        } else {
            if ($totalItems > 0 && $totalRespondents > 0) {
                $questionnaire['questionnaire_completion'] = round(($overallAnswered / ($totalItems * $totalRespondents)) * 100, 2);
            } else {
                $questionnaire['questionnaire_completion'] = 0;
            }

            // User specific progress
            if ($user) {
                $userAnswered = $this->questionnaireModel->countAcceptedAnswersForUser($questionnaireId, $userId);
                if ($totalItems > 0) {
                    $questionnaire['user_completion'] = round(($userAnswered / $totalItems) * 100, 2);
                } else {
                    $questionnaire['user_completion'] = 0;
                }
            } else {
                $questionnaire['user_completion'] = $questionnaire['questionnaire_completion'];
            }
        }

        // Sections & questions tree with user answers
        $sections = $this->questionnaireModel->getSectionsTreeWithAnswers(null, $questionnaireId, $userId);
        $this->annotateUploadedFileAvailability($sections);

        // Stats cards metrics
        $stats = $this->questionnaireModel->getCheckStatistics($questionnaireId, $userId);

        // For general type, fetch all assigned users to display in Respondent Metadata panel
        $usersAssigned = [];
        if ($questionnaire['type'] === 'general') {
            $usersAssigned = $this->questionnaireModel->getAssignedUsers($questionnaireId);
        }

        $this->view('admin/questionnaires/check', [
            'title' => 'Evaluate Questionnaire Answers',
            'questionnaire' => $questionnaire,
            'user' => $user,
            'sections' => $sections,
            'questionnaire_type' => $questionnaireType,
            'usersAssigned' => $usersAssigned,
            'acceptedCount' => $stats['accepted'],
            'rejectedCount' => $stats['rejected'],
            'pendingCount' => $stats['pending'],
            'noAnswerCount' => $stats['no_answer']
        ]);
    }

    public function viewUploadedFile()
    {
        $fileId = filter_input(INPUT_GET, 'file_id', FILTER_VALIDATE_INT);
        if (!$fileId) {
            http_response_code(400);
            echo "File not specified";
            exit;
        }

        $file = $this->questionnaireModel->getReviewableFileUploadById((int)$fileId);
        if (!$file) {
            http_response_code(404);
            echo "File not found";
            exit;
        }

        $storedName = basename((string)$file['file_path']);
        $fullPath = $this->resolveAnswerFilePath((string)$file['file_path']);

        if (!$fullPath) {
            http_response_code(404);
            echo "File not found";
            exit;
        }

        $mime = 'application/pdf';
        if (function_exists('mime_content_type')) {
            $detected = mime_content_type($fullPath);
            if ($detected) {
                $mime = $detected;
            }
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($fullPath));
        $disposition = isset($_GET['download']) && $_GET['download'] === '1' ? 'attachment' : 'inline';
        header('Content-Disposition: ' . $disposition . '; filename="' . $storedName . '"');

        readfile($fullPath);
        exit;
    }


    public function viewCriteriaFile()
    {
        $filePath = (string)($_GET['file'] ?? '');
        if ($filePath === '') {
            http_response_code(400);
            echo "File not specified";
            exit;
        }

        $fullPath = $this->resolveCriteriaFilePath($filePath);
        if (!$fullPath) {
            http_response_code(404);
            echo "File not found";
            exit;
        }

        $mime = 'application/pdf';
        if (function_exists('mime_content_type')) {
            $detected = mime_content_type($fullPath);
            if ($detected) {
                $mime = $detected;
            }
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($fullPath));
        header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');

        readfile($fullPath);
        exit;
    }

    private function resolveCriteriaFilePath(string $filePath): ?string
    {
        $storedName = basename(str_replace('\\', '/', $filePath));
        if ($storedName === '') {
            return null;
        }

        $candidatePaths = [
            MNT_PATH . '/questionnaire-criteria/' . $storedName,
            STORAGE_PATH . '/questionnaire-criteria/' . $storedName,
            __DIR__ . '/../../public/assets/criteria-file-uploads/' . $storedName,
        ];

        foreach ($candidatePaths as $candidatePath) {
            if (is_file($candidatePath)) {
                return $candidatePath;
            }
        }

        return null;
    }

    private function annotateUploadedFileAvailability(array &$sections): void
    {
        foreach ($sections as &$section) {
            if (!empty($section['subsections']) && is_array($section['subsections'])) {
                $this->annotateUploadedFileAvailability($section['subsections']);
            }

            if (empty($section['questions']) || !is_array($section['questions'])) {
                continue;
            }

            foreach ($section['questions'] as &$question) {
                if (!empty($question['uploaded_files']) && is_array($question['uploaded_files'])) {
                    $this->annotateUploadedFileRows($question['uploaded_files']);
                }

                if (empty($question['answers']) || !is_array($question['answers'])) {
                    continue;
                }

                foreach ($question['answers'] as &$answer) {
                    if (!empty($answer['uploaded_files']) && is_array($answer['uploaded_files'])) {
                        $this->annotateUploadedFileRows($answer['uploaded_files']);
                    }
                }
                unset($answer);
            }
            unset($question);
        }
        unset($section);
    }

    private function annotateUploadedFileRows(array &$files): void
    {
        foreach ($files as &$file) {
            $filePath = (string)($file['file_path'] ?? '');
            $file['safe_file_name'] = basename($filePath);
            $file['file_available'] = $filePath !== '' && $this->resolveAnswerFilePath($filePath) !== null;
        }
        unset($file);
    }

    private function resolveAnswerFilePath(string $filePath): ?string
    {
        $storedName = basename($filePath);
        if ($storedName === '') {
            return null;
        }

        $candidatePaths = [
            MNT_PATH . '/answer-files/' . $storedName,
            '/mnt/iad/upload/answer-files/' . $storedName,
            STORAGE_PATH . '/answer-files/' . $storedName,
            __DIR__ . '/../../public/assets/answer-files/' . $storedName,
        ];

        foreach ($candidatePaths as $candidatePath) {
            if (is_file($candidatePath)) {
                return $candidatePath;
            }
        }

        return null;
    }

    public function checked()
    {
        $questionnaire_id = (int)($_POST['questionnaire_id'] ?? 0);
        $question_id = (int)($_POST['question_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $remarks = isset($_POST['remarks']) && $_POST['remarks'] !== '' ? $_POST['remarks'] : null;
        $answer_type = $_POST['answer_type'] ?? '';
        $answer_files_id = (int)($_POST['answer_files_id'] ?? 0);
        $user_id = (isset($_POST['user_id']) && $_POST['user_id'] !== '') ? (int)$_POST['user_id'] : null;

        if (!$answer_files_id || !$status || !in_array($status, ['accepted', 'rejected'])) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
                exit;
            }
            $redirectUrl = "/admin/questionnaire/check?questionnaire_id={$questionnaire_id}";
            if ($user_id) $redirectUrl .= "&user_id={$user_id}";
            header("Location: " . $redirectUrl);
            exit;
        }

        $table = ($answer_type === 'file') ? 'file_uploads' : 'user_answers';
        $checked_by = $_SESSION['user']['userid'];

        $result = $this->questionnaireModel->evaluateAnswer($table, $answer_files_id, $status, $remarks, $checked_by);

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Evaluation updated successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update evaluation.']);
            }
            exit;
        }

        $redirectUrl = "/admin/questionnaire/check?questionnaire_id={$questionnaire_id}";
        if ($user_id) $redirectUrl .= "&user_id={$user_id}";
        header("Location: " . $redirectUrl);
        exit;
    }

    public function checkedUpdate()
    {
        $questionnaire_id = (int)($_POST['edit_questionnaire_id'] ?? 0);
        $user_id = isset($_POST['edit_user_id']) ? (int)$_POST['edit_user_id'] : null;
        $type = $_POST['edit_type'] ?? '';
        $status = $_POST['edit_status'] ?? '';
        $remarks = $_POST['edit_remarks'] ?? '';
        $answer_file_id = (int)($_POST['edit_answer_file_id'] ?? 0);

        if (!$answer_file_id || !$status || !in_array($status, ['accepted', 'rejected'])) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
                exit;
            }
            header("Location: /admin/questionnaire/check?questionnaire_id={$questionnaire_id}&user_id={$user_id}");
            exit;
        }

        $table = ($type === 'answer') ? 'user_answers' : 'file_uploads';
        $checked_by = $_SESSION['user']['userid'];

        $result = $this->questionnaireModel->evaluateAnswer($table, $answer_file_id, $status, $remarks, $checked_by);

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Evaluation updated successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update evaluation.']);
            }
            exit;
        }

        header("Location: /admin/questionnaire/check?questionnaire_id={$questionnaire_id}&user_id={$user_id}");
        exit;
    }

    protected function isAjax()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
            || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
    }

    private function normalizeQuestionnaireType($type): string
    {
        return in_array($type, ['general', 'sub'], true) ? $type : 'general';
    }

    private function validateCsrfToken(): bool
    {
        return Csrf::validate((string)($_POST['csrf_token'] ?? ''), 'keep');
    }

    private function rejectInvalidCsrfToken(): void
    {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
        exit;
    }

    private function resolveParentQuestionnaireId(string $type, $parentId, ?int $currentQuestionnaireId = null)
    {
        if ($type === 'general') {
            return null;
        }

        $parentId = (int)$parentId;
        if ($parentId <= 0 || ($currentQuestionnaireId !== null && $parentId === $currentQuestionnaireId)) {
            return false;
        }

        $parentQuestionnaire = $this->questionnaireModel->getById($parentId);
        if (!$parentQuestionnaire || ($parentQuestionnaire['type'] ?? '') !== 'general') {
            return false;
        }

        return $parentId;
    }

    private function resolveParentSectionId(int $questionnaireId, $sectionType, $parentSectionId, ?int $currentSectionId = null)
    {
        if ($sectionType !== 'child') {
            return null;
        }

        $parentSectionId = (int)$parentSectionId;
        if ($parentSectionId <= 0 || ($currentSectionId !== null && $parentSectionId === $currentSectionId)) {
            return false;
        }

        $parentSection = $this->sectionModel->getById($parentSectionId);
        if (!$parentSection || (int)$parentSection['questionnaire_id'] !== $questionnaireId) {
            return false;
        }

        if ($currentSectionId !== null && $this->sectionModel->isDescendantOf($parentSectionId, $currentSectionId)) {
            return false;
        }

        return $parentSectionId;
    }

    private function sectionBelongsToQuestionnaire(int $sectionId, int $questionnaireId): bool
    {
        if ($sectionId <= 0 || $questionnaireId <= 0) {
            return false;
        }

        $section = $this->sectionModel->getById($sectionId);
        return $section && (int)$section['questionnaire_id'] === $questionnaireId;
    }

    private function normalizeQuestionResponseType($type): string
    {
        $allowed = ['yes_no', 'open_ended', 'file_upload', 'yes_no_file_upload', 'open_ended_file_upload'];
        return in_array($type, $allowed, true) ? $type : 'yes_no';
    }

    private function prepareCriteriaPayload(string $mode, array $existingQuestion = []): array
    {
        $criteriaType = $_POST['criteria_type'] ?? 'none';
        if (!in_array($criteriaType, ['none', 'link', 'file'], true)) {
            $criteriaType = 'none';
        }

        $criteriaLink = null;
        $criteriaFilePath = null;

        if ($criteriaType === 'link') {
            $criteriaLink = trim($_POST['criteria_link'] ?? '');
            if ($criteriaLink === '' || filter_var($criteriaLink, FILTER_VALIDATE_URL) === false) {
                return ['error' => 'Please enter a valid criteria link.'];
            }
        }

        if ($criteriaType === 'file') {
            $criteriaFilePath = $existingQuestion['criteria_file_path'] ?? null;
            if (isset($_FILES['criteria_file']) && $_FILES['criteria_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $storedFilePath = $this->storeCriteriaFile('criteria_file');
                if (isset($storedFilePath['error'])) {
                    return ['error' => $storedFilePath['error']];
                }
                $criteriaFilePath = $storedFilePath['path'];
            } elseif ($mode === 'create' || empty($criteriaFilePath)) {
                return ['error' => 'Please upload a criteria file.'];
            }
        }

        return [
            'criteria_type' => $criteriaType,
            'criteria_link' => $criteriaLink,
            'criteria_file_path' => $criteriaFilePath
        ];
    }

    private function storeCriteriaFile(string $field): array
    {
        $fileResult = validateFile($field, [
            'max_size' => 10 * 1024 * 1024,
            'mime' => ['application/pdf']
        ]);

        if (isset($fileResult['error'])) {
            return ['error' => $fileResult['error']];
        }

        $criteriaFileStorage = new Storage('questionnaire-criteria');

        $originalName = pathinfo($fileResult['file']['name'], PATHINFO_FILENAME);
        $safeOriginalName = preg_replace('/[^A-Za-z0-9_-]+/', '_', $originalName);
        $safeOriginalName = trim((string)$safeOriginalName, '_') ?: 'criteria';
        $extension = $fileResult['ext'] ?? 'pdf';
        $fileName = 'criteria_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '_' . $safeOriginalName . '.' . $extension;
        $savedName = $criteriaFileStorage->saveAs($fileResult['file'], $fileName);

        if ($savedName === false) {
            return ['error' => 'Failed to save criteria file.'];
        }

        return ['path' => 'questionnaire-criteria/' . $savedName];
    }
}

