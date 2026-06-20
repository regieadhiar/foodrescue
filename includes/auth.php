<?php
// includes/auth.php

require_once __DIR__ . '/../config/db.php';

function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set session cookie lifetime to 30 days
        $lifetime = 30 * 24 * 60 * 60;
        
        // Secure cookie options
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        session_start();
    }
}

// Ensure session starts on file import
start_secure_session();

/**
 * Generate or retrieve CSRF token for the current session
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token against the session token
 */
function validate_csrf_token($token) {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Log in a user and optionally set a persistent remember-me cookie
 */
function login_user($userId, $username, $role, $remember = true) {
    global $pdo;
    
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;
    
    // Fetch merchant_id if role is merchant
    if ($role === 'merchant') {
        $stmt = $pdo->prepare("SELECT id, is_active FROM merchants WHERE user_id = ?");
        $stmt->execute([$userId]);
        $merchant = $stmt->fetch();
        if ($merchant) {
            $_SESSION['merchant_id'] = $merchant['id'];
            $_SESSION['merchant_active'] = $merchant['is_active'];
        }
    }
    
    if ($remember) {
        // Generate a cryptographically secure token
        $token = bin2hex(random_bytes(32));
        
        // Save to database
        $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
        $stmt->execute([$token, $userId]);
        
        // Store user ID and token in cookies for 30 days
        setcookie('fr_user_id', $userId, time() + (30 * 24 * 60 * 60), '/', '', isset($_SERVER['HTTPS']), true);
        setcookie('fr_remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', isset($_SERVER['HTTPS']), true);
    }
}

/**
 * Log out user and destroy all session/cookie parameters
 */
function logout_user() {
    global $pdo;
    
    if (isset($_SESSION['user_id'])) {
        // Clear remember token from DB
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    }
    
    // Clear session variables
    $_SESSION = [];
    
    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
    
    // Clear persistent cookies
    setcookie('fr_user_id', '', time() - 3600, '/');
    setcookie('fr_remember_token', '', time() - 3600, '/');
}

/**
 * Get the currently logged in user info (checks session first, then auto-login cookie fallback)
 */
function get_logged_in_user() {
    global $pdo;
    
    // 1. Check if session is already active
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user) {
            // Update role/merchant details dynamically just in case they changed
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === 'merchant') {
                $stmtM = $pdo->prepare("SELECT id, is_active FROM merchants WHERE user_id = ?");
                $stmtM->execute([$user['id']]);
                $merchant = $stmtM->fetch();
                if ($merchant) {
                    $_SESSION['merchant_id'] = $merchant['id'];
                    $_SESSION['merchant_active'] = $merchant['is_active'];
                }
            }
            return $user;
        }
    }
    
    // 2. Cookie auto-login fallback
    if (isset($_COOKIE['fr_user_id']) && isset($_COOKIE['fr_remember_token'])) {
        $userId = $_COOKIE['fr_user_id'];
        $token = $_COOKIE['fr_remember_token'];
        
        $stmt = $pdo->prepare("SELECT id, username, email, role, remember_token FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user && $user['remember_token'] && hash_equals($user['remember_token'], $token)) {
            // Re-establish session
            login_user($user['id'], $user['username'], $user['role'], true);
            return $user;
        }
    }
    
    return null;
}

/**
 * Check if the user has a specific role
 */
function has_role($role) {
    $user = get_logged_in_user();
    return $user && $user['role'] === $role;
}
