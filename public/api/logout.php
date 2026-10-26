<?php
require_once __DIR__ . '/../../config/session.php'; 

// 1. Unset all session variables in memory
$_SESSION = [];

// 2. Expire the cookie, matching all original security flags (including SameSite)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $params["path"],
        'domain'   => $params["domain"],
        'secure'   => $params["secure"],
        'httponly' => $params["httponly"],
        'samesite' => $params["samesite"] ?? 'Lax'
    ]);
}

// 3. Destroy the session file on the server
session_destroy();

header('location: /index.php');
exit;