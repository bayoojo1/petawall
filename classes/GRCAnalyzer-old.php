<?php
require_once __DIR__ . '/ollama-search.php';

class GRCAnalyzer {
    private $ollama;
    private $assessmentData;
    
    public function __construct() {
        $this->ollama = new OllamaSearch(GRC_ANALYSIS_MODEL);
        $this->assessmentData = [];
    }
    
    public function performAssessment($assessmentType, $organizationData, $selectedDomains = [], $selectedFrameworks = []) {
        try {
            // Updated validation - less restrictive for comprehensive assessments
            $this->validateAssessmentData($assessmentType, $organizationData, $selectedDomains, $selectedFrameworks);
            
            // Set time limit for this assessment
            set_time_limit(300); // 5 minutes max
            
            // Perform GRC assessment based on type
            switch ($assessmentType) {
                case 'comprehensive':
                    $result = $this->performComprehensiveAssessment($organizationData, $selectedDomains, $selectedFrameworks);
                    break;
                case 'domain-specific':
                    $result = $this->performDomainSpecificAssessment($organizationData, $selectedDomains);
                    break;
                case 'compliance-framework':
                    $result = $this->performComplianceAssessment($organizationData, $selectedFrameworks);
                    break;
                case 'risk-assessment':
                    $result = $this->performRiskAssessment($organizationData);
                    break;
                case 'policy-review':
                    $result = $this->performPolicyReview($organizationData);
                    break;
                default:
                    throw new Exception('Invalid assessment type specified: ' . $assessmentType);
            }
            
            // Add assessment metadata
            $result['assessment_metadata'] = [
                'type' => $assessmentType,
                'timestamp' => date('Y-m-d H:i:s'),
                'duration_estimate' => $this->getDurationEstimate($assessmentType),
                'version' => '1.0'
            ];
            
            return $result;
            
        } catch (Exception $e) {
            error_log("GRC Assessment Failed: " . $e->getMessage());
            
            // Return graceful error instead of throwing
            return $this->getErrorResponse($assessmentType, $organizationData, $e->getMessage());
        }
    }
    
    private function validateAssessmentData($assessmentType, &$organizationData, &$domains, &$frameworks) {
    // Configuration for limits (could be moved to config.php)
        $limits = [
            'max_domains' => 4,
            'max_frameworks' => 4,
            'default_domains' => ['security_governance', 'asset_security'],
            'default_frameworks' => ['nist_csf', 'iso27001']
        ];
        
        // Basic organization validation
        if (empty($organizationData['name']) || empty($organizationData['industry'])) {
            throw new Exception('Organization name and industry are required');
        }
        
        // Set default scope if empty
        if (empty($organizationData['scope'])) {
            $organizationData['scope'] = "Comprehensive {$assessmentType} assessment for {$organizationData['industry']} organization";
        }
        
        // Assessment-specific validation and defaults
        switch ($assessmentType) {
            case 'domain-specific':
                if (empty($domains)) {
                    throw new Exception('At least one CISSP domain must be selected for domain-specific assessment');
                }
                // Apply limits
                if (count($domains) > $limits['max_domains']) {
                    $domains = array_slice($domains, 0, $limits['max_domains']);
                    error_log("Domain-specific assessment limited to {$limits['max_domains']} domains for performance");
                }
                break;
                
            case 'compliance-framework':
                if (empty($frameworks)) {
                    throw new Exception('At least one compliance framework must be selected for framework assessment');
                }
                // Apply limits
                if (count($frameworks) > $limits['max_frameworks']) {
                    $frameworks = array_slice($frameworks, 0, $limits['max_frameworks']);
                    error_log("Framework assessment limited to {$limits['max_frameworks']} frameworks for performance");
                }
                break;
                
            case 'comprehensive':
                // Set defaults if empty
                if (empty($domains)) {
                    $domains = $limits['default_domains'];
                    error_log("Using default domains for comprehensive assessment");
                }
                if (empty($frameworks)) {
                    $frameworks = $limits['default_frameworks'];
                    error_log("Using default frameworks for comprehensive assessment");
                }
                
                // Apply limits
                if (count($domains) > $limits['max_domains']) {
                    $domains = array_slice($domains, 0, $limits['max_domains']);
                    error_log("Comprehensive assessment domains limited to {$limits['max_domains']} for performance");
                }
                if (count($frameworks) > $limits['max_frameworks']) {
                    $frameworks = array_slice($frameworks, 0, $limits['max_frameworks']);
                    error_log("Comprehensive assessment frameworks limited to {$limits['max_frameworks']} for performance");
                }
                break;
                
            case 'risk-assessment':
            case 'policy-review':
                // These types don't use domains/frameworks, so no validation needed
                break;
                
            default:
                throw new Exception('Invalid assessment type: ' . $assessmentType);
        }
        
        // Final validation - ensure we have something to assess
        if (in_array($assessmentType, ['domain-specific', 'compliance-framework', 'comprehensive'])) {
            if (empty($domains) && empty($frameworks)) {
                throw new Exception('Assessment requires at least one domain or framework to assess');
            }
        }
        
        error_log("Assessment validated: {$assessmentType}, " . 
                count($domains) . " domains, " . 
                count($frameworks) . " frameworks");
        
        return true;
    }

