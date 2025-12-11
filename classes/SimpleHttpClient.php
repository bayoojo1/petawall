<?php

class SimpleHttpClient implements HttpClient {
    public function get($url) {
        return $this->getWithHeaders($url)['content'] ?? '';
    }
    
    public function post($url, $data) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'PhishingDetector/1.0',
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }

    public function getWithHeaders($url) {
        // Try cURL first (most reliable)
        $result = $this->curlRequest($url);
        if (!$result['error']) {
            return $result;
        }
        
        error_log("cURL failed, trying fallback: " . $result['error']);
        
        // Fallback to file_get_contents with stream context
        $result = $this->fileGetContentsRequest($url);
        if (!$result['error']) {
            return $result;
        }
        
        error_log("All methods failed for URL: " . $url);
        return $result;
    }
    
    private function curlRequest($url) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HEADER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_COOKIEFILE => '', // Enable cookie handling
        ]);
        
        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($response === false) {
            return [
                'error' => 'cURL error: ' . $error,
                'error_type' => 'CONNECTION_ERROR',
                'http_status' => 0
            ];
        }
        
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $content = substr($response, $header_size);
        
        return [
            'content' => $content,
            'http_status' => (int)$http_status,
            'headers' => $this->parseHeaders($headers),
            'raw_headers' => $headers,
            'error' => null
        ];
    }
    
    private function fileGetContentsRequest($url) {
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);
        
        $content = @file_get_contents($url, false, $context);
        
        if ($content === false) {
            $error = error_get_last();
            return [
                'error' => 'file_get_contents error: ' . ($error['message'] ?? 'Unknown error'),
                'error_type' => 'CONNECTION_ERROR',
                'http_status' => 0
            ];
        }
        
        $http_response_header = $http_response_header ?? [];
        $status_line = $http_response_header[0] ?? '';
        preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
        $http_status = $match[1] ?? 200;
        
        return [
            'content' => $content,
            'http_status' => (int)$http_status,
            'headers' => $this->parseFileGetContentsHeaders($http_response_header),
            'error' => null
        ];
    }
    
    private function parseHeaders($header_string) {
        $headers = [];
        $lines = explode("\r\n", $header_string);
        
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }
        
        return $headers;
    }
    
    private function parseFileGetContentsHeaders($http_response_header) {
        $headers = [];
        
        foreach ($http_response_header as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }
        
        return $headers;
    }
}