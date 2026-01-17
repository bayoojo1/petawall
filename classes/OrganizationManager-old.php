<?php
require_once __DIR__ . '/Database.php';

class OrganizationManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get or create organization for user
     * If user already has an organization, return it
     * Otherwise, create one based on user's email domain
     */
    public function getOrCreateUserOrganization($userId, $userEmail) {
        try {
            // Check if user already has an organization
            $stmt = $this->db->prepare("
                SELECT organization_id FROM users WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($userData && $userData['organization_id']) {
                return $userData['organization_id'];
            }
            
            // Extract domain from email
            $domain = $this->extractDomainFromEmail($userEmail);
            $organizationName = $this->generateOrganizationName($domain);
            
            // Create new organization
            $stmt = $this->db->prepare("
                INSERT INTO phishing_organizations (name, domain) 
                VALUES (?, ?)
            ");
            $stmt->execute([$organizationName, $domain]);
            $organizationId = $this->db->lastInsertId();
            
            // Update user with organization ID
            $stmt = $this->db->prepare("
                UPDATE users SET organization_id = ? WHERE user_id = ?
            ");
            $stmt->execute([$organizationId, $userId]);
            
            return $organizationId;
            
        } catch (Exception $e) {
            error_log("Organization creation error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Extract domain from email
     */
    private function extractDomainFromEmail($email) {
        $parts = explode('@', $email);
        if (count($parts) === 2) {
            return $parts[1];
        }
        return 'unknown.com';
    }
    
    /**
     * Generate organization name from domain
     */
    private function generateOrganizationName($domain) {
        $domainParts = explode('.', $domain);
        $companyName = ucfirst($domainParts[0]);
        return $companyName . " Company";
    }
    
    /**
     * Create organization with custom name
     */
    public function createOrganization($name, $domain = null, $userId = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO phishing_organizations (name, domain) 
                VALUES (?, ?)
            ");
            $stmt->execute([$name, $domain]);
            $organizationId = $this->db->lastInsertId();
            
            // If user ID provided, associate user with organization
            if ($userId) {
                $this->associateUserWithOrganization($userId, $organizationId);
            }
            
            return [
                'success' => true,
                'organization_id' => $organizationId,
                'message' => 'Organization created successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to create organization: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Associate user with organization
     */
    public function associateUserWithOrganization($userId, $organizationId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE users SET organization_id = ? WHERE user_id = ?
            ");
            $stmt->execute([$organizationId, $userId]);
            
            return true;
        } catch (Exception $e) {
            error_log("User organization association error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get organization by ID
     */
    public function getOrganization($organizationId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM phishing_organizations WHERE id = ?
            ");
            $stmt->execute([$organizationId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get organization error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user's organization
     */
    public function getUserOrganization($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT o.* 
                FROM phishing_organizations o
                JOIN users u ON o.id = u.organization_id
                WHERE u.user_id = ?
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get user organization error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if user has organization
     */
    public function userHasOrganization($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT organization_id FROM users WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return !empty($userData['organization_id']);
        } catch (Exception $e) {
            error_log("Check user organization error: " . $e->getMessage());
            return false;
        }
    }

    /**
 * Update organization name
 */
    public function updateOrganizationName($organizationId, $name) {
        try {
            $stmt = $this->db->prepare("
                UPDATE phishing_organizations SET name = ? WHERE id = ?
            ");
            $stmt->execute([$name, $organizationId]);
            
            return [
                'success' => true,
                'message' => 'Organization name updated successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to update organization: ' . $e->getMessage()
            ];
        }
    }
}
?>