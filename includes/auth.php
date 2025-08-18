<?php
require_once 'config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function login($email, $password) {
    global $pdo;
    
    // Check for too many failed attempts
    $stmt = $pdo->prepare("SELECT failed_attempts, last_attempt FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && $user['failed_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        $lastAttempt = strtotime($user['last_attempt']);
        if (time() - $lastAttempt < 900) { // 15 minutes lockout
            return ['success' => false, 'message' => 'Account temporarily locked due to too many failed attempts'];
        }
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Reset failed attempts on successful login
        $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        // Log the login
        logActivity($user['id'], 'login', 'User logged in from ' . $_SERVER['REMOTE_ADDR']);
        
        return ['success' => true, 'user' => $user];
    } else {
        // Increment failed attempts
        if ($user) {
            $stmt = $pdo->prepare("UPDATE users SET failed_attempts = failed_attempts + 1, last_attempt = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
        }
        
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
}

function logout() {
    if (isLoggedIn()) {
        logActivity($_SESSION['user_id'], 'logout', 'User logged out');
    }
    
    session_destroy();
    header('Location: login.php');
    exit;
}

function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireAuth();
    $user = getCurrentUser();
    if ($user['role'] !== 'Administrator') {
        header('Location: index.php');
        exit;
    }
}

function logActivity($userId, $action, $details = '') {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $action, $details, $_SERVER['REMOTE_ADDR']]);
}
?>