<?php

class RoleMiddleware
{
    protected $roleService; // check services/RoleService.php

    public function __construct()
    {
        $this->roleService = new RoleService();
    }

    public function handle(array $params = []): bool
    {
        $userRole = $_SESSION['user']['roleid'] ?? null;

        if ($userRole === null) {
            return $this->deny($params, "Access Denied: no role assigned to user.");
        }

        // enforce strict usage
        if (!empty($params['requiredRole']) && !empty($params['allowedRoles'])) {
            throw new Exception("Use either 'requiredRole' OR 'allowedRoles', not both.");
        }

        // --- allowedRoles (STRICT whitelist) ---
        if (!empty($params['allowedRoles'])) {
            $allowedRoles = is_array($params['allowedRoles'])
                ? $params['allowedRoles']
                : explode(',', $params['allowedRoles']);

            $allowedRoles = array_map('trim', $allowedRoles);

            if (!in_array($userRole, $allowedRoles, true)) {
                return $this->deny($params, "Access Denied: role not allowed.");
            }

            return true;
        }

        // --- requiredRole (HIERARCHY) ---
        if (!empty($params['requiredRole'])) {

            $requiredRole = $params['requiredRole'];

            $userLevel = $this->roleService->getLevel($userRole);
            $requiredLevel = $this->roleService->getLevel($requiredRole);

            // developer error check
            if ($userLevel === 0 || $requiredLevel === 0) {
                throw new Exception("Invalid role configuration.");
            }

            if ($userLevel < $requiredLevel) {
                return $this->deny(
                    $params,
                    "Access Denied: insufficient role ($userRole)."
                );
            }
        }

        return true;
    }

    private function deny($params, $defaultMessage)
    {
        $message = $params['message'] ?? $defaultMessage;
        $type = $params['type'] ?? "error";

        // AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

            http_response_code(403);
            header('Content-Type: application/json');

            echo json_encode([
                'error' => $message,
                'type' => $type
            ]);
            exit;
        }

        // Normal request → redirect
        $redirect = $params['redirect'] ?? "/dashboard";

        header('Location: ' . $redirect .
            '?alert=' . urlencode($message) .
            '&type=' . urlencode($type));

        exit;
    }
}
