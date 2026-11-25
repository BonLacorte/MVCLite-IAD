<?php

require_once __DIR__ . '/../models/QuestionnaireModel.php';

class QuestionnaireController extends Controller
{
    protected $questionnaireModel;

    public function __construct()
    {
        $this->questionnaireModel = new QuestionnaireModel();
    }

    public function index()
    {
        // Pagination & Sorting Parameters
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        $offset = ($page - 1) * $limit;

        // Fetch user context
        $userId = $_SESSION['user']['userid'] ?? 0;
        $totalRows = $this->questionnaireModel->countAll($search, $userId);
        $totalPages = ceil($totalRows / $limit);
        
        $questionnaires = $this->questionnaireModel->getAllQuestionnaires($limit, $offset, $sort, $order, $userId, $search);
        
        // Fetch user stats
        $stats = $this->questionnaireModel->getUserStats($userId, $search);

        $data = [
            'title' => 'Internal Audit Questionnaires',
            'questionnaires' => $questionnaires,
            'stats' => $stats,
            'search' => $search,
            'pagination' => [
                'current_page' => $page,
                'limit' => $limit,
                'total_rows' => $totalRows,
                'total_pages' => $totalPages,
                'sort' => $sort,
                'order' => $order
            ]
        ];
        $this->view('questionnaires/index', $data);
    }

