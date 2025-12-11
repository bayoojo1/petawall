<?php
require_once __DIR__ . '/ollama-search.php';

class MobileAppScanner {
    private $ollama;
    private $tempDir;
    private $apktoolPath;
    private $dex2jarPath;
    private $jadxPath;
    private $r2path;
    
    public function __construct() {
        $this->ollama = new OllamaSearch(MOBILE_SCAN_MODEL);
        $this->tempDir = __DIR__ . '/../temp/mobile_scans/';
        $this->apktoolPath = APKTOOL_PATH;
        $this->dex2jarPath = DEX2JAR_PATH;
        $this->jadxPath = JADX_PATH;
        $this->r2path = R2_PATH;
        
        // Create temp directory if it doesn't exist
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
        
        // Validate required tools
        $this->validateTools();
    }
    
    /**
     * Validate required external tools
     */
    private function validateTools() {
        $requiredTools = [
            'apktool' => $this->apktoolPath,
            'dex2jar' => $this->dex2jarPath,
            'jadx' => $this->jadxPath,
            'r2path' => $this->r2path
        ];
        
        foreach ($requiredTools as $tool => $path) {
            if (!file_exists($path) || !is_executable($path)) {
                throw new Exception("Required tool not found or not executable: $tool at $path");
            }
        }
    }
    
