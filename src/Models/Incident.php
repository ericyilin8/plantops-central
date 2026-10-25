<?php
namespace PlantOps\Models;

class Incident {
    private $pdo;

    public function __construct($pdo) {
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->pdo = $pdo;
    }

    /**
     * Fetch paginated and sorted incidents joined with asset info
     */
    public function getPaginatedWithAssets(int $limit, int $offset, string $sort, string $direction): array {
        // 1. Strict Whitelisting for defense against SQL Injection
        $allowedColumns = ['id', 'asset_id', 'severity', 'status', 'created_at'];
        $sortCol = in_array($sort, $allowedColumns, true) ? $sort : 'created_at';
        
        $sortDir = (strtoupper($direction) === 'ASC') ? 'ASC' : 'DESC';

        // 2. Prepare query (assuming a foreign key relationship with an 'assets' table)
        // Adjust table/column names to match your exact DB schema if needed
        $sql = "SELECT i.*, a.name AS asset_name 
                FROM incidents i 
                LEFT JOIN assets a ON i.asset_id = a.id 
                ORDER BY i.{$sortCol} {$sortDir} 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        
        // 3. Bind limit and offset explicitly as integers for PDO security
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**unused
     * Get the true grand total count of all incidents for pagination math
     
    public function getTotalCount(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM incidents");
        return (int) $stmt->fetchColumn();
    }*/

    public function getSummary(): array {
 
        $summaryStmt =  $this->pdo->query("SELECT COUNT(*) AS total_incidents, SUM(status = 'Open') AS open_count, SUM(status = 'Resolved') AS resolved_count, SUM(status = 'In Progress') AS in_progress_count, SUM(severity = 'Critical') AS critical_count, ROUND(AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)), 1) AS mttr_hours FROM incidents");
        $summary = $summaryStmt->fetch(\PDO::FETCH_ASSOC);

        return $summary;
    }


    /**unused
     * Fetch all resolved incidents for plant-wide MTTR calculation
     
    public function getAllResolvedForMttr(): array {
        $stmt = $this->pdo->query("SELECT created_at, resolved_at FROM incidents WHERE resolved_at IS NOT NULL");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }*/

    //unused
    /*
    public function getAllWithAssets() {
        $sql = "
            SELECT 
                i.id, i.severity, i.status, i.description, i.created_at, i.resolved_at,
                a.asset_tag, a.name AS asset_name, a.location, a.id AS asset_id
            FROM incidents i
            JOIN assets a ON i.asset_id = a.id
            ORDER BY i.created_at DESC
        ";
        return $this->pdo->query($sql)->fetchAll();
    }*/

public function create($data) {
    $sql = "INSERT INTO incidents (asset_id, reported_by, assigned_to, severity, status, description, created_at) 
            VALUES (:asset_id, :reported_by, :assigned_to, :severity, :status, :description, NOW())";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
        ':asset_id' => $data['asset_id'],
        ':reported_by' => $data['reported_by'],
        ':assigned_to' => $data['assigned_to'] ?? null,
        ':severity' => $data['severity'],
        ':status' => $data['status'] ?? 'Open',
        ':description' => $data['description']
    ]);
    
    return $this->pdo->lastInsertId();
    }
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM incidents WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE incidents 
            SET asset_id = :asset_id, 
                description = :description, 
                status = :status, 
                severity = :severity 
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':asset_id' => $data['asset_id'],
            ':description' => $data['description'],
            ':status' => $data['status'],
            ':severity' => $data['severity']
        ]);
    }
}