    /**
     * AJAX endpoint for real-time filtering and sorting
     */
    public function apiList()
    {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $search = isset($_GET['search']) ? $_GET['search'] : '';

        $offset = ($page - 1) * $limit;
        $userId = $_SESSION['user']['userid'] ?? 0;

        $questionnaires = $this->questionnaireModel->getAllQuestionnaires($limit, $offset, $sort, $order, $userId, $search);
        $totalRows = $this->questionnaireModel->countAll($search, $userId);
        
        // Fetch user stats with search filter
        $stats = $this->questionnaireModel->getUserStats($userId, $search);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $questionnaires,
            'stats' => $stats,
            'pagination' => [
                'total_rows' => $totalRows,
                'total_pages' => ceil($totalRows / $limit),
                'current_page' => $page
            ]
        ]);
        exit;
    }

    public function show($id)
    {
        $userId = $_SESSION['user']['userid'] ?? 0;
        if (!$userId) {
            header("Location: /login");
            exit;
        }

        $questionnaire = $this->questionnaireModel->getAssignedQuestionnaire((int)$id, $userId);
        if (!$questionnaire) {
            header("Location: /questionnaires");
            exit;
        }

        $queryUserId = $questionnaire['type'] === 'general' ? null : $userId;

        $stats = $this->questionnaireModel->getUserShowStats((int)$id, $queryUserId);
        $questions = $this->questionnaireModel->getQuestionsWithAnswerStatus((int)$id, $queryUserId);

        $data = [
            'title' => $questionnaire['title'],
            'questionnaire' => $questionnaire,
            'questions' => $questions,
            'stats' => $stats
        ];
        $this->view('questionnaires/show', $data);
    }

    public function answer($id)
    {
        $userId = $_SESSION['user']['userid'] ?? 0;
        if (!$userId) {
            header("Location: /login");
            exit;
        }

        $questionnaire = $this->questionnaireModel->getAssignedQuestionnaire((int)$id, $userId);
        if (!$questionnaire) {
            header("Location: /questionnaires");
            exit;
        }

        // Tab list build logic for General vs Sub questionnaires grouping
        $tabQuestionnaires = [];
        if ($questionnaire['type'] === 'general') {
            $tabQuestionnaires[] = $questionnaire;
            $subs = $this->questionnaireModel->getSubQuestionnairesForUser((int)$questionnaire['id'], $userId);
            $tabQuestionnaires = array_merge($tabQuestionnaires, $subs);
        } elseif ($questionnaire['type'] === 'sub') {
            $parent = $this->questionnaireModel->getParentQuestionnaire((int)$questionnaire['parent_questionnaire_id']);
            if ($parent) {
                $tabQuestionnaires[] = $parent;
                $subs = $this->questionnaireModel->getSubQuestionnairesForUser((int)$parent['id'], $userId);
                $tabQuestionnaires = array_merge($tabQuestionnaires, $subs);
            } else {
                $tabQuestionnaires[] = $questionnaire;
            }
        } else {
            $tabQuestionnaires[] = $questionnaire;
        }

        // Get recursive tree with answers for this user (N+1 query optimized)
        $queryUserId = $questionnaire['type'] === 'general' ? null : $userId;
        $sections = $this->questionnaireModel->getSectionsTreeForUser(null, (int)$id, $queryUserId, $userId);
        $stats = $this->questionnaireModel->getUserShowStats((int)$id, $queryUserId);

        $data = [
            'title' => 'Answering: ' . $questionnaire['title'],
            'questionnaire' => $questionnaire,
            'sections' => $sections,
            'stats' => $stats,
            'tabQuestionnaires' => $tabQuestionnaires
        ];
        $this->view('questionnaires/answer', $data);
    }

    public function downloadQuestionnaire($id)
    {
        $userId = $_SESSION['user']['userid'] ?? 0;
        if (!$userId) {
            header("Location: /login");
            exit;
        }

        $questionnaire = $this->questionnaireModel->getAssignedQuestionnaire((int)$id, $userId);
        if (!$questionnaire) {
            http_response_code(404);
            echo "Questionnaire not found";
            exit;
        }

        $sections = $this->questionnaireModel->getSectionsTreeForUser(null, (int)$id, null, $userId);
        $pdf = (new QuestionnairePdfService())->buildQuestionnairePdf($questionnaire, $sections);
        $this->downloadPdf($pdf, 'questionnaire-' . (int)$id . '-' . date('Y-m-d') . '.pdf');
    }

    public function downloadDhVerificationForm($id)
    {
        $userId = $_SESSION['user']['userid'] ?? 0;
        if (!$userId) {
            header("Location: /login");
            exit;
        }

        $questionnaire = $this->questionnaireModel->getAssignedQuestionnaire((int)$id, $userId);
        if (!$questionnaire) {
            http_response_code(404);
            echo "Questionnaire not found";
            exit;
        }

        $queryUserId = $questionnaire['type'] === 'general' ? null : $userId;
        $sections = $this->questionnaireModel->getSectionsTreeForUser(null, (int)$id, $queryUserId, $userId);
        $user = $this->questionnaireModel->getUserForPdf((int)$userId) ?? [];
        $pdf = (new QuestionnairePdfService())->buildDhVerificationPdf($questionnaire, $sections, $user);
        $this->downloadPdf($pdf, 'dh-verification-' . (int)$id . '-' . date('Y-m-d') . '.pdf');
    }

    private function downloadPdf(string $pdf, string $filename): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        echo $pdf;
        exit;
    }
    public function answerAction($id)
    {
        header('Content-Type: application/json');
        
        $userId = $_SESSION['user']['userid'] ?? 0;
        if (!$userId) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        // Verify assignment
        $questionnaire = $this->questionnaireModel->getAssignedQuestionnaire((int)$id, $userId);
        if (!$questionnaire) {
            echo json_encode(['success' => false, 'error' => 'Questionnaire not assigned or not found']);
            exit;
        }

        // Input validation and sanitation (php-best-practices rules)
        $questionId = filter_input(INPUT_POST, 'question_id', FILTER_VALIDATE_INT);
        $action = filter_input(INPUT_POST, 'action', FILTER_DEFAULT); // 'draft', 'submit', 'for-review'
        $answer = filter_input(INPUT_POST, 'answer', FILTER_DEFAULT);
        $explanation = filter_input(INPUT_POST, 'explanation', FILTER_DEFAULT);

        if (!$questionId || !$action) {
            echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
            exit;
        }

        $action = trim((string)$action);
        if ($action === 'submit') {
            $action = 'for-review';
        }

        if (!in_array($action, ['draft', 'for-review'], true)) {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            exit;
        }

        $answer = trim($answer ?? '');
        $explanation = trim($explanation ?? '');

        if (!in_array($answer, ['yes', 'no'], true)) {
            echo json_encode(['success' => false, 'error' => 'Please select Yes or No.']);
            exit;
        }

        $explanationLength = function_exists('mb_strlen') ? mb_strlen($explanation, 'UTF-8') : strlen($explanation);
        if ($explanationLength > 750) {
            echo json_encode(['success' => false, 'error' => 'Explanation / Justification is limited to 750 characters.']);
            exit;
        }

        $deleteFileIdsRaw = $_POST['delete_file_ids'] ?? [];
        if (!is_array($deleteFileIdsRaw)) {
            $deleteFileIdsRaw = [$deleteFileIdsRaw];
        }
        $deleteFileIds = array_values(array_unique(array_filter(array_map('intval', $deleteFileIdsRaw), fn($id) => $id > 0)));

        if ($action !== 'draft' && !empty($deleteFileIds)) {
            echo json_encode(['success' => false, 'error' => 'Please save your draft after removing files before submitting for review.']);
            exit;
        }

        // File validation
        $filePaths = [];
        $uploadedFiles = $this->normalizeAnswerUploadFiles();

        if (!empty($uploadedFiles)) {
            require_once __DIR__ . '/../helpers/validateFile.php';

            // Get user's department to format uploaded files name (e.g. (DRAFT)_IAD_filename_timestamp.pdf)
            require_once __DIR__ . '/../models/UserModel.php';
            $userModel = new UserModel();
            $user = $userModel->getUserById($userId);
            $dept = $user['department_office_unit'] ?? 'UA';
            
            $words = explode(' ', $dept);
            $initials = '';
            foreach ($words as $w) {
                if (!empty($w)) {
                    $initials .= strtoupper($w[0]);
                }
            }
            $initials = $initials ?: 'UA';

            $answerFileStorage = new Storage('answer-files');

            $validatedFiles = [];
            foreach ($uploadedFiles as $index => $uploadedFile) {
                $fileResult = $this->validateAnswerUploadedFile($uploadedFile, $index);

                if (isset($fileResult['error'])) {
                    echo json_encode(['success' => false, 'error' => $fileResult['error']]);
                    exit;
                }

                $validatedFiles[] = $fileResult;
            }

            $movedFilePaths = [];
            foreach ($validatedFiles as $index => $fileResult) {
                $origName = pathinfo($fileResult['file']['name'], PATHINFO_FILENAME);
                $safeOrigName = preg_replace('/[^a-zA-Z0-9_\-]/', '', $origName);
                $safeOrigName = $safeOrigName !== '' ? $safeOrigName : 'evidence';
                $origExt = $fileResult['ext'] ?? 'pdf';
                $uniqueSuffix = date('YmdHis') . '_' . str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT);

                $prefixName = ($action === 'draft') ? '(DRAFT)' : '(SUBMITTED)';
                $newName = $prefixName . '_' . $initials . '_' . $safeOrigName . '_' . $uniqueSuffix . '.' . $origExt;
                $savedName = $answerFileStorage->saveAs($fileResult['file'], $newName);

                if ($savedName !== false) {
                    $filePaths[] = $savedName;
                    $movedFilePaths[] = $answerFileStorage->pathFor($savedName);
                } else {
                    $this->cleanupMovedUploadFiles($movedFilePaths);
                    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file']);
                    exit;
                }
            }
        }

        // Save answer to database
        $res = $this->questionnaireModel->saveUserAnswer($userId, $questionId, (int)$id, $answer, $explanation, $action);

        if (!$res['success']) {
            if (!empty($movedFilePaths ?? [])) {
                $this->cleanupMovedUploadFiles($movedFilePaths);
            }
            echo json_encode(['success' => false, 'error' => $res['error']]);
            exit;
        }

        $deletedFileCount = 0;
        if ($action === 'draft' && !empty($deleteFileIds)) {
            $deletedFileCount = $this->questionnaireModel->softDeleteEditableFileUploads($userId, $questionId, (int)$id, (int)$res['user_answer_id'], $deleteFileIds);
            if ($deletedFileCount !== count($deleteFileIds)) {
                echo json_encode(['success' => false, 'error' => 'Only files from the current draft can be removed. Historical submission files are locked.']);
                exit;
            }
        }

        $uploadedFileRows = [];

        // Save file upload mapping if applicable
        if (!empty($filePaths)) {
            $fileStatus = ($action === 'draft') ? 'draft' : 'for review';
            foreach ($filePaths as $filePath) {
                $fileId = $this->questionnaireModel->saveFileUpload($userId, $questionId, (int)$id, $filePath, $fileStatus, $res['user_answer_id']);
                $uploadedFileRows[] = [
                    'id' => $fileId,
                    'path' => $filePath,
                    'name' => basename($filePath),
                    'status' => $fileStatus,
                ];
            }
        }

        $response = [
            'success' => true, 
            'message' => 'Answer saved successfully', 
            'user_answer_id' => $res['user_answer_id'],
            'status' => ($action === 'draft') ? 'draft' : 'for review',
            'deleted_file_count' => $deletedFileCount,
        ];

        if (!empty($deleteFileIds)) {
            $response['deleted_file_ids'] = $deleteFileIds;
        }

        if (!empty($filePaths)) {
            $response['file_paths'] = $filePaths;
            $response['file_path'] = $filePaths[0];
            $response['files'] = $uploadedFileRows;
        }

        echo json_encode($response);
        exit;
    }

    private function normalizeAnswerUploadFiles(): array
    {
        $files = [];

        if (isset($_FILES['files'])) {
            $multiFile = $_FILES['files'];

            if (is_array($multiFile['name'])) {
                foreach ($multiFile['name'] as $index => $name) {
                    $error = $multiFile['error'][$index] ?? UPLOAD_ERR_NO_FILE;
                    if ($error === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    $files[] = [
                        'name' => $name,
                        'type' => $multiFile['type'][$index] ?? '',
                        'tmp_name' => $multiFile['tmp_name'][$index] ?? '',
                        'error' => $error,
                        'size' => $multiFile['size'][$index] ?? 0,
                    ];
                }
            } elseif (($multiFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $files[] = $multiFile;
            }
        }

        if (isset($_FILES['file'])) {
            $legacyFile = $_FILES['file'];

            if (is_array($legacyFile['name'])) {
                foreach ($legacyFile['name'] as $index => $name) {
                    $error = $legacyFile['error'][$index] ?? UPLOAD_ERR_NO_FILE;
                    if ($error === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    $files[] = [
                        'name' => $name,
                        'type' => $legacyFile['type'][$index] ?? '',
                        'tmp_name' => $legacyFile['tmp_name'][$index] ?? '',
                        'error' => $error,
                        'size' => $legacyFile['size'][$index] ?? 0,
                    ];
                }
            } elseif (($legacyFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $files[] = $legacyFile;
            }
        }

        return $files;
    }

    private function validateAnswerUploadedFile(array $file, int $index): array
    {
        $field = '__answer_file_' . $index;
        $previousFile = $_FILES[$field] ?? null;
        $_FILES[$field] = $file;

        $fileResult = validateFile($field, [
            'max_size' => 10 * 1024 * 1024,
            'mime' => ['application/pdf'],
        ]);

        if ($previousFile === null) {
            unset($_FILES[$field]);
        } else {
            $_FILES[$field] = $previousFile;
        }

        if (isset($fileResult['error'])) {
            $displayIndex = $index + 1;
            $fileResult['error'] = "File {$displayIndex}: " . $fileResult['error'];
        }

        return $fileResult;
    }

    private function cleanupMovedUploadFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_string($path) && is_file($path)) {
                @unlink($path);
            }
        }
    }

    public function viewFile()
    {
        $userId = $_SESSION['user']['userid'] ?? 0;
        if (!$userId) {
            http_response_code(401);
            echo "Unauthorized";
            exit;
        }

        $file = (string)($_GET['file'] ?? '');

        if ($file === '') {
            http_response_code(400);
            echo "File not specified";
            exit;
        }

        $fileRow = $this->questionnaireModel->getAccessibleUserFileUploadByName((int)$userId, $file);
        if (!$fileRow) {
            http_response_code(404);
            echo "File not found";
            exit;
        }

        $fullPath = $this->resolveAnswerFilePath((string)$fileRow['file_path']);
        if (!$fullPath) {
            http_response_code(404);
            echo "File not found";
            exit;
        }

        // Detect mime
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

    private function resolveAnswerFilePath(string $filePath): ?string
    {
        $storedName = basename(str_replace('\\', '/', $filePath));
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

    /**
     * Handle DH Verification Form upload and answer status updates.
     */
    public function uploadVerificationForm($id)
    {
        header('Content-Type: application/json');
        
        $userId = $_SESSION['user']['userid'] ?? 0;
        if (!$userId) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        // Verify assignment
        $questionnaire = $this->questionnaireModel->getAssignedQuestionnaire((int)$id, $userId);
        if (!$questionnaire) {
            echo json_encode(['success' => false, 'error' => 'Questionnaire not assigned or not found']);
            exit;
        }

        // Verify Confirm string
        $confirmText = filter_input(INPUT_POST, 'confirm_text', FILTER_DEFAULT);
        if (strtolower(trim($confirmText ?? '')) !== 'confirm') {
            echo json_encode(['success' => false, 'error' => 'You must type "confirm" to submit.']);
            exit;
        }

        // Parse evaluations JSON
        $evaluationsRaw = filter_input(INPUT_POST, 'evaluations', FILTER_DEFAULT);
        $evaluations = json_decode($evaluationsRaw, true);
        if (!is_array($evaluations) || empty($evaluations)) {
            echo json_encode(['success' => false, 'error' => 'No evaluations provided.']);
            exit;
        }

        // Validate evaluations
        $validEvaluations = [];
        foreach ($evaluations as $qId => $status) {
            // 'pending' for Accepted, 'returned' for Returned
            if (!in_array($status, ['pending', 'returned'])) {
                echo json_encode(['success' => false, 'error' => 'Invalid evaluation status detected.']);
                exit;
            }
            $validEvaluations[(int)$qId] = $status;
        }

        // File validation
        $fileKey = 'verification_file';
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
            echo json_encode(['success' => false, 'error' => 'Department Head Verification Form is required.']);
            exit;
        }

        require_once __DIR__ . '/../helpers/validateFile.php';
        
        $fileResult = validateFile($fileKey, [
            'max_size' => 10 * 1024 * 1024, // 10MB
            'mime' => ['application/pdf']
        ]);

        if (isset($fileResult['error'])) {
            echo json_encode(['success' => false, 'error' => $fileResult['error']]);
            exit;
        }

        $verificationFileStorage = new Storage('dhvf-files');

        require_once __DIR__ . '/../models/UserModel.php';
        $userModel = new UserModel();
        $user = $userModel->getUserById($userId);
        $dept = $user['department_office_unit'] ?? 'UA';
        
        $words = explode(' ', $dept);
        $initials = '';
        foreach ($words as $w) {
            if (!empty($w)) {
                $initials .= strtoupper($w[0]);
            }
        }
        $initials = $initials ?: 'UA';

        $origName = pathinfo($fileResult['file']['name'], PATHINFO_FILENAME);
        $origExt = pathinfo($fileResult['file']['name'], PATHINFO_EXTENSION);
        
        $newName = '(DHVF)_' . $initials . '_' . preg_replace('/[^a-zA-Z0-9_\-]/', '', $origName) . '_' . date('YmdHis') . '.' . $origExt;
        $savedName = $verificationFileStorage->saveAs($fileResult['file'], $newName);

        if ($savedName === false) {
            echo json_encode(['success' => false, 'error' => 'Failed to save verification file']);
            exit;
        }

        $dbFilePath = 'dhvf-files/' . $savedName;

        // Process in Model
        $success = $this->questionnaireModel->processDhVerification((int)$id, $userId, $dbFilePath, $validEvaluations);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Verification processed successfully.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update database.']);
        }
        exit;
    }
}

