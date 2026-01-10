<?php
require_once __DIR__ . '/Database.php';

class ToolsManagement {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function listActiveTools() {
        try {
            $stmt = $this->pdo->prepare("
            SELECT id, tool_name, display_name, description, is_active FROM service_type WHERE is_active = TRUE"
        );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get tool permissions error: " . $e->getMessage());
            return [];
        }
    }

    public function listAllTools() {
        try {
            $stmt = $this->pdo->prepare("
            SELECT id, tool_name, display_name, description, is_active FROM service_type"
        );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get tool permissions error: " . $e->getMessage());
            return [];
        }
    }

    public function updateToolVisibility($toolName) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE service_type
                SET is_active = 1 - is_active
                WHERE tool_name = ?
            ");
            return $stmt->execute([$toolName]);
        } catch (Exception $e) {
            error_log("Update tool visibility error: " . $e->getMessage());
            return false;
        }
    }

}
?>