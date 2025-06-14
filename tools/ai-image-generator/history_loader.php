<?php
// File: history_loader.php
// Enhanced history loader with better performance and caching

header('Content-Type: application/json');
header('Cache-Control: public, max-age=60'); // Cache for 1 minute

$history_dir = __DIR__ . '/history/';
$items_per_page = 15; // Increased from 12 for better grid layout

// Enhanced input validation
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
    'options' => ['default' => 1, 'min_range' => 1, 'max_range' => 1000]
]);

/**
 * Enhanced error response
 */
function sendErrorResponse($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'error' => true,
        'message' => $message,
        'totalPages' => 0,
        'currentPage' => 1,
        'items' => []
    ]);
    exit;
}

/**
 * Enhanced success response
 */
function sendSuccessResponse($totalPages, $currentPage, $items, $totalItems = 0) {
    echo json_encode([
        'success' => true,
        'totalPages' => $totalPages,
        'currentPage' => $currentPage,
        'totalItems' => $totalItems,
        'itemsPerPage' => $GLOBALS['items_per_page'],
        'items' => $items,
        'timestamp' => time()
    ]);
    exit;
}

/**
 * Enhanced file validation
 */
function isValidHistoryFile($file_path) {
    if (!is_file($file_path) || !is_readable($file_path)) {
        return false;
    }
    
    $file_size = filesize($file_path);
    if ($file_size === false || $file_size > 10240) { // Max 10KB for JSON files
        return false;
    }
    
    return pathinfo($file_path, PATHINFO_EXTENSION) === 'json';
}

/**
 * Enhanced data parsing with validation
 */
function parseHistoryFile($file_path) {
    $content = file_get_contents($file_path);
    if ($content === false) {
        return null;
    }
    
    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Invalid JSON in history file: $file_path - " . json_last_error_msg());
        return null;
    }
    
    // Validate required fields
    if (!isset($data['imagekit_url']) || !isset($data['prompt']) || !isset($data['timestamp'])) {
        return null;
    }
    
    // Sanitize and validate data
    $data['imagekit_url'] = filter_var($data['imagekit_url'], FILTER_VALIDATE_URL);
    $data['prompt'] = htmlspecialchars(substr($data['prompt'], 0, 500), ENT_QUOTES, 'UTF-8');
    $data['timestamp'] = filter_var($data['timestamp'], FILTER_VALIDATE_INT);
    $data['ip'] = isset($data['ip']) ? preg_replace('/[^0-9a-fA-F:.]/', '', $data['ip']) : 'unknown';
    
    if (!$data['imagekit_url'] || !$data['timestamp']) {
        return null;
    }
    
    return $data;
}

try {
    if (!is_dir($history_dir)) {
        sendSuccessResponse(0, 1, []);
    }

    // Enhanced file discovery with better performance
    $history_files = [];
    $directory_iterator = new DirectoryIterator($history_dir);
    
    foreach ($directory_iterator as $file_info) {
        if ($file_info->isDot() || !$file_info->isFile()) {
            continue;
        }
        
        $file_path = $file_info->getPathname();
        if (isValidHistoryFile($file_path)) {
            $history_files[] = [
                'path' => $file_path,
                'mtime' => $file_info->getMTime()
            ];
        }
    }

    // Sort by modification time (newest first)
    usort($history_files, function($a, $b) {
        return $b['mtime'] - $a['mtime'];
    });

    $total_files = count($history_files);
    if ($total_files === 0) {
        sendSuccessResponse(0, 1, []);
    }

    $total_pages = ceil($total_files / $items_per_page);
    
    // Validate page number
    if ($page > $total_pages) {
        $page = $total_pages;
    }

    // Calculate pagination
    $offset = ($page - 1) * $items_per_page;
    $page_files = array_slice($history_files, $offset, $items_per_page);

    // Enhanced data processing
    $all_generations = [];
    foreach ($page_files as $file_info) {
        $data = parseHistoryFile($file_info['path']);
        if ($data !== null) {
            // Add additional metadata
            $data['file_size'] = filesize($file_info['path']);
            $data['created_date'] = date('Y-m-d H:i:s', $data['timestamp']);
            $all_generations[] = $data;
        }
    }

    // Final validation and response
    if (empty($all_generations) && $page > 1) {
        // If no valid items on this page, try page 1
        sendSuccessResponse($total_pages, 1, []);
    }

    sendSuccessResponse($total_pages, $page, $all_generations, $total_files);

} catch (Exception $e) {
    error_log("History loader error: " . $e->getMessage());
    sendErrorResponse("Failed to load gallery data", 500);
}
?>