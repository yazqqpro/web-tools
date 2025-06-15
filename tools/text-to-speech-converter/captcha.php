<?php
// File: captcha.php
// Simple number display CAPTCHA system for TTS access

session_start();

header('Content-Type: application/json');

/**
 * Generate two random 2-digit numbers for display
 */
function generateNumberCaptcha() {
    $num1 = rand(10, 99);
    $num2 = rand(10, 99);
    $combined = $num1 . $num2; // Combine the numbers as a string
    
    return [
        'display' => "$num1  $num2",
        'answer' => $combined
    ];
}

/**
 * Verify CAPTCHA answer
 */
function verifyCaptcha($userAnswer) {
    if (!isset($_SESSION['captcha_answer']) || !isset($_SESSION['captcha_time'])) {
        return false;
    }
    
    // Check if CAPTCHA is expired (5 minutes)
    if (time() - $_SESSION['captcha_time'] > 300) {
        unset($_SESSION['captcha_answer'], $_SESSION['captcha_time']);
        return false;
    }
    
    $isCorrect = trim($userAnswer) === $_SESSION['captcha_answer'];
    
    if ($isCorrect) {
        // Mark as verified for this session
        $_SESSION['captcha_verified'] = true;
        $_SESSION['captcha_verified_time'] = time();
        unset($_SESSION['captcha_answer'], $_SESSION['captcha_time']);
    }
    
    return $isCorrect;
}

/**
 * Check if user has valid CAPTCHA verification
 */
function isCaptchaVerified() {
    if (!isset($_SESSION['captcha_verified']) || !isset($_SESSION['captcha_verified_time'])) {
        return false;
    }
    
    // Verification expires after 30 minutes
    if (time() - $_SESSION['captcha_verified_time'] > 1800) {
        unset($_SESSION['captcha_verified'], $_SESSION['captcha_verified_time']);
        return false;
    }
    
    return true;
}

// Handle different actions
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'generate':
        $captcha = generateNumberCaptcha();
        $_SESSION['captcha_answer'] = $captcha['answer'];
        $_SESSION['captcha_time'] = time();
        
        echo json_encode([
            'success' => true,
            'display' => $captcha['display'],
            'timestamp' => time()
        ]);
        break;
        
    case 'verify':
        $userAnswer = $_POST['answer'] ?? '';
        $isCorrect = verifyCaptcha($userAnswer);
        
        echo json_encode([
            'success' => $isCorrect,
            'message' => $isCorrect ? 'Numbers verified successfully' : 'Incorrect numbers. Please try again.'
        ]);
        break;
        
    case 'check':
        $isVerified = isCaptchaVerified();
        echo json_encode([
            'verified' => $isVerified,
            'message' => $isVerified ? 'CAPTCHA verification valid' : 'CAPTCHA verification required'
        ]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        break;
}
?>