<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../../core/Storage.php';

class ProfileController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();

        if (!isset($_SESSION['user'])) {
            header("Location: /login");
            exit;
        }
    }

    public function index()
    {
        $user = $this->userModel->getUserById((int)$_SESSION['user']['userid']);

        $this->view('profile/index', [
            'title' => 'My Profile',
            'user' => $user
        ]);
    }

    public function edit()
    {
        $user = $this->userModel->getUserById((int)$_SESSION['user']['userid']);

        $this->view('profile/edit', [
            'title' => 'Edit Profile',
            'user' => $user
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /profile");
            exit;
        }

        $id = (int)$_SESSION['user']['userid'];
        $currentUser = $this->userModel->getUserById($id);
        if (!$currentUser) {
            header("Location: /logout");
            exit;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'cellphone_number' => trim($_POST['cellphone_number'] ?? ''),
            'facebook_account' => trim($_POST['facebook_account'] ?? ''),
            'position' => trim($_POST['position'] ?? ''),
            'department_office_unit' => trim((string)($currentUser['department_office_unit'] ?? '')),
            'for_central_office' => trim((string)($currentUser['for_central_office'] ?? '')),
            'for_region_center' => trim((string)($currentUser['for_region_center'] ?? ''))
        ];

        if ($data['name'] === '' || $data['email'] === '') {
            $this->flashAndRedirect('Name and email are required.', 'error', '/profile/edit');
        }

        $emailChanged = strcasecmp((string)($currentUser['email'] ?? ''), $data['email']) !== 0;
        if ($emailChanged && $this->userModel->emailExists($data['email'], $id)) {
            $this->flashAndRedirect('Email address is already used by another account.', 'error', '/profile/edit');
        }

        $profileImagePath = $currentUser['profile_image'] ?? null;
        if (!empty($_POST['remove_profile_image'])) {
            $profileImagePath = null;
        }

        $uploadedPath = $this->handleProfileImageUpload($id, $data['name']);
        if ($uploadedPath !== null) {
            $profileImagePath = $uploadedPath;
        }

        $updated = $this->userModel->updateUser($id, $data);
        if ($updated) {
            $this->userModel->updateProfileImage($id, $profileImagePath ?: null);
            $_SESSION['user']['email'] = $data['email'];
            $_SESSION['user']['profile_image'] = $profileImagePath ?: null;
            if ($emailChanged) {
                $this->logoutAfterSensitiveChange('/login?notice=email_changed');
            }
            $this->flashAndRedirect('Profile updated successfully.', 'success', '/profile');
        }

        $this->flashAndRedirect('Failed to update profile.', 'error', '/profile/edit');
    }

    public function image()
    {
        $sessionUser = $_SESSION['user'] ?? [];
        $viewerId = (int)($sessionUser['userid'] ?? 0);
        $requestedId = isset($_GET['id']) ? (int)$_GET['id'] : $viewerId;
        $role = strtoupper((string)($sessionUser['roleid'] ?? ''));

        if ($requestedId !== $viewerId && !in_array($role, ['ADMIN', 'SADMIN'], true)) {
            http_response_code(403);
            exit;
        }

        $user = $this->userModel->getUserById($requestedId);
        if (!$user || empty($user['profile_image'])) {
            http_response_code(404);
            exit;
        }

        $relativePath = str_replace('\\', '/', (string)$user['profile_image']);
        if (!str_starts_with($relativePath, 'profile-pictures/')) {
            http_response_code(404);
            exit;
        }

        $filename = basename($relativePath);
        $absolutePath = rtrim(MNT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'profile-pictures' . DIRECTORY_SEPARATOR . $filename;
        if (!is_file($absolutePath)) {
            http_response_code(404);
            exit;
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $mime = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp'
        ][$ext] ?? 'application/octet-stream';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($absolutePath));
        header('Content-Disposition: inline; filename="profile-image-' . $requestedId . '.' . $ext . '"');
        header('Cache-Control: private, max-age=300');
        readfile($absolutePath);
        exit;
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /profile");
            exit;
        }

        $id = (int)$_SESSION['user']['userid'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($newPassword !== $confirmPassword) {
            $this->flashAndRedirect('New passwords do not match.', 'error', '/profile');
        }

        $user = $this->userModel->getUserById($id);
        require_once __DIR__ . '/../services/Crypto.php';

        if (!$user || !Crypto::passwordDecrypt($currentPassword, $user['password'])) {
            $this->flashAndRedirect('Current password is incorrect.', 'error', '/profile');
        }

        $newHash = Crypto::passwordEncrypt($newPassword);
        if ($this->userModel->updatePassword($id, $newHash)) {
            $this->logoutAfterSensitiveChange('/login?notice=password_changed');
        }

        $this->flashAndRedirect('Failed to update password.', 'error', '/profile');
    }

    private function handleProfileImageUpload(int $userId, string $name): ?string
    {
        $file = $_FILES['profile_image'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $this->flashAndRedirect('Profile photo upload failed. Please try another image.', 'error', '/profile/edit');
        }

        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            $this->flashAndRedirect('Profile photo must be 5MB or smaller.', 'error', '/profile/edit');
        }

        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowedExtensions, true)) {
            $this->flashAndRedirect('Profile photo must be a JPG, PNG, or WebP image.', 'error', '/profile/edit');
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $mimeType = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mimeType = (string)finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }
        }
        if ($mimeType !== '' && !in_array($mimeType, $allowedMimeTypes, true)) {
            $this->flashAndRedirect('Profile photo content is not a supported image type.', 'error', '/profile/edit');
        }

        $storage = new Storage('profile-pictures');
        $savedName = $storage->save($file, $extension === 'jpeg' ? 'jpg' : $extension, $name ?: ('user-' . $userId));
        if ($savedName === false) {
            $this->flashAndRedirect('Profile photo could not be saved.', 'error', '/profile/edit');
        }

        return 'profile-pictures/' . $savedName;
    }

    private function flashAndRedirect(string $message, string $type, string $location): void
    {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
        header('Location: ' . $location);
        exit;
    }

    private function logoutAfterSensitiveChange(string $location): void
    {
        if (isset($_SESSION['user']['userid'])) {
            $this->userModel->updateSessionId((int)$_SESSION['user']['userid'], null);
        }

        unset($_SESSION['user']);
        Csrf::destroy();
        session_destroy();
        header('Location: ' . $location);
        exit;
    }
}
