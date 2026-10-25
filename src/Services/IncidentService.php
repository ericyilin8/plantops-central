<?php
// src/Services/IncidentService.php

namespace PlantOps\Services;

class IncidentService {
    private $incidentModel;

    public function __construct($incidentModel) {
        $this->incidentModel = $incidentModel;
    }

    public function getDashboardData(int $page = 1, string $sort = 'created_at', string $direction = 'DESC') {
    $limit = 25;
    $offset = ($page - 1) * $limit;

    // 1. Pass calculated limit and offset to the model
    $incidents = $this->incidentModel->getPaginatedWithAssets($limit, $offset, $sort, $direction);
    // 2. Get the true grand total count of all incidents in the database (crucial for pagination links!)
    $totalIncidentsCount = $this->incidentModel->getTotalCount();

    // 3. Calculate MTTR across historical resolved incidents 
    // (Pro-tip: For real scaling, you'd calculate this via a SQL query, but doing it in PHP is fine for medium datasets)
    $allResolved = $this->incidentModel->getAllResolvedForMttr(); 
    $totalRepairTimeHours = 0;
    $resolvedCount = count($allResolved);

    foreach ($allResolved as $resolvedIncident) {
        $created = strtotime($resolvedIncident['created_at']);
        $resolved = strtotime($resolvedIncident['resolved_at']);
        $durationHours = ($resolved - $created) / 3600;
        if ($durationHours > 0) {
            $totalRepairTimeHours += $durationHours;
        }
    }

    $mttrHours = $resolvedCount > 0 ? round($totalRepairTimeHours / $resolvedCount, 2) : 0;

    // 4. Calculate individual repair durations for the current page slice for display
    foreach ($incidents as &$incident) {
        if (!empty($incident['resolved_at']) && !empty($incident['created_at'])) {
            $created = strtotime($incident['created_at']);
            $resolved = strtotime($incident['resolved_at']);
            $durationHours = ($resolved - $created) / 3600;
            $incident['repair_duration_hours'] = $durationHours > 0 ? round($durationHours, 2) : null;
        } else {
            $incident['repair_duration_hours'] = null;
        }
    }

    return [
        "mttr_hours" => $mttrHours,
        "total_incidents" => $totalIncidentsCount, // True total for pagination
        "incidents" => $incidents // Paginated slice for the table
    ];
}


    public function createIncident($data) {
        if (empty($data['asset_id']) || empty($data['reported_by']) || empty($data['severity']) || empty($data['description'])) {
            throw new \InvalidArgumentException("Missing required fields: asset_id, reported_by, severity, and description are required.");
        }
    // Pass validated data to model
    return $this->incidentModel->create($data);
    }

    //Delete incident by ID
    public function deleteIncident($id) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        
        if (!$id) {
            throw new \InvalidArgumentException("Invalid incident ID provided.");
        }

        // Delegate deletion to the model layer
        return $this->incidentModel->delete($id);
    }

    public function updateIncident($id, $data) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id) {
            throw new \InvalidArgumentException("Invalid incident ID provided.");
        }

        if (empty($data['asset_id']) || empty($data['severity']) || empty($data['description']) || empty($data['status'])) {
            throw new \InvalidArgumentException("Missing required fields for update.");
        }

        return $this->incidentModel->update($id, $data);
    }
}