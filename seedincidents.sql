use plantops_db;
-- Seed 40 mock incidents for pagination testing
INSERT INTO incidents (asset_id, reported_by, assigned_to, severity, status, description, created_at, resolved_at)
SELECT 
    1 AS asset_id,
    1 AS reported_by,
    CASE WHEN n % 3 = 0 THEN 1 ELSE NULL END AS assigned_to,
    ELT(1 + FLOOR(RAND() * 3), 'Low', 'Moderate', 'Critical') AS severity,
    ELT(1 + FLOOR(RAND() * 3), 'Open', 'In Progress', 'Resolved') AS status,
    CONCAT('Automated plant incident ticket #', n, ' - checking system stability') AS description,
    NOW() - INTERVAL (n * 3) HOUR AS created_at,
    CASE 
        WHEN n % 3 = 0 THEN NOW() - INTERVAL (n * 3 - FLOOR(RAND() * 20 + 2)) HOUR 
        ELSE NULL 
    END AS resolved_at
FROM (
    SELECT @row := @row + 1 AS n 
    FROM (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t1,
         (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t2,
         (SELECT @row := 0) r
    LIMIT 40
) numbers;