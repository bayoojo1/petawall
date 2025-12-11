<?php
require_once __DIR__ . '/ollama-search.php';

class PasswordAnalyzer {
    private $ollama;
    
    public function __construct(OllamaSearch $ollama = null) {
        $this->ollama = $ollama ?? new OllamaSearch(PASSWORD_ANALYSIS_MODEL);
    }
    
    public function analyzePassword($password, $options = []) {
        $analysis = $this->analyzePasswordPatterns($password, $options);
        
        // If Ollama is available, use AI analysis
        if ($this->ollama) {
            $contextData = [
                'length' => $analysis['length'],
                'diversity' => $analysis['diversity'],
                'patterns' => $analysis['patterns'],
                'commonality' => $analysis['commonality'],
                'analysis_mode' => $options['mode'] ?? 'advanced',
                'check_common' => $options['check_common'] ?? true,
                'check_patterns' => $options['check_patterns'] ?? true,
                'check_leaks' => $options['check_leaks'] ?? false
            ];
            
            return $this->ollama->analyzeForTool('password', $password, $contextData);
        } else {
            // Fallback to basic analysis without AI
            return $this->basicPasswordAnalysis($password, $analysis);
        }
    }
    
    private function analyzePasswordPatterns($password, $options) {
        $length = strlen($password);
        
        // Calculate character diversity
        $charTypes = 0;
        if (preg_match('/[a-z]/', $password)) $charTypes++;
        if (preg_match('/[A-Z]/', $password)) $charTypes++;
        if (preg_match('/[0-9]/', $password)) $charTypes++;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $charTypes++;
        
        $diversity = ($charTypes / 4) * 100;
        
        // Detect common patterns if enabled
        $patterns = [];
        if ($options['check_patterns'] ?? true) {
            if (preg_match('/^[a-zA-Z]+$/', $password)) $patterns[] = 'letters_only';
            if (preg_match('/^[0-9]+$/', $password)) $patterns[] = 'numbers_only';
            if (preg_match('/^[a-z]+$/', $password)) $patterns[] = 'lowercase_only';
            if (preg_match('/^[A-Z]+$/', $password)) $patterns[] = 'uppercase_only';
            if (preg_match('/(.)\1{2,}/', $password)) $patterns[] = 'repeated_chars';
            if (preg_match('/\b(\w+)\1\b/i', $password)) $patterns[] = 'repeated_words';
            if (preg_match('/123|abc|xyz|qwe|asd/', strtolower($password))) $patterns[] = 'common_sequences';
        }
        
        // Check for common passwords if enabled
        $commonality = 'unknown';
        if ($options['check_common'] ?? true) {
            $commonPasswords = ['password', '123456', 'qwerty', 'letmein', 'welcome', 'admin', '12345678', '123456789'];
            $commonality = in_array(strtolower($password), $commonPasswords) ? 'very_common' : 'uncommon';
        }
        
        return [
            'length' => $length,
            'diversity' => $diversity . '%',
            'patterns' => $patterns,
            'commonality' => $commonality
        ];
    }
    
    private function basicPasswordAnalysis($password, $analysis) {
        // Calculate basic strength score
        $score = 0;
        
        // Length points (max 40)
        $length = strlen($password);
        $score += min($length * 2, 40);
        
        // Character diversity points (max 30)
        $charTypes = 0;
        if (preg_match('/[a-z]/', $password)) $charTypes++;
        if (preg_match('/[A-Z]/', $password)) $charTypes++;
        if (preg_match('/[0-9]/', $password)) $charTypes++;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $charTypes++;
        $score += $charTypes * 7.5;
        
        // Penalties for bad patterns
        if (in_array('letters_only', $analysis['patterns'])) $score -= 20;
        if (in_array('numbers_only', $analysis['patterns'])) $score -= 20;
        if (in_array('common_sequences', $analysis['patterns'])) $score -= 15;
        if (in_array('repeated_chars', $analysis['patterns'])) $score -= 10;
        
        $score = max(0, min(100, $score));
        
        // Determine strength level
        if ($score >= 80) {
            $strength = ['id' => 'very-strong', 'label' => 'Very Strong', 'value' => $score];
            $crackTime = 'Centuries';
        } elseif ($score >= 60) {
            $strength = ['id' => 'strong', 'label' => 'Strong', 'value' => $score];
            $crackTime = 'Years';
        } elseif ($score >= 40) {
            $strength = ['id' => 'medium', 'label' => 'Medium', 'value' => $score];
            $crackTime = 'Months';
        } elseif ($score >= 20) {
            $strength = ['id' => 'weak', 'label' => 'Weak', 'value' => $score];
            $crackTime = 'Days';
        } else {
            $strength = ['id' => 'very-weak', 'label' => 'Very Weak', 'value' => $score];
            $crackTime = 'Instantly';
        }
        
        // Generate recommendations
        $recommendations = [];
        if ($length < 8) {
            $recommendations[] = 'Use at least 8 characters';
        }
        if ($charTypes < 3) {
            $recommendations[] = 'Mix uppercase, lowercase, numbers, and symbols';
        }
        if (in_array('common_sequences', $analysis['patterns'])) {
            $recommendations[] = 'Avoid common keyboard sequences';
        }
        if (in_array('repeated_chars', $analysis['patterns'])) {
            $recommendations[] = 'Avoid repeated characters';
        }
        
        return [
            'strength' => $strength,
            'crackTime' => $crackTime,
            'analysis' => $analysis,
            'recommendations' => $recommendations,
            'chartData' => [
                'labels' => ['Length', 'Diversity', 'Complexity'],
                'datasets' => [[
                    'label' => 'Password Metrics',
                    'data' => [min($length * 10, 100), (float)$analysis['diversity'], $score],
                    'backgroundColor' => ['#3498db', '#2ecc71', '#9b59b6']
                ]]
            ]
        ];
    }
    
    public function analyzePasswordPolicy($policy) {
        if ($this->ollama) {
            return $this->ollama->analyzeForTool('policy', $policy);
        } else {
            return $this->basicPolicyAnalysis($policy);
        }
    }
    
    private function basicPolicyAnalysis($policy) {
        // Basic policy analysis without AI
        return [
            'analysis' => 'Basic policy analysis: Enable multi-factor authentication and use longer passwords instead of frequent changes.',
            'recommendations' => [
                'Require minimum 12 characters',
                'Allow all character types',
                'Use password block list for common passwords',
                'Implement multi-factor authentication'
            ]
        ];
    }
}
?>