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
                        $tbody.append('<tr><td colspan="4" style="text-align:center;">No incidents found.</td></tr>');
                    } else {
                        // Loop through each incident and append a row
                        $.each(incidents, function(index, incident) {
                            const rowHtml = `
                                <tr>
                                    <td>${escapeHtml(incident.id ?? '')}</td>
                                    <td>${escapeHtml(incident.title ?? incident.name ?? '')}</td>
                                    <td>${escapeHtml(incident.status ?? '')}</td>
                                    <td>${escapeHtml(incident.created_at ?? '')}</td>
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