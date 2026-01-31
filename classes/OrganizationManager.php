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
            
            // Create new organization WITH DOMAIN
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
    public function extractDomainFromEmail(string $email): string {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return substr(strrchr($email, '@'), 1);
        }

        return 'unknown.com';
    }

    
    /**
     * Generate organization name from domain
     */
    public function generateOrganizationName(string $domain): string {
        $domain = strtolower($domain);

        // Remove subdomains
        $domain = preg_replace('/^www\./', '', $domain);

        $parts = explode('.', $domain);

        if (count($parts) < 2) {
            return 'Unknown Company';
        }

        // Handle common multi-part TLDs
        $tlds = ['co.uk', 'org.uk', 'ac.uk', 'gov.uk'];

        $base = implode('.', array_slice($parts, -2));
        foreach ($tlds as $tld) {
            if (str_ends_with($domain, $tld)) {
                $base = $parts[count($parts) - 3];
                break;
            }
        }

        $name = str_replace('-', ' ', $base);

        return ucwords($name) . ' Company';
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
            ");+
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
    
    /**
     * Update organization details
     */
    public function updateOrganization($organizationId, $data) {
        try {
            $sql = "UPDATE phishing_organizations SET ";
            $params = [];
            $updates = [];
            
            $allowedFields = ['name', 'domain', 'industry', 'size'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (empty($updates)) {
                return ['success' => false, 'error' => 'No data to update'];
            }
            
            $sql .= implode(', ', $updates) . " WHERE id = ?";
            $params[] = $organizationId;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return [
                'success' => true,
                'message' => 'Organization updated successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to update organization: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get organization members (users in same organization)
     */
    public function getOrganizationMembers($organizationId, $excludeUserId = null) {
        try {
            $sql = "
                SELECT u.user_id, u.email, u.username
                FROM users u
                WHERE u.organization_id = ?
            ";
            
            $params = [$organizationId];
            
            if ($excludeUserId) {
                $sql .= " AND u.user_id != ?";
                $params[] = $excludeUserId;
            }
            
            $sql .= " ORDER BY u.username";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get organization members error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get organization statistics
     */
    public function getOrganizationStats($organizationId) {
        try {
            $stats = [];
            
            // Get total users in organization
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_users 
                FROM users 
                WHERE organization_id = ?
            ");
            $stmt->execute([$organizationId]);
            $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'] ?? 0;
            
            // Get total campaigns
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_campaigns 
                FROM phishing_campaigns 
                WHERE organization_id = ?
            ");
            $stmt->execute([$organizationId]);
            $stats['total_campaigns'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_campaigns'] ?? 0;
            
            // Get campaign statistics
            $stmt = $this->db->prepare("
                SELECT 
                    COALESCE(SUM(total_recipients), 0) as total_recipients,
                    COALESCE(SUM(total_opened), 0) as total_opens,
                    COALESCE(SUM(total_clicked), 0) as total_clicks,
                    COUNT(DISTINCT c.id) as active_campaigns
                FROM phishing_campaigns c
                LEFT JOIN phishing_campaign_results r ON c.id = r.campaign_id
                WHERE c.organization_id = ?
            ");
            $stmt->execute([$organizationId]);
            $campaignStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stats = array_merge($stats, $campaignStats);
            
            // Calculate rates
            $stats['open_rate'] = $stats['total_recipients'] > 0 
                ? round(($stats['total_opens'] / $stats['total_recipients']) * 100, 1)
                : 0;
            
            $stats['click_rate'] = $stats['total_recipients'] > 0
                ? round(($stats['total_clicks'] / $stats['total_recipients']) * 100, 1)
                : 0;
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Get organization stats error: " . $e->getMessage());
            return [
                'total_users' => 0,
                'total_campaigns' => 0,
                'total_recipients' => 0,
                'total_opens' => 0,
                'total_clicks' => 0,
                'active_campaigns' => 0,
                'open_rate' => 0,
                'click_rate' => 0
            ];
        }
    }
    
    /**
     * Check if user can access organization
     */
    public function canUserAccessOrganization($userId, $organizationId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM users 
                WHERE user_id = ? AND organization_id = ?
            ");
            $stmt->execute([$userId, $organizationId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] > 0;
        } catch (Exception $e) {
            error_log("Check user access error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete organization (admin only)
     */
    public function deleteOrganization($organizationId) {
        try {
            $this->db->beginTransaction();
            
            // Remove organization from users
            $stmt = $this->db->prepare("
                UPDATE users SET organization_id = NULL WHERE organization_id = ?
            ");
            $stmt->execute([$organizationId]);
            
            // Delete organization
            $stmt = $this->db->prepare("
                DELETE FROM organizations WHERE id = ?
            ");
            $stmt->execute([$organizationId]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Organization deleted successfully'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'error' => 'Failed to delete organization: ' . $e->getMessage()
            ];
        }
    }
}
?>