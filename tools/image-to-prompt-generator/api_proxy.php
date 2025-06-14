<?php
// File: api_proxy.php

ini_set('display_errors', 0);
error_reporting(0);

// session_start(); // Dihapus karena CSRF tidak lagi digunakan

header('Content-Type: application/json');

// --- Keamanan & Konfigurasi ---
$allowed_referer = 'https://app.andrias.web.id/tools/image-to-prompt-generator/';
define('EXTERNAL_API_URL', 'https://img.amstream.live/api/generate-advanced-prompt'); // URL eksternal API

// Validasi Referer tetap dipertahankan sebagai lapisan keamanan
$referer_valid = false;
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $allowed_referer) === 0) {
    $referer_valid = true;
}
if (!$referer_valid) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak (R).']);
    exit;
}

// Validasi Metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

// --- BLOK VALIDASI CSRF DIHAPUS ---

// Validasi Unggahan File
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File gambar tidak ada atau bermasalah.']);
    exit;
}

// --- Persiapan Data untuk API Eksternal ---
$postData = [];
$fieldsToForward = ['style', 'mood', 'language'];
foreach ($fieldsToForward as $field) {
    if (isset($_POST[$field]) && $_POST[$field] !== '') {
        $postData[$field] = $_POST[$field];
    }
}

$imageFile = $_FILES['image'];
// Membuat objek CURLFile untuk mengirim file
$cfile = new CURLFile($imageFile['tmp_name'], $imageFile['type'], $imageFile['name']);
$postData['image'] = $cfile;

// --- Panggilan cURL ke API Eksternal ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, EXTERNAL_API_URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Mengembalikan transfer sebagai string
curl_setopt($ch, CURLOPT_TIMEOUT, 45); // Timeout setelah 45 detik
$responseBody = curl_exec($ch);
$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Periksa jika ada kesalahan cURL (misalnya, gagal koneksi)
if ($curlError) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menghubungi API eksternal: ' . $curlError]);
    exit;
}

// Mengatur status HTTP respons proksi sesuai dengan status API eksternal
http_response_code($httpStatusCode);
$decodedResponse = json_decode($responseBody, true);

// --- Pemrosesan dan Pembersihan Respons ---
// Kondisi sukses: status 200, JSON valid, dan kunci 'prompt' ada di respons eksternal
if ($httpStatusCode === 200 && json_last_error() === JSON_ERROR_NONE && isset($decodedResponse['prompt'])) {
    $prompt = $decodedResponse['prompt'];
    $sentenceToRemove1 = "Berikut prompt untuk menghasilkan gambar AI berdasarkan deskripsi yang diberikan:";
    $sentenceToRemove2 = "Berikut prompt untuk menghasilkan gambar AI:";
    // Menghapus kalimat awalan dari prompt jika ada
    $prompt = str_replace([$sentenceToRemove1, $sentenceToRemove2], '', $prompt);
    $prompt = trim($prompt); // Menghapus spasi di awal/akhir

    // Membangun respons untuk klien (frontend JS) sesuai format yang diharapkan
    $responseToClient = [
        'success' => true,
        'data' => [
            'prompt' => $prompt
        ]
    ];

    // Jika 'analysis' juga ada dari API eksternal, sertakan dalam respons klien
    if (isset($decodedResponse['analysis'])) {
        $responseToClient['data']['analysis'] = $decodedResponse['analysis'];
    }

    echo json_encode($responseToClient);
} else {
    // --- Penanganan Error jika respons dari API eksternal tidak sesuai ---
    $errorMessage = 'Terjadi kesalahan yang tidak diketahui dari API eksternal.';
    
    // Informasi debugging untuk membantu melacak masalah
    $debugInfo = [
        'http_status_code' => $httpStatusCode,
        'json_decode_error_code' => json_last_error(),
        'json_decode_error_message' => json_last_error_msg(),
        'raw_external_api_response' => $responseBody // Respons mentah dari API eksternal
    ];

    // Coba ambil pesan error dari respons API eksternal jika tersedia
    if ($decodedResponse && isset($decodedResponse['message'])) {
        $errorMessage = $decodedResponse['message'];
    } elseif ($decodedResponse && isset($decodedResponse['error'])) {
        $errorMessage = is_array($decodedResponse['error']) ? json_encode($decodedResponse['error']) : $decodedResponse['error'];
    } elseif (!empty($responseBody)) {
        // Jika ada respons tapi bukan JSON valid atau tidak ada 'prompt'
        $errorMessage = "Respons tidak valid diterima dari API eksternal (tidak ada kunci 'prompt' atau JSON tidak valid)."; 
    }
    
    echo json_encode([
        'success' => false, 
        'message' => $errorMessage,
        'original_response' => $decodedResponse, // Respons yang didecode (mungkin null/parsial)
        'debug_info' => $debugInfo // Informasi debugging tambahan
    ]);
}
exit; // Pastikan skrip berhenti di sini
?>
