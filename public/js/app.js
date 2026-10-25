// Track current state globally or in closure scope
let currentState = {
    page: 1,
    sort: '',
    direction: ''
};

$(document).ajaxStart(function() {
    $('#loading-overlay').fadeIn(150);
});

$(document).ajaxStop(function() {
    $('#loading-overlay').fadeOut(150);
});
// Centralized function to fetch, sort, paginate, and render incidents
function loadIncidents(page = 1, sort = 'created_at', direction = 'DESC') {
    const $container = $('#incidents-container');
    
    // 1. Lock the container to its exact pixel height right now so the body doesn't shrink
    $container.css('height', $container.height());
    const $resultBox = $('#result-box');
    const $outputText = $('#output-text');
    const $tbody = $('#incidents-tbody');

    // Optional: show loading state on a general status or button
    $tbody.html('<tr><td colspan="7" style="text-align:center;">Loading incidents...</td></tr>');

    $.ajax({
        url: 'api/get_incidents.php',
        method: 'GET',
        data: {
            page: page,
            sort: sort,
            direction: direction
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Inside success: function(response) {
                const summary = response.data.summary; // Assume your backend passes aggregate counts & MTTR

                $('#metric-total').text(summary.total_incidents);
                $('#metric-open').text(summary.open_count);
                $('#metric-critical').text(summary.critical_count);
                $('#metric-mttr').text(summary.mttr_hours + ' hrs');

                // Update visual bar percentages
                const total = summary.total_incidents || 1;
                const openPct = (summary.open_count / total) * 100;
                const progPct = (summary.in_progress_count / total) * 100;
                const resPct = (summary.resolved_count / total) * 100;

                $('#bar-open').css('width', openPct + '%');
                $('#bar-progress').css('width', progPct + '%');
                $('#bar-resolved').css('width', resPct + '%');

                $('#breakdown-counts').text(`Open: ${summary.open_count} | In Progress: ${summary.in_progress_count} | Resolved: ${summary.resolved_count}`);

                const incidents = response.data.incidents || [];

                $tbody.empty();

                if (incidents.length === 0) {
                    $tbody.append('<tr><td colspan="8" style="text-align:center;">No incidents found.</td></tr>');
                } else {
                    $.each(incidents, function(index, incident) {
                        const rowHtml = `
                            <tr data-id="${escapeHtml(incident.id ?? '')}" 
                                data-asset-id="${escapeHtml(incident.asset_id ?? '')}"
                                data-description="${escapeHtml(incident.description ?? '')}"
                                data-severity="${escapeHtml(incident.severity ?? '')}"
                                data-status="${escapeHtml(incident.status ?? '')}">
                                <td>${escapeHtml(incident.id ?? '')}</td>
                                <td>${escapeHtml(incident.asset_id ?? '')}</td>
                                <td>${escapeHtml(incident.asset_name ?? '')}</td>
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
                $resultBox.addClass('hidden');
                // 2. Render Pagination Controls based on true grand total
                const itemsPerPage = 25;
                const totalPages = Math.ceil(total/ itemsPerPage);
                renderPagination(totalPages, currentState.page);
                window.scrollTo({
                    top: scrollY,
                    behavior: 'instant'
                });
            } else {
                $resultBox.removeClass('hidden').addClass('error-box');
                $outputText.addClass('error-text').text('Failed to load dashboard data.');
            }//redundant
        },
        error: function(xhr, status, error) {
            $resultBox.removeClass('hidden').addClass('error-box');
            $outputText.addClass('error-text').text('Error fetching data: ' + error);
        },
        complete: function() {
                    
                    $container.css('height', '');
                }      
    });
}

function renderPagination(totalPages, currentPage) {
    const $container = $('#pagination-container');
    $container.empty();
    if (totalPages <= 1) return; // Hide pagination if everything fits on one page

    for (let i = 1; i <= totalPages; i++) {
        const activeClass = (i === currentPage) ? 'active' : '';
        const btnHtml = `<button type="button" class="page-btn ${activeClass}" data-page="${i}">${i}</button>`;
        $container.append(btnHtml);
    }
}
// Helper to safely escape HTML output
function escapeHtml(str) {
    return $('<div>').text(str).html();
}
// Click handler for pagination buttons
$(document).on('click', '.page-btn', function(e) {
    e.preventDefault();
    currentState.page = parseInt($(this).data('page'), 10);
    loadIncidents(currentState.page, currentState.sort, currentState.direction);
});

//#region on page load, fetch incidents and populate the table, and attach event handlers for sorting and refreshing
$(document).ready(function() {


    // 1. Fetch automatically on initial page load
    loadIncidents(currentState.page, currentState.sort, currentState.direction);

    // 2. Fetch or refresh when the button is clicked
    $('#load-data-btn').on('click', function() {
        loadIncidents(currentState.page, currentState.sort, currentState.direction);
    });

    // 3. Click handler for sortable column headers (Severity, Status, Created At)
    $(document).on('click', '.sort-header', function(e) {
        e.preventDefault();
        const clickedCol = $(this).data('column');

        $('.sort-header').removeClass('asc desc'); // Clear from all headers

        // Toggle sort direction logic: ASC -> DESC -> None (reset)
        if (currentState.sort === clickedCol) {
            if (currentState.direction === 'ASC') {
                currentState.direction = 'DESC';
                $(this).addClass('desc');
            
            } else if (currentState.direction === 'DESC') {
                // Reset sorting back to default
                currentState.sort = 'created_at';
                currentState.direction = '';
          
            } else {
            currentState.sort = clickedCol;
            currentState.direction = 'ASC'; // Default new column to ASC
            $(this).addClass('asc');
          
            }
        } else {
            currentState.sort = clickedCol;
            currentState.direction = 'ASC'; // Default new column to ASC
            $(this).addClass('asc');
           
        }

        // Reload table with new sorting state
        loadIncidents(currentState.page, currentState.sort, currentState.direction);
    });
});
//#endregion


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
                loadIncidents(currentState.page, currentState.sort, currentState.direction);

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
                }//redundant
                loadIncidents(currentState.page, currentState.sort, currentState.direction);
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
                    }//redundant
                    loadIncidents(currentState.page, currentState.sort, currentState.direction);
            },
            error: function() {
                alert('Server error while updating incident.');
            }
        });
    });

});
//#endregion UPDATE