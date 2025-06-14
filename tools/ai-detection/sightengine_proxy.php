<?php
// sightengine_proxy.php

header('Content-Type: application/json');

// --- PENGATURAN KEAMANAN & KREDENSIAL ---
// Ganti dengan API User dan Secret Anda
define('SIGHTENGINE_API_USER', '87536531');
define('SIGHTENGINE_API_SECRET', 'RTiFGNfbBppjy3TiBWdrMixkJVTvyrzo');

// Keamanan: Hanya izinkan permintaan dari domain Anda
$allowed_referer = 'https://app.andrias.web.id'; // Ganti dengan domain Anda
if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $allowed_referer) !== 0) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'error' => ['message' => 'Akses ditolak.']]);
    exit;
}

// Hanya izinkan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'error' => ['message' => 'Metode tidak diizinkan.']]);
    exit;
}

// --- LOGIKA PEMROSESAN BERDASARKAN TIPE ANALISIS ---
// Mengambil tipe analisis yang dikirim dari frontend
$analysis_type = $_POST['analysis_type'] ?? '';

// Mengarahkan permintaan ke fungsi yang sesuai berdasarkan tipe analisis
switch ($analysis_type) {
    case 'image_api':
        handle_image_api_request();
        break;
    case 'text_moderation':
        handle_text_moderation_request();
        break;
    case 'ai_detection':
        handle_ai_detection_request();
        break;
    default:
        // Jika tipe analisis tidak dikenal, kirim error
        http_response_code(400);
        echo json_encode(['status' => 'error', 'error' => ['message' => 'Tipe analisis tidak valid.']]);
        exit;
}

// --- FUNGSI UNTUK MENANGANI SETIAP TIPE REQUEST ---

/**
 * Menangani permintaan untuk moderasi gambar umum (nudity, WAD, dll.).
 * Menargetkan endpoint check.json.
 */
function handle_image_api_request() {
    // Validasi file gambar dengan detail error
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        $error_message = get_upload_error_message($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE);
        echo json_encode(['status' => 'error', 'error' => ['message' => $error_message]]);
        exit;
    }

    // Ambil model yang dipilih dari checkbox, default ke 'nudity,wad' jika tidak ada yang dipilih
    $models = isset($_POST['models']) && is_array($_POST['models']) ? implode(',', $_POST['models']) : 'nudity,wad';

    // Siapkan parameter untuk API
    $params = [
        'models' => $models,
        'api_user' => SIGHTENGINE_API_USER,
        'api_secret' => SIGHTENGINE_API_SECRET,
        // PERBAIKAN: Ganti key dari 'file' menjadi 'media' sesuai permintaan API
        'media' => new CURLFile($_FILES['image']['tmp_name'], $_FILES['image']['type'], $_FILES['image']['name'])
    ];

    $response = call_sightengine_api('https://api.sightengine.com/1.0/check.json', $params);
    echo $response;
}

/**
 * Menangani permintaan untuk moderasi teks.
 * Menargetkan endpoint text/check.json.
 */
function handle_text_moderation_request() {
    if (!isset($_POST['text']) || empty(trim($_POST['text']))) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'error' => ['message' => 'Teks tidak boleh kosong.']]);
        exit;
    }

    // Siapkan parameter untuk API
    $params = [
        'text' => trim($_POST['text']),
        'lang' => 'en,id', // Cek untuk Bahasa Inggris dan Indonesia
        'mode' => 'standard',
        'api_user' => SIGHTENGINE_API_USER,
        'api_secret' => SIGHTENGINE_API_SECRET
    ];
    
    $response = call_sightengine_api('https://api.sightengine.com/1.0/text/check.json', $params);
    echo $response;
}

/**
 * Menangani permintaan untuk deteksi gambar buatan AI.
 * Menargetkan endpoint check.json dengan model spesifik.
 */
function handle_ai_detection_request() {
     // Validasi file gambar dengan detail error
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        $error_message = get_upload_error_message($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE);
        echo json_encode(['status' => 'error', 'error' => ['message' => $error_message]]);
        exit;
    }
    
    // Siapkan parameter untuk API, model di-hardcode ke 'ai_generated'
    $params = [
        'models' => 'genai', 
        'api_user' => SIGHTENGINE_API_USER,
        'api_secret' => SIGHTENGINE_API_SECRET,
        // PERBAIKAN: Ganti key dari 'file' menjadi 'media' sesuai permintaan API
        'media' => new CURLFile($_FILES['image']['tmp_name'], $_FILES['image']['type'], $_FILES['image']['name'])
    ];

    $response = call_sightengine_api('https://api.sightengine.com/1.0/check.json', $params);
    echo $response;
}

/**
 * Fungsi pembantu untuk menerjemahkan kode error upload file PHP.
 * @param int $errorCode Kode error dari $_FILES['file']['error']
 * @return string Pesan error yang mudah dipahami
 */
function get_upload_error_message($errorCode) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => "File melebihi directive upload_max_filesize di php.ini.",
        UPLOAD_ERR_FORM_SIZE  => "File melebihi directive MAX_FILE_SIZE.",
        UPLOAD_ERR_PARTIAL    => "File hanya terunggah sebagian.",
        UPLOAD_ERR_NO_FILE    => "Tidak ada file yang diunggah.",
        UPLOAD_ERR_NO_TMP_DIR => "Server kekurangan folder sementara untuk unggahan.",
        UPLOAD_ERR_CANT_WRITE => "Server gagal menulis file ke disk.",
        UPLOAD_ERR_EXTENSION  => "Ekstensi PHP menghentikan unggahan file."
    ];
    return $uploadErrors[$errorCode] ?? 'Terjadi kesalahan tidak diketahui saat unggah file.';
}


/**
 * Fungsi utama cURL untuk menghubungi API Sightengine.
 * @param string $url URL endpoint API Sightengine
 * @param array $params Parameter yang akan dikirim
 * @return string Respons JSON dari API
 */
function call_sightengine_api($url, $params) {
    $ch = curl_init();
    
    // Periksa apakah ini request dengan file (multipart/form-data) atau tidak
    $is_multipart = false;
    foreach ($params as $param) {
        if ($param instanceof CURLFile) {
            $is_multipart = true;
            break;
        }
    }

    if ($is_multipart) {
        // Jika ada file, gunakan multipart/form-data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    } else {
        // Jika tidak ada file (seperti moderasi teks), gunakan application/x-www-form-urlencoded
        $query_string = http_build_query($params);
        // Endpoint teks menggunakan POST
        if (strpos($url, '/text/check.json') !== false) { 
             curl_setopt($ch, CURLOPT_URL, $url);
             curl_setopt($ch, CURLOPT_POST, true);
             curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
        } else { // Fallback jika ada endpoint GET di masa depan
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $query_string);
        }
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $responseBody = curl_exec($ch);
    $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        http_response_code(500);
        return json_encode(['status' => 'error', 'error' => ['message' => 'Kesalahan cURL: ' . $curlError]]);
    }
    
    // Set status code dari proxy sama dengan dari Sightengine untuk diteruskan ke frontend
    http_response_code($httpStatusCode);
    return $responseBody; // Teruskan respons asli dari Sightengine
}

?>
