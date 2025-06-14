<?php
// File: imagen_proxy.php

ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

// --- Konfigurasi ---
define('HISTORY_DIR', __DIR__ . '/history/');
define('ALLOWED_ORIGIN', 'https://app.andrias.web.id'); // Domain yang diizinkan untuk mengakses proxy

// --- Konfigurasi ImageKit.io ---
define('IMAGEKIT_PUBLIC_KEY', 'public_I7wfMAWEVbcai9/DN1cgr2vFk+0=');
define('IMAGEKIT_PRIVATE_KEY', 'private_bIH4qZI8CHPpjaUsY3+QTFvsv8s=');
define('IMAGEKIT_UPLOAD_URL', 'https://upload.imagekit.io/api/v1/files/upload');

// --- Konfigurasi Rate Limit ---
define('RATE_LIMIT_DIR', __DIR__ . '/rate_limit_logs/');
define('RATE_LIMIT_COUNT', 40);
define('RATE_LIMIT_WINDOW_SECONDS', 3600);


/**
 * Mengunggah data gambar ke ImageKit.io.
 *
 * @param string $imageData Data biner dari gambar.
 * @param string $fileName Nama file untuk diunggah.
 * @return string|null URL gambar yang diunggah atau null jika gagal.
 */
function upload_to_imagekit($imageData, $fileName) {
    // Payload untuk ImageKit API
    $payload = [
        'file'     => base64_encode($imageData), // Data gambar harus dalam format base64
        'fileName' => $fileName,
        'publicKey'=> IMAGEKIT_PUBLIC_KEY,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, IMAGEKIT_UPLOAD_URL);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_USERPWD, IMAGEKIT_PRIVATE_KEY . ':'); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $reply = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($http_code == 200 && !$curl_error) {
        $response = json_decode($reply, true);
        return $response['url'] ?? null; // Mengembalikan URL dari respons ImageKit
    }

    error_log("ImageKit Upload Failed. Status: " . $http_code . ". Response: " . $reply . ". cURL Error: " . $curl_error);
    return null;
}


/**
 * Memeriksa batasan penggunaan berdasarkan IP.
 * @param string $ip Alamat IP.
 * @return bool True jika diizinkan, false jika dibatasi.
 */
function check_rate_limit($ip) {
    if (!is_dir(RATE_LIMIT_DIR)) {
        if (!mkdir(RATE_LIMIT_DIR, 0755, true)) return true; // Jika gagal membuat folder, lewati saja
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
    if ($ip_data['count'] >= RATE_LIMIT_COUNT) return false;
    $ip_data['count']++;
    file_put_contents($log_file, json_encode($ip_data));
    return true;
}

// --- Logika Utama ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

// **PENAMBAHAN KEAMANAN: Cek asal permintaan (Origin)**
// Memastikan hanya domain yang diizinkan yang bisa mengakses.
$request_origin = $_SERVER['HTTP_ORIGIN'] ?? '';
// Normalisasi URL dengan menghapus trailing slash
$normalized_origin = rtrim($request_origin, '/');
$normalized_allowed = rtrim(ALLOWED_ORIGIN, '/');

if ($normalized_origin !== $normalized_allowed) {
    // Blokir jika origin tidak cocok DAN bukan permintaan kosong (seperti dari Postman/cURL tanpa origin)
    // Untuk keamanan maksimal, Anda bisa menghapus `&& !empty($request_origin)`
    if (!empty($request_origin)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
        exit;
    }
}


$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!check_rate_limit($ip_address)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Anda telah mencapai batas 40 kali generate per jam.']);
    exit;
}

$input_data = json_decode(file_get_contents('php://input'), true);
if (!$input_data || !isset($input_data['prompt']) || empty(trim($input_data['prompt']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Prompt tidak boleh kosong.']);
    exit;
}

$prompt = trim($input_data['prompt']);
$model = $input_data['model'] ?? 'flux';
$size = $input_data['size'] ?? '1024x1024';
$safeFilter = isset($input_data['safeFilter']) && $input_data['safeFilter'] === true ? 'true' : 'false';

list($width, $height) = explode('x', $size);
if (!is_numeric($width) || !is_numeric($height)) {
    list($width, $height) = [1024, 1024];
}
$seed = rand(10000, 99999);
$encodedPrompt = rawurlencode($prompt);
$apiUrl = "https://image.pollinations.ai/prompt/{$encodedPrompt}?model={$model}&width={$width}&height={$height}&nologo=true&safe={$safeFilter}&seed={$seed}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');
$imageData = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error || $http_code !== 200 || strpos($content_type, 'image/') === false) {
    http_response_code($http_code !== 200 ? $http_code : 502);
    error_log("Pollinations API Failed. Status: " . $http_code . ". cURL Error: " . $curl_error . ". Content-Type: " . $content_type);
    echo json_encode(['success' => false, 'message' => 'Gagal menghasilkan gambar dari API eksternal.']);
    exit;
}

// Tentukan nama file unik untuk unggahan
$timestamp = time();
$file_basename = $timestamp . '_' . bin2hex(random_bytes(4));
$upload_filename = $file_basename . '.webp';

// Unggah ke ImageKit.io
$imageUrl = upload_to_imagekit($imageData, $upload_filename);

if (!$imageUrl) {
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => 'Gagal mengunggah gambar ke layanan penyimpanan.']);
    exit;
}

// Proses penyimpanan riwayat (tanpa menghapus file lama)
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
    'size' => "{$width}x{$height}"
];
file_put_contents($metadata_path, json_encode($metadata, JSON_PRETTY_PRINT));


// Kirim respons sukses ke frontend
echo json_encode([
    'success' => true,
    'imageData' => $imageUrl,
    'imagekitUrl' => $imageUrl
]);
exit;
?>
