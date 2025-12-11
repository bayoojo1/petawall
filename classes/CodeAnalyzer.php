<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/ollama-search.php';

class CodeAnalyzer {
    private $ollama;
    private $scanResults;
    private $fileCount;
    
    public function __construct() {
        $this->ollama = new OllamaSearch();
        $this->scanResults = [
            'security_issues' => [],
            'quality_issues' => [],
            'performance_issues' => [],
            'compliance_issues' => [],
            'summary' => [
                'total_files' => 0,
                'files_analyzed' => 0,
                'total_lines' => 0,
                'security_issues_count' => 0,
                'quality_issues_count' => 0,
                'performance_issues_count' => 0,
                'compliance_issues_count' => 0
            ],
            'languages_detected' => [],
            'scan_metrics' => []
        ];
        $this->fileCount = 0;
    }
    
    /**
     * Main analysis method
     */
    public function analyzeCode($sourcePath, $analysisType = 'comprehensive', $options = []) {
        $startTime = microtime(true);
        
        try {
            // Validate source path
            if (!$this->validateSourcePath($sourcePath)) {
                throw new Exception("Invalid source path: $sourcePath");
            }
            
            // Determine if it's a directory or single file
            if (is_dir($sourcePath)) {
                $files = $this->scanDirectory($sourcePath, $options['exclude_dirs'] ?? []);
            } else {
                $files = [$sourcePath];
            }
            
            $this->scanResults['summary']['total_files'] = count($files);
            
            // Analyze each file
            foreach ($files as $file) {
                if ($this->fileCount >= MAX_SCAN_FILES) {
                    $this->addScanMetric('warning', 'File limit reached', 'Stopped analysis after ' . MAX_SCAN_FILES . ' files');
                    break;
                }
                
                $this->analyzeFile($file, $analysisType, $options);
                $this->fileCount++;
            }
            
            // Perform AI-powered overall analysis
            if ($analysisType === 'comprehensive') {
                $this->performAIOverallAnalysis($sourcePath);
            }
            
            // Generate final summary
            $this->generateFinalSummary();
            
            $endTime = microtime(true);
            $this->scanResults['scan_metrics']['total_duration'] = round($endTime - $startTime, 2) . ' seconds';
            $this->scanResults['scan_metrics']['files_per_second'] = round($this->fileCount / ($endTime - $startTime), 2);
            
            return [
                'success' => true,
                'results' => $this->scanResults,
                'scan_id' => uniqid('scan_', true)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'results' => $this->scanResults
            ];
        }
    }
    
    /**
     * Validate source path
     */
    private function validateSourcePath($path) {
        if (!file_exists($path)) {
            return false;
        }
        
        // Check file size if it's a single file
        if (is_file($path) && filesize($path) > MAX_FILE_SIZE) {
            throw new Exception("File size exceeds maximum limit of " . (MAX_FILE_SIZE / 1024 / 1024) . "MB");
        }
        
        return true;
    }
    
