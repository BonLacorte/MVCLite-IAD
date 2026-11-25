<?php
class UserModel extends Model
{
    public function __construct()
    {
        parent::__construct('default');
    }

    // Get all users (full detail for admin table)
    public function getAllUsers()
    {
        $stmt = $this->db->query("SELECT id, name, email, role, position, department_office_unit, for_central_office, for_region_center, cellphone_number, facebook_account, profile_image, is_deleted, createdTime FROM users WHERE is_deleted = 0 ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get users filtered by role
    public function getAllByRole($role)
    {
        $stmt = $this->db->prepare("SELECT id, name, email, role, position, department_office_unit, for_central_office, for_region_center, cellphone_number, facebook_account, profile_image, createdTime FROM users WHERE role = :role AND is_deleted = 0 ORDER BY name ASC");
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Admin: Create a new user
    public function createUser($data)
    {
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, position, department_office_unit, for_central_office, for_region_center, cellphone_number, facebook_account) 
            VALUES (:name, :email, :password, :role, :position, :department_office_unit, :for_central_office, :for_region_center, :cellphone_number, :facebook_account)");
        return $stmt->execute($data);
    }

    // Admin: Full update (including role and is_deleted)
    public function adminUpdateUser($id, $data)
    {
        $stmt = $this->db->prepare("UPDATE users SET 
            name = :name,
            email = :email,
            role = :role,
            position = :position,
            department_office_unit = :department_office_unit,
            for_central_office = :for_central_office,
            for_region_center = :for_region_center,
            cellphone_number = :cellphone_number,
            facebook_account = :facebook_account,
            edited_at = NOW()
            WHERE id = :id");
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    // Soft delete a user
    public function softDeleteUser($id)
    {
        $stmt = $this->db->prepare("UPDATE users SET is_deleted = 1 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // Check if email exists (excluding a specific id for updates)
    public function emailExists($email, $excludeId = null)
    {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email AND id != :excludeId AND is_deleted = 0");
            $stmt->execute(['email' => $email, 'excludeId' => $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email AND is_deleted = 0");
            $stmt->execute(['email' => $email]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }


    public function getTotalUsers()
    {
        $stmt = $this->db->prepare("SELECT count(*) as total FROM users WHERE is_deleted = 0");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Get a single user by ID
    public function getUserById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get username (login)
    public function findByUsername($username)
    {
        // Try to find by email or name
        $stmt = $this->db->prepare("SELECT * FROM users WHERE (email = :username OR name = :username) AND is_deleted = 0 LIMIT 1");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Add a new user
    public function addUser($userid, $email)
    {
        $stmt = $this->db->prepare("INSERT INTO users (userid, email) VALUES (:userid, :email)");
        return $stmt->execute(['userid' => $userid, 'email' => $email]);
    }

    // Update user profile
    public function updateUser($id, $data)
    {
        $stmt = $this->db->prepare("UPDATE users SET 
            name = :name, 
            email = :email, 
            cellphone_number = :cellphone_number, 
            facebook_account = :facebook_account, 
            position = :position, 
            department_office_unit = :department_office_unit, 
            for_central_office = :for_central_office, 
            for_region_center = :for_region_center,
            edited_at = NOW()
            WHERE id = :id");
        
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function updateProfileImage(int $id, ?string $profileImage): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET profile_image = :profile_image, edited_at = NOW() WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'profile_image' => $profileImage
        ]);
    }
    // Update user password
    public function updatePassword($id, $passwordHash)
    {
        $stmt = $this->db->prepare("UPDATE users SET password = :password, edited_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $id, 'password' => $passwordHash]);
    }

    /**
     * Get the currently active session ID for the user
     */
    public function getActiveSessionId(int $userId): ?string
    {
        $stmt = $this->db->prepare("SELECT session_id FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result && $result['session_id'] ? $result['session_id'] : null;
    }

    /**
     * Update the session ID for the user
     */
    public function updateSessionId(int $userId, ?string $sessionId): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET session_id = :session_id WHERE id = :id");
        return $stmt->execute(['id' => $userId, 'session_id' => $sessionId]);
    }
}