<?php
// 1. Enforce session hardening
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
// ini_set('session.cookie_secure', '1'); // Enable when using HTTPS

// 2. Set any global security or response headers here if needed
// header("X-Frame-Options: DENY");

// 3. Kick off the session
session_start();

