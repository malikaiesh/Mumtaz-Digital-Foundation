<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

define('SITE_NAME', 'Mumtaz Digital Foundation');
define('SITE_TAGLINE', 'Empowering Digital Skills for the Future');
define('SITE_URL', 'https://' . $_SERVER['HTTP_HOST']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('CURRENCY', 'PKR');
define('CURRENCY_SYMBOL', 'Rs.');

require_once __DIR__ . '/database.php';

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin']);
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function verifyCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function setCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

function formatPrice($price) {
    return CURRENCY_SYMBOL . ' ' . number_format($price, 0);
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return date('M d, Y', $time);
}

function generateCertificateId() {
    return 'MDF-' . strtoupper(substr(md5(uniqid()), 0, 8)) . '-' . date('Y');
}