    /**
     * Main scan method
     */
    public function scanMobileApp($filePath, $platform, $scanType = 'comprehensive', $options = []) {
        try {
            // Validate input
            $this->validateInput($filePath, $platform);
            
            // Extract app contents
            $appData = $this->extractAppContents($filePath, $platform);
            
            // Perform platform-specific analysis
            $analysisResults = [];
            
            switch ($platform) {
                case 'android':
                    $analysisResults = $this->analyzeAndroidApp($appData, $scanType, $options);
                    break;
                    
                case 'ios':
                    $analysisResults = $this->analyzeIOSApp($appData, $scanType, $options);
                    break;
                    
                case 'hybrid':
                    $analysisResults = $this->analyzeHybridApp($appData, $scanType, $options);
                    break;
            }
            
            // Generate comprehensive report
            $report = $this->generateSecurityReport($analysisResults, $platform, $scanType);
            
            // Clean up temporary files
            $this->cleanupTempFiles($appData['temp_dir']);
            
            return $report;
            
        } catch (Exception $e) {
            // Clean up on error
            if (isset($appData['temp_dir'])) {
                $this->cleanupTempFiles($appData['temp_dir']);
            }
            
            throw new Exception('Mobile app scan failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Analyze by package name (Google Play Store)
     */
    public function analyzeByPackageName($packageName, $scanType = 'comprehensive') {
        try {
            // Download APK from Google Play Store
            $apkPath = $this->downloadFromPlayStore($packageName);
            
            if (!$apkPath) {
                throw new Exception('Could not download app from Google Play Store');
            }
            
            return $this->scanMobileApp($apkPath, 'android', $scanType);
            
        } catch (Exception $e) {
            throw new Exception('Package analysis failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Analyze by bundle ID (Apple App Store)
     */
    public function analyzeByBundleId($bundleId, $scanType = 'comprehensive') {
        try {
            // Download IPA from App Store
            $ipaPath = $this->downloadFromAppStore($bundleId);
            
            if (!$ipaPath) {
                throw new Exception('Could not download app from App Store');
            }
            
            return $this->scanMobileApp($ipaPath, 'ios', $scanType);
            
        } catch (Exception $e) {
            throw new Exception('Bundle analysis failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate input parameters
     */
    private function validateInput($filePath, $platform) {
        if (!file_exists($filePath)) {
            throw new Exception('App file not found: ' . $filePath);
        }
        
        $allowedPlatforms = ['android', 'ios', 'hybrid'];
        if (!in_array($platform, $allowedPlatforms)) {
            throw new Exception('Invalid platform. Supported: ' . implode(', ', $allowedPlatforms));
        }
        
        // Check file size (max 100MB)
        $fileSize = filesize($filePath);
        if ($fileSize > MAX_APP_FILE_SIZE) {
            throw new Exception('App file too large. Maximum size is ' . (MAX_APP_FILE_SIZE / 1024 / 1024) . 'MB');
        }
        
        // Check file type
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($platform === 'android' && $fileExtension !== 'apk') {
            throw new Exception('Android apps must be APK files');
        }
        if ($platform === 'ios' && $fileExtension !== 'ipa') {
            throw new Exception('iOS apps must be IPA files');
        }
    }
    
    /**
     * Extract app contents based on platform
     */
    private function extractAppContents($filePath, $platform) {
        $tempDir = $this->tempDir . uniqid('mobile_scan_') . '/';
        mkdir($tempDir, 0755, true);
        
        $appData = [
            'temp_dir' => $tempDir,
            'original_file' => $filePath,
            'platform' => $platform
        ];
        
        switch ($platform) {
            case 'android':
                $appData = array_merge($appData, $this->extractAPK($filePath, $tempDir));
                break;
                
            case 'ios':
                $appData = array_merge($appData, $this->extractIPA($filePath, $tempDir));
                break;
                
            case 'hybrid':
                $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if ($fileExtension === 'apk') {
                    $appData = array_merge($appData, $this->extractAPK($filePath, $tempDir));
                    $appData['hybrid_framework'] = $this->detectHybridFramework($appData);
                } else {
                    $appData = array_merge($appData, $this->extractIPA($filePath, $tempDir));
                    $appData['hybrid_framework'] = $this->detectHybridFramework($appData);
                }
                break;
        }
        
        return $appData;
    }
    
    /**
     * Extract APK file contents using apktool
     */
    private function extractAPK($apkPath, $tempDir) {
        $apkData = [];
        
        try {
            $outputDir = $tempDir . 'apk_contents/';
            mkdir($outputDir, 0755, true);
            
            // Use apktool for extraction
            $command = escapeshellcmd($this->apktoolPath) . ' d ' . 
                      escapeshellarg($apkPath) . ' -o ' . 
                      escapeshellarg($outputDir) . ' -f';
            
            $output = [];
            $returnCode = 0;
            exec($command . ' 2>&1', $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new Exception('APK extraction failed: ' . implode("\n", $output));
            }
            
            // Parse AndroidManifest.xml
            $apkData['manifest'] = $this->parseAndroidManifest($outputDir);
            
            // Extract and analyze DEX files
            $apkData['dex_files'] = $this->extractDexFiles($apkPath, $tempDir);
            
            // Analyze resources
            $apkData['resources'] = $this->analyzeAndroidResources($outputDir);
            
            // Extract certificates
            $apkData['certificates'] = $this->extractCertificates($apkPath);
            
            // Extract source code
            $apkData['source_code'] = $this->extractSourceCode($apkPath, $tempDir);
            
            $apkData['extraction_dir'] = $outputDir;
            
        } catch (Exception $e) {
            throw new Exception('APK extraction failed: ' . $e->getMessage());
        }
        
        return $apkData;
    }
    
    /**
     * Extract IPA file contents
     */
    private function extractIPA($ipaPath, $tempDir) {
        $ipaData = [];
        
        try {
            $outputDir = $tempDir . 'ipa_contents/';
            mkdir($outputDir, 0755, true);
            
            // Extract IPA (which is a zip file)
            $zip = new ZipArchive();
            if ($zip->open($ipaPath) === TRUE) {
                $zip->extractTo($outputDir);
                $zip->close();
                
                // Find the .app bundle
                $appBundle = $this->findAppBundle($outputDir);
                $ipaData['app_bundle'] = $appBundle;
                
                // Parse Info.plist
                $ipaData['plist'] = $this->parseInfoPlist($appBundle);
                
                // Extract binary
                $ipaData['binary'] = $this->extractBinary($appBundle);
                
                // Analyze entitlements
                $ipaData['entitlements'] = $this->analyzeEntitlements($appBundle);
                
                // Extract embedded frameworks
                $ipaData['frameworks'] = $this->extractFrameworks($appBundle);
                
            } else {
                throw new Exception('Failed to extract IPA file');
            }
            
            $ipaData['extraction_dir'] = $outputDir;
            
        } catch (Exception $e) {
            throw new Exception('IPA extraction failed: ' . $e->getMessage());
        }
        
        return $ipaData;
    }
    
    /**
     * Parse AndroidManifest.xml
     */
    private function parseAndroidManifest($extractionDir) {
        $manifestPath = $extractionDir . 'AndroidManifest.xml';
        
        if (!file_exists($manifestPath)) {
            throw new Exception('AndroidManifest.xml not found');
        }
        
        // Use AXMLParser or similar to parse binary XML
        $manifestContent = file_get_contents($manifestPath);
        
        // For binary AndroidManifest.xml, we need special parsing
        // This is a simplified version - in production you'd use a proper parser
        $manifest = [
            'package' => $this->extractPackageName($manifestContent),
            'versionCode' => $this->extractVersionCode($manifestContent),
            'versionName' => $this->extractVersionName($manifestContent),
            'uses-permission' => $this->extractPermissions($manifestContent),
            'application' => $this->extractApplicationInfo($manifestContent)
        ];
        
        return $manifest;
    }
    
    /**
     * Extract package name from manifest
     */
    private function extractPackageName($manifestContent) {
        // Simplified extraction - in production use proper binary XML parser
        preg_match('/package="([^"]+)"/', $manifestContent, $matches);
        return $matches[1] ?? 'unknown';
    }
    
    /**
     * Extract version code from manifest
     */
    private function extractVersionCode($manifestContent) {
        preg_match('/android:versionCode="([^"]+)"/', $manifestContent, $matches);
        return $matches[1] ?? '1';
    }
    
    /**
     * Extract version name from manifest
     */
    private function extractVersionName($manifestContent) {
        preg_match('/android:versionName="([^"]+)"/', $manifestContent, $matches);
        return $matches[1] ?? '1.0';
    }
    
    /**
     * Extract permissions from manifest
     */
    private function extractPermissions($manifestContent) {
        $permissions = [];
        preg_match_all('/<uses-permission[^>]+android:name="([^"]+)"/', $manifestContent, $matches);
        
        foreach ($matches[1] as $permission) {
            $permissions[] = ['@android:name' => $permission];
        }
        
        return $permissions;
    }
    
    /**
     * Extract application info from manifest
     */
    private function extractApplicationInfo($manifestContent) {
        preg_match('/<application[^>]*>/', $manifestContent, $appMatch);
        $appTag = $appMatch[0] ?? '';
        
        return [
            '@android:debuggable' => strpos($appTag, 'android:debuggable="true"') !== false ? 'true' : 'false',
            '@android:allowBackup' => strpos($appTag, 'android:allowBackup="false"') === false ? 'true' : 'false',
            '@android:usesCleartextTraffic' => strpos($appTag, 'android:usesCleartextTraffic="true"') !== false ? 'true' : 'false'
        ];
    }
    
    /**
     * Extract and convert DEX files
     */
    private function extractDexFiles($apkPath, $tempDir) {
        $dexFiles = [];
        $dexDir = $tempDir . 'dex_files/';
        mkdir($dexDir, 0755, true);
        
        // Extract DEX files from APK
        $zip = new ZipArchive();
        if ($zip->open($apkPath) === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                if (pathinfo($entry, PATHINFO_EXTENSION) === 'dex') {
                    // Extract DEX file
                    $dexContent = $zip->getFromIndex($i);
                    $dexPath = $dexDir . basename($entry);
                    file_put_contents($dexPath, $dexContent);
                    
                    // Convert to JAR for analysis
                    $jarPath = $this->convertDexToJar($dexPath, $tempDir);
                    if ($jarPath) {
                        $dexFiles[] = [
                            'dex_path' => $dexPath,
                            'jar_path' => $jarPath,
                            'name' => basename($entry)
                        ];
                    }
                }
            }
            $zip->close();
        }
        
        return $dexFiles;
    }
    
    /**
     * Convert DEX to JAR using dex2jar
     */
    private function convertDexToJar($dexPath, $tempDir) {
        $jarPath = $tempDir . 'jar_files/' . pathinfo($dexPath, PATHINFO_FILENAME) . '.jar';
        $jarDir = dirname($jarPath);
        
        if (!is_dir($jarDir)) {
            mkdir($jarDir, 0755, true);
        }
        
        $command = escapeshellcmd($this->dex2jarPath) . ' ' . 
                  escapeshellarg($dexPath) . ' -o ' . 
                  escapeshellarg($jarPath) . ' 2>&1';
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($jarPath)) {
            return $jarPath;
        }
        
        return null;
    }
    
    /**
     * Extract source code using jadx
     */
    private function extractSourceCode($apkPath, $tempDir) {
        $sourceDir = $tempDir . 'source_code/';
        mkdir($sourceDir, 0755, true);
        
        $command = escapeshellcmd($this->jadxPath) . ' -d ' . 
                  escapeshellarg($sourceDir) . ' ' . 
                  escapeshellarg($apkPath) . ' 2>&1';
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && is_dir($sourceDir)) {
            return [
                'source_dir' => $sourceDir,
                'files' => $this->scanDirectory($sourceDir)
            ];
        }
        
        return null;
    }
    
    /**
     * Scan directory for files
     */
    private function scanDirectory($dir) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = [
                    'path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'extension' => $file->getExtension()
                ];
            }
        }
        
        return $files;
    }
    
    /**
     * Analyze Android resources
     */
    private function analyzeAndroidResources($outputDir) {
        $resources = [];
        
        // Analyze strings.xml for hardcoded secrets
        $stringsPath = $outputDir . 'res/values/strings.xml';
        if (file_exists($stringsPath)) {
            $resources['strings'] = $this->analyzeStringsFile($stringsPath);
        }
        
        // Analyze other resource files
        $resources['files'] = $this->scanDirectory($outputDir . 'res/');
        
        return $resources;
    }
    
    /**
     * Analyze strings.xml for secrets
     */
    private function analyzeStringsFile($stringsPath) {
        $secrets = [];
        $content = file_get_contents($stringsPath);
        
        // Look for potential API keys, secrets, etc.
        $secretPatterns = [
            '/api[_-]?key/i' => 'API Key',
            '/secret/i' => 'Secret',
            '/password/i' => 'Password',
            '/token/i' => 'Token',
            '/auth/i' => 'Authentication',
            '/key/i' => 'Key'
        ];
        
        preg_match_all('/<string name="([^"]+)">([^<]+)<\/string>/', $content, $matches);
        
        for ($i = 0; $i < count($matches[1]); $i++) {
            $name = $matches[1][$i];
            $value = $matches[2][$i];
            
            foreach ($secretPatterns as $pattern => $type) {
                if (preg_match($pattern, $name)) {
                    $secrets[] = [
                        'type' => $type,
                        'name' => $name,
                        'value' => $value,
                        'risk' => $this->assessSecretRisk($value)
                    ];
                }
            }
        }
        
        return $secrets;
    }
    
    /**
     * Assess risk level of potential secret
     */
    private function assessSecretRisk($value) {
        if (empty($value)) {
            return 'low';
        }
        
        // Check if it looks like a real secret (not placeholder)
        if (strlen($value) > 20 && preg_match('/[a-zA-Z0-9]{20,}/', $value)) {
            return 'high';
        }
        
        if (preg_match('/^(your_|test_|demo_)/i', $value)) {
            return 'low';
        }
        
        return 'medium';
    }
    
    /**
     * Extract certificates from APK
     */
    private function extractCertificates($apkPath) {
        $certificates = [];
        
        // Extract APK as zip
        $zip = new ZipArchive();
        if ($zip->open($apkPath) === TRUE) {
            // Look for certificate files in META-INF
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                if (preg_match('#^META-INF/.*\.(RSA|DSA|EC)$#', $entry)) {
                    $certContent = $zip->getFromIndex($i);
                    $certInfo = $this->parseCertificate($certContent, $entry);
                    if ($certInfo) {
                        $certificates[] = $certInfo;
                    }
                }
            }
            $zip->close();
        }
        
        return $certificates;
    }
    
    /**
     * Parse certificate information
     */
    private function parseCertificate($certContent, $filename) {
        // Use OpenSSL to parse certificate
        $tempCert = tempnam(sys_get_temp_dir(), 'cert');
        file_put_contents($tempCert, $certContent);
        
        $output = [];
        $returnCode = 0;
        exec("openssl pkcs7 -inform DER -in " . escapeshellarg($tempCert) . " -print_certs 2>&1", $output, $returnCode);
        
        unlink($tempCert);
        
        if ($returnCode === 0) {
            $certText = implode("\n", $output);
            
            return [
                'filename' => $filename,
                'subject' => $this->extractCertificateField($certText, 'Subject:'),
                'issuer' => $this->extractCertificateField($certText, 'Issuer:'),
                'validity' => $this->extractCertificateValidity($certText)
            ];
        }
        
        return null;
    }
    
    /**
     * Extract certificate field
     */
    private function extractCertificateField($certText, $field) {
        preg_match('/' . preg_quote($field) . '\s*(.+)/', $certText, $matches);
        return $matches[1] ?? 'Unknown';
    }
    
    /**
     * Extract certificate validity
     */
    private function extractCertificateValidity($certText) {
        preg_match('/Not Before\s*:\s*(.+)/', $certText, $notBefore);
        preg_match('/Not After\s*:\s*(.+)/', $certText, $notAfter);
        
        return [
            'not_before' => $notBefore[1] ?? 'Unknown',
            'not_after' => $notAfter[1] ?? 'Unknown'
        ];
    }
    
    /**
     * Find .app bundle in extracted IPA
     */
    private function findAppBundle($outputDir) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($outputDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir() && substr($file->getFilename(), -4) === '.app') {
                return $file->getPathname();
            }
        }
        
        throw new Exception('App bundle not found in IPA');
    }
    
