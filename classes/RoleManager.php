<?php
require_once __DIR__ . '/Database.php';

class RoleManager {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Assign a role to a user
    public function assignRole($userId, $roleName) {
        try {
            // Get role ID
            $stmt = $this->pdo->prepare("SELECT id FROM user_type WHERE role = ?");
            $stmt->execute([$roleName]);
            $role = $stmt->fetch();

            if (!$role) {
                throw new Exception("Role '$roleName' not found");
            }

            // Check if user already has this role
            $stmt = $this->pdo->prepare("SELECT id FROM user_role WHERE user_id = ? AND role_id = ?");
            $stmt->execute([$userId, $role['id']]);
            
            if ($stmt->fetch()) {
                return true; // Role already assigned
            }

            // Assign role
            $stmt = $this->pdo->prepare("INSERT INTO user_role (user_id, role_id) VALUES (?, ?)");
            $stmt->execute([$userId, $role['id']]);

            return true;

        } catch (Exception $e) {
            error_log("Role assignment error: " . $e->getMessage());
            return false;
        }
    }

    // Remove a role from a user
    public function removeRole($userId, $roleName) {
        try {
            $stmt = $this->pdo->prepare("
                DELETE ur FROM user_role ur 
                JOIN user_type ut ON ur.role_id = ut.id 
                WHERE ur.user_id = ? AND ut.role = ?
            ");
            $stmt->execute([$userId, $roleName]);

            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            error_log("Role removal error: " . $e->getMessage());
            return false;
        }
    }

    // Get all roles for a user
    public function getUserRoles($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT ut.role, ut.description 
                FROM user_role ur 
                JOIN user_type ut ON ur.role_id = ut.id 
                WHERE ur.user_id = ?
            ");
            $stmt->execute([$userId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Get user roles error: " . $e->getMessage());
            return [];
        }
    }

    // Check if user has a specific role
    public function hasRole($userId, $roleName) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count 
                FROM user_role ur 
                JOIN user_type ut ON ur.role_id = ut.id 
                WHERE ur.user_id = ? AND ut.role = ?
            ");
            $stmt->execute([$userId, $roleName]);
            $result = $stmt->fetch();

            return $result['count'] > 0;

        } catch (Exception $e) {
            error_log("Check role error: " . $e->getMessage());
            return false;
        }
    }

    // Check if user has any of the specified roles
    public function hasAnyRole($userId, array $roles) {
        if (empty($roles)) {
            return false;
        }

        try {
            $placeholders = str_repeat('?,', count($roles) - 1) . '?';
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count 
                FROM user_role ur 
                JOIN user_type ut ON ur.role_id = ut.id 
                WHERE ur.user_id = ? AND ut.role IN ($placeholders)
            ");
            
            $params = array_merge([$userId], $roles);
            $stmt->execute($params);
            $result = $stmt->fetch();

            return $result['count'] > 0;

        } catch (Exception $e) {
            error_log("Check any role error: " . $e->getMessage());
            return false;
        }
    }

