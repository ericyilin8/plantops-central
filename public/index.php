<?php
require_once __DIR__ . '/../config/session.php'; 

$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlantOps Central</title>
    <!-- Pure Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Login Overlay Modal -->
    <div id="login-overlay" class="login-overlay" style="display: <?php echo $isLoggedIn ? 'none' : 'flex'; ?>;">
        <div class="login-modal">
            <h2>PlantOps Central Login</h2>
            
            <form id="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="operator_jake" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" value="password123" required>
                </div>
                
                <button type="submit" id="login-btn" class="btn-submit">Sign In</button>
            </form>
        </div>
    </div>
    <!-- Login Overlay Modal -->

    <div id="loading-overlay" class="loading-overlay" style="display: none;">
    <div class="spinner-container">
        <div class="spinner"></div>
    </div>
    </div>
    <div class="container">
        
            <header class="header">
                <h2>PlantOps Central</h2>
                <form class="logout-form" action="/api/logout.php" method="POST">
                <!-- Optional: Add a CSRF token here for extra protection -->
                    <button type="submit" class="logout-btn">Log Out</button>
                </form>
            </header>

            <main class="card">
                <h1>Dashboard Overview</h1>
                
                <button id="load-data-btn" class="btn" type="button">Fetch Latest Incidents</button>
                
                <!-- Result Container -->
                <div id="result-box" class="result-box hidden">
                    <p id="output-text" class="success-text"></p>
                </div>

                <div class="metrics-grid">
                    <div class="metric-card">
                        <span class="metric-label">Total Incidents</span>
                        <span class="metric-value" id="metric-total">-</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Open / Active</span>
                        <span class="metric-value text-danger" id="metric-open">-</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Critical Severity</span>
                        <span class="metric-value text-warning" id="metric-critical">-</span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-label">Plant MTTR</span>
                        <span class="metric-value" id="metric-mttr">-</span>
                    </div>
                </div>

                <div class="visual-breakdown-container">
                    <div class="breakdown-header">
                        <span>Status Distribution</span>
                        <span id="breakdown-counts">Open: 0 | In Progress: 0 | Resolved: 0</span>
                    </div>
                    <div class="status-bar">
                        <div id="bar-open" class="status-segment bg-danger" style="width: 0%;"></div>
                        <div id="bar-progress" class="status-segment bg-warning" style="width: 0%;"></div>
                        <div id="bar-resolved" class="status-segment bg-success" style="width: 0%;"></div>
                    </div>
                </div>

            </main>

            <main class="card">
                <h1>Incident Dashboard</h1>
                <p class="text-muted">Manage and monitor plant incidents in real time.</p>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Log New Incident</h5>
                        <form id="addIncidentForm">
                            <div class="mb-3">
                                <label for="asset_id" class="form-label">Asset ID</label>
                                <input type="number" class="form-control" id="asset_id" required placeholder="e.g., 1">
                            </div>
                            <div class="mb-3">
                                <label for="reported_by" class="form-label">Reported By (User ID)</label>
                                <input type="number" class="form-control" id="reported_by" required placeholder="e.g., 1">
                            </div>
                            <div class="mb-3">
                                <label for="severity" class="form-label">Severity</label>
                                <select class="form-select" id="severity" required>
                                    <option value="Low">Low</option>
                                    <option value="Moderate">Moderate</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description:</label>
                                <textarea class="form-control" id="description" rows="3" required placeholder="Describe the equipment failure..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Incident</button>
                        </form>
                        <div id="formAlert" class="mt-3"></div>
                    </div>
                </div>

                <!-- Incidents Table Container -->
                <div id="incidents-container" class="table-container">
                    <div class="table-controls">
                        <div id="pagination-container" class="pagination-nav"></div>
                    </div>
                    <table class="incidents-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Asset ID</th>
                                <th>Asset Name</th>
                                <th>Description</th>
                                <th>
                                    <a href="#" class="sort-header" data-column="severity">Severity</a>
                                </th>
                                <th>
                                    <a href="#" class="sort-header" data-column="status">Status</a>
                                </th>
                                <th>
                                    <a href="#" class="sort-header" data-column="created_at">Created At</a>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="incidents-tbody">
                            <!-- Dynamic rows will be inserted here by jQuery -->
                        </tbody>
                    </table>
                </div>
            </main>

    </div>

    <!-- Custom Popup Modal -->
    <div id="editModalOverlay" class="custom-modal-overlay">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h3>Edit Incident</h3>
                <button type="button" class="btn-close-modal" style="background:none; border:none; font-size:1.2rem; cursor:pointer;">&times;</button>
            </div>
            <form id="editForm">
                <div style="margin-bottom: 12px;">
                    <!-- Visible display for the user -->
                    <span id="editIncidentIdDisplay" style="font-family: monospace; background: #f1f3f5; padding: 4px 8px; border-radius: 4px; display: inline-block;"></span>
                    
                    <!-- Hidden input to hold the value for your AJAX payload -->
                    <input type="hidden" id="editIncidentId" name="id">
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="editAssetId" style="display:block; margin-bottom:4px;">Asset ID</label>
                    <input type="text" id="editAssetId" name="asset_id" style="width:100%; padding:8px; box-sizing:border-box;" required>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="editDescription" style="display:block; margin-bottom:4px;">Description</label>
                    <textarea id="editDescription" name="description" rows="3" style="width:100%; padding:8px; box-sizing:border-box;" required></textarea>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="editSeverity" style="display:block; margin-bottom:4px;">Severity</label>
                    <select id="editSeverity" name="severity" style="width:100%; padding:8px; box-sizing:border-box;" required>
                        <option value="Low">Low</option>
                        <option value="Moderate">Moderate</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="editStatus" style="display:block; margin-bottom:4px;">Status</label>
                    <select id="editStatus" name="status" style="width:100%; padding:8px; box-sizing:border-box;" required>
                        <option value="Open">Open</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                </div>

                <div class="custom-modal-footer">
                    <button type="button" id="cancelModalBtn" style="padding:8px 16px; cursor:pointer;">Cancel</button>
                    <button type="submit" style="padding:8px 16px; background:#007bff; color:#fff; border:none; border-radius:4px; cursor:pointer;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Custom JS -->
    <script src="js/app.js"></script>
</body>
</html>