    private function getErrorResponse($assessmentType, $organizationData, $errorMessage) {
        $baseResponse = [
            'executive_summary' => [
                'organization_name' => $organizationData['name'],
                'assessment_date' => date('Y-m-d'),
                'status' => 'failed',
                'error' => $errorMessage
            ],
            'assessment_metadata' => [
                'type' => $assessmentType,
                'timestamp' => date('Y-m-d H:i:s'),
                'status' => 'error'
            ]
        ];
        
        // Add type-specific structure
        switch ($assessmentType) {
            case 'comprehensive':
                $baseResponse['cissp_domains'] = [];
                $baseResponse['compliance_frameworks'] = [];
                $baseResponse['risk_assessment'] = ['error' => $errorMessage];
                $baseResponse['gap_analysis'] = ['error' => $errorMessage];
                break;
                
            case 'domain-specific':
                $baseResponse['domain_assessments'] = [];
                break;
                
            case 'compliance-framework':
                $baseResponse['framework_assessments'] = [];
                break;
        }
        
        return $baseResponse;
    }

    private function getDurationEstimate($assessmentType) {
        $durations = [
            'comprehensive' => '5-10 minutes',
            'domain-specific' => '2-5 minutes', 
            'compliance-framework' => '2-5 minutes',
            'risk-assessment' => '1-3 minutes',
            'policy-review' => '1-3 minutes'
        ];
        
        return $durations[$assessmentType] ?? 'Unknown';
    }
        
    private function performComprehensiveAssessment($organizationData, $domains, $frameworks) {
        $startTime = microtime(true);
        
        // If no domains specified, assess all 8 CISSP domains
        $domainsToAssess = empty($domains) ? $this->getAllCISSPDomains() : $domains;
        $frameworksToAssess = empty($frameworks) ? $this->getAllComplianceFrameworks() : $frameworks;
        
        $assessmentResults = [
            'executive_summary' => $this->generateExecutiveSummary($organizationData),
            'cissp_domains' => $this->assessCISSPDomains($organizationData, $domainsToAssess),
            'compliance_frameworks' => $this->assessComplianceFrameworks($organizationData, $frameworksToAssess),
            'risk_assessment' => $this->performDetailedRiskAssessment($organizationData),
            'gap_analysis' => $this->performGapAnalysis($organizationData),
            'remediation_plan' => $this->generateRemediationPlan($organizationData)
        ];
        
        // Calculate overall metrics
        $assessmentResults['metrics'] = $this->calculateAssessmentMetrics($assessmentResults);
        $assessmentResults['assessment_info'] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'duration' => round(microtime(true) - $startTime, 2) . ' seconds',
            'scope' => count($domainsToAssess) . ' domains, ' . count($frameworksToAssess) . ' frameworks',
            'assessment_id' => uniqid('GRC_', true)
        ];
        
