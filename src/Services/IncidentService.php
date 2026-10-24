<?php
// src/Services/IncidentService.php

namespace PlantOps\Services;

class IncidentService {
    private $incidentModel;

    public function __construct($incidentModel) {
        $this->incidentModel = $incidentModel;
    }

    /**
     * Fetch all incidents and calculate overall MTTR (Mean Time to Repair) in hours.
     */
    public function getDashboardData() {
        $incidents = $this->incidentModel->getAllWithAssets();
        
        $totalRepairTimeHours = 0;
        $resolvedCount = 0;

        foreach ($incidents as &$incident) {
            // If the incident has been resolved, calculate duration
            if (!empty($incident['resolved_at']) && !empty($incident['created_at'])) {
                $created = strtotime($incident['created_at']);
                $resolved = strtotime($incident['resolved_at']);
                $durationHours = ($resolved - $created) / 3600; // convert seconds to hours
                
                if ($durationHours > 0) {
                    $totalRepairTimeHours += $durationHours;
                    $resolvedCount++;
                }
                
                $incident['repair_duration_hours'] = round($durationHours, 2);
            } else {
                $incident['repair_duration_hours'] = null;
            }
        }

        // Calculate Average MTTR if there are resolved incidents
        $mttrHours = $resolvedCount > 0 ? round($totalRepairTimeHours / $resolvedCount, 2) : 0;

        return [
            "mttr_hours" => $mttrHours,
            "total_incidents" => count($incidents),
            "incidents" => $incidents
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