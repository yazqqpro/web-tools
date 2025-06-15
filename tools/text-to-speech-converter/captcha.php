<?php
// File: captcha.php
// Simple CAPTCHA system for TTS access

session_start();

header('Content-Type: application/json');

/**
 * Generate a simple 2-digit CAPTCHA
 */
function generateCaptcha() {
    $num1 = rand(10, 99);
    $num2 = rand(10, 99);
    $operation = rand(0, 1) ? '+' : '-';
    
    if ($operation === '+') {
        $answer = $num1 + $num2;
        $question = "$num1 + $num2";
    } else {
        // Ensure positive result for subtraction
        if ($num1 < $num2) {
            $temp = $num1;
            $num1 = $num2;
            $num2 = $temp;
        }
        $answer = $num1 - $num2;
        $question = "$num1 - $num2";
    }
    
    return [
        'question' => $question,
        'answer' => $answer
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
    
    $isCorrect = (int)$userAnswer === $_SESSION['captcha_answer'];
    
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
        $captcha = generateCaptcha();
        $_SESSION['captcha_answer'] = $captcha['answer'];
        $_SESSION['captcha_time'] = time();
        
        echo json_encode([
            'success' => true,
            'question' => $captcha['question'],
            'timestamp' => time()
        ]);
        break;
        
    case 'verify':
        $userAnswer = $_POST['answer'] ?? '';
        $isCorrect = verifyCaptcha($userAnswer);
        
        echo json_encode([
            'success' => $isCorrect,
            'message' => $isCorrect ? 'CAPTCHA verified successfully' : 'Incorrect answer. Please try again.'
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