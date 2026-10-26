<?php
// public/api/add_incident.php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/session.php'; // Ensures session is running and user logged in
// 1. Strict backend authentication check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}
require_once __DIR__ . '/../../config/database.php';

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed. Use POST."]);
    exit();
}

// Read incoming JSON payload
$input = json_decode(file_get_contents('php://input'), true);

try {
    $incidentModel = new \PlantOps\Models\Incident($pdo);
    $incidentService = new \PlantOps\Services\IncidentService($incidentModel);
    $newIncidentId = $incidentService->createIncident($input);

    http_response_code(201); // Created
    echo json_encode([
        "success" => true,
        "message" => "Incident logged successfully.",
        "incident_id" => (int)$newIncidentId
    ]);

} catch (\InvalidArgumentException $e) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
} catch (\Exception $e) {
    error_log("Add Incident Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Internal server error while logging incident."]);
}