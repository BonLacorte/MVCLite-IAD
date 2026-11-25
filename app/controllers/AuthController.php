<?php
require_once __DIR__ . '/../models/UserModel.php'; 

class AuthController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function login()
    {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user'])) {
            header("Location: /dashboard");
            exit;
        }

        $notice = '';
        $noticeKey = $_GET['notice'] ?? '';
        if ($noticeKey === 'password_changed') {
            $notice = 'Your password was changed successfully. Please sign in again.';
        } elseif ($noticeKey === 'email_changed') {
            $notice = 'Your email address was changed successfully. Please sign in again.';
        }

        $data = [
            'title' => 'Login Page',
            'login_notice' => $notice
        ];
        $this->view('login', $data);
    }

    public function loginPost()
    {
        $username = (string) ($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        $csrfToken = $_POST['csrf_token'] ?? '';

        //check token
        if (!Csrf::validate($csrfToken)) {
            echo "<script>
                    alert('Invalid CSRF token');
                    window.location.href = '/';
                </script>";
            exit;
        }
        Csrf::destroy();

        $user = $this->userModel->findByUsername($username);

        //// login failed
        if (!$user || !Crypto::passwordDecrypt($password, $user['password'])) {
            // login attempt counter, monitored in login page
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            $_SESSION['last_attempt_time'] = time();

            $this->view('login', [
                'title' => 'Login Page',
                'login_error' => 'Invalid credentials. Please check your email or password.',
                'submitted_username' => $username,
                'submitted_password' => $password
            ]);
            exit;
        }

        // After successful login
        session_regenerate_id(true);
        
        // Update the active session ID in the database for single session login
        $this->userModel->updateSessionId((int)$user['id'], session_id());

        $_SESSION['user'] = [
            'userid'      => $user['id'],
            'roleid'      => ($user['role'] === 'admin') ? 'ADMIN' : 'ENCODER', 
            'permissions' => [], 
            'email'       => $user['email'],
            'profile_image' => $user['profile_image'] ?? null
        ];

        // Reset attempts on successful login
        unset($_SESSION['login_attempts'], $_SESSION['last_attempt_time']);
        header("Location: /dashboard");
        exit;
    }

    public function logout()
    {
        if (isset($_SESSION['user']['userid'])) {
            $this->userModel->updateSessionId((int)$_SESSION['user']['userid'], null);
        }
        
        unset($_SESSION['user']);
        Csrf::destroy();
        session_destroy();
        header("Location: /");
        exit;
    }

    public function keepAlive()
    {
        session_start(); // This refreshes the session
        // Return nothing or a simple 200 status
    }
}


 // Dummy user DB
        // $users = [
        //     [
        //         'id' => 1,
        //         'username' => 'guest',
        //         'password' => Crypto::encrypt('guest123'), // encrypted
        //         'role' => 'GUEST',
        //         'scope' => 'GLOBAL',
        //         'region' => null,
        //         'permissions' => [],
        //         'name' => 'Guest User'
        //     ],
        //     [
        //         'id' => 2,
        //         'username' => 'encoder1',
        //         'password' => Crypto::encrypt('encoder123'),
        //         'role' => 'ENCODER',
        //         'scope' => 'GLOBAL',
        //         'region' => null,
        //         'permissions' => ['edit_post'],
        //         'name' => 'Encoder One'
        //     ]
        // ];

        // Find user
        // $user = null;
        // foreach ($users as $u) {
        //     if ($u['username'] === $username) {
        //         $user = $u;
        //         break;
        //     }
        // }

        // Set session
        // $_SESSION['user'] = $user;