    /**
     * Parse Info.plist file
     */
    private function parseInfoPlist($appBundle) {
        $plistPath = $appBundle . '/Info.plist';
        
        if (!file_exists($plistPath)) {
            throw new Exception('Info.plist not found at: ' . $plistPath);
        }
        
        // Use plutil or other method to parse plist
        $command = 'plutil -convert json -o - ' . escapeshellarg($plistPath) . ' 2>&1';
        $output = shell_exec($command);
        
        if ($output) {
            $plistData = json_decode($output, true);
            if ($plistData) {
                return $plistData;
            }
        }
        
        // Fallback: simple XML parsing for binary plist
        $content = file_get_contents($plistPath);
        return $this->parsePlistManually($content);
    }
    
    /**
     * Manual plist parsing fallback
     */
    private function parsePlistManually($content) {
        $plist = [];
        
        // Simple XML parsing for text plists
        if (strpos($content, '<?xml') === 0) {
            $xml = simplexml_load_string($content);
            if ($xml) {
                $plist = json_decode(json_encode($xml), true);
            }
        }
        
        return $plist;
    }
    
    /**
     * Extract main binary from app bundle
     */
    private function extractBinary($appBundle) {
        $plist = $this->parseInfoPlist($appBundle);
        $bundleExecutable = $plist['CFBundleExecutable'] ?? '';
        
        if ($bundleExecutable) {
            $binaryPath = $appBundle . '/' . $bundleExecutable;
            if (file_exists($binaryPath)) {
                return [
                    'path' => $binaryPath,
                    'size' => filesize($binaryPath),
                    'executable' => $bundleExecutable
                ];
            }
        }
        
        // Fallback: find first executable file
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($appBundle, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && is_executable($file->getPathname())) {
                return [
                    'path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'executable' => $file->getFilename()
                ];
            }
        }
        
