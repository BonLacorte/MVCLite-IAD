<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/QuestionnaireModel.php';
require_once __DIR__ . '/../services/SimpleXlsxBuilder.php';
require_once __DIR__ . '/../../core/Storage.php';

class AdminController extends Controller
{
    protected $userModel;
    protected $eventModel;
    protected $questionnaireModel;

    public function __construct()
    {
        // Role check is handled by RoleMiddleware in routes
        $this->userModel        = new UserModel();
        $this->eventModel       = new EventModel();
        $this->questionnaireModel = new QuestionnaireModel();
    }

    // ========================================================
    // USERS
    // ========================================================

    public function users()
    {
        $users = $this->userModel->getAllByRole('user');
        $this->view('admin/users', [
            'title' => 'Admin: Users',
            'users' => $users
        ]);
    }

    public function storeUser()
    {
        header('Content-Type: application/json');

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'user';
        $errors   = [];

        if (strlen($name) < 2)                              $errors['name']     = 'Name must be at least 2 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))     $errors['email']    = 'Enter a valid email address.';
        if (strlen($password) < 7)                          $errors['password'] = 'Password must be at least 7 characters.';
        if ($this->userModel->emailExists($email))          $errors['email_exists'] = 'Email is already in use.';

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        $data = $this->userPayloadFromRequest($role);
        $data['password'] = password_hash($password, PASSWORD_BCRYPT);

        if ($this->userModel->createUser($data)) {
            echo json_encode(['success' => true, 'message' => 'User created successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create user.']);
        }
        exit;
    }

