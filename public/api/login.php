<?php
header('Content-Type: application/json');
//should set headers before session
require_once __DIR__ . '/../../config/session.php'; 

// Include your database connection (adjust filename as needed)
require_once __DIR__ . '/../../config/database.php'; 

$response = [
    'success' => false, 
    'message' => 'Invalid username or password' // Generic error to prevent username enumeration
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        try {
            // 1. Fetch user by username using a PDO prepared statement
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Verify password against the stored hash
            if ($user && password_verify($password, $user['password_hash'])) {
                
                // 3. Prevent session fixation attacks
                session_regenerate_id(true);

                // 4. Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                session_regenerate_id(true);

                $response['success'] = true;
                $response['message'] = 'Login successful!';
            }
        } catch (PDOException $e) {
            // In production, log this error internally rather than showing it to the user
            $response['message'] = 'Database error occurred.';
        }
    }
}

// Send the JSON response back to jQuery
echo json_encode($response);
exit;