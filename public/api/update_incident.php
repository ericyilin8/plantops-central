<?php
// api/update_incident.php
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

try {
    $incidentModel = new \PlantOps\Models\Incident($pdo);
    $incidentService = new \PlantOps\Services\IncidentService($incidentModel);
    $success = $incidentService->updateIncident($id, $input);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Incident updated successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Incident not found or no changes made']);
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log("Update error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}