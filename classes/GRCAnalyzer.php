<?php
require_once __DIR__ . '/ollama-search.php';
require_once __DIR__ . '/Database.php';

class GRCAnalyzer {
    private $ollama;
    private $db;
    private $assessmentId;
    
    public function __construct() {
        $this->ollama = new OllamaSearch(GRC_ANALYSIS_MODEL);
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function performAssessment($assessmentType, $organizationData, $userResponses, $selectedDomains = [], $selectedFrameworks = []) {
        try {
            // Validate inputs with selected domains
            $this->validateAssessmentData($organizationData, $userResponses, $selectedDomains);
            
            // Create assessment record
            $this->assessmentId = $this->createAssessmentRecord($organizationData, $assessmentType);
            
            // Store responses
            $this->storeAssessmentResponses($userResponses);
            
            // Calculate scores and generate report
            $results = $this->generateEvidenceBasedReport($organizationData, $userResponses, $selectedDomains);
            
            // Update assessment status
            $this->updateAssessmentStatus('completed', $results['overall_score']);
            
            return $results;
            
        } catch (Exception $e) {
            //error_log("GRC Assessment Failed: " . $e->getMessage());
            return $this->getErrorResponse($assessmentType, $organizationData, $e->getMessage());
        }
    }
    
    private function validateAssessmentData($organizationData, $userResponses) {
        if (empty($organizationData['name']) || strlen(trim($organizationData['name'])) < 2) {
            throw new Exception('Organization name is required and must be at least 2 characters long');
        }
        
        if (empty($organizationData['industry'])) {
            throw new Exception('Organization industry is required');
        }
        
        if (empty($userResponses) || !is_array($userResponses)) {
            throw new Exception('Assessment responses are required');
        }
        
        // No required questions validation - users can answer any questions they want
    }
    
    private function getRequiredQuestions() {
        // Define critical questions that must be answered
        return [
            'SRM-001', // Security policy
            'SRM-002', // Risk assessment
            'IAM-001', // MFA
            'CNS-001', // Network segmentation
            'SAT-001'  // Vulnerability scanning
        ];
    }
    
    private function createAssessmentRecord($organizationData, $assessmentType) {
        $orgId = $this->getOrCreateOrganization($organizationData);
        $assessmentUuid = uniqid('GRC_', true);
        
        $query = "INSERT INTO assessments (assessment_uuid, organization_id, assessment_type, status) 
                  VALUES (:uuid, :org_id, :type, 'in_progress')";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':uuid', $assessmentUuid);
        $stmt->bindParam(':org_id', $orgId);
        $stmt->bindParam(':type', $assessmentType);
        $stmt->execute();
        
        return $assessmentUuid;
    }
    
    private function getOrCreateOrganization($organizationData) {
        // Check if organization exists
        $query = "SELECT id FROM organizations WHERE name = :name AND industry = :industry";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $organizationData['name']);
        $stmt->bindParam(':industry', $organizationData['industry']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC)['id'];
        }
        
        // Create new organization
        $query = "INSERT INTO organizations (name, industry, size, compliance_frameworks) 
                  VALUES (:name, :industry, :size, :frameworks)";
        
        $stmt = $this->db->prepare($query);
        $frameworksJson = json_encode($organizationData['frameworks'] ?? []);
        
