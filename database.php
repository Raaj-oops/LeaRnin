<?php
/**
 * TechLearn Platform - Database Configuration
 * Update these settings based on your XAMPP/WAMP configuration
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');  // Default XAMPP has no password
define('DB_NAME', 'techlearn');

// Create database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper function to get PDO instance
function getDB() {
    global $pdo;
    return $pdo;
}

// Session configuration
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Check if user is admin
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['is_admin'] == 1;
}

// Redirect function
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Flash message system
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// CSRF Protection
function generateCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Security: Sanitize output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Upload directories
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('PROJECT_UPLOADS', UPLOAD_DIR . 'projects/');
define('AVATAR_UPLOADS', UPLOAD_DIR . 'avatars/');
define('SUBMISSION_UPLOADS', UPLOAD_DIR . 'submissions/');

// Create upload directories if they don't exist
foreach ([PROJECT_UPLOADS, AVATAR_UPLOADS, SUBMISSION_UPLOADS] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