        return $assessmentResults;
    }
    
    private function getAllCISSPDomains() {
        return [
            'security_governance', 'asset_security', 'security_architecture',
            'communications', 'identity_access', 'security_assessment',
            'security_operations', 'software_security'
        ];
    }
    
    private function getAllComplianceFrameworks() {
        return [
            'nist_csf', 'iso27001', 'soc2', 'pcidss', 
            'hipaa', 'gdpr', 'cis_controls', 'cmmc'
        ];
    }
    
    private function assessCISSPDomains($organizationData, $domains) {
        $domainResults = [];
        
        foreach ($domains as $domain) {
            $domainResults[$domain] = $this->assessSingleDomain($domain, $organizationData);
        }
        
        return $domainResults;
    }
    
    public function assessSingleDomain($domain, $organizationData) {
        $domainDefinitions = $this->getCISSPDomainDefinitions();
        $domainInfo = $domainDefinitions[$domain] ?? [];
        
        $prompt = $this->buildDomainAssessmentPrompt($domain, $domainInfo, $organizationData);
        
        $aiResponse = $this->ollama->analyzeForTool('grc_domain', $domain, [
            'organization_data' => $organizationData,
            'domain_info' => $domainInfo,
            'assessment_type' => 'cissp_domain'
        ]);
        
        return $this->formatDomainResponse($aiResponse, $domain, $domainInfo);
    }
    
    private function getCISSPDomainDefinitions() {
        return [
            'security_governance' => [
                'name' => 'Security & Risk Management',
                'description' => 'Understand and apply concepts of confidentiality, integrity, and availability. Evaluate and apply security governance principles. Determine compliance requirements.',
                'key_areas' => [
                    'Security Governance Principles',
                    'Compliance Requirements',
                    'Legal and Regulatory Issues',
                    'Professional Ethics',
                    'Security Policies and Procedures',
                    'Risk Management Concepts'
                ]
            ],
            'asset_security' => [
                'name' => 'Asset Security',
                'description' => 'Identify and classify information and assets. Determine and maintain ownership. Protect privacy. Ensure appropriate retention. Determine data security controls.',
                'key_areas' => [
                    'Information and Asset Classification',
                    'Ownership and Responsibilities',
                    'Privacy Protection',
                    'Data Retention and Destruction',
                    'Data Security Controls'
                ]
            ],
            'security_architecture' => [
                'name' => 'Security Architecture & Engineering',
                'description' => 'Implement and manage engineering processes using secure design principles. Understand fundamental concepts of security models. Select controls based on systems security requirements.',
                'key_areas' => [
                    'Engineering Processes Using Secure Design Principles',
                    'Security Models Fundamental Concepts',
                    'Security Capabilities of Information Systems',
                    'Cryptography and PKI',
                    'Physical Security Design Principles'
                ]
            ],
            'communications' => [
                'name' => 'Communications & Network Security',
                'description' => 'Apply secure design principles to network architecture. Secure network components. Implement secure communication channels according to design.',
                'key_areas' => [
                    'Secure Network Architecture Design',
                    'Secure Network Components',
                    'Secure Communication Channels',
                    'Network Attacks and Countermeasures'
                ]
            ],
            'identity_access' => [
                'name' => 'Identity & Access Management',
                'description' => 'Control physical and logical access to assets. Manage identification and authentication of people and devices. Integrate identity as a service. Implement authorization mechanisms.',
                'key_areas' => [
                    'Physical and Logical Access to Assets',
                    'Identification and Authentication',
                    'Identity as a Service',
                    'Authorization Mechanisms',
                    'Identity and Access Provisioning Lifecycle'
                ]
            ],
            'security_assessment' => [
                'name' => 'Security Assessment & Testing',
                'description' => 'Design and validate assessment and test strategies. Conduct security control testing. Collect security process data. Analyze test output and generate report.',
                'key_areas' => [
                    'Assessment and Test Strategies',
                    'Security Control Testing',
                    'Security Process Data Collection',
                    'Test Output Analysis and Reporting',
                    'Internal and Third-party Audits'
                ]
            ],
            'security_operations' => [
                'name' => 'Security Operations',
                'description' => 'Understand and support investigations. Conduct logging and monitoring activities. Secure provision of resources. Apply foundational security operations concepts.',
                'key_areas' => [
                    'Investigations Support',
                    'Logging and Monitoring Activities',
                    'Resource Provision Security',
                    'Foundational Security Operations Concepts',
                    'Incident Management and Response'
                ]
            ],
            'software_security' => [
                'name' => 'Software Development Security',
                'description' => 'Understand and apply security in the software development lifecycle. Enforce security controls in development environments. Assess software security effectiveness.',
                'key_areas' => [
                    'Security in Software Development Lifecycle',
                    'Development Environment Security Controls',
                    'Software Security Effectiveness Assessment',
                    'Secure Coding Guidelines and Standards'
                ]
            ]
        ];
    }
    
    private function buildDomainAssessmentPrompt($domain, $domainInfo, $organizationData) {
        return "Conduct a comprehensive security assessment for CISSP Domain: {$domainInfo['name']}
        
        Organization Context:
        - Name: {$organizationData['name']}
        - Industry: {$organizationData['industry']}
        - Size: {$organizationData['size']}
        - Scope: {$organizationData['scope']}
        
        Domain Focus Areas: " . implode(', ', $domainInfo['key_areas']) . "
        
        Provide detailed analysis covering:
        1. Current state assessment
        2. Compliance level with domain requirements
        3. Identified gaps and vulnerabilities
        4. Risk assessment specific to this domain
        5. Industry-specific considerations
        6. Recommendations for improvement
        
        Format response as structured JSON.";
    }
    
    private function formatDomainResponse($aiResponse, $domain, $domainInfo) {
        // Ensure proper structure for domain assessment results
        if (!isset($aiResponse['assessment'])) {
            $aiResponse = [
                'assessment' => [
                    'compliance_score' => 0,
                    'maturity_level' => 'initial',
                    'key_findings' => ['Assessment data not available'],
                    'risks' => ['Unable to assess risks'],
                    'recommendations' => ['Review assessment methodology']
                ]
            ];
        }
        
        return [
            'domain_name' => $domainInfo['name'],
            'domain_description' => $domainInfo['description'],
            'assessment_results' => $aiResponse['assessment'],
            'key_areas_assessed' => $domainInfo['key_areas'],
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    private function assessComplianceFrameworks($organizationData, $frameworks) {
        $frameworkResults = [];
        
        foreach ($frameworks as $framework) {
            $frameworkResults[$framework] = $this->assessSingleFramework($framework, $organizationData);
        }
        
        return $frameworkResults;
    }
    
    private function assessSingleFramework($framework, $organizationData) {
        $frameworkDefinitions = $this->getComplianceFrameworkDefinitions();
        $frameworkInfo = $frameworkDefinitions[$framework] ?? [];
        
        $prompt = $this->buildFrameworkAssessmentPrompt($framework, $frameworkInfo, $organizationData);
        
        $aiResponse = $this->ollama->analyzeForTool('grc_compliance', $framework, [
            'organization_data' => $organizationData,
            'framework_info' => $frameworkInfo,
            'assessment_type' => 'compliance_framework'
        ]);
        
        return $this->formatFrameworkResponse($aiResponse, $framework, $frameworkInfo);
    }
    
    private function getComplianceFrameworkDefinitions() {
        return [
            'nist_csf' => [
                'name' => 'NIST Cybersecurity Framework',
                'description' => 'Voluntary framework consisting of standards, guidelines, and best practices to manage cybersecurity-related risk.',
                'core_functions' => ['Identify', 'Protect', 'Detect', 'Respond', 'Recover'],
                'applicable_industries' => ['all'],
                'compliance_levels' => ['Tier 1: Partial', 'Tier 2: Risk Informed', 'Tier 3: Repeatable', 'Tier 4: Adaptive']
            ],
            'iso27001' => [
                'name' => 'ISO 27001',
                'description' => 'International standard for information security management systems (ISMS).',
                'core_functions' => ['Context Establishment', 'Leadership', 'Planning', 'Support', 'Operation', 'Performance Evaluation', 'Improvement'],
                'applicable_industries' => ['all'],
                'certification_required' => true
            ],
            'soc2' => [
                'name' => 'SOC 2',
                'description' => 'Service Organization Control reports focusing on security, availability, processing integrity, confidentiality, and privacy.',
                'trust_service_criteria' => ['Security', 'Availability', 'Processing Integrity', 'Confidentiality', 'Privacy'],
                'applicable_industries' => ['technology', 'cloud_services', 'saas'],
                'report_types' => ['Type I', 'Type II']
            ],
            'pcidss' => [
                'name' => 'PCI DSS',
                'description' => 'Payment Card Industry Data Security Standard for organizations handling credit card transactions.',
                'requirements' => 12,
                'applicable_industries' => ['retail', 'finance', 'ecommerce'],
                'compliance_levels' => ['Level 1', 'Level 2', 'Level 3', 'Level 4']
            ],
            'hipaa' => [
                'name' => 'HIPAA',
                'description' => 'Health Insurance Portability and Accountability Act for healthcare organizations handling protected health information.',
                'rules' => ['Privacy Rule', 'Security Rule', 'Breach Notification Rule'],
                'applicable_industries' => ['healthcare', 'insurance'],
                'penalties' => ['Tier 1', 'Tier 2', 'Tier 3', 'Tier 4']
            ],
            'gdpr' => [
                'name' => 'GDPR',
                'description' => 'General Data Protection Regulation for organizations handling EU citizen data.',
                'principles' => ['Lawfulness', 'Fairness', 'Transparency', 'Purpose Limitation', 'Data Minimization', 'Accuracy', 'Storage Limitation', 'Integrity and Confidentiality', 'Accountability'],
                'applicable_industries' => ['all'],
                'territorial_scope' => 'EU citizens data'
            ],
            'cis_controls' => [
                'name' => 'CIS Critical Security Controls',
                'description' => 'Prioritized set of actions to protect organizations and data from known cyber attack vectors.',
                'controls_count' => 18,
                'implementation_groups' => ['IG1', 'IG2', 'IG3'],
                'applicable_industries' => ['all']
            ],
            'cmmc' => [
                'name' => 'CMMC 2.0',
                'description' => 'Cybersecurity Maturity Model Certification for defense industrial base organizations.',
                'maturity_levels' => ['Level 1', 'Level 2', 'Level 3'],
                'applicable_industries' => ['defense', 'government_contracting'],
                'certification_required' => true
            ]
        ];
    }
    
    private function buildFrameworkAssessmentPrompt($framework, $frameworkInfo, $organizationData) {
        return "Conduct compliance assessment for framework: {$frameworkInfo['name']}
        
        Organization Context:
        - Name: {$organizationData['name']}
        - Industry: {$organizationData['industry']}
        - Size: {$organizationData['size']}
        
        Framework Details: {$frameworkInfo['description']}
        
        Assess compliance level and provide:
        1. Current compliance status
        2. Gap analysis against framework requirements
        3. Industry-specific compliance considerations
        4. Implementation recommendations
        5. Estimated effort for full compliance
        
        Format response as structured JSON.";
    }
    
    private function formatFrameworkResponse($aiResponse, $framework, $frameworkInfo) {
        if (!isset($aiResponse['compliance_assessment'])) {
            $aiResponse = [
                'compliance_assessment' => [
                    'compliance_level' => 'not_assessed',
                    'gap_analysis' => ['Framework assessment not available'],
                    'recommendations' => ['Review assessment methodology'],
                    'estimated_effort' => 'unknown'
                ]
            ];
        }
        
        return [
            'framework_name' => $frameworkInfo['name'],
            'framework_description' => $frameworkInfo['description'],
            'compliance_results' => $aiResponse['compliance_assessment'],
            'applicable_requirements' => $frameworkInfo['core_functions'] ?? $frameworkInfo['trust_service_criteria'] ?? [],
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    private function performDetailedRiskAssessment($organizationData) {
        $riskCategories = [
            'strategic_risks',
            'operational_risks', 
            'financial_risks',
            'compliance_risks',
            'reputational_risks'
        ];
        
        $riskAssessment = [];
        
        foreach ($riskCategories as $category) {
            $riskAssessment[$category] = $this->assessRiskCategory($category, $organizationData);
        }
        
        // Calculate overall risk score
        $riskAssessment['overall_risk'] = $this->calculateOverallRisk($riskAssessment);
        $riskAssessment['risk_matrix'] = $this->generateRiskMatrix($riskAssessment);
        
        return $riskAssessment;
    }
    
    private function assessRiskCategory($category, $organizationData) {
        $industry = $organizationData['industry'];
        $industrySpecificRisks = [
            'software_development' => [
                'strategic_risks' => ['Open source vulnerabilities', 'API security breaches', 'Cloud misconfigurations'],
                'operational_risks' => ['Code vulnerabilities', 'CI/CD pipeline attacks', 'Third-party library risks']
            ],
            'healthcare' => [
                'strategic_risks' => ['HIPAA violations', 'Patient data breaches', 'Medical device compromises'],
                'operational_risks' => ['Ransomware attacks', 'Medical record tampering', 'Phishing targeting staff']
            ],
            'finance' => [
                'strategic_risks' => ['Regulatory fines', 'Market manipulation', 'Systemic risk'],
                'operational_risks' => ['Transaction fraud', 'ATM skimming', 'SWIFT network attacks']
            ],
            'energy' => [
                'strategic_risks' => ['Grid disruption', 'Critical infrastructure attacks', 'Supply chain compromise'],
                'operational_risks' => ['SCADA system attacks', 'Physical security breaches', 'Operational technology risks']
            ]
            // Add more industry-specific risks...
        ];
        $industryRisks = $industrySpecificRisks[$industry][$category] ?? [];
        
        $prompt = "Assess {$category} for organization: {$organizationData['name']} in {$industry} industry.
        Consider industry-specific risks: " . implode(', ', $industryRisks);
        
        
        $aiResponse = $this->ollama->analyzeForTool('grc_risk', $category, [
            'organization_data' => $organizationData,
            'risk_category' => $category,
            'category_definition' => $categoryDefinitions[$category]
        ]);
        
        return $aiResponse['risk_assessment'] ?? [
            'risk_level' => 'medium',
            'identified_risks' => ['Risk assessment not available'],
            'impact' => 'unknown',
            'likelihood' => 'unknown'
        ];
    }
    
    private function calculateOverallRisk($riskAssessment) {
        $riskScores = [
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'critical' => 4
        ];
        
        $totalScore = 0;
        $categoryCount = 0;
        
        foreach ($riskAssessment as $category => $assessment) {
            if ($category !== 'overall_risk' && $category !== 'risk_matrix') {
                $riskLevel = $assessment['risk_level'] ?? 'medium';
                $totalScore += $riskScores[$riskLevel] ?? 2;
                $categoryCount++;
            }
        }
        
        $averageScore = $categoryCount > 0 ? $totalScore / $categoryCount : 2;
        
        if ($averageScore >= 3.5) return 'critical';
        if ($averageScore >= 2.5) return 'high';
        if ($averageScore >= 1.5) return 'medium';
        return 'low';
    }
    
    private function generateRiskMatrix($riskAssessment) {
        return [
            'high_impact_high_likelihood' => $this->countRisksByMatrix($riskAssessment, 'high', 'high'),
            'high_impact_low_likelihood' => $this->countRisksByMatrix($riskAssessment, 'high', 'low'),
            'low_impact_high_likelihood' => $this->countRisksByMatrix($riskAssessment, 'low', 'high'),
            'low_impact_low_likelihood' => $this->countRisksByMatrix($riskAssessment, 'low', 'low')
        ];
    }
    
    private function countRisksByMatrix($riskAssessment, $impact, $likelihood) {
        $count = 0;
        foreach ($riskAssessment as $category => $assessment) {
            if ($category !== 'overall_risk' && $category !== 'risk_matrix') {
                if (($assessment['impact'] ?? 'medium') === $impact && 
                    ($assessment['likelihood'] ?? 'medium') === $likelihood) {
                    $count++;
                }
            }
        }
        return $count;
    }
    
    private function performGapAnalysis($organizationData) {
        $prompt = "Perform comprehensive security gap analysis for {$organizationData['name']} in {$organizationData['industry']} industry.
        Identify gaps in people, processes, and technology across security domains.";
        
        $aiResponse = $this->ollama->analyzeForTool('grc_gap', $organizationData['name'], [
            'organization_data' => $organizationData,
            'analysis_type' => 'gap_analysis'
        ]);
        
        return $aiResponse['gap_analysis'] ?? [
            'critical_gaps' => ['Gap analysis not available'],
            'high_priority_gaps' => [],
            'medium_priority_gaps' => [],
            'low_priority_gaps' => []
        ];
    }
    
    private function generateRemediationPlan($organizationData) {
        $prompt = "Create prioritized remediation action plan for {$organizationData['name']} addressing identified security gaps and compliance issues.";
        
        $aiResponse = $this->ollama->analyzeForTool('grc_remediation', $organizationData['name'], [
            'organization_data' => $organizationData,
            'plan_type' => 'remediation_actions'
        ]);
        
        return $aiResponse['remediation_plan'] ?? [
            'immediate_actions' => ['Develop comprehensive remediation plan'],
            'short_term_actions' => [],
            'long_term_actions' => [],
            'resource_requirements' => 'To be determined'
        ];
    }
    
    private function generateExecutiveSummary($organizationData) {
        return [
            'organization_name' => $organizationData['name'],
            'assessment_date' => date('Y-m-d'),
            'industry_context' => $organizationData['industry'],
            'key_findings_summary' => 'Comprehensive GRC assessment completed',
            'recommendation_summary' => 'Implement prioritized action plan'
        ];
    }
    
    private function calculateAssessmentMetrics($assessmentResults) {
        $domainScores = [];
        $frameworkScores = [];
        
        // Calculate domain compliance scores
        foreach ($assessmentResults['cissp_domains'] as $domain => $results) {
            $score = $results['assessment_results']['compliance_score'] ?? 50;
            $domainScores[] = $score;
        }
        
        // Calculate framework compliance scores
        foreach ($assessmentResults['compliance_frameworks'] as $framework => $results) {
            $complianceLevel = $results['compliance_results']['compliance_level'] ?? 'partial';
            $score = $this->convertComplianceLevelToScore($complianceLevel);
            $frameworkScores[] = $score;
        }
        
        $avgDomainScore = !empty($domainScores) ? array_sum($domainScores) / count($domainScores) : 0;
        $avgFrameworkScore = !empty($frameworkScores) ? array_sum($frameworkScores) / count($frameworkScores) : 0;
        
        return [
            'overall_compliance_score' => round(($avgDomainScore + $avgFrameworkScore) / 2, 1),
            'domain_compliance_avg' => round($avgDomainScore, 1),
            'framework_compliance_avg' => round($avgFrameworkScore, 1),
            'risk_level' => $assessmentResults['risk_assessment']['overall_risk'] ?? 'medium',
            'critical_findings_count' => count($assessmentResults['gap_analysis']['critical_gaps'] ?? []),
            'compliance_rate' => round($avgFrameworkScore, 1) . '%'
        ];
    }
    
    private function convertComplianceLevelToScore($complianceLevel) {
        $scores = [
            'fully_compliant' => 100,
            'mostly_compliant' => 80,
            'partially_compliant' => 60,
            'minimally_compliant' => 40,
            'non_compliant' => 20,
            'not_assessed' => 0
        ];
        
        return $scores[$complianceLevel] ?? 50;
    }
    
    /**
     * Domain-specific assessment
     */
    public function performDomainSpecificAssessment($organizationData, $domains) {
        return [
            'assessment_type' => 'domain_specific',
            'selected_domains' => $domains,
            'domain_assessments' => $this->assessCISSPDomains($organizationData, $domains),
            'domain_metrics' => $this->calculateDomainMetrics($domains),
            'cross_domain_recommendations' => $this->generateCrossDomainRecommendations($domains)
        ];
    }
    
    /**
     * Compliance framework assessment
     */
    public function performComplianceAssessment($organizationData, $frameworks) {
        return [
            'assessment_type' => 'compliance_framework',
            'selected_frameworks' => $frameworks,
            'framework_assessments' => $this->assessComplianceFrameworks($organizationData, $frameworks),
            'compliance_metrics' => $this->calculateComplianceMetrics($frameworks),
            'regulatory_landscape' => $this->analyzeRegulatoryLandscape($organizationData['industry'])
        ];
    }
    
    /**
     * Risk-focused assessment
     */
    public function performRiskAssessment($organizationData) {
        return [
            'assessment_type' => 'risk_assessment',
            'risk_assessment' => $this->performDetailedRiskAssessment($organizationData),
            'risk_treatment_plan' => $this->generateRiskTreatmentPlan(),
            'risk_monitoring_framework' => $this->createRiskMonitoringFramework()
        ];
    }
    
    /**
     * Policy review assessment
     */
    public function performPolicyReview($organizationData) {
        return [
            'assessment_type' => 'policy_review',
            'policy_framework_assessment' => $this->assessPolicyFramework($organizationData),
            'policy_gap_analysis' => $this->analyzePolicyGaps($organizationData),
            'policy_development_roadmap' => $this->createPolicyDevelopmentRoadmap($organizationData)
        ];
    }
    
    // Additional helper methods for specialized assessments
    private function calculateDomainMetrics($domains) {
        return [
            'domains_assessed' => count($domains),
            'coverage_percentage' => round((count($domains) / 8) * 100, 1),
            'focus_areas' => $this->getDomainFocusAreas($domains)
        ];
    }
    
    private function calculateComplianceMetrics($frameworks) {
        return [
            'frameworks_assessed' => count($frameworks),
            'industry_relevance_score' => $this->calculateIndustryRelevance($frameworks),
            'compliance_maturity' => $this->assessComplianceMaturity($frameworks)
        ];
    }
    
    private function getDomainFocusAreas($domains) {
        $domainDefinitions = $this->getCISSPDomainDefinitions();
        $focusAreas = [];
        
        foreach ($domains as $domain) {
            if (isset($domainDefinitions[$domain]['key_areas'])) {
                $focusAreas = array_merge($focusAreas, $domainDefinitions[$domain]['key_areas']);
            }
        }
        
        return array_slice(array_unique($focusAreas), 0, 10); // Return top 10 focus areas
    }
    
    private function calculateIndustryRelevance($frameworks) {
        // Simple relevance calculation - in practice, this would be more sophisticated
        return min(100, count($frameworks) * 15);
    }
    
    private function assessComplianceMaturity($frameworks) {
        $maturityLevels = ['initial', 'developing', 'defined', 'managed', 'optimizing'];
        return $maturityLevels[min(count($frameworks) - 1, 4)] ?? 'initial';
    }
    
    private function generateCrossDomainRecommendations($domains) {
        return [
            'integrated_controls' => 'Implement controls that address multiple domains simultaneously',
            'governance_framework' => 'Establish unified governance framework across assessed domains',
            'risk_aggregation' => 'Aggregate risks across domains for comprehensive risk management'
        ];
    }
    
    private function analyzeRegulatoryLandscape($industry) {
        $regulatoryMap = [
            // Technology & Digital
            'software_development' => ['GDPR', 'CCPA', 'SOX', 'SOC 2', 'ISO 27001'],
            'cloud_services' => ['SOC 2', 'ISO 27001', 'FedRAMP', 'CSA STAR', 'GDPR'],
            'it_services' => ['ISO 27001', 'SOC 2', 'NIST CSF', 'GDPR'],
            'cybersecurity' => ['NIST CSF', 'ISO 27001', 'CIS Controls', 'GDPR'],
            'ecommerce' => ['PCI DSS', 'GDPR', 'CCPA', 'SOC 2'],
            'fintech' => ['PCI DSS', 'GLBA', 'SOX', 'NYDFS', 'GDPR'],
            'healthtech' => ['HIPAA', 'HITECH', 'GDPR', 'FDA Regulations'],
            'edtech' => ['FERPA', 'COPPA', 'GDPR', 'SOC 2'],
            'gaming' => ['GDPR', 'CCPA', 'Age Verification Laws', 'SOC 2'],
            'telecom' => ['FCC Regulations', 'CPNI', 'GDPR', 'CALEA'],
            'semiconductor' => ['ITAR', 'EAR', 'NIST SP 800-171', 'CMMC'],
            
            // Finance & Insurance
            'banking' => ['GLBA', 'SOX', 'FFIEC', 'BASEL', 'PCI DSS'],
            'investment' => ['SEC Regulations', 'FINRA', 'SOX', 'MiFID II'],
            'insurance' => ['NAIC', 'SOX', 'State Insurance Regulations'],
            'payment_processing' => ['PCI DSS', 'GLBA', 'SOX', 'State Money Transmitter Laws'],
            'cryptocurrency' => ['FinCEN', 'SEC Regulations', 'State Crypto Laws', 'Travel Rule'],
            'stock_exchange' => ['SEC Regulations', 'Regulation SCI', 'SOX', 'MiFID II'],
            
            // Healthcare
            'hospitals' => ['HIPAA', 'HITECH', 'HITRUST', 'PCI DSS', 'State Laws'],
            'pharmaceutical' => ['HIPAA', 'FDA Regulations', 'GDPR', 'GxP'],
            'medical_devices' => ['FDA Regulations', 'HIPAA', 'ISO 13485', 'GDPR'],
            'health_insurance' => ['HIPAA', 'HITECH', 'State Insurance Laws', 'ACA'],
            'telemedicine' => ['HIPAA', 'HITECH', 'State Telemedicine Laws', 'GDPR'],
            'research_labs' => ['HIPAA', 'FDA Regulations', 'CLIA', 'GDPR'],
            
            // Government & Public Sector
            'federal_government' => ['FISMA', 'NIST Standards', 'FedRAMP', 'CMMC'],
            'state_government' => ['State Security Laws', 'NIST CSF', 'IRS 1075'],
            'defense' => ['CMMC', 'NIST SP 800-171', 'ITAR', 'DFARS'],
            'intelligence' => ['ICD 503', 'CNSSI 1253', 'NIST SP 800-53'],
            'law_enforcement' => ['CJIS', 'State Security Laws', 'NIST CSF'],
            'public_health' => ['HIPAA', 'HITECH', 'CDC Guidelines', 'State Laws'],
            'education_public' => ['FERPA', 'CIPA', 'State Student Privacy Laws'],
            
            // Critical Infrastructure
            'energy' => ['NERC CIP', 'FERC', 'DOE Regulations', 'NIST CSF'],
            'oil_gas' => ['NERC CIP', 'TSA Security Directives', 'API Standards'],
            'electrical_grid' => ['NERC CIP', 'FERC', 'NIST IR 7628'],
            'water_systems' => ['AWIA', 'State Water Security Regulations'],
            'transportation' => ['TSA Security Directives', 'FAA Regulations', 'NIST CSF'],
            'aviation' => ['FAA Regulations', 'EASA', 'ICAO Standards'],
            'maritime' => ['USCG Regulations', 'IMO Standards', 'MTSA'],
            'rail' => ['TSA Security Directives', 'FRA Regulations'],
            
            // Manufacturing & Industrial
            'automotive' => ['ITAR', 'TISAX', 'ISO 27001', 'GDPR'],
            'aerospace_manufacturing' => ['ITAR', 'EAR', 'CMMC', 'NIST SP 800-171'],
            'electronics_manufacturing' => ['ITAR', 'EAR', 'ISO 27001', 'GDPR'],
            'industrial_equipment' => ['NIST CSF', 'ISO 27001', 'Sector-Specific Standards'],
            'chemicals' => ['CFATS', 'OSHA', 'EPA Regulations'],
            'food_manufacturing' => ['FDA Food Safety', 'FSMA', 'GDPR'],
            'pharma_manufacturing' => ['FDA Regulations', 'GMP', 'GDPR', 'HIPAA'],
            
            // Retail & Consumer
            'retail_brick_mortar' => ['PCI DSS', 'State Privacy Laws', 'SOC 2'],
            'hospitality' => ['PCI DSS', 'State Privacy Laws', 'GDPR'],
            'restaurants' => ['PCI DSS', 'State Privacy Laws'],
            'entertainment' => ['GDPR', 'CCPA', 'COPPA', 'SOC 2'],
            'travel' => ['PCI DSS', 'GDPR', 'State Privacy Laws'],
            'real_estate' => ['State Privacy Laws', 'GLBA', 'SOC 2'],
            
            // Professional Services
            'legal' => ['Attorney-Client Privilege', 'State Bar Rules', 'GDPR'],
            'consulting' => ['Client Confidentiality', 'ISO 27001', 'SOC 2'],
            'accounting' => ['SOX', 'PCAOB', 'Client Confidentiality', 'SOC 2'],
            'marketing' => ['GDPR', 'CCPA', 'CAN-SPAM', 'TCPA'],
            'research' => ['Data Protection Laws', 'Intellectual Property', 'Export Controls'],
            
            // Education & Non-Profit
            'higher_education' => ['FERPA', 'GLBA', 'HIPAA', 'PCI DSS'],
            'k12_education' => ['FERPA', 'CIPA', 'COPPA', 'State Laws'],
            'non_profit' => ['State Non-Profit Laws', 'Donor Privacy', 'GDPR'],
            'research_institutions' => ['FERPA', 'HIPAA', 'Export Controls', 'Data Protection'],
            
            // Other
            'agriculture' => ['State Agriculture Laws', 'Food Safety', 'GDPR'],
            'mining' => ['MSHA', 'State Mining Regulations', 'NIST CSF'],
            'construction' => ['State Security Laws', 'Client Confidentiality'],
            'logistics' => ['TSA Regulations', 'Customs Regulations', 'GDPR'],
            'startup' => ['GDPR', 'CCPA', 'SOC 2', 'Industry-Specific Regulations'],
            'other' => ['GDPR', 'CCPA', 'NIST CSF', 'Industry Best Practices']
        ];
        
        return $regulatoryMap[$industry] ?? ['GDPR', 'NIST CSF', 'Industry Best Practices'];
    }
    
    private function generateRiskTreatmentPlan() {
        return [
            'risk_avoidance' => ['Eliminate activities that pose unacceptable risks'],
            'risk_mitigation' => ['Implement controls to reduce risk likelihood or impact'],
            'risk_transfer' => ['Transfer risk through insurance or contracts'],
            'risk_acceptance' => ['Formally accept residual risks with management approval']
        ];
    }
    
    private function createRiskMonitoringFramework() {
        return [
            'key_risk_indicators' => ['Define and monitor KRIs for critical risks'],
            'risk_reporting' => ['Establish regular risk reporting to management'],
            'risk_review_cadence' => ['Quarterly risk reviews with annual deep dives'],
            'continuous_monitoring' => ['Implement automated risk monitoring where possible']
        ];
    }
    
    private function assessPolicyFramework($organizationData) {
        return [
            'policy_coverage' => 'Assess coverage of required security policies',
            'policy_effectiveness' => 'Evaluate policy implementation and enforcement',
            'policy_communication' => 'Review policy awareness and training',
            'policy_maintenance' => 'Assess policy review and update processes'
        ];
    }
    
    private function analyzePolicyGaps($organizationData) {
        return [
            'missing_policies' => ['Identify policies required but not implemented'],
            'outdated_policies' => ['Flag policies needing review and update'],
            'unenforced_policies' => ['Identify policies without enforcement mechanisms'],
            'inconsistent_policies' => ['Find conflicting or overlapping policies']
        ];
    }
    
    private function createPolicyDevelopmentRoadmap($organizationData) {
        return [
            'immediate_actions' => ['Address critical policy gaps within 30 days'],
            'short_term_goals' => ['Develop missing essential policies within 90 days'],
            'medium_term_goals' => ['Enhance policy framework within 6 months'],
            'long_term_goals' => ['Achieve policy maturity within 12 months']
        ];
    }
}
?>