        $stmt->bindParam(':name', $organizationData['name']);
        $stmt->bindParam(':industry', $organizationData['industry']);
        $stmt->bindParam(':size', $organizationData['size']);
        $stmt->bindParam(':frameworks', $frameworksJson);
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }

    public function getAssessmentQuestions($domains = [], $frameworks = []) {
        //error_log("Getting questions for domains: " . print_r($domains, true));
        //error_log("Getting questions for frameworks: " . print_r($frameworks, true));
        
        // Start with base query
        $query = "SELECT 
                    d.id as domain_id,
                    d.domain_key,
                    d.domain_name,
                    d.domain_description,
                    q.id as question_id,
                    q.question_code,
                    q.question_text,
                    q.question_type,
                    q.compliance_framework,
                    q.weight,
                    q.evidence_required,
                    q.help_text,
                    q.sort_order as question_sort,
                    o.id as option_id,
                    o.option_value,
                    o.option_label,
                    o.compliance_score,
                    o.risk_level,
                    o.sort_order as option_sort
                FROM cissp_domains d
                JOIN assessment_questions q ON d.id = q.domain_id
                LEFT JOIN question_options o ON q.id = o.question_id
                WHERE q.is_active = TRUE";
        
        $params = [];
        
        // Add domain filter
        if (!empty($domains)) {
            $placeholders = implode(',', array_fill(0, count($domains), '?'));
            $query .= " AND d.domain_key IN ($placeholders)";
            $params = array_merge($params, $domains);
        }
        
        // Add framework filter using LIKE conditions
        if (!empty($frameworks)) {
            $frameworkConditions = [];
            
            foreach ($frameworks as $framework) {
                if ($framework === 'ISO27001:2022') {
                    $frameworkConditions[] = "q.compliance_framework LIKE ?";
                    $params[] = '%ISO27001:2022%';
                } elseif ($framework === 'NIST CSF') {
                    $frameworkConditions[] = "q.compliance_framework LIKE ?";
                    $params[] = '%NIST CSF%';
                } elseif ($framework === 'CIS Controls') {
                    $frameworkConditions[] = "q.compliance_framework LIKE ?";
                    $params[] = '%CIS Control%';
                } elseif ($framework === 'GDPR') {
                    $frameworkConditions[] = "q.compliance_framework LIKE ?";
                    $params[] = '%GDPR%';
                } elseif ($framework === 'PCI DSS') {
                    $frameworkConditions[] = "q.compliance_framework LIKE ?";
                    $params[] = '%PCI DSS%';
                }
            }
            
            if (!empty($frameworkConditions)) {
                $query .= " AND (" . implode(' OR ', $frameworkConditions) . ")";
            }
        }
        
        $query .= " ORDER BY d.id, q.sort_order, o.sort_order";
        
        //error_log("SQL Query: " . $query);
        //error_log("SQL Params: " . print_r($params, true));
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            //error_log("Found " . count($rows) . " rows from database");
            
            // if (empty($rows)) {
            //     error_log("No questions found. Check if:");
            //     error_log("1. Domains exist in cissp_domains table");
            //     error_log("2. Questions exist in assessment_questions table");
            //     error_log("3. Domain keys match: " . implode(', ', $domains));
            //     error_log("4. Framework patterns match compliance_framework values");
            // }
            
            return $this->formatQuestions($rows);
            
        } catch (Exception $e) {
            //error_log("Database error in getAssessmentQuestions: " . $e->getMessage());
            return [];
        }
    }

    private function formatQuestions($rows) {
        $questions = [];
        
        if (empty($rows)) {
            //error_log("No rows to format");
            return $questions;
        }
        
        foreach ($rows as $row) {
            $domainKey = $row['domain_key'];
            
            if (!isset($questions[$domainKey])) {
                $questions[$domainKey] = [
                    'domain_name' => $row['domain_name'],
                    'domain_description' => $row['domain_description'],
                    'questions' => []
                ];
            }
            
            $questionId = $row['question_id'];
            if (!isset($questions[$domainKey]['questions'][$questionId])) {
                $questions[$domainKey]['questions'][$questionId] = [
                    'question_code' => $row['question_code'],
                    'question_text' => $row['question_text'],
                    'question_type' => $row['question_type'],
                    'compliance_framework' => $row['compliance_framework'],
                    'weight' => (int)$row['weight'],
                    'evidence_required' => $row['evidence_required'],
                    'help_text' => $row['help_text'],
                    'options' => []
                ];
            }
            
            // Add option if it exists
            if ($row['option_value'] && $row['option_label']) {
                $questions[$domainKey]['questions'][$questionId]['options'][] = [
                    'value' => $row['option_value'],
                    'label' => $row['option_label'],
                    'score' => (int)$row['compliance_score'],
                    'risk_level' => $row['risk_level']
                ];
            }
        }
        
        // Convert to sequential arrays and log results
        foreach ($questions as $domainKey => &$domain) {
            $domain['questions'] = array_values($domain['questions']);
            //error_log("Domain {$domainKey}: " . count($domain['questions']) . " questions");
        }
        
        //error_log("Formatted questions: " . count($questions) . " domains");
        return $questions;
    }
    
    private function storeAssessmentResponses($userResponses) {
        foreach ($userResponses as $questionCode => $response) {
            $questionId = $this->getQuestionIdByCode($questionCode);
            
            if ($questionId) {
                $query = "INSERT INTO assessment_responses 
                          (assessment_id, question_id, response_value, evidence_provided, notes) 
                          VALUES (:assessment_id, :question_id, :response, :evidence, :notes)";
                
                $stmt = $this->db->prepare($query);
                $evidence = $response['evidence'] ?? null;
                $notes = $response['notes'] ?? null;
                
                $stmt->bindParam(':assessment_id', $this->assessmentId);
                $stmt->bindParam(':question_id', $questionId);
                $stmt->bindParam(':response', $response['value']);
                $stmt->bindParam(':evidence', $evidence);
                $stmt->bindParam(':notes', $notes);
                $stmt->execute();
            }
        }
    }
    
    private function getQuestionIdByCode($questionCode) {
        $query = "SELECT id FROM assessment_questions WHERE question_code = :code";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':code', $questionCode);
        $stmt->execute();
        
        return $stmt->rowCount() > 0 ? $stmt->fetch(PDO::FETCH_ASSOC)['id'] : null;
    }
    
    private function generateEvidenceBasedReport($organizationData, $userResponses, $selectedDomains) {
        $domainScores = [];
        $findings = [];
        $recommendations = [];
        $frameworkCompliance = [];
        
        foreach ($userResponses as $questionCode => $response) {
            $questionData = $this->getQuestionData($questionCode);
            
            if (!$questionData) continue;
            
            $domainKey = $questionData['domain_key'];
            $framework = $questionData['compliance_framework'];
            
            // Initialize domain scoring
            if (!isset($domainScores[$domainKey])) {
                $domainScores[$domainKey] = [
                    'total_score' => 0, 
                    'max_score' => 0,
                    'question_count' => 0
                ];
            }
            
            // Initialize framework compliance
            if ($framework && !isset($frameworkCompliance[$framework])) {
                $frameworkCompliance[$framework] = [
                    'total_score' => 0,
                    'max_score' => 0,
                    'question_count' => 0
                ];
            }
            
            $score = $this->calculateQuestionScore($questionData, $response);
            $weight = $questionData['weight'];
            
            // Update domain scores
            $domainScores[$domainKey]['total_score'] += $score * $weight;
            $domainScores[$domainKey]['max_score'] += 100 * $weight;
            $domainScores[$domainKey]['question_count']++;
            
            // Update framework compliance
            if ($framework) {
                $frameworkCompliance[$framework]['total_score'] += $score * $weight;
                $frameworkCompliance[$framework]['max_score'] += 100 * $weight;
                $frameworkCompliance[$framework]['question_count']++;
            }
            
            // Generate findings for low-scoring responses
            if ($score < 70) {
                $findings[] = $this->generateFinding($questionData, $response, $score);
            }
            
            // Generate recommendations
            if ($score < 100) {
                $recommendations = array_merge(
                    $recommendations, 
                    $this->generateRecommendations($questionData, $response, $score)
                );
            }
        }
        
        // Calculate overall and domain scores
        $overallScore = $this->calculateOverallScore($domainScores);
        $domainResults = $this->calculateDomainScores($domainScores);
        $frameworkResults = $this->calculateFrameworkCompliance($frameworkCompliance);
        
        // Store findings in database
        $this->storeFindings($findings);
        
        return [
            'executive_summary' => $this->generateExecutiveSummary($organizationData, $overallScore, $domainResults),
            'domain_results' => $domainResults,
            'framework_compliance' => $frameworkResults,
            'findings' => $findings,
            'recommendations' => $this->prioritizeRecommendations($recommendations),
            'assessment_id' => $this->assessmentId,
            'overall_score' => $overallScore,
            'evidence_based' => true,
            'data_sources' => ['user_responses', 'compliance_frameworks', 'industry_benchmarks'],
            'assessment_metadata' => [
                'total_questions' => count($userResponses),
                'domains_assessed' => count($domainResults),
                'frameworks_assessed' => count($frameworkResults),
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }

    private function getQuestionData($questionCode) {
        $query = "SELECT q.*, d.domain_key 
                FROM assessment_questions q 
                JOIN cissp_domains d ON q.domain_id = d.id 
                WHERE q.question_code = :code";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':code', $questionCode);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }
    
    private function calculateQuestionScore($questionData, $response) {
        if ($questionData['question_type'] === 'multiple_choice') {
            $query = "SELECT compliance_score FROM question_options 
                      WHERE question_id = :qid AND option_value = :value";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':qid', $questionData['id']);
            $stmt->bindParam(':value', $response['value']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC)['compliance_score'];
            }
        }
        
        return 0;
    }
    
    private function calculateOverallScore($domainScores) {
        $totalScore = 0;
        $totalMaxScore = 0;
        
        foreach ($domainScores as $domain) {
            $totalScore += $domain['total_score'];
            $totalMaxScore += $domain['max_score'];
        }
        
        return $totalMaxScore > 0 ? round(($totalScore / $totalMaxScore) * 100, 2) : 0;
    }
    
    private function calculateDomainScores($domainScores) {
        $results = [];
        
        foreach ($domainScores as $domainKey => $scores) {
            $score = $scores['max_score'] > 0 ? 
                round(($scores['total_score'] / $scores['max_score']) * 100, 2) : 0;
            
            $results[$domainKey] = [
                'score' => $score,
                'question_count' => $scores['question_count'],
                'risk_level' => $this->getRiskLevel($score)
            ];
        }
        
        return $results;
    }

    private function calculateFrameworkCompliance($frameworkCompliance) {
        $results = [];
        
        foreach ($frameworkCompliance as $framework => $data) {
            $score = $data['max_score'] > 0 ? 
                round(($data['total_score'] / $data['max_score']) * 100, 2) : 0;
                
            $results[$framework] = [
                'total_score' => $data['total_score'],
                'max_score' => $data['max_score'],
                'question_count' => $data['question_count'],
                'compliance_score' => $score,
                'risk_level' => $this->getRiskLevel($score)
            ];
        }
        
        return $results;
    }
    
    private function getRiskLevel($score) {
        if ($score >= 85) return 'low';
        if ($score >= 70) return 'medium';
        if ($score >= 50) return 'high';
        return 'critical';
    }
    
    private function generateFinding($questionData, $response, $score) {
        $riskLevel = $this->getRiskLevel($score);
        
        return [
            'finding_code' => 'F-' . uniqid(),
            'domain' => $questionData['domain_key'],
            'question_code' => $questionData['question_code'],
            'question_text' => $questionData['question_text'],
            'current_state' => $response['value'],
            'score' => $score,
            'risk_level' => $riskLevel,
            'description' => $this->generateFindingDescription($questionData, $response, $score),
            'compliance_framework' => $questionData['compliance_framework'],
            'evidence_required' => $questionData['evidence_required']
        ];
    }
    
    private function generateFindingDescription($questionData, $response, $score) {
        $templates = [
            'critical' => "Critical gap identified in {$questionData['question_text']}. Current implementation: {$response['value']}.",
            'high' => "Significant improvement needed in {$questionData['question_text']}. Current state: {$response['value']}.",
            'medium' => "Moderate gap in {$questionData['question_text']}. Consider enhancing current approach: {$response['value']}.",
            'low' => "Minor improvement opportunity in {$questionData['question_text']}."
        ];
        
        $riskLevel = $this->getRiskLevel($score);
        return $templates[$riskLevel] ?? $templates['medium'];
    }
    
    private function generateRecommendations($questionData, $response, $score) {
        $recommendations = [];
        $riskLevel = $this->getRiskLevel($score);
        
        // Get recommendation templates from database or generate based on question
        $baseRecommendation = [
            'recommendation_code' => 'REC-' . uniqid(),
            'domain' => $questionData['domain_key'],
            'question_code' => $questionData['question_code'],
            'priority' => $riskLevel,
            'effort' => $this->estimateEffort($riskLevel),
            'timeframe' => $this->estimateTimeframe($riskLevel),
            'description' => "Improve {$questionData['question_text']} to achieve better security posture and compliance.",
            'implementation_guidance' => "Review current implementation and align with {$questionData['compliance_framework']} requirements."
        ];
        
        $recommendations[] = $baseRecommendation;
        
        return $recommendations;
    }
    
    private function estimateEffort($riskLevel) {
        $effortMap = [
            'critical' => 'high',
            'high' => 'medium',
            'medium' => 'low',
            'low' => 'low'
        ];
        
        return $effortMap[$riskLevel] ?? 'medium';
    }
    
    private function estimateTimeframe($riskLevel) {
        $timeframeMap = [
            'critical' => '0-30 days',
            'high' => '1-3 months',
            'medium' => '3-6 months',
            'low' => '6-12 months'
        ];
        
        return $timeframeMap[$riskLevel] ?? '3-6 months';
    }
    
    private function prioritizeRecommendations($recommendations) {
        $priorityOrder = ['critical', 'high', 'medium', 'low'];
        
        usort($recommendations, function($a, $b) use ($priorityOrder) {
            $aPriority = array_search($a['priority'], $priorityOrder);
            $bPriority = array_search($b['priority'], $priorityOrder);
            return $aPriority - $bPriority;
        });
        
        return $recommendations;
    }
    
    private function storeFindings($findings) {
        foreach ($findings as $finding) {
            $query = "INSERT INTO security_findings 
                      (assessment_id, finding_code, domain_id, severity, description, recommendation, question_codes) 
                      VALUES (:assessment_id, :finding_code, :domain_id, :severity, :description, :recommendation, :question_codes)";
            
            $stmt = $this->db->prepare($query);
            $domainId = $this->getDomainIdByKey($finding['domain']);
            $questionCodes = json_encode([$finding['question_code']]);
            
            $stmt->bindParam(':assessment_id', $this->assessmentId);
            $stmt->bindParam(':finding_code', $finding['finding_code']);
            $stmt->bindParam(':domain_id', $domainId);
            $stmt->bindParam(':severity', $finding['risk_level']);
            $stmt->bindParam(':description', $finding['description']);
            $stmt->bindParam(':recommendation', $finding['description']); // Simplified
            $stmt->bindParam(':question_codes', $questionCodes);
            $stmt->execute();
        }
    }
    
    private function getDomainIdByKey($domainKey) {
        $query = "SELECT id FROM cissp_domains WHERE domain_key = :key";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':key', $domainKey);
        $stmt->execute();
        
        return $stmt->rowCount() > 0 ? $stmt->fetch(PDO::FETCH_ASSOC)['id'] : null;
    }
    
    private function updateAssessmentStatus($status, $score = null) {
        $query = "UPDATE assessments SET status = :status, overall_score = :score, completed_at = NOW() 
                  WHERE assessment_uuid = :uuid";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':score', $score);
        $stmt->bindParam(':uuid', $this->assessmentId);
        $stmt->execute();
    }
    
    private function generateExecutiveSummary($organizationData, $overallScore, $domainResults) {
        $riskLevel = $this->getRiskLevel($overallScore);
        
        return [
            'organization_name' => $organizationData['name'],
            'industry' => $organizationData['industry'],
            'size' => $organizationData['size'],
            'assessment_date' => date('Y-m-d'),
            'overall_score' => $overallScore,
            'risk_level' => $riskLevel,
            'domains_assessed' => count($domainResults),
            'key_strengths' => $this->identifyStrengths($domainResults),
            'critical_areas' => $this->identifyCriticalAreas($domainResults),
            'next_steps' => $this->generateNextSteps($riskLevel)
        ];
    }
    
    private function identifyStrengths($domainResults) {
        $strengths = [];
        foreach ($domainResults as $domain => $result) {
            if ($result['score'] >= 85) {
                $strengths[] = ucfirst(str_replace('_', ' ', $domain));
            }
        }
        return array_slice($strengths, 0, 3);
    }
    
    private function identifyCriticalAreas($domainResults) {
        $critical = [];
        foreach ($domainResults as $domain => $result) {
            if ($result['score'] < 50) {
                $critical[] = ucfirst(str_replace('_', ' ', $domain));
            }
        }
        return array_slice($critical, 0, 3);
    }
    
    private function generateNextSteps($riskLevel) {
        $nextSteps = [
            'critical' => [
                'Immediate risk mitigation actions required',
                'Executive briefing on critical findings',
                'Develop remediation plan within 30 days'
            ],
            'high' => [
                'Prioritize high-risk findings',
                'Allocate resources for remediation',
                'Quarterly progress reviews'
            ],
            'medium' => [
                'Address medium-priority findings',
                'Enhance security controls',
                'Semi-annual assessments'
            ],
            'low' => [
                'Continuous improvement program',
                'Annual comprehensive assessments',
                'Maintain security posture'
            ]
        ];
        
        return $nextSteps[$riskLevel] ?? $nextSteps['medium'];
    }
    
    private function getErrorResponse($assessmentType, $organizationData, $errorMessage) {
        return [
            'executive_summary' => [
                'organization_name' => $organizationData['name'],
                'assessment_date' => date('Y-m-d'),
                'status' => 'failed',
                'error' => $errorMessage
            ],
            'error' => $errorMessage,
            'assessment_id' => $this->assessmentId ?? 'unknown'
        ];
    }
}
?>