    /**
     * Scan directory recursively
     */
    private function scanDirectory($directory, $excludeDirs = []) {
        $files = [];
        $excludePatterns = array_merge(['\.git', 'node_modules', 'vendor', '\.svn'], $excludeDirs);
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filePath = $file->getPathname();
                $relativePath = str_replace($directory, '', $filePath);
                
                // Check exclude patterns
                $exclude = false;
                foreach ($excludePatterns as $pattern) {
                    if (preg_match("/$pattern/", $relativePath)) {
                        $exclude = true;
                        break;
                    }
                }
                
                if (!$exclude && $this->isSupportedFile($filePath)) {
                    $files[] = $filePath;
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Check if file is supported
     */
    private function isSupportedFile($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // If no extension, try to detect by content
        if (empty($extension)) {
            return $this->detectFileByContent($filePath);
        }
        
        foreach (SUPPORTED_LANGUAGES as $lang => $config) {
            if (in_array($extension, $config['extensions'])) {
                return true;
            }
        }
        
        return false;
    }

    private function detectFileByContent($filePath) {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return false;
        }
        
        $content = file_get_contents($filePath);
        if (empty($content)) {
            return false;
        }
        
        // Common file signatures and patterns
        $patterns = [
            'php' => '/<\?php|<\?=/',
            'html' => '/<!DOCTYPE html|<html/i',
            'javascript' => '/function\s*\w*\s*\(|const\s+|let\s+|var\s+/',
            'python' => '/def\s+\w+\s*\(|import\s+|from\s+/',
            'java' => '/public\s+class|import\s+java/',
            'css' => '/@import|@media|\.\w+\s*\{/',
            'json' => '/^\s*[\{\[]/',
            'xml' => '/<\?xml|<\/?\w+>/'
        ];
        
        foreach ($patterns as $lang => $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Analyze individual file
     */
    private function analyzeFile($filePath, $analysisType, $options) {
        $fileContent = file_get_contents($filePath);
        $fileSize = strlen($fileContent);
        $lines = explode("\n", $fileContent);
        $lineCount = count($lines);
        
        $this->scanResults['summary']['total_lines'] += $lineCount;
        
        // Detect language
        $language = $this->detectLanguage($filePath);
        if (!in_array($language, $this->scanResults['languages_detected'])) {
            $this->scanResults['languages_detected'][] = $language;
        }
        
        $fileAnalysis = [
            'file_path' => $filePath,
            'language' => $language,
            'line_count' => $lineCount,
            'file_size' => $fileSize,
            'issues' => []
        ];
        
        // Security analysis
        $securityIssues = $this->analyzeSecurity($fileContent, $language, $filePath, $lines);
        if (!empty($securityIssues)) {
            $fileAnalysis['issues']['security'] = $securityIssues;
            $this->scanResults['summary']['security_issues_count'] += count($securityIssues);
        }
        
        // Code quality analysis
        $qualityIssues = $this->analyzeCodeQuality($fileContent, $language, $filePath, $lines);
        if (!empty($qualityIssues)) {
            $fileAnalysis['issues']['quality'] = $qualityIssues;
            $this->scanResults['summary']['quality_issues_count'] += count($qualityIssues);
        }
        
        // Performance analysis
        $performanceIssues = $this->analyzePerformance($fileContent, $language, $filePath, $lines);
        if (!empty($performanceIssues)) {
            $fileAnalysis['issues']['performance'] = $performanceIssues;
            $this->scanResults['summary']['performance_issues_count'] += count($performanceIssues);
        }
        
        // Compliance analysis
        $complianceIssues = $this->analyzeCompliance($fileContent, $language, $filePath, $lines, $options['compliance_standards'] ?? []);
        if (!empty($complianceIssues)) {
            $fileAnalysis['issues']['compliance'] = $complianceIssues;
            $this->scanResults['summary']['compliance_issues_count'] += count($complianceIssues);
        }
        
        // Add to results if issues found or if detailed scan requested
        if (!empty($fileAnalysis['issues']) || ($analysisType === 'detailed')) {
            $this->scanResults['files_analyzed'][] = $fileAnalysis;
            $this->scanResults['summary']['files_analyzed']++;
        }
    }
    
    /**
     * Detect programming language
     */
    private function detectLanguage($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        foreach (SUPPORTED_LANGUAGES as $lang => $config) {
            if (in_array($extension, $config['extensions'])) {
                return $lang;
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Security analysis
     */
    private function analyzeSecurity($content, $language, $filePath, $lines) {
        $issues = [];
        
        foreach (SECURITY_PATTERNS as $issueType => $patternConfig) {
            foreach ($patternConfig['patterns'] as $pattern) {
                if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                    foreach ($matches[0] as $match) {
                        $lineNumber = $this->getLineNumber($content, $match[1]);
                        $issues[] = [
                            'type' => $issueType,
                            'severity' => $patternConfig['severity'],
                            'description' => $patternConfig['description'],
                            'line_number' => $lineNumber,
                            'code_snippet' => $this->getCodeSnippet($lines, $lineNumber),
                            'match' => $match[0],
                            'pattern' => $pattern
                        ];
                    }
                }
            }
        }
        
        // Language-specific security checks
        $languageSpecificIssues = $this->performLanguageSpecificSecurityChecks($content, $language, $lines);
        $issues = array_merge($issues, $languageSpecificIssues);
        
        return $issues;
    }
    
    /**
     * Language-specific security checks
     */
    private function performLanguageSpecificSecurityChecks($content, $language, $lines) {
        $issues = [];
        
        switch ($language) {
            case 'php':
                $issues = array_merge($issues, $this->analyzePHPSecurity($content, $lines));
                break;
            case 'javascript':
                $issues = array_merge($issues, $this->analyzeJavaScriptSecurity($content, $lines));
                break;
            case 'python':
                $issues = array_merge($issues, $this->analyzePythonSecurity($content, $lines));
                break;
            case 'java':
                $issues = array_merge($issues, $this->analyzeJavaSecurity($content, $lines));
                break;
        }
        
        return $issues;
    }
    
    /**
     * PHP-specific security analysis
     */
    private function analyzePHPSecurity($content, $lines) {
        $issues = [];
        
        // Check for disabled security functions
        $dangerousFunctions = [
            'exec', 'system', 'passthru', 'shell_exec', 'proc_open', 'popen',
            'eval', 'assert', 'create_function'
        ];
        
        foreach ($dangerousFunctions as $func) {
            if (preg_match_all("/$func\s*\(/i", $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $lineNumber = $this->getLineNumber($content, $match[1]);
                    $issues[] = [
                        'type' => 'dangerous_function',
                        'severity' => 'high',
                        'description' => "Potentially dangerous PHP function: $func",
                        'line_number' => $lineNumber,
                        'code_snippet' => $this->getCodeSnippet($lines, $lineNumber),
                        'match' => $match[0]
                    ];
                }
            }
        }
        
        // Check for file inclusion vulnerabilities
        if (preg_match_all('/(include|require)(_once)?\s*\(\s*[\'\"].*\$_/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $lineNumber = $this->getLineNumber($content, $match[1]);
                $issues[] = [
                    'type' => 'file_inclusion',
                    'severity' => 'high',
                    'description' => 'Dynamic file inclusion with user input',
                    'line_number' => $lineNumber,
                    'code_snippet' => $this->getCodeSnippet($lines, $lineNumber),
                    'match' => $match[0]
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * JavaScript-specific security analysis
     */
    private function analyzeJavaScriptSecurity($content, $lines) {
        $issues = [];
        
        // Check for eval usage
        if (preg_match_all('/eval\s*\(/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $lineNumber = $this->getLineNumber($content, $match[1]);
                $issues[] = [
                    'type' => 'eval_usage',
                    'severity' => 'high',
                    'description' => 'eval() function usage - potential code injection',
                    'line_number' => $lineNumber,
                    'code_snippet' => $this->getCodeSnippet($lines, $lineNumber),
                    'match' => $match[0]
                ];
            }
        }
        
        // Check for innerHTML with user input
        if (preg_match_all('/innerHTML\s*=\s*[^;]*\$(?!\w)/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $lineNumber = $this->getLineNumber($content, $match[1]);
                $issues[] = [
                    'type' => 'dom_xss',
                    'severity' => 'high',
                    'description' => 'Potential DOM-based XSS via innerHTML',
                    'line_number' => $lineNumber,
                    'code_snippet' => $this->getCodeSnippet($lines, $lineNumber),
                    'match' => $match[0]
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * Python-specific security analysis
     */
    private function analyzePythonSecurity($content, $lines) {
        $issues = [];
        
        // Check for unsafe deserialization
        if (preg_match_all('/pickle\.loads/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $lineNumber = $this->getLineNumber($content, $match[1]);
                $issues[] = [
                    'type' => 'unsafe_deserialization',
                    'severity' => 'high',
                    'description' => 'Unsafe deserialization with pickle',
                    'line_number' => $lineNumber,
                    'code_snippet' => $this->getCodeSnippet($lines, $lineNumber),
                    'match' => $match[0]
                ];
            }
        }
        
        // Check for shell=True in subprocess
        if (preg_match_all('/subprocess\.(run|call|Popen).*shell\s*=\s*True/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $lineNumber = $this->getLineNumber($content, $match[1]);
                $issues[] = [
                    'type' => 'shell_injection',
                    'severity' => 'high',
                    'description' => 'Potential shell injection with shell=True',
                    'line_number' => $lineNumber,
                    'code_snippet' => $this->getCodeSnippet($lines, $lineNumber),
                    'match' => $match[0]
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * Java-specific security analysis
     */
    private function analyzeJavaSecurity($content, $lines) {
        $issues = [];
        
        // Check for unsafe reflection
        if (preg_match_all('/Class\.forName/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $lineNumber = $this->getLineNumber($content, $match[1]);
                $issues[] = [
                    'type' => 'unsafe_reflection',
                    'severity' => 'medium',
                    'description' => 'Dynamic class loading with Class.forName',
                    'line_number' => $lineNumber,
                    'code_snippet' => $this->getCodeSnippet($lines, $lineNumber),
                    'match' => $match[0]
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * Code quality analysis
     */
    private function analyzeCodeQuality($content, $language, $filePath, $lines) {
        $issues = [];
        
        // Check for long functions/methods
        $functionPatterns = $this->getFunctionPatterns($language);
        foreach ($functionPatterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $lineNumber = $this->getLineNumber($content, $match[1]);
                    $functionContent = $this->extractFunctionContent($content, $match[0], $language);
                    
                    if ($functionContent && $this->countFunctionLines($functionContent) > CODE_QUALITY_PATTERNS['long_function']['threshold']) {
                        $issues[] = [
                            'type' => 'long_function',
                            'severity' => 'low',
                            'description' => CODE_QUALITY_PATTERNS['long_function']['description'],
                            'line_number' => $lineNumber,
                            'code_snippet' => $this->getCodeSnippet($lines, $lineNumber),
                            'metric' => $this->countFunctionLines($functionContent) . ' lines'
                        ];
                    }
                }
            }
        }
        
        // Check for complex conditions
        $complexityIssues = $this->analyzeComplexity($content, $language, $lines);
        $issues = array_merge($issues, $complexityIssues);
        
        return $issues;
    }
    
    /**
     * Get function patterns for different languages
     */
    private function getFunctionPatterns($language) {
        $patterns = [
            'php' => [
                '/function\s+\w+\s*\([^)]*\)\s*\{/',
                '/public\s+function\s+\w+\s*\([^)]*\)\s*\{/',
                '/private\s+function\s+\w+\s*\([^)]*\)\s*\{/',
                '/protected\s+function\s+\w+\s*\([^)]*\)\s*\{/'
            ],
            'javascript' => [
                '/function\s+\w+\s*\([^)]*\)\s*\{/',
                '/const\s+\w+\s*=\s*\([^)]*\)\s*=>\s*\{/',
                '/let\s+\w+\s*=\s*\([^)]*\)\s*=>\s*\{/',
                '/\w+\s*\([^)]*\)\s*\{/'
            ],
            'python' => [
                '/def\s+\w+\s*\([^)]*\)\s*:/'
            ],
            'java' => [
                '/(public|private|protected)\s+\w+\s+\w+\s*\([^)]*\)\s*\{/'
            ],
            'csharp' => [
                '/(public|private|protected)\s+\w+\s+\w+\s*\([^)]*\)\s*\{/'
            ]
        ];
        
        return $patterns[$language] ?? [];
    }
    
    /**
     * Extract function content with improved logic
     */
    private function extractFunctionContent($content, $functionStart, $language) {
        $lines = explode("\n", $content);
        $inFunction = false;
        $braceCount = 0;
        $functionContent = '';
        $functionStartLine = '';
        
        // Find the function start
        foreach ($lines as $line) {
            if (strpos($line, $functionStart) !== false && !$inFunction) {
                $inFunction = true;
                $functionStartLine = $line;
                $braceCount += substr_count($line, '{');
                $braceCount -= substr_count($line, '}');
                $functionContent .= $line . "\n";
                continue;
            }
            
            if ($inFunction) {
                $functionContent .= $line . "\n";
                $braceCount += substr_count($line, '{');
                $braceCount -= substr_count($line, '}');
                
                if ($braceCount <= 0) {
                    break;
                }
            }
        }
        
        return $functionContent;
    }
    
    /**
     * Analyze code complexity
     */
    private function analyzeComplexity($content, $language, $lines) {
        $issues = [];
        
        // Check for deep nesting
        $nestingLevel = 0;
        $maxNesting = 0;
        $lineNumber = 1;
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            
            // Count opening braces/brackets for nesting
            $opening = substr_count($trimmedLine, '{') + substr_count($trimmedLine, '(') + substr_count($trimmedLine, '[');
            $closing = substr_count($trimmedLine, '}') + substr_count($trimmedLine, ')') + substr_count($trimmedLine, ']');
            
            $nestingLevel += ($opening - $closing);
            $maxNesting = max($maxNesting, $nestingLevel);
            
            if ($nestingLevel > CODE_QUALITY_PATTERNS['deep_nesting']['threshold']) {
                $issues[] = [
                    'type' => 'deep_nesting',
                    'severity' => 'low',
                    'description' => CODE_QUALITY_PATTERNS['deep_nesting']['description'],
                    'line_number' => $lineNumber,
                    'code_snippet' => $this->getCodeSnippet($lines, $lineNumber),
                    'metric' => 'Nesting level: ' . $nestingLevel
                ];
            }
            
            $lineNumber++;
        }
        
        return $issues;
    }
    
    /**
     * Performance analysis
     */
    private function analyzePerformance($content, $language, $filePath, $lines) {
        $issues = [];
        
        // Check for nested loops
        if (preg_match_all('/(for|while).*\{[^{}]*(for|while).*\{/s', $content)) {
            $issues[] = [
                'type' => 'nested_loops',
                'severity' => 'medium',
                'description' => 'Nested loops detected - potential performance issue',
                'line_number' => 1,
                'code_snippet' => 'Multiple nested loops found in file'
            ];
        }
        
        // Check for expensive operations in loops
        if (preg_match_all('/(for|while).*\{[^{}]*(query|exec|file_get_contents|curl)/is', $content)) {
            $issues[] = [
                'type' => 'expensive_operation_in_loop',
                'severity' => 'medium',
                'description' => 'Expensive operation inside loop - potential performance bottleneck',
                'line_number' => 1,
                'code_snippet' => 'I/O or network operations inside loops'
            ];
        }
        
        return $issues;
    }
    
    /**
     * Compliance analysis
     */
    private function analyzeCompliance($content, $language, $filePath, $lines, $standards) {
        $issues = [];
        
        if (empty($standards)) {
            $standards = ['owasp']; // Default to OWASP
        }
        
        foreach ($standards as $standard) {
            if (isset(COMPLIANCE_STANDARDS[$standard])) {
                $standardChecks = COMPLIANCE_STANDARDS[$standard]['checks'];
                
                foreach ($standardChecks as $check) {
                    if (isset(SECURITY_PATTERNS[$check])) {
                        $patternConfig = SECURITY_PATTERNS[$check];
                        foreach ($patternConfig['patterns'] as $pattern) {
                            if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                                foreach ($matches[0] as $match) {
                                    $lineNumber = $this->getLineNumber($content, $match[1]);
                                    $issues[] = [
                                        'type' => 'compliance_violation',
                                        'severity' => $patternConfig['severity'],
                                        'description' => COMPLIANCE_STANDARDS[$standard]['name'] . ' violation: ' . $patternConfig['description'],
                                        'line_number' => $lineNumber,
                                        'code_snippet' => $this->getCodeSnippet($lines, $lineNumber),
                                        'standard' => $standard,
                                        'requirement' => $check
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Helper methods
     */
    private function getLineNumber($content, $offset) {
        return substr_count(substr($content, 0, $offset), "\n") + 1;
    }
    
    private function getCodeSnippet($lines, $lineNumber, $context = 3) {
        $start = max(0, $lineNumber - $context - 1);
        $end = min(count($lines), $lineNumber + $context);
        
        $snippet = [];
        for ($i = $start; $i < $end; $i++) {
            $snippet[] = [
                'line' => $i + 1,
                'code' => $lines[$i],
                'current' => ($i + 1) === $lineNumber
            ];
        }
        
        return $snippet;
    }
    
    private function countFunctionLines($functionContent) {
        return count(explode("\n", $functionContent));
    }
    
    /**
     * AI-powered overall analysis
     */
    private function performAIOverallAnalysis($sourcePath) {
        try {
            $summaryData = [
                'total_files' => $this->scanResults['summary']['total_files'],
                'files_analyzed' => $this->scanResults['summary']['files_analyzed'],
                'languages_detected' => $this->scanResults['languages_detected'],
                'security_issues_count' => $this->scanResults['summary']['security_issues_count'],
                'quality_issues_count' => $this->scanResults['summary']['quality_issues_count'],
                'performance_issues_count' => $this->scanResults['summary']['performance_issues_count']
            ];
            
            $prompt = "Provide an overall security and code quality assessment based on this scan data: " . 
                     json_encode($summaryData) . 
                     ". Focus on critical risks, overall code health, and provide actionable recommendations.";
            
            $aiAnalysis = $this->ollama->generateResponse($prompt, "You are an expert code security and quality analyst.");
            
            $this->scanResults['ai_analysis'] = [
                'overall_assessment' => $aiAnalysis['analysis'] ?? $aiAnalysis,
                'recommendations' => $aiAnalysis['recommendations'] ?? [],
                'risk_level' => $this->calculateOverallRiskLevel()
            ];
            
        } catch (Exception $e) {
            $this->addScanMetric('warning', 'AI Analysis Failed', $e->getMessage());
        }
    }
    
    /**
     * Calculate overall risk level
     */
    private function calculateOverallRiskLevel() {
        $criticalCount = 0;
        $highCount = 0;
        $mediumCount = 0;
        
        foreach ($this->scanResults['files_analyzed'] as $file) {
            if (isset($file['issues']['security'])) {
                foreach ($file['issues']['security'] as $issue) {
                    switch ($issue['severity']) {
                        case 'critical': $criticalCount++; break;
                        case 'high': $highCount++; break;
                        case 'medium': $mediumCount++; break;
                    }
                }
            }
        }
        
        if ($criticalCount > 0) return 'critical';
        if ($highCount > 5) return 'high';
        if ($highCount > 0 || $mediumCount > 10) return 'medium';
        return 'low';
    }
    
    /**
     * Generate final summary
     */
    public function generateFinalSummary() {
        $this->scanResults['summary']['overall_risk_level'] = $this->calculateOverallRiskLevel();
        $this->scanResults['summary']['scan_completion_time'] = date('Y-m-d H:i:s');
    }
    
    /**
     * Add scan metric
     */
    public function addScanMetric($type, $name, $value) {
        $this->scanResults['scan_metrics'][] = [
            'type' => $type,
            'name' => $name,
            'value' => $value,
            'timestamp' => microtime(true)
        ];
    }
    
    /**
     * Get supported languages
     */
    public function getSupportedLanguages() {
        return SUPPORTED_LANGUAGES;
    }
    
    /**
     * Get compliance standards
     */
    public function getComplianceStandards() {
        return COMPLIANCE_STANDARDS;
    }
    
    /**
     * =========================================================================
     * UTILITY FUNCTIONS - For API integration
     * =========================================================================
     */
    
    /**
     * Clone Git repository for analysis
     */
    public static function cloneGitRepository($repoUrl) {
        $cloneDir = __DIR__ . '/../uploads/git_repos/' . uniqid() . '/';
        
        if (!is_dir($cloneDir)) {
            mkdir($cloneDir, 0755, true);
        }
        
        // Validate Git URL
        if (!filter_var($repoUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid Git repository URL');
        }
        
        // Check if git is available
        $gitCheck = shell_exec('git --version');
        if (!$gitCheck) {
            throw new Exception('Git is not installed or not in PATH');
        }
        
        // Clone repository
        $command = "git clone " . escapeshellarg($repoUrl) . " " . escapeshellarg($cloneDir) . " 2>&1";
        $output = shell_exec($command);
        
        if (!is_dir($cloneDir . '/.git')) {
            self::deleteDirectory($cloneDir);
            throw new Exception('Git clone failed: ' . ($output ?: 'Unknown error'));
        }
        
        return $cloneDir;
    }
    
    /**
     * Recursively delete directory
     */
    public static function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                self::deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    
    }
    
    /**
     * Validate and sanitize file uploads
     */
    public static function validateFileUpload($file) {
        // Check if file has a name
        if (empty($file['name'])) {
            throw new Exception("File name is empty");
        }
        
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception("File too large: " . $file['name'] . " (" . round($file['size'] / 1024 / 1024, 2) . "MB)");
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
            ];
            throw new Exception($errorMessages[$file['error']] ?? 'Unknown upload error');
        }
        
        // Check if file is actually uploaded
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception("Possible file upload attack: " . $file['name']);
        }
        
        return true;
    }
    
    /**
     * Process uploaded files for analysis
     */
    public static function processFileUploads($uploadedFiles) {
        $uploadDir = __DIR__ . '/../uploads/code_analysis/' . uniqid() . '/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $savedFiles = [];
        
        // Handle single file upload
        if (!is_array($uploadedFiles['name'])) {
            $uploadedFiles = [
                'name' => [$uploadedFiles['name']],
                'tmp_name' => [$uploadedFiles['tmp_name']],
                'error' => [$uploadedFiles['error']],
                'size' => [$uploadedFiles['size']]
            ];
        }
        
        foreach ($uploadedFiles['tmp_name'] as $key => $tmpName) {
            if ($uploadedFiles['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = $uploadedFiles['name'][$key];
                
                // Skip files without names
                if (empty($fileName)) {
                    continue;
                }
                
                // Validate file
                $fileData = [
                    'name' => $fileName,
                    'size' => $uploadedFiles['size'][$key],
                    'tmp_name' => $tmpName,
                    'error' => $uploadedFiles['error'][$key]
                ];
                
                try {
                    self::validateFileUpload($fileData);
                    
                    $filePath = $uploadDir . $fileName;
                    
                    // Create subdirectories if needed
                    $dirPath = dirname($filePath);
                    if (!is_dir($dirPath)) {
                        mkdir($dirPath, 0755, true);
                    }
                    
                    if (move_uploaded_file($tmpName, $filePath)) {
                        $savedFiles[] = $filePath;
                    }
                } catch (Exception $e) {
                    // Log the error but continue processing other files
                    error_log("File upload validation failed for $fileName: " . $e->getMessage());
                    continue;
                }
            }
        }
        
        if (empty($savedFiles)) {
            self::deleteDirectory($uploadDir);
            throw new Exception("No valid files were uploaded for analysis");
        }
        
        return [
            'upload_dir' => $uploadDir,
            'saved_files' => $savedFiles
        ];
    }

    /**
     * Enhanced file type detection for uploaded files
     */
    public static function getFileLanguage($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // Map extensions to languages
        $extensionMap = [];
        foreach (SUPPORTED_LANGUAGES as $lang => $config) {
            foreach ($config['extensions'] as $ext) {
                $extensionMap[$ext] = $lang;
            }
        }
        
        if (!empty($extension) && isset($extensionMap[$extension])) {
            return $extensionMap[$extension];
        }
        
        // Fallback to content detection
        return self::detectLanguageByContent($filePath);
    }

    /**
     * Detect language by file content
     */
    private static function detectLanguageByContent($filePath) {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return 'unknown';
        }
        
        $content = file_get_contents($filePath);
        if (empty($content)) {
            return 'unknown';
        }
        
        $content = substr($content, 0, 1000); // Check first 1000 chars
        
        $patterns = [
            'php' => ['/<\?php/', '/<\?=/'],
            'html' => ['/<!DOCTYPE html/i', '/<html/i', '/<head/i', '/<body/i'],
            'javascript' => ['/function\s*\w*\s*\(/', '/const\s+/', '/let\s+/', '/var\s+/', '/=>/'],
            'python' => ['/def\s+\w+\s*\(/', '/import\s+/', '/from\s+/', '/print\s*\(/'],
            'java' => ['/public\s+class/', '/import\s+java/', '/System\.out\.print/'],
            'css' => ['/@import/', '/@media/', '/\.\w+\s*\{/', '/#\w+\s*\{/'],
            'json' => ['/^\s*[\{\[]/'],
            'xml' => ['/<\?xml/', '/<\/?\w+>/']
        ];
        
        foreach ($patterns as $lang => $langPatterns) {
            foreach ($langPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    return $lang;
                }
            }
        }
        
        return 'unknown';
    }
}

/**
 * =========================================================================
 * STANDALONE UTILITY FUNCTIONS - For API integration
 * =========================================================================
 */

function cloneGitRepository($repoUrl) {
    return CodeAnalyzer::cloneGitRepository($repoUrl);
}

function deleteDirectory($dir) {
    return CodeAnalyzer::deleteDirectory($dir);
}

function validateFileUpload($file) {
    return CodeAnalyzer::validateFileUpload($file);
}

function processFileUploads($uploadedFiles) {
    return CodeAnalyzer::processFileUploads($uploadedFiles);
}

function getFileLanguage($filePath) {
    return CodeAnalyzer::getFileLanguage($filePath);
}
?>