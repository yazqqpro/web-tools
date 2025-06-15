<?php
// File: cleanup_audio.php
// Automatic cleanup script for old audio files

ini_set('display_errors', 0);
error_reporting(0);

define('AUDIO_DIR', __DIR__ . '/audio/');
define('LOG_FILE', __DIR__ . '/logs/cleanup.log');
define('CLEANUP_AGE_MINUTES', 10); // Delete files older than 10 minutes

/**
 * Log cleanup operations
 */
function logCleanup($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] CLEANUP: $message" . PHP_EOL;
    
    // Ensure logs directory exists
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Clean up old audio files
 */
function cleanupOldAudioFiles() {
    if (!is_dir(AUDIO_DIR)) {
        logCleanup("Audio directory does not exist: " . AUDIO_DIR);
        return;
    }
    
    $files = glob(AUDIO_DIR . 'tts_*.mp3');
    $cutoffTime = time() - (CLEANUP_AGE_MINUTES * 60);
    $deletedCount = 0;
    $totalSize = 0;
    
    foreach ($files as $file) {
        $fileTime = filemtime($file);
        $fileSize = filesize($file);
        
        if ($fileTime < $cutoffTime) {
            if (unlink($file)) {
                $deletedCount++;
                $totalSize += $fileSize;
                logCleanup("Deleted file: " . basename($file) . " (Age: " . round((time() - $fileTime) / 60, 1) . " minutes, Size: " . round($fileSize / 1024, 2) . " KB)");
            } else {
                logCleanup("Failed to delete file: " . basename($file));
            }
        }
    }
    
    if ($deletedCount > 0) {
        logCleanup("Cleanup completed: $deletedCount files deleted, " . round($totalSize / 1024, 2) . " KB freed");
    } else {
        logCleanup("Cleanup completed: No files to delete");
    }
    
    return $deletedCount;
}

// Run cleanup when called directly
if (basename($_SERVER['PHP_SELF']) === 'cleanup_audio.php') {
    // Verify this is being called from command line or cron
    if (php_sapi_name() === 'cli' || !isset($_SERVER['HTTP_HOST'])) {
        echo "Starting audio cleanup...\n";
        $deleted = cleanupOldAudioFiles();
        echo "Cleanup completed: $deleted files deleted\n";
    } else {
        // If called via web, require authentication
        if (!isset($_GET['auth']) || $_GET['auth'] !== md5('cleanup_' . date('Y-m-d'))) {
            http_response_code(403);
            echo "Access denied";
            exit;
        }
        
        header('Content-Type: application/json');
        $deleted = cleanupOldAudioFiles();
        echo json_encode(['success' => true, 'deleted_files' => $deleted]);
    }
}
?>