        throw new Exception('App binary not found');
    }
    
    /**
     * Analyze entitlements
     */
    private function analyzeEntitlements($appBundle) {
        $entitlementsPath = $appBundle . '/archived-expanded-entitlements.xcent';
        if (!file_exists($entitlementsPath)) {
            $entitlementsPath = $appBundle . '/embedded.mobileprovision';
        }
        
        if (file_exists($entitlementsPath)) {
            return $this->parseEntitlements($entitlementsPath);
        }
        
        return [];
    }
    
    /**
     * Parse entitlements file
     */
    private function parseEntitlements($entitlementsPath) {
        $content = file_get_contents($entitlementsPath);
        
        // Try to extract plist from mobileprovision
        if (strpos($content, '<?xml') !== false) {
            $xmlStart = strpos($content, '<?xml');
            $xmlEnd = strpos($content, '</plist>') + 8;
            $xmlContent = substr($content, $xmlStart, $xmlEnd - $xmlStart);
            
            $plist = $this->parsePlistManually($xmlContent);
            return $plist['Entitlements'] ?? [];
        }
        
        return [];
    }
    
    /**
     * Extract embedded frameworks
     */
    private function extractFrameworks($appBundle) {
        $frameworksDir = $appBundle . '/Frameworks/';
        $frameworks = [];
        
        if (is_dir($frameworksDir)) {
            $iterator = new DirectoryIterator($frameworksDir);
            foreach ($iterator as $file) {
                if ($file->isDir() && !$file->isDot() && substr($file->getFilename(), -10) === '.framework') {
                    $frameworks[] = [
                        'name' => $file->getFilename(),
                        'path' => $file->getPathname()
                    ];
                }
            }
        }
        
        return $frameworks;
    }
    
    /**
     * Detect hybrid framework
     */
    private function detectHybridFramework($appData) {
        if (isset($appData['extraction_dir'])) {
            $extractionDir = $appData['extraction_dir'];
            
            // Check for React Native
            if (file_exists($extractionDir . '/assets/index.android.bundle') ||
                file_exists($extractionDir . '/main.jsbundle') ||
                $this->searchForFile($extractionDir, 'react-native')) {
                return 'react-native';
            }
            
            // Check for Flutter
            if (file_exists($extractionDir . '/libflutter.so') ||
                is_dir($extractionDir . '/flutter_assets/') ||
                $this->searchForFile($extractionDir, 'flutter')) {
                return 'flutter';
            }
            
            // Check for Cordova
            if (file_exists($extractionDir . '/assets/www/cordova.js') ||
                file_exists($extractionDir . '/www/cordova.js') ||
                $this->searchForFile($extractionDir, 'cordova')) {
                return 'cordova';
            }
            
            // Check for Ionic
            if ($this->searchForFile($extractionDir, 'ionic') ||
                $this->searchForFile($extractionDir, 'capacitor')) {
                return 'ionic';
            }
            
            // Check for Xamarin
            if ($this->searchForFile($extractionDir, 'xamarin') ||
                is_dir($extractionDir . '/assemblies/')) {
                return 'xamarin';
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Search for framework indicators in files
     */
    private function searchForFile($directory, $pattern) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && stripos($file->getFilename(), $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Download from Google Play Store
     */
    private function downloadFromPlayStore($packageName) {
        // Use google-play-scraper or similar tool
        $tempPath = $this->tempDir . uniqid('playstore_') . '.apk';
        
        // This would integrate with a Play Store downloader
        // For now, we'll simulate the process
        $command = 'python3 -m google_play_scraper.app ' . 
                  escapeshellarg($packageName) . ' -d ' . 
                  escapeshellarg($tempPath) . ' 2>&1';
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($tempPath)) {
            return $tempPath;
        }
        
        // Alternative method using other tools
        return $this->alternativePlayStoreDownload($packageName, $tempPath);
    }
    
    /**
     * Alternative Play Store download method
     */
    private function alternativePlayStoreDownload($packageName, $tempPath) {
        // Use apkeep or other tools
        $command = 'apkeep -a ' . escapeshellarg($packageName) . ' ' . escapeshellarg($tempPath) . ' 2>&1';
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($tempPath)) {
            return $tempPath;
        }
        
        return null;
    }
    
    /**
     * Download from App Store
     */
    private function downloadFromAppStore($bundleId) {
        // App Store downloading is more complex and may require legal considerations
        // This is a placeholder for implementation
        throw new Exception('App Store downloading requires additional setup and legal compliance');
    }
    
    /**
     * Comprehensive Android app analysis
     */
    private function analyzeAndroidApp($appData, $scanType, $options) {
        $analysis = [];
        
        if ($options['check_permissions'] ?? true) {
            $analysis['permissions'] = $this->analyzeAndroidPermissions($appData['manifest']);
        }
        
        if ($options['check_code'] ?? true) {
            $analysis['code'] = $this->analyzeAndroidCode($appData, $scanType);
        }
        
        if ($options['check_network'] ?? true) {
            $analysis['network'] = $this->analyzeNetworkSecurity($appData, 'android');
        }
        
        if ($options['check_storage'] ?? true) {
            $analysis['storage'] = $this->analyzeDataStorage($appData, 'android');
        }
        
        if ($options['check_crypto'] ?? true) {
            $analysis['cryptography'] = $this->analyzeCryptography($appData, 'android');
        }
        
        if ($options['check_api'] ?? true) {
            $analysis['api'] = $this->analyzeAPISecurity($appData, 'android');
        }
        
        $analysis['manifest'] = $this->analyzeAndroidManifest($appData['manifest']);
        $analysis['certificates'] = $this->analyzeCertificates($appData['certificates']);
        $analysis['resources'] = $this->analyzeResources($appData['resources']);
        $analysis['masvs'] = $this->checkMASVSCompliance($analysis, 'android');
        
        return $analysis;
    }
    
    /**
     * Comprehensive iOS app analysis
     */
    private function analyzeIOSApp($appData, $scanType, $options) {
        $analysis = [];
        
        $analysis['plist'] = $this->analyzeInfoPlist($appData['plist']);
        $analysis['binary'] = $this->analyzeIOSBinary($appData['binary']);
        $analysis['entitlements'] = $this->analyzeIOSEntitlements($appData['entitlements']);
        
        if ($options['check_network'] ?? true) {
            $analysis['network'] = $this->analyzeNetworkSecurity($appData, 'ios');
        }
        
        if ($options['check_storage'] ?? true) {
            $analysis['storage'] = $this->analyzeDataStorage($appData, 'ios');
        }
        
        if ($options['check_crypto'] ?? true) {
            $analysis['cryptography'] = $this->analyzeCryptography($appData, 'ios');
        }
        
        $analysis['masvs'] = $this->checkMASVSCompliance($analysis, 'ios');
        
        return $analysis;
    }
    
    /**
     * Hybrid app analysis
     */
    private function analyzeHybridApp($appData, $scanType, $options) {
        $framework = $appData['hybrid_framework'] ?? 'unknown';
        $analysis = [];
        
        $analysis['framework'] = $this->analyzeHybridFramework($appData, $framework);
        $analysis['webview'] = $this->analyzeWebViewSecurity($appData);
        $analysis['bridge'] = $this->analyzeNativeBridge($appData, $framework);
        
        // Perform base platform analysis
        if ($appData['platform'] === 'android' || isset($appData['manifest'])) {
            $baseAnalysis = $this->analyzeAndroidApp($appData, $scanType, $options);
        } else {
            $baseAnalysis = $this->analyzeIOSApp($appData, $scanType, $options);
        }
        
        $analysis = array_merge($analysis, $baseAnalysis);
        
        return $analysis;
    }
    
    /**
     * Android Manifest Analysis
     */
    private function analyzeAndroidManifest($manifest) {
        $issues = [];
        
        // Check for debuggable flag
        if (isset($manifest['application']['@android:debuggable']) && 
            $manifest['application']['@android:debuggable'] === 'true') {
            $issues[] = [
                'severity' => 'high',
                'title' => 'App is debuggable',
                'description' => 'The app has android:debuggable=true which exposes it to debugging attacks',
                'remediation' => 'Set android:debuggable=false in production builds',
                'category' => 'manifest'
            ];
        }
        
        // Check for backup allowed
        if (isset($manifest['application']['@android:allowBackup']) && 
            $manifest['application']['@android:allowBackup'] === 'true') {
            $issues[] = [
                'severity' => 'medium',
                'title' => 'Backup enabled',
                'description' => 'App allows backup which could expose sensitive data',
                'remediation' => 'Set android:allowBackup=false or implement backup security',
                'category' => 'manifest'
            ];
        }
        
        // Check for cleartext traffic
        if (!isset($manifest['application']['@android:usesCleartextTraffic']) || 
            $manifest['application']['@android:usesCleartextTraffic'] === 'true') {
            $issues[] = [
                'severity' => 'medium',
                'title' => 'Cleartext traffic allowed',
                'description' => 'App may allow unencrypted HTTP traffic',
                'remediation' => 'Set android:usesCleartextTraffic=false',
                'category' => 'manifest'
            ];
        }
        
        return $issues;
    }
    
    /**
     * Android Permission Analysis
     */
    private function analyzeAndroidPermissions($manifest) {
        $permissions = $manifest['uses-permission'] ?? [];
        $analysis = [
            'total_permissions' => count($permissions),
            'dangerous_permissions' => [],
            'normal_permissions' => [],
            'signature_permissions' => [],
            'permission_list' => $permissions
        ];
        
        $dangerousPermissions = [
            'android.permission.READ_EXTERNAL_STORAGE',
            'android.permission.WRITE_EXTERNAL_STORAGE',
            'android.permission.CAMERA',
            'android.permission.RECORD_AUDIO',
            'android.permission.ACCESS_FINE_LOCATION',
            'android.permission.ACCESS_COARSE_LOCATION',
            'android.permission.READ_CONTACTS',
            'android.permission.WRITE_CONTACTS',
            'android.permission.READ_PHONE_STATE',
            'android.permission.CALL_PHONE',
            'android.permission.READ_SMS',
            'android.permission.RECEIVE_SMS',
            'android.permission.SEND_SMS',
            'android.permission.ACCESS_MEDIA_LOCATION',
            'android.permission.ACTIVITY_RECOGNITION',
            'android.permission.BODY_SENSORS',
            'android.permission.READ_CALENDAR',
            'android.permission.WRITE_CALENDAR'
        ];
        
        $signaturePermissions = [
            'android.permission.BIND_ACCESSIBILITY_SERVICE',
            'android.permission.BIND_DEVICE_ADMIN',
            'android.permission.BIND_VPN_SERVICE',
            'android.permission.SYSTEM_ALERT_WINDOW',
            'android.permission.WRITE_SETTINGS'
        ];
        
        foreach ($permissions as $permission) {
            $permName = $permission['@android:name'] ?? '';
            
            if (in_array($permName, $dangerousPermissions)) {
                $analysis['dangerous_permissions'][] = [
                    'permission' => $permName,
                    'risk' => 'high',
                    'description' => 'This permission grants access to sensitive user data'
                ];
            } elseif (in_array($permName, $signaturePermissions)) {
                $analysis['signature_permissions'][] = [
                    'permission' => $permName,
                    'risk' => 'high',
                    'description' => 'This is a signature-level permission'
                ];
            } else {
                $analysis['normal_permissions'][] = [
                    'permission' => $permName,
                    'risk' => 'low',
                    'description' => 'Normal permission'
                ];
            }
        }
        
        return $analysis;
    }
    
    /**
     * Android Code Analysis
     */
    private function analyzeAndroidCode($appData, $scanType) {
        $issues = [];
        
        // Analyze source code if available
        if (isset($appData['source_code'])) {
            $sourceIssues = $this->analyzeSourceCode($appData['source_code']);
            $issues = array_merge($issues, $sourceIssues);
        }
        
        // Analyze DEX/JAR files
        if (isset($appData['dex_files'])) {
            foreach ($appData['dex_files'] as $dexFile) {
                $dexIssues = $this->analyzeDexFile($dexFile);
                $issues = array_merge($issues, $dexIssues);
            }
        }
        
        // Use AI for additional analysis
        if ($scanType === 'comprehensive') {
            $aiIssues = $this->analyzeCodeWithAI($appData);
            $issues = array_merge($issues, $aiIssues);
        }
        
        return $issues;
    }
    
    /**
     * Analyze source code
     */
    private function analyzeSourceCode($sourceData) {
        $issues = [];
        $sourceDir = $sourceData['source_dir'] ?? '';
        
        if (!$sourceDir || !is_dir($sourceDir)) {
            return $issues;
        }
        
        // Look for common security issues in source code
        $patterns = [
            '/hardcoded.*password/i' => 'Hardcoded password',
            '/api[_-]?key.*=.*["\']([^"\']+)["\']/i' => 'Hardcoded API key',
            '/secret.*=.*["\']([^"\']+)["\']/i' => 'Hardcoded secret',
            '/http:\/\/([^"\']*)/' => 'HTTP URL usage',
            '/SSLSocketFactory\.setHostnameVerifier.*ALLOW_ALL_HOSTNAME_VERIFIER/' => 'SSL hostname verification disabled',
            '/TrustManager.*acceptAllCertificates/' => 'Certificate validation disabled'
        ];
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['java', 'kt', 'xml'])) {
                $content = file_get_contents($file->getPathname());
                $relativePath = str_replace($sourceDir, '', $file->getPathname());
                
                foreach ($patterns as $pattern => $issueType) {
                    if (preg_match_all($pattern, $content, $matches)) {
                        $issues[] = [
                            'severity' => 'high',
                            'title' => $issueType,
                            'description' => "Found in $relativePath",
                            'remediation' => 'Remove hardcoded credentials and use secure storage',
                            'category' => 'code',
                            'file' => $relativePath,
                            'line' => $this->findLineNumber($content, $matches[0][0])
                        ];
                    }
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Find line number of matched content
     */
    private function findLineNumber($content, $search) {
        $lines = explode("\n", $content);
        foreach ($lines as $lineNumber => $line) {
            if (strpos($line, $search) !== false) {
                return $lineNumber + 1;
            }
        }
        return 0;
    }
    
    /**
     * Analyze DEX file
     */
    private function analyzeDexFile($dexFile) {
        $issues = [];
        $jarPath = $dexFile['jar_path'] ?? '';
        
        if (!$jarPath || !file_exists($jarPath)) {
            return $issues;
        }
        
        // Analyze JAR file for security issues
        // This could use additional tools like FindSecBugs
        $command = 'findsecbugs -html ' . escapeshellarg($jarPath) . ' 2>/dev/null';
        $output = shell_exec($command);
        
        if ($output) {
            $issues[] = [
                'severity' => 'medium',
                'title' => 'Bytecode analysis available',
                'description' => 'Use dedicated bytecode analysis tools for detailed results',
                'category' => 'code'
            ];
        }
        
        return $issues;
    }
    
    /**
     * Analyze code with AI
     */
    private function analyzeCodeWithAI($appData) {
        $issues = [];
        
        try {
            // Extract code samples for AI analysis
            $codeSamples = $this->extractCodeSamples($appData);
            
            foreach ($codeSamples as $sample) {
                $prompt = "Analyze this mobile app code for security vulnerabilities:\n\n" . $sample;
                
                $response = $this->ollama->generateResponse($prompt, 
                    "You are a mobile security expert. Identify security vulnerabilities in the code.");
                
                $aiIssues = $this->parseAICodeAnalysis($response);
                $issues = array_merge($issues, $aiIssues);
            }
            
        } catch (Exception $e) {
            // Log AI analysis failure but continue
            error_log('AI code analysis failed: ' . $e->getMessage());
        }
        
        return $issues;
    }
    
    /**
     * Extract code samples for AI analysis
     */
    private function extractCodeSamples($appData) {
        $samples = [];
        $maxSamples = 10;
        $sampleCount = 0;
        
        if (isset($appData['source_code']['files'])) {
            foreach ($appData['source_code']['files'] as $file) {
                if ($sampleCount >= $maxSamples) break;
                
                if (in_array($file['extension'], ['java', 'kt', 'm', 'swift'])) {
                    $content = file_get_contents($file['path']);
                    if (strlen($content) < 10000) { // Limit size for AI
                        $samples[] = "File: " . $file['path'] . "\n" . $content;
                        $sampleCount++;
                    }
                }
            }
        }
        
        return $samples;
    }
    
    /**
     * Parse AI code analysis response
     */
    private function parseAICodeAnalysis($response) {
        $issues = [];
        
        if (is_string($response)) {
            // Simple parsing - in production you'd want more sophisticated parsing
            if (strpos($response, 'vulnerability') !== false || 
                strpos($response, 'security') !== false) {
                $issues[] = [
                    'severity' => 'medium',
                    'title' => 'AI Security Analysis',
                    'description' => substr($response, 0, 500) . '...',
                    'category' => 'code',
                    'source' => 'ai_analysis'
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * Network Security Analysis
     */
    private function analyzeNetworkSecurity($appData, $platform) {
        $issues = [];
        
        // Check for hardcoded URLs
        $hardcodedUrls = $this->findHardcodedURLs($appData);
        if (!empty($hardcodedUrls)) {
            $issues[] = [
                'severity' => 'medium',
                'title' => 'Hardcoded URLs found',
                'description' => 'The app contains hardcoded URLs: ' . implode(', ', array_slice($hardcodedUrls, 0, 5)),
                'remediation' => 'Use configuration files or remote configuration for URLs',
                'category' => 'network'
            ];
        }
        
        // Check for SSL bypass
        $sslBypass = $this->checkSSLBypass($appData, $platform);
        if ($sslBypass) {
            $issues[] = [
                'severity' => 'critical',
                'title' => 'SSL Certificate Validation Bypass',
                'description' => 'The app contains code that bypasses SSL certificate validation',
                'remediation' => 'Implement proper SSL certificate validation',
                'category' => 'network'
            ];
        }
        
        // Check for cleartext traffic
        $cleartextTraffic = $this->checkCleartextTraffic($appData, $platform);
        if ($cleartextTraffic) {
            $issues[] = [
                'severity' => 'high',
                'title' => 'Cleartext Network Traffic',
                'description' => 'The app may send unencrypted network traffic',
                'remediation' => 'Use HTTPS for all network communications',
                'category' => 'network'
            ];
        }
        
        return $issues;
    }
    
    /**
     * Find hardcoded URLs in app
     */
    private function findHardcodedURLs($appData) {
        $urls = [];
        $urlPattern = '/https?:\/\/[^\s"\'<>]+/';
        
        if (isset($appData['source_code'])) {
            $sourceDir = $appData['source_code']['source_dir'] ?? '';
            if ($sourceDir && is_dir($sourceDir)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS)
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile() && in_array($file->getExtension(), ['java', 'kt', 'm', 'swift', 'xml'])) {
                        $content = file_get_contents($file->getPathname());
                        if (preg_match_all($urlPattern, $content, $matches)) {
                            $urls = array_merge($urls, $matches[0]);
                        }
                    }
                }
            }
        }
        
        return array_unique($urls);
    }
    
    /**
     * Check for SSL bypass
     */
    private function checkSSLBypass($appData, $platform) {
        $patterns = [
            '/ALLOW_ALL_HOSTNAME_VERIFIER/',
            '/setDefaultHostnameVerifier.*ALLOW_ALL/',
            '/TrustManager.*acceptAllCertificates/',
            '/checkServerTrusted.*empty/',
            '/setHostnameVerifier.*return true/'
        ];
        
        return $this->searchPatternsInCode($appData, $patterns);
    }
    
    /**
     * Check for cleartext traffic
     */
    private function checkCleartextTraffic($appData, $platform) {
        if ($platform === 'android') {
            $manifest = $appData['manifest'] ?? [];
            if (isset($manifest['application']['@android:usesCleartextTraffic']) &&
                $manifest['application']['@android:usesCleartextTraffic'] === 'true') {
                return true;
            }
        }
        
        // Check for HTTP URLs in code
        $httpUrls = $this->findHardcodedHTTPUrls($appData);
        return !empty($httpUrls);
    }
    
    /**
     * Find hardcoded HTTP URLs
     */
    private function findHardcodedHTTPUrls($appData) {
        $httpUrls = [];
        $httpPattern = '/http:\/\/[^\s"\'<>]+/';
        
        if (isset($appData['source_code'])) {
            $sourceDir = $appData['source_code']['source_dir'] ?? '';
            if ($sourceDir && is_dir($sourceDir)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS)
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $content = file_get_contents($file->getPathname());
                        if (preg_match_all($httpPattern, $content, $matches)) {
                            $httpUrls = array_merge($httpUrls, $matches[0]);
                        }
                    }
                }
            }
        }
        
        return array_unique($httpUrls);
    }
    
    /**
     * Search for patterns in code
     */
    private function searchPatternsInCode($appData, $patterns) {
        if (!isset($appData['source_code'])) {
            return false;
        }
        
        $sourceDir = $appData['source_code']['source_dir'] ?? '';
        if (!$sourceDir || !is_dir($sourceDir)) {
            return false;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['java', 'kt', 'm', 'swift'])) {
                $content = file_get_contents($file->getPathname());
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $content)) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Data Storage Analysis
     */
    private function analyzeDataStorage($appData, $platform) {
        $issues = [];
        
        // Check for unencrypted storage
        $unencryptedStorage = $this->checkUnencryptedStorage($appData, $platform);
        if ($unencryptedStorage) {
            $issues[] = [
                'severity' => 'high',
                'title' => 'Unencrypted Data Storage',
                'description' => 'Sensitive data may be stored without encryption',
                'remediation' => 'Use Android Keystore or iOS Keychain for sensitive data',
                'category' => 'storage'
            ];
        }
        
        // Check for shared preferences (Android)
        if ($platform === 'android') {
            $sharedPrefs = $this->checkSharedPreferences($appData);
            if ($sharedPrefs) {
                $issues[] = [
                    'severity' => 'medium',
                    'title' => 'Sensitive Data in SharedPreferences',
                    'description' => 'Sensitive data stored in SharedPreferences without encryption',
                    'remediation' => 'Use EncryptedSharedPreferences or encrypt data before storage',
                    'category' => 'storage'
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * Check for unencrypted storage patterns
     */
    private function checkUnencryptedStorage($appData, $platform) {
        $patterns = [
            '/SharedPreferences.*edit/',
            '/FileOutputStream/',
            '/FileWriter/',
            '/NSUserDefaults/',
            '/writeToFile:/'
        ];
        
        return $this->searchPatternsInCode($appData, $patterns);
    }
    
    /**
     * Check SharedPreferences usage
     */
    private function checkSharedPreferences($appData) {
        $patterns = [
            '/getSharedPreferences/',
            '/SharedPreferences\.Editor/'
        ];
        
        return $this->searchPatternsInCode($appData, $patterns);
    }
    
    /**
     * Cryptography Analysis
     */
    private function analyzeCryptography($appData, $platform) {
        $issues = [];
        
        // Check for weak cryptographic algorithms
        $weakCrypto = $this->checkWeakCryptography($appData, $platform);
        if (!empty($weakCrypto)) {
            $issues[] = [
                'severity' => 'high',
                'title' => 'Weak Cryptographic Algorithms',
                'description' => 'The app uses weak or deprecated cryptographic algorithms: ' . implode(', ', $weakCrypto),
                'remediation' => 'Use strong cryptographic algorithms (AES-GCM, RSA-OAEP, etc.)',
                'category' => 'cryptography'
            ];
        }
        
        // Check for hardcoded keys
        $hardcodedKeys = $this->findHardcodedKeys($appData);
        if (!empty($hardcodedKeys)) {
            $issues[] = [
                'severity' => 'critical',
                'title' => 'Hardcoded Cryptographic Keys',
                'description' => 'The app contains hardcoded encryption keys',
                'remediation' => 'Use secure key storage (Android Keystore, iOS Keychain)',
                'category' => 'cryptography'
            ];
        }
        
        // Check for custom crypto implementations
        $customCrypto = $this->checkCustomCrypto($appData);
        if ($customCrypto) {
            $issues[] = [
                'severity' => 'high',
                'title' => 'Custom Cryptography Implementation',
                'description' => 'The app uses custom cryptography instead of proven libraries',
                'remediation' => 'Use well-established cryptographic libraries',
                'category' => 'cryptography'
            ];
        }
        
        return $issues;
    }
    
    /**
     * Check for weak cryptography
     */
    private function checkWeakCryptography($appData, $platform) {
        $weakAlgorithms = [];
        $patterns = [
            '/DES/' => 'DES',
            '/RC4/' => 'RC4',
            '/MD5/' => 'MD5',
            '/SHA1/' => 'SHA-1',
            '/ECB/' => 'ECB mode',
            '/PBEWithMD5AndDES/' => 'Weak PBE'
        ];
        
        if (isset($appData['source_code'])) {
            $sourceDir = $appData['source_code']['source_dir'] ?? '';
            if ($sourceDir && is_dir($sourceDir)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS)
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile() && in_array($file->getExtension(), ['java', 'kt', 'm', 'swift'])) {
                        $content = file_get_contents($file->getPathname());
                        foreach ($patterns as $pattern => $algorithm) {
                            if (preg_match($pattern, $content) && !in_array($algorithm, $weakAlgorithms)) {
                                $weakAlgorithms[] = $algorithm;
                            }
                        }
                    }
                }
            }
        }
        
        return $weakAlgorithms;
    }
    
    /**
     * Find hardcoded keys
     */
    private function findHardcodedKeys($appData) {
        $keys = [];
        $keyPatterns = [
            '/["\']([A-Za-z0-9+/=]{16,})["\']/' => 'Potential encryption key',
            '/key.*=.*["\']([^"\']{16,})["\']/i' => 'Hardcoded key',
            '/secret.*=.*["\']([^"\']{16,})["\']/i' => 'Hardcoded secret'
        ];
        
        if (isset($appData['source_code'])) {
            $sourceDir = $appData['source_code']['source_dir'] ?? '';
            if ($sourceDir && is_dir($sourceDir)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS)
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile() && in_array($file->getExtension(), ['java', 'kt', 'm', 'swift'])) {
                        $content = file_get_contents($file->getPathname());
                        foreach ($keyPatterns as $pattern => $keyType) {
                            if (preg_match_all($pattern, $content, $matches)) {
                                foreach ($matches[1] as $match) {
                                    if (strlen($match) >= 16) { // Reasonable minimum for a key
                                        $keys[] = [
                                            'type' => $keyType,
                                            'value' => substr($match, 0, 20) . '...', // Don't expose full key
                                            'file' => str_replace($sourceDir, '', $file->getPathname())
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $keys;
    }
    
    /**
     * Check for custom crypto
     */
    private function checkCustomCrypto($appData) {
        $customPatterns = [
            '/my.*crypto/i',
            '/custom.*encrypt/i',
            '/encrypt.*function/',
            '/decrypt.*function/'
        ];
        
        return $this->searchPatternsInCode($appData, $customPatterns);
    }
    
    /**
     * API Security Analysis
     */
    private function analyzeAPISecurity($appData, $platform) {
        $issues = [];
        
        // Check for API key exposure
        $apiKeys = $this->findAPIKeys($appData);
        if (!empty($apiKeys)) {
            $issues[] = [
                'severity' => 'high',
                'title' => 'API Keys Found in Code',
                'description' => 'The app contains hardcoded API keys',
                'remediation' => 'Use secure storage or backend services for API keys',
                'category' => 'api'
            ];
        }
        
        return $issues;
    }
    
    /**
     * Find API keys in code
     */
    private function findAPIKeys($appData) {
        $apiKeys = [];
        $apiKeyPatterns = [
            '/api[_-]?key["\']?\\s*[:=]\\s*["\']([A-Za-z0-9_\-]{20,50})["\']/i',
            '/["\'](AKIA[0-9A-Z]{16})["\']/', // AWS Access Key
            '/["\'](sk_[a-zA-Z0-9]{24})["\']/', // Stripe Secret Key
            '/["\'](gh[pousr]_[A-Za-z0-9_]{36})["\']/' // GitHub Token
        ];
        
        if (isset($appData['source_code'])) {
            $sourceDir = $appData['source_code']['source_dir'] ?? '';
            if ($sourceDir && is_dir($sourceDir)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS)
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $content = file_get_contents($file->getPathname());
                        foreach ($apiKeyPatterns as $pattern) {
                            if (preg_match_all($pattern, $content, $matches)) {
                                foreach ($matches[1] as $match) {
                                    $apiKeys[] = [
                                        'key' => substr($match, 0, 8) . '...', // Partial exposure
                                        'file' => str_replace($sourceDir, '', $file->getPathname()),
                                        'pattern' => $pattern
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $apiKeys;
    }
    
    /**
     * Analyze iOS binary
     */
    private function analyzeIOSBinary($binary) {
        $issues = [];
        $binaryPath = $binary['path'] ?? '';
        
        if (!$binaryPath || !file_exists($binaryPath)) {
            return $issues;
        }
        
        // Check binary security flags
        $command = 'otool -hv ' . escapeshellarg($binaryPath) . ' 2>&1';
        $output = shell_exec($command);
        
        if ($output) {
            // Check for PIE (Position Independent Executable)
            if (strpos($output, 'PIE') === false) {
                $issues[] = [
                    'severity' => 'medium',
                    'title' => 'PIE Not Enabled',
                    'description' => 'Binary is not compiled as Position Independent Executable',
                    'remediation' => 'Enable PIE in build settings',
                    'category' => 'binary'
                ];
            }
            
            // Check for stack canaries
            if (strpos($output, 'stack_canary') === false) {
                $issues[] = [
                    'severity' => 'medium',
                    'title' => 'Stack Canaries Not Enabled',
                    'description' => 'Stack smashing protection is not enabled',
                    'remediation' => 'Enable stack protection in compiler flags',
                    'category' => 'binary'
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * Analyze iOS entitlements
     */
    private function analyzeIOSEntitlements($entitlements) {
        $issues = [];
        
        if (empty($entitlements)) {
            return $issues;
        }
        
        // Check for excessive entitlements
        $riskyEntitlements = [
            'get-task-allow' => 'Debugging enabled',
            'com.apple.developer.associated-domains' => 'Associated domains',
            'com.apple.security.application-groups' => 'App groups sharing'
        ];
        
        foreach ($riskyEntitlements as $entitlement => $description) {
            if (isset($entitlements[$entitlement])) {
                $issues[] = [
                    'severity' => 'medium',
                    'title' => "Risky Entitlement: $description",
                    'description' => "The app uses $entitlement entitlement",
                    'remediation' => 'Review if this entitlement is necessary',
                    'category' => 'entitlements'
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * Analyze Info.plist
     */
    private function analyzeInfoPlist($plist) {
        $issues = [];
        
        if (empty($plist)) {
            return $issues;
        }
        
        // Check for ATS (App Transport Security) exceptions
        $atsConfig = $plist['NSAppTransportSecurity'] ?? [];
        if (isset($atsConfig['NSAllowsArbitraryLoads']) && $atsConfig['NSAllowsArbitraryLoads']) {
            $issues[] = [
                'severity' => 'high',
                'title' => 'ATS Bypass Enabled',
                'description' => 'App allows arbitrary loads, bypassing ATS security',
                'remediation' => 'Remove NSAllowsArbitraryLoads and use proper ATS configuration',
                'category' => 'plist'
            ];
        }
        
        // Check for exposed URL schemes
        $urlTypes = $plist['CFBundleURLTypes'] ?? [];
        foreach ($urlTypes as $urlType) {
            $schemes = $urlType['CFBundleURLSchemes'] ?? [];
            foreach ($schemes as $scheme) {
                if (strpos($scheme, 'http') === 0) {
                    $issues[] = [
                        'severity' => 'medium',
                        'title' => 'HTTP URL Scheme',
                        'description' => "App registers HTTP URL scheme: $scheme",
                        'remediation' => 'Use custom URL schemes instead of HTTP',
                        'category' => 'plist'
                    ];
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Analyze hybrid framework
     */
    private function analyzeHybridFramework($appData, $framework) {
        $issues = [];
        
        switch ($framework) {
            case 'react-native':
                $issues = $this->analyzeReactNative($appData);
                break;
            case 'flutter':
                $issues = $this->analyzeFlutter($appData);
                break;
            case 'cordova':
            case 'ionic':
                $issues = $this->analyzeCordova($appData);
                break;
        }
        
        return $issues;
    }
    
    /**
     * Analyze React Native app
     */
    private function analyzeReactNative($appData) {
        $issues = [];
        
        // Check for React Native specific issues
        $patterns = [
            '/__DEV__/' => 'Development mode enabled',
            '/console\.log/' => 'Debug logging',
            '/react-native-config/' => 'Potential config exposure'
        ];
        
        if ($this->searchPatternsInCode($appData, array_keys($patterns))) {
            $issues[] = [
                'severity' => 'medium',
                'title' => 'React Native Development Artifacts',
                'description' => 'The app contains React Native development code',
                'remediation' => 'Remove development code and use production builds',
                'category' => 'hybrid'
            ];
        }
        
        return $issues;
    }
    
    /**
     * Analyze Flutter app
     */
    private function analyzeFlutter($appData) {
        $issues = [];
        
        // Check for Flutter specific issues
        $issues[] = [
            'severity' => 'low',
            'title' => 'Flutter Framework Detected',
            'description' => 'The app uses Flutter framework',
            'remediation' => 'Ensure Flutter security best practices are followed',
            'category' => 'hybrid'
        ];
        
        return $issues;
    }
    
    /**
     * Analyze Cordova app
     */
    private function analyzeCordova($appData) {
        $issues = [];
        
        // Check for Cordova specific issues
        $patterns = [
            '/cordova\.exec/' => 'Cordova bridge usage',
            '/window\.webkit/' => 'WebView messaging'
        ];
        
        if ($this->searchPatternsInCode($appData, array_keys($patterns))) {
            $issues[] = [
                'severity' => 'medium',
                'title' => 'Cordova WebView Bridge',
                'description' => 'The app uses Cordova WebView bridge',
                'remediation' => 'Validate all WebView messages and use CSP',
                'category' => 'hybrid'
            ];
        }
        
        return $issues;
    }
    
    /**
     * Analyze WebView security
     */
    private function analyzeWebViewSecurity($appData) {
        $issues = [];
        
        $patterns = [
            '/setJavaScriptEnabled.*true/' => 'JavaScript enabled in WebView',
            '/setAllowFileAccess.*true/' => 'File access enabled in WebView',
            '/WebViewClient/' => 'Custom WebView client'
        ];
        
        if ($this->searchPatternsInCode($appData, array_keys($patterns))) {
            $issues[] = [
                'severity' => 'medium',
                'title' => 'WebView Security Configuration',
                'description' => 'The app uses WebView with potential security issues',
                'remediation' => 'Implement proper WebView security controls',
                'category' => 'webview'
            ];
        }
        
        return $issues;
    }
    
    /**
     * Analyze native bridge
     */
    private function analyzeNativeBridge($appData, $framework) {
        $issues = [];
        
        $patterns = [
            '/@JavascriptInterface/' => 'JavaScript interface',
            '/WebView\.addJavascriptInterface/' => 'Javascript interface addition'
        ];
        
        if ($this->searchPatternsInCode($appData, array_keys($patterns))) {
            $issues[] = [
                'severity' => 'high',
                'title' => 'Native Bridge Exposure',
                'description' => 'The app exposes native methods to WebView',
                'remediation' => 'Validate all bridge calls and implement proper security',
                'category' => 'bridge'
            ];
        }
        
        return $issues;
    }
    
    /**
     * Analyze resources
     */
    private function analyzeResources($resources) {
        $issues = [];
        
        if (isset($resources['strings'])) {
            foreach ($resources['strings'] as $string) {
                if ($string['risk'] === 'high') {
                    $issues[] = [
                        'severity' => 'high',
                        'title' => 'Potential Secret in Resources',
                        'description' => "Found {$string['type']} in strings: {$string['name']}",
                        'remediation' => 'Remove hardcoded secrets from resources',
                        'category' => 'resources'
                    ];
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Analyze certificates
     */
    private function analyzeCertificates($certificates) {
        $issues = [];
        
        foreach ($certificates as $cert) {
            // Check certificate expiration
            if (isset($cert['validity']['not_after'])) {
                $expiry = strtotime($cert['validity']['not_after']);
                if ($expiry && $expiry < time()) {
                    $issues[] = [
                        'severity' => 'high',
                        'title' => 'Expired Certificate',
                        'description' => "Certificate expired on {$cert['validity']['not_after']}",
                        'remediation' => 'Update app with new certificate',
                        'category' => 'certificates'
                    ];
                }
            }
            
            // Check for debug certificates
            if (stripos($cert['subject'] ?? '', 'debug') !== false ||
                stripos($cert['issuer'] ?? '', 'debug') !== false) {
                $issues[] = [
                    'severity' => 'high',
                    'title' => 'Debug Certificate',
                    'description' => 'App signed with debug certificate',
                    'remediation' => 'Use production signing certificate',
                    'category' => 'certificates'
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * OWASP MASVS Compliance Check
     */
    private function checkMASVSCompliance($analysis, $platform) {
        $compliance = [];
        $masvsRequirements = $this->getMASVSRequirements($platform);
        
        foreach ($masvsRequirements as $requirement) {
            $compliance[$requirement['id']] = [
                'requirement' => $requirement['description'],
                'category' => $requirement['category'],
                'compliant' => $this->checkRequirementCompliance($requirement, $analysis),
                'evidence' => $this->getComplianceEvidence($requirement, $analysis)
            ];
        }
        
        return $compliance;
    }
    
    /**
     * Get MASVS requirements
     */
    private function getMASVSRequirements($platform) {
        $requirements = [
            [
                'id' => 'MASVS-STORAGE-1',
                'description' => 'System credential storage facilities need to be used to store sensitive data, such as PII, user credentials or cryptographic keys.',
                'category' => 'storage'
            ],
            [
                'id' => 'MASVS-CRYPTO-1',
                'description' => 'The app uses cryptographic primitives that are appropriate for the particular use-case, configured with parameters that adhere to industry best practices.',
                'category' => 'cryptography'
            ],
            [
                'id' => 'MASVS-PLATFORM-1',
                'description' => 'The app only requests the minimum set of permissions necessary.',
                'category' => 'permissions'
            ],
            [
                'id' => 'MASVS-NETWORK-1',
                'description' => 'Data is encrypted on the network using TLS. The secure channel is used consistently throughout the app.',
                'category' => 'network'
            ]
        ];
        
        return $requirements;
    }
    
    /**
     * Check requirement compliance
     */
    private function checkRequirementCompliance($requirement, $analysis) {
        switch ($requirement['id']) {
            case 'MASVS-STORAGE-1':
                return !$this->hasUnencryptedStorageIssues($analysis);
            case 'MASVS-CRYPTO-1':
                return empty($analysis['cryptography'] ?? []);
            case 'MASVS-PLATFORM-1':
                return $this->hasReasonablePermissions($analysis);
            case 'MASVS-NETWORK-1':
                return !$this->hasNetworkSecurityIssues($analysis);
            default:
                return false;
        }
    }
    
    /**
     * Check for unencrypted storage issues
     */
    private function hasUnencryptedStorageIssues($analysis) {
        $storageIssues = $analysis['storage'] ?? [];
        foreach ($storageIssues as $issue) {
            if ($issue['severity'] === 'high' || $issue['severity'] === 'critical') {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check for reasonable permissions
     */
    private function hasReasonablePermissions($analysis) {
        $permissions = $analysis['permissions'] ?? [];
        $dangerousCount = count($permissions['dangerous_permissions'] ?? []);
        return $dangerousCount <= 5; // Arbitrary threshold
    }
    
    /**
     * Check for network security issues
     */
    private function hasNetworkSecurityIssues($analysis) {
        $networkIssues = $analysis['network'] ?? [];
        foreach ($networkIssues as $issue) {
            if ($issue['severity'] === 'high' || $issue['severity'] === 'critical') {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get compliance evidence
     */
    private function getComplianceEvidence($requirement, $analysis) {
        switch ($requirement['id']) {
            case 'MASVS-STORAGE-1':
                $issues = $analysis['storage'] ?? [];
                return empty($issues) ? 'No storage security issues found' : 'Storage security issues detected';
            default:
                return 'Automated check completed';
        }
    }
    
    /**
     * Generate comprehensive security report
     */
    private function generateSecurityReport($analysis, $platform, $scanType) {
        // Calculate security score
        $securityScore = $this->calculateSecurityScore($analysis);
        
        // Generate recommendations
        $recommendations = $this->generateRecommendations($analysis);
        
        // Format vulnerabilities
        $vulnerabilities = $this->formatVulnerabilities($analysis);
        
        return [
            'security_score' => $securityScore,
            'platform' => $platform,
            'scan_type' => $scanType,
            'vulnerabilities' => $vulnerabilities,
            'analysis_details' => $analysis,
            'recommendations' => $recommendations,
            'summary' => $this->generateSummary($vulnerabilities),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Calculate overall security score
     */
    private function calculateSecurityScore($analysis) {
        $baseScore = 100;
        $deductions = 0;
        
        // Flatten all issues
        $allIssues = [];
        foreach ($analysis as $category => $data) {
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (isset($item['severity'])) {
                        $allIssues[] = $item;
                    } elseif (is_array($item)) {
                        foreach ($item as $subItem) {
                            if (isset($subItem['severity'])) {
                                $allIssues[] = $subItem;
                            }
                        }
                    }
                }
            }
        }
        
        // Calculate deductions based on severity
        foreach ($allIssues as $issue) {
            switch ($issue['severity']) {
                case 'critical':
                    $deductions += 10;
                    break;
                case 'high':
                    $deductions += 7;
                    break;
                case 'medium':
                    $deductions += 4;
                    break;
                case 'low':
                    $deductions += 1;
                    break;
            }
        }
        
        $score = max(0, $baseScore - $deductions);
        return min(100, $score);
    }
    
    /**
     * Generate security recommendations
     */
    private function generateRecommendations($analysis) {
        $recommendations = [];
        
        foreach ($analysis as $category => $data) {
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (isset($item['remediation'])) {
                        $recommendations[] = [
                            'category' => $item['category'] ?? $category,
                            'issue' => $item['title'] ?? 'Security Issue',
                            'recommendation' => $item['remediation'],
                            'priority' => $item['severity'] ?? 'medium'
                        ];
                    } elseif (is_array($item)) {
                        foreach ($item as $subItem) {
                            if (isset($subItem['remediation'])) {
                                $recommendations[] = [
                                    'category' => $subItem['category'] ?? $category,
                                    'issue' => $subItem['title'] ?? 'Security Issue',
                                    'recommendation' => $subItem['remediation'],
                                    'priority' => $subItem['severity'] ?? 'medium'
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Format vulnerabilities for output
     */
    private function formatVulnerabilities($analysis) {
        $vulnerabilities = [];
        
        foreach ($analysis as $category => $data) {
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (isset($item['severity'])) {
                        $vulnerabilities[] = [
                            'category' => $item['category'] ?? $category,
                            'severity' => $item['severity'],
                            'title' => $item['title'] ?? 'Security Issue',
                            'description' => $item['description'] ?? '',
                            'remediation' => $item['remediation'] ?? '',
                            'file' => $item['file'] ?? '',
                            'line' => $item['line'] ?? ''
                        ];
                    } elseif (is_array($item)) {
                        foreach ($item as $subItem) {
                            if (isset($subItem['severity'])) {
                                $vulnerabilities[] = [
                                    'category' => $subItem['category'] ?? $category,
                                    'severity' => $subItem['severity'],
                                    'title' => $subItem['title'] ?? 'Security Issue',
                                    'description' => $subItem['description'] ?? '',
                                    'remediation' => $subItem['remediation'] ?? '',
                                    'file' => $subItem['file'] ?? '',
                                    'line' => $subItem['line'] ?? ''
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        return $vulnerabilities;
    }
    
    /**
     * Generate summary statistics
     */
    private function generateSummary($vulnerabilities) {
        $summary = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'total' => count($vulnerabilities)
        ];
        
        foreach ($vulnerabilities as $vuln) {
            $severity = $vuln['severity'] ?? 'low';
            if (isset($summary[$severity])) {
                $summary[$severity]++;
            }
        }
        
        return $summary;
    }
    
    /**
     * Clean up temporary files
     */
    private function cleanupTempFiles($tempDir) {
        if (is_dir($tempDir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                try {
                    $todo($fileinfo->getRealPath());
                } catch (Exception $e) {
                    // Log cleanup errors but don't fail
                    error_log('Cleanup error: ' . $e->getMessage());
                }
            }
            
            try {
                rmdir($tempDir);
            } catch (Exception $e) {
                error_log('Cleanup error: ' . $e->getMessage());
            }
        }
    }
}
?>