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

    <div class="container">
        <header class="header">
            <h2>PlantOps Central</h2>
        </header>

        <main class="card">
            <h1>Dashboard Overview</h1>
            <p class="text-muted">Click the button below to fetch data asynchronously from your PHP backend endpoint.</p>
            
            <button id="load-data-btn" class="btn" type="button">Fetch PHP Data</button>
            
            <!-- Result Container -->
            <div id="result-box" class="result-box hidden">
                <p id="output-text" class="success-text"></p>
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
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" rows="3" required placeholder="Describe the equipment failure..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Incident</button>
            </form>
            <div id="formAlert" class="mt-3"></div>
        </div>
    </div>

    <!-- Incidents Table Container -->
    <div id="incidents-container" class="table-container">
        <table class="incidents-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody id="incidents-tbody">
                <!-- Dynamic rows will be inserted here by jQuery -->
            </tbody>
        </table>
    </div>
</main>
    </div>

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Custom JS -->
    <script src="js/app.js"></script>
</body>
</html>