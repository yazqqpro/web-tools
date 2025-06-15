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
 * Generate speech using Web Speech API simulation (for demo)
 * In production, you would integrate with services like:
 * - Azure Cognitive Services Speech
 * - Google Cloud Text-to-Speech
 * - Amazon Polly
 * - ElevenLabs API
 */
function generateSpeech($text, $voice, $speed, $pitch) {
    // Create audio directory if it doesn't exist
    if (!is_dir(AUDIO_DIR)) {
        if (!mkdir(AUDIO_DIR, 0755, true)) {
            logError("Failed to create audio directory");
            return null;
        }
    }
    
    // For demo purposes, we'll create a simple audio file placeholder
    // In production, replace this with actual TTS API calls
    
    $filename = 'tts_' . time() . '_' . md5($text . $voice) . '.mp3';
    $filepath = AUDIO_DIR . $filename;
    
    // Simulate TTS processing time
    usleep(rand(500000, 2000000)); // 0.5-2 seconds
    
    // For demo: create a simple audio file (in production, this would be the actual TTS output)
    // This is just a placeholder - replace with actual TTS service integration
    $demoAudioContent = base64_decode('SUQzBAAAAAAAI1RTU0UAAAAPAAADTGF2ZjU4Ljc2LjEwMAAAAAAAAAAAAAAA//tQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAWGluZwAAAA8AAAAEAAABIADAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDV1dXV1dXV1dXV1dXV1dXV1dXV1dXV1dXV6urq6urq6urq6urq6urq6urq6urq6urq6v////////////////////////////////8AAAAATGF2YzU4LjEzAAAAAAAAAAAAAAAAJAAAAAAAAAAAASDs90hvAAAAAAAAAAAAAAAAAAAA//tQxAADwAABpAAAACAAADSAAAAETEFNRTMuMTAwVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVQ==');
    
    if (file_put_contents($filepath, $demoAudioContent) === false) {
        logError("Failed to create audio file", ['filepath' => $filepath]);
        return null;
    }
    
    // Return the URL to access the audio file
    $base_url = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/audio/';
    return $base_url . $filename;
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
$voice = $input_data['voice'] ?? 'en-US-AriaNeural';
$speed = floatval($input_data['speed'] ?? 1.0);
$pitch = intval($input_data['pitch'] ?? 0);

// Validate voice parameter
$allowedVoices = [
    'en-US-AriaNeural', 'en-US-DavisNeural', 'en-US-JennyNeural', 'en-US-GuyNeural',
    'en-GB-SoniaNeural', 'en-GB-RyanNeural', 'id-ID-ArdiNeural', 'id-ID-GadisNeural'
];

if (!in_array($voice, $allowedVoices)) {
    $voice = 'en-US-AriaNeural';
}

// Validate speed and pitch ranges
$speed = max(0.5, min(2.0, $speed));
$pitch = max(-50, min(50, $pitch));

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