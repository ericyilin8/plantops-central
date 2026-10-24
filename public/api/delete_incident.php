<?php
// api/delete_incident.php
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
    $success = $incidentService->deleteIncident($id);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Incident deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Incident not found']);
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log("Delete error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}