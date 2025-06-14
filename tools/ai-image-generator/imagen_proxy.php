<?php
// File: imagen_proxy.php

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
define('HISTORY_DIR', __DIR__ . '/history/');
define('ALLOWED_ORIGIN', 'https://app.andrias.web.id');

// --- ImageKit.io Configuration ---
define('IMAGEKIT_PUBLIC_KEY', 'public_I7wfMAWEVbcai9/DN1cgr2vFk+0=');
define('IMAGEKIT_PRIVATE_KEY', 'private_bIH4qZI8CHPpjaUsY3+QTFvsv8s=');
define('IMAGEKIT_UPLOAD_URL', 'https://upload.imagekit.io/api/v1/files/upload');

// --- Enhanced Rate Limiting ---
define('RATE_LIMIT_DIR', __DIR__ . '/rate_limit_logs/');
define('RATE_LIMIT_COUNT', 50); // Increased from 40
define('RATE_LIMIT_WINDOW_SECONDS', 3600);

/**
 * Enhanced error logging with context
 */
function logError($message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' Context: ' . json_encode($context) : '';
    error_log("[$timestamp] AI Image Generator: $message$contextStr");
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
 * Enhanced ImageKit upload with retry mechanism
 */
function upload_to_imagekit($imageData, $fileName, $retries = 3) {
    for ($attempt = 1; $attempt <= $retries; $attempt++) {
        $payload = [
            'file' => base64_encode($imageData),
            'fileName' => $fileName,
            'publicKey' => IMAGEKIT_PUBLIC_KEY,
            'folder' => '/ai-generated/' . date('Y/m'), // Organized by year/month
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => IMAGEKIT_UPLOAD_URL,
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_USERPWD => IMAGEKIT_PRIVATE_KEY . ':',
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_USERAGENT => 'AI-Image-Generator/1.0',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json'
            ]
        ]);

        $reply = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($http_code == 200 && !$curl_error) {
            $response = json_decode($reply, true);
            if (isset($response['url'])) {
                return $response['url'];
            }
        }

        logError("ImageKit Upload Attempt $attempt Failed", [
            'http_code' => $http_code,
            'curl_error' => $curl_error,
            'response' => substr($reply, 0, 500)
        ]);

        if ($attempt < $retries) {
            sleep(1); // Wait before retry
        }
    }

    return null;
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
 * Enhanced prompt validation and sanitization
 */
function validateAndSanitizePrompt($prompt) {
    $prompt = trim($prompt);
    
    if (empty($prompt)) {
        return ['valid' => false, 'message' => 'Prompt cannot be empty'];
    }
    
    if (strlen($prompt) > 2000) {
        return ['valid' => false, 'message' => 'Prompt too long (max 2000 characters)'];
    }
    
    // Remove potentially harmful content
    $prompt = preg_replace('/[^\p{L}\p{N}\p{P}\p{S}\p{Z}]/u', '', $prompt);
    
    return ['valid' => true, 'prompt' => $prompt];
}

/**
 * Enhanced image generation with better error handling
 */
function generateImage($prompt, $model, $width, $height, $safeFilter, $seed) {
    $encodedPrompt = rawurlencode($prompt);
    $apiUrl = "https://image.pollinations.ai/prompt/{$encodedPrompt}?" . http_build_query([
        'model' => $model,
        'width' => $width,
        'height' => $height,
        'nologo' => 'true',
        'safe' => $safeFilter,
        'seed' => $seed,
        'enhance' => 'true' // Enhanced quality
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; AI-Image-Generator/1.0)',
        CURLOPT_HTTPHEADER => [
            'Accept: image/*',
            'Cache-Control: no-cache'
        ]
    ]);

    $imageData = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error || $http_code !== 200 || !str_contains($content_type, 'image/')) {
        logError("Image generation failed", [
            'http_code' => $http_code,
            'content_type' => $content_type,
            'curl_error' => $curl_error,
            'model' => $model
        ]);
        return null;
    }

    return $imageData;
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

// Validate prompt
$promptValidation = validateAndSanitizePrompt($input_data['prompt'] ?? '');
if (!$promptValidation['valid']) {
    sendResponse(false, $promptValidation['message'], null, 400);
}

$prompt = $promptValidation['prompt'];
$model = in_array($input_data['model'] ?? '', ['flux', 'turbo', 'dalle3', 'stability']) 
    ? $input_data['model'] : 'flux';
$size = $input_data['size'] ?? '1024x1024';
$safeFilter = isset($input_data['safeFilter']) && $input_data['safeFilter'] === true ? 'true' : 'false';

// Enhanced size validation
$validSizes = ['512x512', '720x1280', '1024x1024', '1280x720', '1792x1024', '1024x1792'];
if (!in_array($size, $validSizes)) {
    $size = '1024x1024';
}

list($width, $height) = explode('x', $size);
$seed = rand(100000, 999999); // Better seed range

// Generate image
$imageData = generateImage($prompt, $model, $width, $height, $safeFilter, $seed);
if (!$imageData) {
    sendResponse(false, 'Failed to generate image. Please try a different model or try again later.', null, 502);
}

// Enhanced file naming
$timestamp = time();
$hash = substr(md5($prompt . $timestamp), 0, 8);
$file_basename = $timestamp . '_' . $hash;
$upload_filename = $file_basename . '.webp';

// Upload to ImageKit with retry
$imageUrl = upload_to_imagekit($imageData, $upload_filename);
if (!$imageUrl) {
    sendResponse(false, 'Failed to save image. Please try again.', null, 502);
}

// Enhanced metadata storage
if (!is_dir(HISTORY_DIR)) {
    mkdir(HISTORY_DIR, 0755, true);
}

$metadata_path = HISTORY_DIR . $file_basename . '.json';
$metadata = [
    'prompt' => $prompt,
    'ip' => $ip_address,
    'timestamp' => $timestamp,
    'imagekit_url' => $imageUrl,
    'model' => $model,
    'size' => "{$width}x{$height}",
    'safe_filter' => $safeFilter === 'true',
    'seed' => $seed,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
];

file_put_contents($metadata_path, json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Enhanced success response
sendResponse(true, 'Image generated successfully!', [
    'imageData' => $imageUrl,
    'imagekitUrl' => $imageUrl,
    'metadata' => [
        'model' => $model,
        'size' => $size,
        'seed' => $seed
    ]
]);
?>