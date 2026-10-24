
//FETCH INCIDENTS
$(document).ready(function() {
    $('#load-data-btn').on('click', function() {
        const $btn = $(this);
        const $resultBox = $('#result-box');
        const $outputText = $('#output-text');
        const $tableContainer = $('#incidents-container');
        const $tbody = $('#incidents-tbody');

        $btn.prop('disabled', true).text('Loading incidents...');

        $.ajax({
            url: 'api/get_incidents.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Extract incidents array from dashboard data
                    const incidents = Array.isArray(response.data) 
                        ? response.data 
                        : (response.data.incidents || []);

                    // Clear any previous rows
                    $tbody.empty();

                    if (incidents.length === 0) {
                        $tbody.append('<tr><td colspan="6" style="text-align:center;">No incidents found.</td></tr>');
                    } else {
                        // Loop through each incident and append a row
                        $.each(incidents, function(index, incident) {
                            const rowHtml = `
                                <tr data-id="${escapeHtml(incident.id ?? '')}" 
                                    data-asset-id="${escapeHtml(incident.asset_id ?? '')}"
                                    data-description="${escapeHtml(incident.description ?? '')}"
                                    data-severity="${escapeHtml(incident.severity ?? '')}"
                                    data-status="${escapeHtml(incident.status ?? '')}">
                                    <td>${escapeHtml(incident.id ?? '')}</td>
                                    <td>${escapeHtml(incident.asset_id ?? '')}</td>
                                    <td>${escapeHtml(incident.description ?? '')}</td>
                                    <td>${escapeHtml(incident.severity ?? '')}</td>
                                    <td>${escapeHtml(incident.status ?? '')}</td>
                                    <td>${escapeHtml(incident.created_at ?? '')}</td>
                                    <td>
                                        <button class="btn btn-warning btn-sm btn-edit me-1">Edit</button>
                                        <button class="btn btn-danger btn-sm btn-delete">Delete</button>
                                    </td>
                                </tr>
                            `;
                            $tbody.append(rowHtml);
                        });
                    }

                    // Show table and hide error status
                    //$tableContainer.removeClass('hidden');
                    $resultBox.addClass('hidden');
                } else {
                    $resultBox.removeClass('hidden').addClass('error-box');
                    $outputText.addClass('error-text').text('Failed to load dashboard data.');
                }
            },
            error: function(xhr, status, error) {
                $resultBox.removeClass('hidden').addClass('error-box');
                $outputText.addClass('error-text').text('Error fetching data: ' + error);
            },
            complete: function() {
                $btn.prop('disabled', false).text('Fetch Incidents');
            }
        });
    });
    
    // Helper to safely escape HTML output
    function escapeHtml(str) {
        return $('<div>').text(str).html();
    }
});

//ADD INCIDENT
$(document).ready(function() {
    $('#addIncidentForm').on('submit', function(e) {
        e.preventDefault(); // Stop page reload

        // Gather form data into an object
        const formData = {
            asset_id: $('#asset_id').val(),
            reported_by: $('#reported_by').val(),
            severity: $('#severity').val(),
            description: $('#description').val(),
            status: 'Open'
        };

        // Send POST request to your API endpoint
        $.ajax({
            url: '/api/add_incident.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                $('#formAlert').html('<div class="alert alert-success">' + response.message + '</div>');
                $('#addIncidentForm')[0].reset(); // Clear form
                
                // Optional: Trigger your refresh table function here if you have one
                // fetchIncidents(); 
            },
            error: function(xhr) {
                let errorMsg = "An error occurred.";
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                $('#formAlert').html('<div class="alert alert-danger">' + errorMsg + '</div>');
            }
        });
    });
});

//DELETE INCIDENT
$(document).ready(function() {
    
    // 1. Handle Delete Button Click
    $(document).on('click', '.btn-delete', function() {
        const row = $(this).closest('tr');
        const incidentId = row.data('id');

        if (!confirm("Are you sure you want to delete incident #" + incidentId + "?")) {
            return;
        }

        $.ajax({
            url: 'api/delete_incident.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ id: incidentId }),
            success: function(response) {
                if (response.success) {
                    // Remove row smoothly from DOM
                    row.fadeOut(300, function() { $(this).remove(); });
                } else {
                    alert("Error: " + response.error);
                }
            },
            error: function(xhr) {
                alert("Failed to delete incident. Server returned status " + xhr.status);
            }
        });
    });
});

//#region UPDATE
$(document).ready(function() {

    // 1. Open modal & populate fields (Delegated for safety)
    $(document).on('click', '.btn-edit', function() {
        const row = $(this).closest('tr');
        const incidentId = row.data('id');

        $('#editIncidentId').val(incidentId);
        $('#editIncidentIdDisplay').text('Incident ID:' + incidentId);
        $('#editAssetId').val(row.data('asset-id'));
        $('#editDescription').val(row.data('description'));
        $('#editSeverity').val(row.data('severity'));
        $('#editStatus').val(row.data('status'));

        $('#editModalOverlay').css('display', 'flex');
    });

    // Close modal triggers
    $(document).on('click', '.btn-close-modal, #cancelModalBtn', function() {
        $('#editModalOverlay').css('display', 'none');
    });

    $(document).on('click', '#editModalOverlay', function(e) {
        if ($(e.target).is('#editModalOverlay')) {
            $(this).css('display', 'none');
        }
    });

    // 2. Submit edits via AJAX (Delegated version of form submit)
    $(document).on('submit', '#editForm', function(e) {
        e.preventDefault();

        const formData = {
            id: $('#editIncidentId').val(),
            asset_id: $('#editAssetId').val(),
            description: $('#editDescription').val(),
            severity: $('#editSeverity').val(),
            status: $('#editStatus').val()
        };

        $.ajax({
            url: '../api/update_incident.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                        $('#editModalOverlay').css('display', 'none');

                        // Grab the ID of the incident we just edited
                        const id = $('#editIncidentId').val();
                        const assetId = $('#editAssetId').val();
                        const description = $('#editDescription').val();
                        const severity = $('#editSeverity').val();
                        const status = $('#editStatus').val();

                        // Find the specific row in the table
                        const row = $(`tr[data-id="${id}"]`);

                        // Update the row's data attributes
                        row.data('asset-id', assetId);
                        row.data('description', description);
                        row.data('severity', severity);
                        row.data('status', status);

                        // Update the visible table cells (adjust indices to match your columns)
                        row.find('td:nth-child(2)').text(assetId);
                        row.find('td:nth-child(3)').text(description);
                        row.find('td:nth-child(4)').text(severity);
                        row.find('td:nth-child(5)').text(status);
                    } else {
                        alert('Error updating incident: ' + response.error);
                    }
            },
            error: function() {
                alert('Server error while updating incident.');
            }
        });
    });

});
//#endregion UPDATE