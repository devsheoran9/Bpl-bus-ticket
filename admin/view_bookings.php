<?php
// view_bookings.php (Card Grid with Conditional Delete Button)
include_once('function/_db.php');
session_security_check();
check_permission('can_view_bookings'); // Check if user can even view this page

try {
    // PHP to fetch routes for the filter dropdown
    $routes_query = $_conn_db->query("
        SELECT r.route_id, r.route_name, b.bus_name 
        FROM routes r 
        JOIN buses b ON r.bus_id = b.bus_id 
        WHERE r.status = 'Active' 
        ORDER BY r.route_name
    ");
    $routes = $routes_query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { 
    $routes = []; 
}

// --- NEW: Check for delete permission ONCE on the server side ---
$user_can_delete = user_has_permission('can_delete_bookings');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Route Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    
    <style>
        /* All CSS from the previous version remains exactly the same */
        .filter-card { background-color: #f8f9fa; }
        #details-panel { display: none; }
        .detail-item .label { font-weight: 600; color: #6c757d; }
        .timeline { display: flex; flex-wrap: wrap; list-style: none; padding: 0; }
        .timeline-item { position: relative; padding-left: 20px; margin-right: 20px; color: #6c757d; margin-bottom: 5px; }
        .timeline-item::before { content: ''; position: absolute; left: 0; top: 8px; width: 10px; height: 10px; border-radius: 50%; background-color: #ced4da; }
        .timeline-item:not(:last-child)::after { content: ''; position: absolute; left: 4px; top: 13px; width: 25px; height: 2px; background-color: #ced4da; }
        .bookings-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
        .booking-card { background-color: #fff; border: 1px solid #dee2e6; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); display: flex; flex-direction: column; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .booking-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .booking-card-header { padding: 0.75rem 1.25rem; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; border-top-left-radius: 8px; border-top-right-radius: 8px; }
        .booking-card-header .ticket-no { font-size: 1.1rem; font-weight: 600; color: #0d6efd; }
        .booking-card-body { padding: 1.25rem; flex-grow: 1; }
        .booking-card .info-row { display: flex; align-items: center; margin-bottom: 0.75rem; font-size: 0.9rem; }
        .booking-card .info-row i { color: #6c757d; width: 20px; margin-right: 10px; text-align: center; }
        .booking-card-footer { padding: 0.75rem 1.25rem; background-color: #f8f9fa; border-top: 1px solid #dee2e6; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; display: flex; justify-content: flex-end; gap: 0.5rem; }
        #no-results-message { grid-column: 1 / -1; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Route Dashboard & Bookings</h2>

            <!-- Filter Section and Details Panel (No HTML Changes) -->
            <div class="card filter-card mb-4">
                 <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4"><label for="route-filter" class="form-label fw-bold">Select Route & Bus</label><select id="route-filter" class="form-select"><option value="">-- Choose a Route --</option><?php foreach ($routes as $route): ?><option value="<?php echo $route['route_id']; ?>"><?php echo htmlspecialchars($route['route_name']); ?> (Bus: <?php echo htmlspecialchars($route['bus_name']); ?>)</option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label for="date-filter" class="form-label fw-bold">Select Travel Date</label><input type="text" id="date-filter" class="form-control"></div>
                        <div class="col-md-3"><label for="search-filter" class="form-label fw-bold">Search Bookings</label><input type="text" id="search-filter" class="form-control" placeholder="By Ticket No, Name..."></div>
                        <div class="col-md-2"><button id="clear-filter-btn" class="btn btn-outline-secondary w-100">Clear</button></div>
                    </div>
                </div>
            </div>
            <div id="details-panel" class="card mb-4">
                <div class="card-header bg-primary text-white"><h5 class="mb-0">Route & Bus Details</h5></div>
                <div class="card-body">
                    <div id="details-content" class="row"></div><hr>
                    <h6><i class="fas fa-map-signs me-2"></i>Complete Route Timeline</h6>
                    <ul id="timeline-content" class="timeline"></ul>
                </div>
            </div>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Booking List</h5>
                    <span id="booking-count" class="badge bg-primary rounded-pill">0 Bookings</span>
                </div>
                <div class="card-body">
                    <div id="bookings-container" class="bookings-grid">
                        <div id="no-results-message" class="text-center text-muted p-4 w-100">Please select a route to see bookings.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "foot.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
// --- NEW: Pass the PHP permission check result to JavaScript ---
const userCanDelete = <?php echo $user_can_delete ? 'true' : 'false'; ?>;

$(document).ready(function() {
    // --- All filter and event listener JS remains the same ---
    const datePicker = flatpickr("#date-filter", {
        dateFormat: "Y-m-d", defaultDate: "today", onChange: function() { loadDashboardData(); }
    });
    $('#route-filter').on('change', function() { loadDashboardData(); });
    $('#clear-filter-btn').on('click', function() {
        $('#route-filter').val(''); $('#search-filter').val(''); datePicker.setDate(new Date());
        $('#details-panel').hide();
        $('#bookings-container').html('<div id="no-results-message" class="text-center text-muted p-4 w-100">Please select a route to see bookings.</div>');
        updateBookingCount(0);
    });
    $('#search-filter').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase(); let visibleCount = 0;
        $('.booking-card').each(function() {
            const cardText = $(this).text().toLowerCase();
            if (cardText.includes(searchTerm)) { $(this).show(); visibleCount++; } else { $(this).hide(); }
        });
        $('#no-results-message').toggle(visibleCount === 0 && $('.booking-card').length > 0);
        updateBookingCount(visibleCount);
    });

    // --- Main Data Loading Function ---
    function loadDashboardData() {
        const routeId = $('#route-filter').val(); 
        const travelDate = $('#date-filter').val();
        const bookingsContainer = $('#bookings-container');
        $('#search-filter').val('');

        if (!routeId) {
            $('#details-panel').hide();
            bookingsContainer.html('<div id="no-results-message" class="text-center text-muted p-4 w-100">Please select a route.</div>');
            updateBookingCount(0);
            return;
        }

        $('#details-panel').show();
        $('#details-content').html('<div class="d-flex justify-content-center p-3"><div class="spinner-border text-primary"></div></div>');
        $('#timeline-content').html('Loading timeline...');
        bookingsContainer.html('<div class="d-flex justify-content-center p-5 w-100"><div class="spinner-border text-primary"></div></div>');

        $.getJSON('function/backend/booking_actions.php', { action: 'get_route_dashboard_details', route_id: routeId, travel_date: travelDate })
        .done(function(response) {
            if (response.status === 'success') {
                const details = response.details;
                $('#details-content').html(`<div class="col-md-6"><p class="mb-2"><span class="label">Bus:</span> ${details.bus_name} (${details.registration_number})</p><p class="mb-0"><span class="label">Type:</span> ${details.bus_type}</p></div><div class="col-md-6"><p class="mb-2"><span class="label">Operator:</span> ${details.operator_name}</p><p class="mb-0"><span class="label">Contact:</span> ${details.operator_phone}</p></div>`);
                let timelineHtml = '';
                response.timeline.forEach(stop => { timelineHtml += `<li class="timeline-item">${stop}</li>`; });
                $('#timeline-content').html(timelineHtml);
                
                if (response.bookings.length > 0) {
                    let cards = '';
                    response.bookings.forEach(function(booking) {
                        const seatBadges = booking.seat_codes.split(', ').map(seat => `<span class="badge bg-dark me-1">${seat}</span>`).join('');
                        
                        // --- FIX: Conditionally build the delete button ---
                        let deleteButtonHtml = '';
                        if (userCanDelete) {
                            deleteButtonHtml = `<button class="btn btn-sm btn-outline-danger delete-booking-btn" data-booking-id="${booking.booking_id}" data-ticket-no="${booking.ticket_no || 'N/A'}" title="Delete Booking"><i class="fas fa-trash-alt"></i> Delete</button>`;
                        }
                        
                        cards += `
                            <div class="booking-card" id="booking-card-${booking.booking_id}">
                                <div class="booking-card-header"><span class="ticket-no"><i class="fas fa-ticket-alt me-2"></i>${booking.ticket_no || 'N/A'}</span></div>
                                <div class="booking-card-body">
                                    <div class="info-row"><i class="fas fa-users fa-fw"></i><span>${booking.passenger_names}</span></div>
                                    <div class="info-row"><i class="fas fa-chair fa-fw"></i><div>${seatBadges}</div></div>
                                    <div class="info-row"><i class="fas fa-rupee-sign fa-fw"></i><strong>${parseFloat(booking.total_fare).toFixed(2)}</strong></div>
                                    <div class="info-row"><i class="fas fa-calendar-check fa-fw"></i><small>${new Date(booking.created_at).toLocaleString('en-GB')}</small></div>
                                </div>
                                <div class="booking-card-footer">
                                    <a href="generate_ticket.php?booking_id=${booking.booking_id}" target="_blank" class="btn btn-sm btn-outline-primary" title="View Ticket"><i class="fas fa-eye"></i> View</a>
                                    ${deleteButtonHtml}
                                </div>
                            </div>
                        `;
                    });
                    bookingsContainer.html(cards + '<div id="no-results-message" class="text-center text-muted p-4 w-100" style="display:none;"></div>');
                    updateBookingCount(response.bookings.length);
                } else {
                    bookingsContainer.html('<div id="no-results-message" class="text-center text-muted p-4 w-100">No bookings found for this date.</div>');
                    updateBookingCount(0);
                }
            } else { /* ... error handling ... */ }
        })
        .fail(function() { /* ... error handling ... */ });
    }

    function updateBookingCount(count) { /* ... */ }

    // Delete Functionality (no changes needed, it will only trigger if the button exists)
    $(document).on('click', '.delete-booking-btn', function() { /* ... */ });
});
</script>
</body>
</html>