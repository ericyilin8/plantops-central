<?php
// public/api/get_incidents.php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Models/Incident.php';
require_once __DIR__ . '/../../src/Services/IncidentService.php';

try {
    $incidentModel = new \PlantOps\Models\Incident($pdo);
    $incidentService = new \PlantOps\Services\IncidentService($incidentModel);
    
    $dashboardData = $incidentService->getDashboardData();

    echo json_encode([
        "success" => true,
        "data" => $dashboardData
    ]);
} catch (\Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Internal server error."]);
}