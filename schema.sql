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
('operator_john', '$2y$10$abcdefghijklmnopqrstuvwxyz0123456789', 'operator'),
('tech_sarah', '$2y$10$abcdefghijklmnopqrstuvwxyz0123456789', 'technician');

INSERT INTO assets (asset_tag, name, location, status) VALUES 
('AST-001', 'CNC Milling Machine #1', 'Building A - Floor 1', 'active'),
('AST-002', 'Industrial Conveyor Belt', 'Building B - Packaging', 'maintenance'),
('AST-003', 'Server Rack Alpha', 'Server Room', 'active');