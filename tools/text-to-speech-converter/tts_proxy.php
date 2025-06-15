<?php
// File: tts_proxy.php

ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- Enhanced Configuration ---
define('AUDIO_DIR', __DIR__ . '/audio/');
define('ALLOWED_ORIGIN', 'https://app.andrias.web.id');

// --- Rate Limiting ---
define('RATE_LIMIT_DIR', __DIR__ . '/rate_limit_logs/');
define('RATE_LIMIT_COUNT', 30); // 30 requests per hour
define('RATE_LIMIT_WINDOW_SECONDS', 3600);

// --- Pollinations AI TTS API Configuration ---
define('TTS_API_BASE', 'https://text.pollinations.ai/');

/**
 * Enhanced error logging with context
 */
function logError($message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' Context: ' . json_encode($context) : '';
    error_log("[$timestamp] TTS Converter: $message$contextStr");
}

/**
 * Enhanced response helper
 */
function sendResponse($success, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => time()
    ];
    
    if ($data !== null) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response, JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Enhanced rate limiting with IP tracking
 */
function check_rate_limit($ip) {
    if (!is_dir(RATE_LIMIT_DIR)) {
        if (!mkdir(RATE_LIMIT_DIR, 0755, true)) {
            logError("Failed to create rate limit directory");
            return true; // Allow if can't create directory
        }
    }
    
    $log_file = RATE_LIMIT_DIR . md5($ip) . '.json';
    $current_time = time();
    $ip_data = ['count' => 0, 'first_request_time' => $current_time];
    
    if (file_exists($log_file)) {
        $data = json_decode(file_get_contents($log_file), true);
        if ($data && ($current_time - $data['first_request_time']) < RATE_LIMIT_WINDOW_SECONDS) {
            $ip_data = $data;
        }
    }
    
    if ($ip_data['count'] >= RATE_LIMIT_COUNT) {
        return false;
    }
    
    $ip_data['count']++;
    $ip_data['last_request_time'] = $current_time;
    file_put_contents($log_file, json_encode($ip_data));
    return true;
}

/**
 * Enhanced text validation and sanitization
 */
function validateAndSanitizeText($text) {
    $text = trim($text);
    
    if (empty($text)) {
        return ['valid' => false, 'message' => 'Text cannot be empty'];
    }
    
    if (strlen($text) > 5000) {
        return ['valid' => false, 'message' => 'Text too long (max 5000 characters)'];
    }
    
    // Remove potentially harmful content but keep basic punctuation
    $text = preg_replace('/[^\p{L}\p{N}\p{P}\p{S}\p{Z}\r\n]/u', '', $text);
    
    return ['valid' => true, 'text' => $text];
}

/**
 * Validate voice parameter
 */
function validateVoice($voice) {
    $allowedVoices = [
        'alloy', 'echo', 'fable', 'onyx', 'nova', 'shimmer', 'coral', 'verse', 
        'ballad', 'ash', 'sage', 'amuch', 'aster', 'brook', 'clover', 'dan', 
        'elan', 'marilyn', 'meadow', 'jazz', 'rio', 'megan-wetherall', 'jade-hardy', 
        'megan-wetherall-2025-03-07', 'jade-hardy-2025-03-07'
    ];
    
    return in_array($voice, $allowedVoices) ? $voice : 'rio';
}

/**
 * Generate speech using Pollinations AI TTS API
 */
