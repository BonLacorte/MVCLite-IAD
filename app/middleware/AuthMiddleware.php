<?php
require_once __DIR__ . '/../models/UserModel.php';

class AuthMiddleware
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function handle($params = [])
    {
        $currentPage = $params['page'] ?? ''; // pass 'login' if this is login page

        if (!isset($_SESSION['user'])) {
            // Not logged in → protected pages
            if ($currentPage !== 'login') {
                header('Location: /login?alert=' . urlencode('Access denied. Please login.') . '&type=error');
                exit;
            }
            // If login page, just show form (do nothing)
        } else {
            // User is logged in, verify concurrent session
            $activeSessionId = $this->userModel->getActiveSessionId((int)$_SESSION['user']['userid']);
            
            if ($activeSessionId !== null && $activeSessionId !== session_id()) {
                // Session ID mismatch: user logged in on another device
                unset($_SESSION['user']);
                session_destroy();
                
                echo "<script>
                    alert('You have been logged out because your account was accessed from another device.');
                    window.location.href = '/';
                    </script>";
                exit;
            }

            // Logged in → login page
            if ($currentPage === 'login') {
                header('Location: /dashboard');
                exit;
            }
            // Else: logged in and accessing protected page → allow
        }

        return true;
    }
}
