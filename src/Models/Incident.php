<?php
namespace PlantOps\Models;

class Incident {
    private $pdo;

    public function __construct($pdo) {
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->pdo = $pdo;
    }

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
    }

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