function generateSpeech($text, $voice, $speed, $pitch) {
    // Create audio directory if it doesn't exist
    if (!is_dir(AUDIO_DIR)) {
        if (!mkdir(AUDIO_DIR, 0755, true)) {
            logError("Failed to create audio directory");
            return null;
        }
    }
    
    // URL encode the text for the API
    $encodedText = urlencode($text);
    
    // Build the API URL
    $apiUrl = TTS_API_BASE . $encodedText . '?model=openai-audio&voice=' . $voice;
    
    // Add speed and pitch parameters if supported by the API
    if ($speed != 1.0) {
        $apiUrl .= '&speed=' . $speed;
    }
    if ($pitch != 0) {
        $apiUrl .= '&pitch=' . $pitch;
    }
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; TTS-Converter/1.0)',
        CURLOPT_HTTPHEADER => [
            'Accept: audio/*',
            'Cache-Control: no-cache'
        ]
    ]);
    
    $audioData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Check for errors
    if ($curlError || $httpCode !== 200) {
        logError("TTS API request failed", [
            'http_code' => $httpCode,
            'curl_error' => $curlError,
            'voice' => $voice,
            'text_length' => strlen($text)
        ]);
        return null;
    }
    
    // Validate that we received audio data
    if (empty($audioData) || !str_contains($contentType, 'audio')) {
        logError("Invalid audio response from TTS API", [
            'content_type' => $contentType,
            'data_length' => strlen($audioData)
        ]);
        return null;
    }
    
    // Generate unique filename
    $filename = 'tts_' . time() . '_' . md5($text . $voice . $speed . $pitch) . '.mp3';
    $filepath = AUDIO_DIR . $filename;
    
    // Save the audio file
    if (file_put_contents($filepath, $audioData) === false) {
        logError("Failed to save audio file", ['filepath' => $filepath]);
        return null;
    }
    
    // Return the URL to access the audio file
    $baseUrl = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/audio/';
    return $baseUrl . $filename;
}

/**
 * Clean up old audio files (older than 24 hours)
 */
function cleanupOldFiles() {
    if (!is_dir(AUDIO_DIR)) {
        return;
    }
    
    $files = glob(AUDIO_DIR . 'tts_*.mp3');
    $cutoffTime = time() - (24 * 60 * 60); // 24 hours ago
    
    foreach ($files as $file) {
        if (filemtime($file) < $cutoffTime) {
            unlink($file);
        }
    }
}

// --- Main Logic ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Only POST method allowed', null, 405);
}

// Enhanced origin validation
$request_origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$normalized_origin = rtrim($request_origin, '/');
$normalized_allowed = rtrim(ALLOWED_ORIGIN, '/');

if (!empty($request_origin) && $normalized_origin !== $normalized_allowed) {
    logError("Unauthorized origin access", ['origin' => $request_origin]);
    sendResponse(false, 'Access denied', null, 403);
}

// Enhanced rate limiting
$ip_address = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!check_rate_limit($ip_address)) {
    sendResponse(false, 'Rate limit exceeded. Please try again in an hour.', null, 429);
}

// Enhanced input validation
$input_data = json_decode(file_get_contents('php://input'), true);
if (!$input_data) {
    sendResponse(false, 'Invalid JSON input', null, 400);
}

// Validate text
$textValidation = validateAndSanitizeText($input_data['text'] ?? '');
if (!$textValidation['valid']) {
    sendResponse(false, $textValidation['message'], null, 400);
}

$text = $textValidation['text'];
$voice = validateVoice($input_data['voice'] ?? 'rio');
$speed = floatval($input_data['speed'] ?? 1.0);
$pitch = intval($input_data['pitch'] ?? 0);

// Validate speed and pitch ranges
$speed = max(0.5, min(2.0, $speed));
$pitch = max(-50, min(50, $pitch));

// Clean up old files periodically (10% chance)
if (rand(1, 10) === 1) {
    cleanupOldFiles();
}

// Generate speech
$audioUrl = generateSpeech($text, $voice, $speed, $pitch);
if (!$audioUrl) {
    sendResponse(false, 'Failed to generate speech. Please try again later.', null, 502);
}

// Enhanced success response
sendResponse(true, 'Speech generated successfully!', [
    'audioUrl' => $audioUrl,
    'metadata' => [
        'voice' => $voice,
        'speed' => $speed,
        'pitch' => $pitch,
        'textLength' => strlen($text)
    ]
]);
?>