    public function updateUser()
    {
        header('Content-Type: application/json');

        $id    = (int)($_POST['id'] ?? 0);
        $email = trim($_POST['email'] ?? '');
        $errors = [];

        if (!$id)                                                       $errors['id']    = 'Invalid user ID.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))                 $errors['email'] = 'Enter a valid email address.';
        if ($this->userModel->emailExists($email, $id))                 $errors['email_exists'] = 'Email is already in use.';

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        $data = $this->userPayloadFromRequest($_POST['role'] ?? 'user');

        if ($this->userModel->adminUpdateUser($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user.']);
        }
        exit;
    }

    private function userPayloadFromRequest(string $role): array
    {
        $officeType = $_POST['office_type'] ?? 'central';
        $centralOffice = trim((string)($_POST['for_central_office'] ?? ''));
        $department = trim((string)($_POST['department_office_unit'] ?? ''));
        $regionCenter = trim((string)($_POST['for_region_center'] ?? ''));

        if ($officeType === 'regional') {
            $centralOffice = '';
            $department = '';
        } else {
            $regionCenter = '';
        }

        return [
            'name'                  => trim($_POST['name'] ?? ''),
            'email'                 => trim($_POST['email'] ?? ''),
            'role'                  => $role,
            'position'              => trim($_POST['position'] ?? ''),
            'department_office_unit'=> $department,
            'for_central_office'    => $centralOffice,
            'for_region_center'     => $regionCenter,
            'cellphone_number'      => trim($_POST['cellphone_number'] ?? ''),
            'facebook_account'      => trim($_POST['facebook_account'] ?? ''),
        ];
    }

    public function deleteUser()
    {
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);

        if ($id && $this->userModel->softDeleteUser($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
        }
        exit;
    }

    public function changeUserPassword()
    {
        header('Content-Type: application/json');
        $id       = (int)($_POST['id'] ?? 0);
        $password = $_POST['password'] ?? '';

        if (!$id || strlen($password) < 7) {
            echo json_encode(['success' => false, 'message' => 'Invalid data. Password must be at least 7 characters.']);
            exit;
        }

        if ($this->userModel->updatePassword($id, password_hash($password, PASSWORD_BCRYPT))) {
            echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
        }
        exit;
    }

    // ── Standalone page routes ──────────────────────────────

    public function exportUsers()
    {
        $users = $this->userModel->getAllUsers();
        $this->downloadUsersXlsx($users, 'active-users-', 'Active Users');
    }

    public function exportAdmins()
    {
        $admins = array_values(array_filter(
            $this->userModel->getAllUsers(),
            static fn($user) => ($user['role'] ?? '') === 'admin'
        ));
        $this->downloadUsersXlsx($admins, 'active-admins-', 'Active Admins');
    }

    private function downloadUsersXlsx(array $users, string $filenamePrefix, string $sheetName)
    {
        $headers = [
            'Name',
            'Email',
            'Role',
            'Position',
            'Department/Office/Unit',
            'Central Office',
            'Region Center',
            'Cellphone Number',
            'Facebook Account',
            'Created Time',
        ];

        $rows = array_map(static function ($user) {
            return [
                $user['name'] ?? '',
                $user['email'] ?? '',
                $user['role'] ?? '',
                $user['position'] ?? '',
                $user['department_office_unit'] ?? '',
                $user['for_central_office'] ?? '',
                $user['for_region_center'] ?? '',
                $user['cellphone_number'] ?? '',
                $user['facebook_account'] ?? '',
                $user['createdTime'] ?? '',
            ];
        }, $users);

        $filename = $filenamePrefix . date('Y-m-d-His') . '.xlsx';
        $xlsx = $this->buildUsersXlsx($headers, $rows, $sheetName);

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

    private function buildUsersXlsx(array $headers, array $rows, string $sheetName = 'Active Users'): string
    {
        return (new SimpleXlsxBuilder())->build(
            $headers,
            $rows,
            $sheetName,
            [28, 32, 14, 28, 34, 30, 24, 20, 28, 22]
        );
    }

    private function buildWorksheetXml(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><cols>';

        foreach ([28, 32, 14, 28, 34, 30, 24, 20, 28, 22] as $index => $width) {
            $column = $index + 1;
            $xml .= '<col min="' . $column . '" max="' . $column . '" width="' . $width . '" customWidth="1"/>';
        }

        $xml .= '</cols><sheetData>';

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 1;
            $xml .= '<row r="' . $rowNumber . '">';
            foreach ($row as $columnIndex => $value) {
                $style = $rowIndex === 0 ? ' s="1"' : '';
                $cellRef = $this->columnName($columnIndex + 1) . $rowNumber;
                $xml .= '<c r="' . $cellRef . '" t="inlineStr"' . $style . '><is><t>' .
                    $this->xmlEscape((string)$value) .
                    '</t></is></c>';
            }
            $xml .= '</row>';
        }

        return $xml . '</sheetData></worksheet>';
    }

    private function buildZipArchive(array $files): string
    {
        $localData = '';
        $centralDirectory = '';
        $offset = 0;

        foreach ($files as $name => $data) {
            $crc = crc32($data);
            $size = strlen($data);
            $nameLength = strlen($name);
            $localHeader = pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, 0, 0, $crc, $size, $size, $nameLength, 0) . $name;
            $localData .= $localHeader . $data;
            $centralDirectory .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, 0, 0, $crc, $size, $size, $nameLength, 0, 0, 0, 0, 0, $offset) . $name;
            $offset += strlen($localHeader) + $size;
        }

        $centralSize = strlen($centralDirectory);
        $centralOffset = strlen($localData);
        $fileCount = count($files);
        $end = pack('VvvvvVVv', 0x06054b50, 0, 0, $fileCount, $fileCount, $centralSize, $centralOffset, 0);

