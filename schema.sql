-- 1. Create the database
CREATE DATABASE IF NOT EXISTS plantops_db;
USE plantops_db;

-- 2. Users Table (RBAC: Operators and Technicians/Admins)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('operator', 'technician') NOT NULL DEFAULT 'operator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Assets Table (Equipment/Machinery tracking)
CREATE TABLE IF NOT EXISTS assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_tag VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    status ENUM('active', 'maintenance', 'retired') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Incidents Table (Work Orders & MTTR tracking)
CREATE TABLE IF NOT EXISTS incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    reported_by INT NOT NULL,
    assigned_to INT NULL,
    severity ENUM('Low', 'Moderate', 'Critical') NOT NULL DEFAULT 'Moderate',
    status ENUM('Open', 'In Progress', 'Resolved') NOT NULL DEFAULT 'Open',
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- 5. Insert sample seed data to test your UI immediately
INSERT INTO users (username, password_hash, role) VALUES 
('operator_jake', '$2y$12$RhpB/usrSqUaODnzmibDkuMUiY4nHyf2NO9hm8iZsR/LsvXF.m/KK', 'operator'),
('tech_sally', '$2y$12$A6dqdCQ96dx68536jtLrA.4VkJslbBKbbBgoxeHeyxnGbV1aJEGcy', 'technician');
--password123
INSERT INTO assets (asset_tag, name, location, status) VALUES 
('AST-001', 'CNC Milling Machine #1', 'Building A - Floor 1', 'active'),
('AST-002', 'Industrial Conveyor Belt', 'Building B - Packaging', 'maintenance'),
('AST-003', 'Server Rack Alpha', 'Server Room', 'active');