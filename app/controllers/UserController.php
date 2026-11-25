<?php
require_once __DIR__ . '/../models/UserModel.php'; // adjust path
require_once __DIR__ . '/../models/UserModelDataTables.php'; 

class UserController extends Controller
{
    protected $userModel;
    protected $userModelDataTables;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userModelDataTables = new UserModelDataTables();
    }

    public function index()
    {
        //$data = ['title' => 'Users Page'];
        //$users = $this->userModel->getAllUsers();
        $data = [
            'title' => 'Users Page', 
            'userid' => $_SESSION['user']['userid'], 
            'roleid' => $_SESSION['user']['roleid']
            //'users' => $this->userModel->getAllUsers()
        ];
        $this->view('users', $data);
    }

    public function getAllUsers() {
        //header('Content-Type: application/json');
        $users = $this->userModel->getAllUsers();
        echo json_encode($users);
        exit;
    }

    public function getUsersDataTables() {
        header('Content-Type: application/json');

        $draw = $_GET['draw'] ?? 1;
        $start = $_GET['start'] ?? 0;
        $length = $_GET['length'] ?? 10;
        $search = $_GET['search']['value'] ?? '';
        $orderColumnIndex = $_GET['order'][0]['column'] ?? 0;
        $orderDir = $_GET['order'][0]['dir'] ?? 'ASC';

        $columns = ['userid', 'roleid', 'email'];
        $orderColumn = $columns[$orderColumnIndex] ?? 'userid';

        // create custom 'where' condition here (based on scope and/or role heiarchy).

        $data = $this->userModelDataTables->getUsersData($start, $length, $search, $orderColumn, $orderDir);
        $recordsFiltered = $this->userModelDataTables->getFilteredCount($search);
        $recordsTotal = $this->userModelDataTables->getTotalUsers();

        echo json_encode([
            'draw' => (int)$draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
        exit;
    }

    public function user()
    {
        // Get the 'id' parameter from URL
        $id = $_GET['id'] ?? null;

        $data = [
            'title' => 'User Page',
            'user_id' => $id
        ];

        if($id == null || $id == ''){
            $this->view('users', ['title' => 'Users Page']);
        } else {
            $this->view('user', $data);
        }
    }

    public function getTotalUsers() 
    {
        $totalUsers = $this->userModel->getTotalUsers();
        echo json_encode(['total' => (int)$totalUsers]);
    }
}