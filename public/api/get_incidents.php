<?php
// public/api/get_incidents.php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/session.php'; // Ensures session is running and user logged in
// 1. Strict backend authentication check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}
require_once __DIR__ . '/../../config/database.php';

try {

// 1. Capture query parameters sent by frontend AJAX (with safe defaults)
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $sort = $_GET['sort'] ?? 'created_at';
    $direction = $_GET['direction'] ?? 'DESC';

    $incidentModel = new \PlantOps\Models\Incident($pdo);
    $incidentService = new \PlantOps\Services\IncidentService($incidentModel);
    // 2. Pass the parameters into your service layer
    $dashboardData = $incidentService->getDashboardData($page, $sort, $direction);

    echo json_encode([
        "success" => true,
        "data" => $dashboardData
    ]);
} catch (\Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Internal server error."]);
}