    // Get all available roles
    public function getAllRoles() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM user_type ORDER BY id");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Get all roles error: " . $e->getMessage());
            return [];
        }
    }

    // Get users by role
    public function getUsersByRole($roleName) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.user_id, u.username, u.email, u.created_at 
                FROM users u 
                JOIN user_role ur ON u.user_id = ur.user_id 
                JOIN user_type ut ON ur.role_id = ut.id 
                WHERE ut.role = ?
            ");
            $stmt->execute([$roleName]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Get users by role error: " . $e->getMessage());
            return [];
        }
    }

    // Get all service types (tools)
    public function getAllServiceTypes() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM service_type WHERE is_active = TRUE ORDER BY id");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get service types error: " . $e->getMessage());
            return [];
        }
    }

    // Get tool permissions for a specific role
    public function getToolPermissions($roleId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT st.tool_name, st.display_name, tp.is_allowed 
                FROM tool_permissions tp 
                JOIN service_type st ON tp.tool_id = st.id 
                WHERE tp.role_id = ? AND st.is_active = TRUE
            ");
            $stmt->execute([$roleId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get tool permissions error: " . $e->getMessage());
            return [];
        }
    }

    // Check if a role has permission for a specific tool
    public function canUseTool($roleName, $toolName) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT tp.is_allowed 
                FROM tool_permissions tp 
                JOIN service_type st ON tp.tool_id = st.id 
                JOIN user_type ut ON tp.role_id = ut.id 
                WHERE ut.role = ? AND st.tool_name = ? AND st.is_active = TRUE
            ");
            $stmt->execute([$roleName, $toolName]);
            $result = $stmt->fetch();
            
            return $result ? (bool)$result['is_allowed'] : false;
        } catch (Exception $e) {
            error_log("Check tool permission error: " . $e->getMessage());
            return false;
        }
    }

    // Update tool permissions (admin function)
    public function updateToolPermission($toolName, $roleName, $isAllowed) {
        try {
            $this->pdo->beginTransaction();

            // Get tool ID
            $stmt = $this->pdo->prepare("SELECT id FROM service_type WHERE tool_name = ?");
            $stmt->execute([$toolName]);
            $tool = $stmt->fetch();
            
            if (!$tool) {
                throw new Exception("Tool '$toolName' not found");
            }

            // Get role ID
            $stmt = $this->pdo->prepare("SELECT id FROM user_type WHERE role = ?");
            $stmt->execute([$roleName]);
            $role = $stmt->fetch();
            
            if (!$role) {
                throw new Exception("Role '$roleName' not found");
            }

            // Check if permission exists
            $stmt = $this->pdo->prepare("SELECT id FROM tool_permissions WHERE tool_id = ? AND role_id = ?");
            $stmt->execute([$tool['id'], $role['id']]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update existing permission
                $stmt = $this->pdo->prepare("UPDATE tool_permissions SET is_allowed = ? WHERE tool_id = ? AND role_id = ?");
                $stmt->execute([$isAllowed ? 1 : 0, $tool['id'], $role['id']]);
            } else {
                // Insert new permission
                $stmt = $this->pdo->prepare("INSERT INTO tool_permissions (tool_id, role_id, is_allowed) VALUES (?, ?, ?)");
                $stmt->execute([$tool['id'], $role['id'], $isAllowed ? 1 : 0]);
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Update tool permission error: " . $e->getMessage());
            return false;
        }
    }

    // Get all permissions matrix (for admin page)
    public function getAllPermissionsMatrix() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    st.tool_name,
                    st.display_name,
                    ut.role,
                    ut.id as role_id,
                    st.id as tool_id,
                    COALESCE(tp.is_allowed, 0) as is_allowed
                FROM service_type st
                CROSS JOIN user_type ut
                LEFT JOIN tool_permissions tp ON st.id = tp.tool_id AND ut.id = tp.role_id
                WHERE st.is_active = TRUE
                ORDER BY st.id, ut.id
            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Organize by tool
            $matrix = [];
            foreach ($results as $row) {
                if (!isset($matrix[$row['tool_name']])) {
                    $matrix[$row['tool_name']] = [
                        'display_name' => $row['display_name'],
                        'permissions' => []
                    ];
                }
                $matrix[$row['tool_name']]['permissions'][$row['role']] = (bool)$row['is_allowed'];
            }

            return $matrix;

        } catch (Exception $e) {
            error_log("Get permissions matrix error: " . $e->getMessage());
            return [];
        }
    }

    // Add this method to RoleManager class
    public function getToolPermissionsByRoleName($roleName) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    st.tool_name,
                    st.display_name,
                    tp.is_allowed
                FROM tool_permissions tp
                JOIN service_type st ON tp.tool_id = st.id
                JOIN user_type ut ON tp.role_id = ut.id
                WHERE ut.role = ? AND st.is_active = TRUE
                ORDER BY st.display_name
            ");
            $stmt->execute([$roleName]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get tool permissions by role name error: " . $e->getMessage());
            return [];
        }
    }

    public function getPrimaryUserRole($userId) {
        $roles = $this->getUserRoles($userId);
        return !empty($roles) ? $roles[0]['role_id'] : null;
    }
}
?>