<?php
// File: history_loader.php
// Bertugas menyediakan data riwayat per halaman dalam format JSON.

header('Content-Type: application/json');

$history_dir = __DIR__ . '/history/';
$items_per_page = 12;

// Ambil nomor halaman dari parameter GET, default ke halaman 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

$all_generations = [];
if (is_dir($history_dir)) {
    $history_files = glob($history_dir . '*.json');
    usort($history_files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    foreach ($history_files as $json_file) {
        $data = json_decode(file_get_contents($json_file), true);
        if ($data && isset($data['imagekit_url'])) {
            $all_generations[] = $data;
        }
    }
}

$total_items = count($all_generations);
$total_pages = ceil($total_items / $items_per_page);

// Hitung offset untuk mengambil data sesuai halaman
$offset = ($page - 1) * $items_per_page;
$page_items = array_slice($all_generations, $offset, $items_per_page);

// Kembalikan data dalam format JSON
echo json_encode([
    'totalPages' => $total_pages,
    'currentPage' => $page,
    'items' => $page_items
]);

exit;