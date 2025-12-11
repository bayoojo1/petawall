<?php
require_once __DIR__ . '/Auth.php';

class AccessControl {
    private $auth;
    private $roleManager;

    public function __construct() {
        $this->auth = new Auth();
        $this->roleManager = new RoleManager();
    }

    // Check if current user can access a resource
    public function canAccess($requiredRole) {
        if (!$this->auth->isLoggedIn()) {
            return false;
        }

        return $this->auth->hasRole($requiredRole);
    }

    // Check if current user can access any of the required roles
    public function canAccessAny(array $requiredRoles) {
        if (!$this->auth->isLoggedIn()) {
            return false;
        }

        return $this->auth->hasAnyRole($requiredRoles);
    }

    // Middleware for protected pages
    public function requireRole($requiredRole, $redirectUrl = 'index.php') {
        if (!$this->canAccess($requiredRole)) {
            header("Location: $redirectUrl");
            exit;
        }
    }

    public function requireAnyRole(array $requiredRoles, $redirectUrl = 'index.php') {
        if (!$this->canAccessAny($requiredRoles)) {
            header("Location: $redirectUrl");
            exit;
        }
    }

     // Check if current user can access a specific tool
    public function canUseTool($toolName) {
        if (!$this->auth->isLoggedIn()) {
            return false;
        }

        // Get user roles
        $userRoles = $this->auth->getUserRoles();
        
        // Check each role for permission
        foreach ($userRoles as $userRole) {
            if ($this->roleManager->canUseTool($userRole['role'], $toolName)) {
                return true;
            }
        }

        return false;
    }

    // Check if user can access any of the tools
    public function canUseAnyTool(array $toolNames) {
        if (!$this->auth->isLoggedIn()) {
            return false;
        }

        foreach ($toolNames as $toolName) {
            if ($this->canUseTool($toolName)) {
                return true;
            }
        }

        return false;
    }

    // Get all tools that the current user can access
    public function getAllowedTools() {
        if (!$this->auth->isLoggedIn()) {
            return [];
        }

        $allTools = $this->roleManager->getAllServiceTypes();
        $allowedTools = [];

        foreach ($allTools as $tool) {
            if ($this->canUseTool($tool['tool_name'])) {
                $allowedTools[] = $tool;
            }
        }

        return $allowedTools;
    }

    // Middleware for tool pages
    public function requireToolAccess($toolName, $redirectUrl = 'index.php') {
        if (!$this->canUseTool($toolName)) {
            $_SESSION['error_message'] = 'You do not have permission to access this tool.';
            header("Location: $redirectUrl");
            exit;
        }
    }

    // Check permission for specific role (for admin use)
    public function canRoleUseTool($roleName, $toolName) {
        return $this->roleManager->canUseTool($roleName, $toolName);
    }

    // Admin function to update permissions
    public function updateToolPermission($toolName, $roleName, $isAllowed) {
        if (!$this->auth->hasRole('admin')) {
            return false;
        }
        
        return $this->roleManager->updateToolPermission($toolName, $roleName, $isAllowed);
    }

    // Get permissions matrix for admin page
    public function getPermissionsMatrix() {
        if (!$this->auth->hasRole('admin')) {
            return [];
        }
        
        return $this->roleManager->getAllPermissionsMatrix();
    }

    // Get all available tools
    public function getAllTools() {
        return $this->roleManager->getAllServiceTypes();
    }

    // Get all available roles
    public function getAllRoles() {
        return $this->roleManager->getAllRoles();
    }
}
?>