        return $localData . $centralDirectory . $end;
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)) . $name;
            $number = intdiv($number, 26);
        }
        return $name;
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    public function createUserPage()
    {
        $this->view('admin/users/create', ['title' => 'Admin: Create User']);
    }

    public function editUserPage()
    {
        $id   = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->getUserById($id);
        if (!$user) { header('Location: /admin/users'); exit; }
        $this->view('admin/users/edit', ['title' => 'Admin: Edit User', 'user' => $user]);
    }

    public function viewUserPage()
    {
        $id   = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->getUserById($id);
        if (!$user) { header('Location: /admin/users'); exit; }
        $this->view('admin/users/view', ['title' => 'Admin: User Profile', 'user' => $user]);
    }

    // ========================================================
    // ADMINS
    // ========================================================

    public function admins()
    {
        $admins = $this->userModel->getAllByRole('admin');
        $this->view('admin/admins', [
            'title' => 'Admin: Admins',
            'admins' => $admins
        ]);
    }

    public function storeAdmin()
    {
        // Reuse storeUser but force role=admin
        $_POST['role'] = 'admin';
        $this->storeUser();
    }

    public function updateAdmin()
    {
        $_POST['role'] = 'admin';
        $this->updateUser();
    }

    public function deleteAdmin()
    {
        $this->deleteUser();
    }

    // ── Standalone page routes ──────────────────────────────

    public function createAdminPage()
    {
        $this->view('admin/admins/create', ['title' => 'Admin: Create Administrator']);
    }

    public function editAdminPage()
    {
        $id    = (int)($_GET['id'] ?? 0);
        $admin = $this->userModel->getUserById($id);
        if (!$admin) { header('Location: /admin/admins'); exit; }
        $this->view('admin/admins/edit', ['title' => 'Admin: Edit Administrator', 'admin' => $admin]);
    }

    public function viewAdminPage()
    {
        $id    = (int)($_GET['id'] ?? 0);
        $admin = $this->userModel->getUserById($id);
        if (!$admin) { header('Location: /admin/admins'); exit; }
        $this->view('admin/admins/view', ['title' => 'Admin: Administrator Profile', 'admin' => $admin]);
    }

    // ========================================================
    // EVENTS
    // ========================================================

    public function events()
    {
        $events = $this->eventModel->getAllEvents();
        $this->view('admin/events', [
            'title' => 'Admin: Events',
            'events' => $events
        ]);
    }

    public function storeEvent()
    {
        header('Content-Type: application/json');

        $imageValidation = $this->validateEventImageUploads();
        if (isset($imageValidation['error'])) {
            echo json_encode(['success' => false, 'message' => $imageValidation['error']]);
            exit;
        }

        $data = [
            'title'       => trim($_POST['title'] ?? ''),
            'venue'       => trim($_POST['venue'] ?? ''),
            'date'        => $_POST['date'] ?? '',
            'time'        => $_POST['time'] ?? '',
            'status'      => $_POST['status'] ?? 'active',
            'description' => $_POST['description'] ?? '',
            'created_by'  => $_SESSION['user']['userid'],
        ];

        if (empty($data['title'])) {
            echo json_encode(['success' => false, 'message' => 'Title is required.']);
            exit;
        }

        if ($this->eventModel->createEvent($data)) {
            $eventId = (int)$this->eventModel->getLastInsertId();
            $saveResult = $this->saveEventImages($eventId, $imageValidation['files'] ?? [], 1);
            if (isset($saveResult['error'])) {
                echo json_encode(['success' => false, 'message' => $saveResult['error']]);
                exit;
            }
            echo json_encode(['success' => true, 'message' => 'Event created successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create event.']);
        }
        exit;
    }

    public function updateEvent()
    {
        header('Content-Type: application/json');

        $imageValidation = $this->validateEventImageUploads();
        if (isset($imageValidation['error'])) {
            echo json_encode(['success' => false, 'message' => $imageValidation['error']]);
            exit;
        }

        $id   = (int)($_POST['id'] ?? 0);
        $data = [
            'title'       => trim($_POST['title'] ?? ''),
            'venue'       => trim($_POST['venue'] ?? ''),
            'date'        => $_POST['date'] ?? '',
            'time'        => $_POST['time'] ?? '',
            'status'      => $_POST['status'] ?? 'active',
            'description' => $_POST['description'] ?? '',
        ];

        if ($id && $this->eventModel->updateEvent($id, $data)) {
            $uploadMode = $_POST['image_upload_mode'] ?? 'add';
            $deletedIds = $this->csvToIntList((string)($_POST['deleted_image_ids'] ?? ''));
            $orderTokens = $this->csvToStringList((string)($_POST['event_image_order'] ?? ''));

            if ($uploadMode === 'replace') {
                $existingImages = $this->eventModel->getEventImages($id);
                $replaceDeleteIds = array_map(static fn($image) => (int)$image['id'], $existingImages);
                $nextOrder = 1;
                $saveResult = $this->saveEventImages($id, $imageValidation['files'] ?? [], $nextOrder);
                if (!isset($saveResult['error']) && !$this->hardDeleteEventImages($id, $replaceDeleteIds)) {
                    $saveResult = ['error' => 'Event was saved, but one or more replaced photos could not be removed.'];
                }
            } else {
                $nextOrder = $this->eventModel->nextImageSortOrder($id);
                $saveResult = $this->saveEventImages($id, $imageValidation['files'] ?? [], $nextOrder);
                if (!isset($saveResult['error']) && !$this->hardDeleteEventImages($id, $deletedIds)) {
                    $saveResult = ['error' => 'Event was saved, but one or more removed photos could not be deleted.'];
                }
                if (!isset($saveResult['error'])) {
                    $finalOrder = $this->resolveEventImageOrder($orderTokens, $deletedIds, $saveResult['image_ids'] ?? []);
                    if (!empty($finalOrder)) {
                        $this->eventModel->updateEventImageOrder($id, $finalOrder);
                    }
                }
            }

            if (isset($saveResult['error'])) {
                echo json_encode(['success' => false, 'message' => $saveResult['error']]);
                exit;
            }

            echo json_encode(['success' => true, 'message' => 'Event updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update event.']);
        }
        exit;
    }

    public function deleteEvent()
    {
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);

        if ($id && $this->eventModel->softDeleteEvent($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete event.']);
        }
        exit;
    }

    // ── Standalone page routes ──────────────────────────────

    public function createEventPage()
    {
        $this->view('admin/events/create', ['title' => 'Admin: Create Event']);
    }

    public function editEventPage()
    {
        $id    = (int)($_GET['id'] ?? 0);
        $event = $this->eventModel->getEventById($id);
        if (!$event) { header('Location: /admin/events'); exit; }

        $event['images'] = $this->eventImagesForView($this->eventModel->getEventImages((int)$event['id']), true);

        $this->view('admin/events/edit', ['title' => 'Admin: Edit Event', 'event' => $event]);
    }

    public function viewEventPage()
    {
        $id    = (int)($_GET['id'] ?? 0);
        $event = $this->eventModel->getEventById($id);
        if (!$event) { header('Location: /admin/events'); exit; }

        $event['images'] = $this->eventImagesForView($this->eventModel->getEventImages((int)$event['id']), true);

        $this->view('admin/events/view', ['title' => 'Admin: View Event', 'event' => $event]);
    }

    public function eventImage()
    {
        $imageId = (int)($_GET['id'] ?? 0);
        $image = $this->eventModel->getEventImageById($imageId);
        if (!$image || (int)$image['is_deleted'] === 1 || (int)$image['event_is_deleted'] === 1) {
            http_response_code(404);
            echo 'Image not found.';
            exit;
        }

        $this->serveEventImage($image);
    }

    private function eventImagesForView(array $images, bool $adminRoute = false): array
    {
        return array_map(static function (array $image) use ($adminRoute) {
            $id = (int)$image['id'];
            return [
                'id' => $id,
                'src' => ($adminRoute ? '/admin/events/image?id=' : '/events/image?id=') . $id,
                'original_name' => $image['original_name'] ?? '',
                'sort_order' => (int)($image['sort_order'] ?? 0),
            ];
        }, $images);
    }

    private function validateEventImageUploads(): array
    {
        if (!isset($_FILES['images'])) {
            return ['files' => []];
        }

        $files = $this->normalizeUploadedFiles($_FILES['images']);
        if (count($files) > 20) {
            return ['error' => 'You can upload a maximum of 20 event images at once.'];
        }

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        $validFiles = [];
        foreach ($files as $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                return ['error' => 'One of the event images failed to upload.'];
            }
            if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
                return ['error' => 'Event images must be 5MB or smaller.'];
            }
            if (!is_uploaded_file($file['tmp_name'] ?? '')) {
                return ['error' => 'One of the selected event images is invalid.'];
            }

            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            if (!isset($allowed[$mime])) {
                return ['error' => 'Event images must be JPG, PNG, or WebP files.'];
            }
            if (!@getimagesize($file['tmp_name'])) {
                return ['error' => 'One of the selected files is not a valid image.'];
            }

            $file['event_ext'] = $allowed[$mime];
            $validFiles[] = $file;
        }

        return ['files' => $validFiles];
    }

    private function normalizeUploadedFiles(array $files): array
    {
        if (!is_array($files['name'] ?? null)) {
            return [$files];
        }

        $normalized = [];
        foreach ($files['name'] as $index => $name) {
            $normalized[] = [
                'name' => $name,
                'type' => $files['type'][$index] ?? '',
                'tmp_name' => $files['tmp_name'][$index] ?? '',
                'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$index] ?? 0,
            ];
        }

        return $normalized;
    }

    private function saveEventImages(int $eventId, array $files, int $startOrder): array
    {
        if (empty($files)) {
            return ['success' => true, 'image_ids' => []];
        }

        $storage = new Storage('event-images');
        $sortOrder = $startOrder;
        $imageIds = [];
        foreach ($files as $file) {
            $savedName = $storage->save($file, $file['event_ext'], 'EVT');
            if (!$savedName) {
                return ['error' => 'Failed to save one of the event images.'];
            }

            $relativePath = 'event-images/' . $savedName;
            if (!$this->eventModel->createEventImage($eventId, $relativePath, (string)($file['name'] ?? $savedName), $sortOrder)) {
                return ['error' => 'Failed to save event image metadata.'];
            }
            $imageIds[] = (int)$this->eventModel->getLastInsertId();
            $sortOrder++;
        }

        return ['success' => true, 'image_ids' => $imageIds];
    }

    private function hardDeleteEventImages(int $eventId, array $imageIds): bool
    {
        $imageIds = array_values(array_unique(array_filter(array_map('intval', $imageIds))));
        if (empty($imageIds)) {
            return true;
        }

        $images = $this->eventModel->getEventImagesByIds($eventId, $imageIds);
        foreach ($images as $image) {
            $relativePath = str_replace(['../', './', '\\'], ['', '', '/'], (string)($image['file_path'] ?? ''));
            if (!str_starts_with($relativePath, 'event-images/')) {
                continue;
            }

            $filename = basename($relativePath);
            $fullPath = rtrim(MNT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'event-images' . DIRECTORY_SEPARATOR . $filename;
            if (is_file($fullPath) && !@unlink($fullPath)) {
                return false;
            }
        }

        return $this->eventModel->deleteEventImages($eventId, $imageIds);
    }

    private function csvToIntList(string $csv): array
    {
        if (trim($csv) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn($value) => (int)trim($value),
            explode(',', $csv)
        )));
    }

    private function csvToStringList(string $csv): array
    {
        if (trim($csv) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $csv))));
    }

    private function resolveEventImageOrder(array $tokens, array $deletedIds, array $newImageIds): array
    {
        $deletedLookup = array_flip(array_map('intval', $deletedIds));
        $newIndex = 0;
        $finalOrder = [];

        foreach ($tokens as $token) {
            if (str_starts_with($token, 'existing:')) {
                $imageId = (int)substr($token, strlen('existing:'));
                if ($imageId > 0 && !isset($deletedLookup[$imageId])) {
                    $finalOrder[] = $imageId;
                }
                continue;
            }

            if ($token === 'new' && isset($newImageIds[$newIndex])) {
                $finalOrder[] = (int)$newImageIds[$newIndex];
                $newIndex++;
            }
        }

        while (isset($newImageIds[$newIndex])) {
            $finalOrder[] = (int)$newImageIds[$newIndex];
            $newIndex++;
        }

        return array_values(array_unique(array_filter($finalOrder)));
    }

    private function serveEventImage(array $image): void
    {
        $relativePath = str_replace(['../', './', '\\'], ['', '', '/'], (string)($image['file_path'] ?? ''));
        if (!str_starts_with($relativePath, 'event-images/')) {
            http_response_code(404);
            echo 'Image not found.';
            exit;
        }

        $filename = basename($relativePath);
        $fullPath = rtrim(MNT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'event-images' . DIRECTORY_SEPARATOR . $filename;
        if (!is_file($fullPath)) {
            http_response_code(404);
            echo 'Image file not found.';
            exit;
        }

        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            http_response_code(415);
            echo 'Unsupported image type.';
            exit;
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($fullPath));
        header('Content-Disposition: inline; filename="' . basename((string)($image['original_name'] ?? $filename)) . '"');
        readfile($fullPath);
        exit;
    }

    // ========================================================
    // QUESTIONNAIRES (Moved to AdminQuestionnaireController)
    // ========================================================

}
