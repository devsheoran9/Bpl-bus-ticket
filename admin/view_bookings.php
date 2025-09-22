<?php
// view_bookings.php
include_once('function/_db.php');
session_security_check();
check_permission('can_view_bookings');

// --- Pre-load data from URL ---
// Validate and sanitize URL inputs
$route_id_from_url = filter_input(INPUT_GET, 'route_id', FILTER_VALIDATE_INT);
$date_from_url = null;
if (isset($_GET['date'])) {
    // Basic validation for YYYY-MM-DD format
    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $_GET['date'])) {
        $date_from_url = $_GET['date'];
    }
}


$routes = [];
try {
    $allowed_route_ids = get_assigned_route_ids_for_employee($_SESSION['user']['id']);
    if (!empty($allowed_route_ids)) {
        $placeholders = implode(',', array_fill(0, count($allowed_route_ids), '?'));
        $routes_query = $_conn_db->prepare("SELECT r.route_id, r.route_name, b.bus_name FROM routes r JOIN buses b ON r.bus_id = b.bus_id WHERE r.status = 'Active' AND r.route_id IN ($placeholders) ORDER BY r.route_name");
        $routes_query->execute($allowed_route_ids);
        $routes = $routes_query->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Optionally log the error: error_log($e->getMessage());
    $routes = [];
}
$user_can_delete = user_has_permission('can_delete_bookings');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "head.php"; ?>
    <title>Route Dashboard & Bookings</title>
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.13.6/b-2.4.1/b-html5-2.4.1/b-print-2.4.1/r-2.5.0/datatables.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css"/>

    <style>
        /* --- General Layout & Modern Theme --- */
        body {
            background-color: #f8f9fa; 
            font-family: 'Inter', sans-serif;
            color: #343a40;
            line-height: 1.6;
        }
        #wrapper{
            display: block;
        }
        .container-fluid {
            padding-top: 1.5rem;
            padding-bottom: 2rem;
        }

        h2.my-4 {
            font-weight: 700;
            color: #212529;
            font-size: 1.8rem;
            margin-bottom: 1.5rem !important;
        }

        /* --- Filter Card --- */
        .rbd_filters_panel {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            border-top: 5px solid #0d6efd; /* Blue accent */
        }

        .rbd_filters_panel .form-label { font-weight: 600; font-size: 0.875rem; color: #495057; }
        .rbd_filters_panel .form-control, .rbd_filters_panel .form-select {
            border-radius: 0.5rem; border: 1px solid #ced4da; padding: 0.6rem 1rem;
            font-size: 0.95rem; transition: all 0.2s ease;
        }
        .rbd_filters_panel .form-control:focus, .rbd_filters_panel .form-select:focus {
            border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.2);
        }
        .rbd_filters_panel .btn {
            border-radius: 0.5rem; font-weight: 600; padding: 0.6rem 1.2rem;
            font-size: 0.95rem; transition: all 0.2s ease-in-out;
        }

        /* --- Details Panel --- */
        #rbd_route_info_panel {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.06);
            border: 1px solid #e9ecef;
            margin-top: 2rem; /* Spacing from filters */
            display: none; /* Hidden by default */
            border-top: 5px solid #198754; /* Green accent */
        }
        #rbd_route_info_panel .card-header {
            background-color: transparent; border-bottom: 1px solid #e9ecef;
            color: #198754; font-weight: 700;
            padding: 1.25rem 1.5rem; font-size: 1.2rem;
        }
        #rbd_route_info_panel .card-body { padding: 1.5rem; }

        /* MODIFIED: Compact Details Grid */
        .rbd_detail_grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* Reduced min-width for compactness */
            gap: 1.25rem; /* Slightly reduced gap */
        }
        .rbd_detail_item { display: flex; align-items: center; gap: 0.8rem; }
        .rbd_detail_item .icon { font-size: 1.4rem; color: #0d6efd; width: 30px; text-align: center;}
        .rbd_detail_item .label { font-size: 0.85em; color: #6c757d; font-weight: 500; }
        .rbd_detail_item .value { font-size: 0.95em; color: #212529; font-weight: 600; }
        .rbd_detail_item .rbd_staff_list { margin: 0; padding: 0; list-style: none;}
        .rbd_detail_item .rbd_staff_list li { font-size: 0.9em; line-height: 1.4; }
        .rbd_detail_item .rbd_staff_list li strong { color: #495057; }

        /* --- NEW: Horizontal Route Timeline Section --- */
        .rbd_timeline_section {
            display: flex;
            align-items: flex-start;
            overflow-x: auto; /* Enable horizontal scrolling */
            padding-bottom: 1.5rem; /* Space for scrollbar */
            scrollbar-width: thin;
            scrollbar-color: #0d6efd #e9ecef;
        }
        .rbd_timeline_section::-webkit-scrollbar { height: 8px; }
        .rbd_timeline_section::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .rbd_timeline_section::-webkit-scrollbar-thumb { background: #0d6efd; border-radius: 10px; }
        .rbd_timeline_section::-webkit-scrollbar-thumb:hover { background: #0b5ed7; }

        .rbd_timeline_entry {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 0 0 160px; /* Each stop has a fixed width, doesn't shrink or grow */
            text-align: center;
        }
        /* The connecting line */
        .rbd_timeline_entry:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 13px; /* Align with the center of the marker */
            left: 50%;
            width: 100%;
            height: 2px;
            background-color: #e9ecef;
            z-index: 0; /* Behind the marker */
        }
        .rbd_timeline_marker {
            position: relative; /* Changed from absolute */
            width: 28px; height: 28px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; z-index: 1; /* Keep it on top */
            font-size: 0.9rem;
            box-shadow: 0 0 0 4px #ffffff;
            margin-bottom: 0.75rem; /* Space between marker and text */
        }
        .rbd_timeline_marker.start-node { background-color: #0d6efd; } /* Blue */
        .rbd_timeline_marker.end-node { background-color: #198754; } /* Green */
        .rbd_timeline_marker.stop-node { background-color: #6c757d; } /* Grey */

        .rbd_timeline_info {
            padding: 0 5px; /* Prevent long text from touching edges */
        }
        .rbd_timeline_info strong.rbd_stop_name {
            font-size: 1rem; /* Adjusted font size */
            font-weight: 700;
            color: #212529;
            word-break: break-word; /* Break long names */
        }
        .rbd_timeline_info .rbd_details {
            display: flex;
            flex-direction: column; /* Stack time and duration */
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.25rem;
            font-size: 0.85rem;
            color: #6c757d;
        }
        .rbd_time_info { display: flex; align-items: center; gap: 0.3rem; }
        .rbd_duration_tag { background-color: #e9ecef; color: #495057; padding: 0.15rem 0.6rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600;}


        /* --- Bookings List Panel --- */
        .rbd_bookings_panel {
            background: #fff; border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.06);
            border: 1px solid #e9ecef;
            margin-top: 2rem;
            border-top: 5px solid #0d6efd; /* Blue accent */
        }
        .rbd_bookings_panel .card-header {
            background-color: transparent; border-bottom: 1px solid #e9ecef;
            color: #0d6efd; font-weight: 700;
            padding: 1.25rem 1.5rem; font-size: 1.2rem;
        }

        /* --- DataTables Customizations --- */
        .dataTables_wrapper .dt-buttons .btn { border-radius: 0.5rem; font-size: 0.85rem; background-color: #6c757d; color: white; border-color: #6c757d; }
        .dataTables_wrapper .dataTables_filter input { border-radius: 0.5rem; border: 1px solid #ced4da; padding: 0.5rem 0.75rem; }
        .dataTables_wrapper .pagination .page-item .page-link { border-radius: 0.5rem !important; margin: 0 2px; }
        .dataTables_wrapper .pagination .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; }

        table.dataTable { border-collapse: collapse !important; }
        .rbd_table_header th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; }
        .rbd_table_body td { vertical-align: middle; font-size: 0.9rem; border-top: 1px solid #e9ecef; }
        .rbd_ticket_cell { font-weight: 600; color: #0d6efd; }
        .rbd_seat_cell .badge { background-color: #e9ecef; color: #495057; }
        .rbd_fare_cell { font-weight: 700; color: #198754; }
        .rbd_actions_cell .btn { margin-left: 5px; border-radius: 0.4rem; }

        /* --- Responsive Adjustments --- */
        @media (max-width: 767.98px) {
            h2.my-4 { font-size: 1.6rem; }
            .rbd_filters_panel { padding: 1.5rem; }
            .dataTables_wrapper .dt-buttons, .dataTables_wrapper .dataTables_filter { float: none; text-align: left; margin-bottom: 0.5rem; }
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <?php include_once('sidebar.php'); ?>
        <div class="main-content">
            <?php include_once('header.php'); ?>
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h2 class="my-4">Route Dashboard & Bookings</h2>
                    <?php if (user_has_permission('can_view_reports')) : ?>
                        <a href="deleted_bookings_report.php" class="btn btn-sm btn-outline-secondary mb-3"><i class="fas fa-trash-restore me-1"></i> View Deleted Bookings</a>
                    <?php endif; ?>
                </div>

                <?php if (empty($routes)) : ?>
                    <div class="alert alert-warning text-center">
                        <h4><i class="fas fa-exclamation-triangle"></i> No Routes Assigned</h4>
                        <p class="mb-0">You do not have any routes assigned to you. Please contact an administrator to gain access.</p>
                    </div>
                <?php else : ?>
                    <!-- Filter Section -->
                    <div class="card rbd_filters_panel">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-5 col-md-12">
                                <label for="rbd_route_selector" class="form-label">Select Your Assigned Route</label>
                                <select id="rbd_route_selector" class="form-select">
                                    <option value="">-- Choose a Route --</option>
                                    <?php foreach ($routes as $route) : ?>
                                        <option value="<?php echo htmlspecialchars($route['route_id']); ?>" <?php if ($route_id_from_url == $route['route_id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($route['route_name']); ?> (Bus: <?php echo htmlspecialchars($route['bus_name']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <label for="rbd_date_picker" class="form-label">Select Travel Date</label>
                                <input type="text" id="rbd_date_picker" class="form-control" placeholder="Select Date">
                            </div>
                            <div class="col-lg-3 col-md-6 d-flex gap-2">
                                <button id="rbd_clear_filters_button" class="btn btn-outline-secondary w-100">Clear</button>
                            </div>
                        </div>
                    </div>
 
                    <div id="rbd_route_info_panel" class="card">
                        <div class="card-header"><i class="fas fa-route me-2"></i>Route & Bus Details</div>
                        <div class="card-body">
                            <div id="rbd_route_info_content">
                                <div class="text-center py-4"><div class="spinner-border text-primary"></div><span class="ms-2">Loading Route Details...</span></div>
                            </div>
                            <hr class="my-4">
                            <h6 class="mb-3 fw-bold"><i class="fas fa-map-signs me-2"></i>Complete Route Timeline</h6>
                            <div id="rbd_route_timeline_container">
                                <div class="text-center py-4"><div class="spinner-border text-primary"></div><span class="ms-2">Loading Timeline...</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Bookings List Panel -->
                    <div class="card rbd_bookings_panel">
                        <div class="card-header"><i class="fas fa-ticket-alt me-2"></i>Bookings Manifest <span id="rbd_total_bookings_display" class="fw-normal fs-6"></span></div>
                        <div class="card-body">
                            <table class="table table-hover dt-responsive nowrap" id="rbd_manifest_table" style="width:100%">
                                <thead class="rbd_table_header">
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Journey</th>
                                        <th>Fare</th>
                                        <th>Status</th>
                                        <th>Passengers</th>
                                        <th>Seats</th>
                                        <th class="no-export rbd_actions_cell">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="rbd_table_body">
                                    <!-- DataTables will populate this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include "foot.php"; ?>
    <!-- JS Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.13.6/b-2.4.1/b-html5-2.4.1/b-print-2.4.1/r-2.5.0/datatables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>

    <script>
        // Pass PHP variables to JavaScript securely
        const userCanDelete = <?php echo json_encode($user_can_delete); ?>;
        const initialDateFromUrl = <?php echo json_encode($date_from_url); ?>;
        const initialDate = initialDateFromUrl || new Date().toISOString().slice(0, 10);

        // Helper function for PHP's htmlspecialchars equivalent in JS
        function htmlspecialchars(str) {
            if (typeof str !== 'string') return '';
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return str.replace(/[&<>"']/g, m => map[m]);
        }

        // Helper to format time (e.g., "14:30:00" -> "02:30 PM")
        function formatTime(time24) {
            if (!time24) return 'N/A';
            try {
                const [hours, minutes] = time24.split(':');
                const date = new Date(0);
                date.setUTCHours(parseInt(hours), parseInt(minutes));
                return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
            } catch (e) {
                return time24;
            }
        }


        $(document).ready(function() {
            // --- DATATABLE INITIALIZATION ---
            let manifestTable = $('#rbd_manifest_table').DataTable({
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'].map(type => ({
                    extend: type,
                    exportOptions: { columns: ':not(.no-export)' },
                    title: () => `${$('#rbd_route_selector option:selected').text().trim()} - ${$('#rbd_date_picker').val()}`
                })),
                language: {
                    emptyTable: "Please select a route and date to view bookings.",
                    processing: '<div class="d-flex justify-content-center align-items-center"><div class="spinner-border text-primary"></div><span class="ms-2">Loading Bookings...</span></div>'
                },
                processing: true,
                serverSide: false, // Data is fetched via our custom AJAX and loaded client-side
                responsive: {
                    details: {
                        type: 'column',
                        target: 'tr'
                    }
                },
                columns: [
                    { data: "ticket_no", className: "rbd_ticket_cell" },
                    { data: "journey" },
                    { data: "total_fare", className: "rbd_fare_cell" },
                    { data: "booking_status" },
                    { data: "passenger_names" },
                    { data: "seat_codes", className: "rbd_seat_cell" },
                    { data: "actions", orderable: false, searchable: false, className: "no-export rbd_actions_cell" }
                ],
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },  // Ticket #
                    { responsivePriority: 2, targets: 1 },  // Journey
                    { responsivePriority: 3, targets: 6 },  // Actions
                    { responsivePriority: 4, targets: 2 },  // Fare
                    { responsivePriority: 10001, targets: 3 }, // Status
                    { responsivePriority: 10002, targets: 4 }, // Passengers
                    { responsivePriority: 10003, targets: 5 }  // Seats
                ],
                order: [[0, 'desc']] // Default sort by ticket number
            });

            // --- FILTER INITIALIZATION & EVENTS ---
            const datePicker = flatpickr("#rbd_date_picker", {
                dateFormat: "Y-m-d",
                defaultDate: initialDate,
                onChange: () => loadDashboardData()
            });

            $('#rbd_route_selector').on('change', () => loadDashboardData());

            $('#rbd_clear_filters_button').on('click', () => {
                $('#rbd_route_selector').val('');
                datePicker.setDate(new Date());
                $('#rbd_route_info_panel').slideUp();
                manifestTable.clear().draw();
                updateBookingCounter(0);
            });

            // --- MAIN DATA LOADING FUNCTION ---
            function loadDashboardData() {
                const routeId = $('#rbd_route_selector').val();
                const travelDate = $('#rbd_date_picker').val();

                if (!routeId || !travelDate) {
                    $('#rbd_route_info_panel').slideUp();
                    manifestTable.clear().draw();
                    updateBookingCounter(0);
                    return;
                }

                $('#rbd_route_info_panel').slideDown();
                const loaderHtml = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><span class="ms-2">Loading...</span></div>';
                $('#rbd_route_info_content').html(loaderHtml);
                $('#rbd_route_timeline_container').html(loaderHtml);
                manifestTable.clear().draw(); // Clear old data and show processing message

                $.getJSON('function/backend/booking_actions.php', {
                    action: 'get_route_dashboard_details',
                    route_id: routeId,
                    travel_date: travelDate
                })
                .done(response => {
                    if (response.status === 'success') {
                        const { details, staff, bookings, timeline } = response;
                        
                        renderRouteInfo(details, staff);
                        renderTimelineVisual(timeline);
                        renderBookingsData(bookings);

                    } else {
                        const errorHtml = `<div class="alert alert-danger text-center">${htmlspecialchars(response.message)}</div>`;
                        $('#rbd_route_info_content').html(errorHtml);
                        $('#rbd_route_timeline_container').html('');
                    }
                })
                .fail(() => {
                    const errorHtml = `<div class="alert alert-danger text-center">Failed to load data. Please check your connection and try again.</div>`;
                    $('#rbd_route_info_content').html(errorHtml);
                    $('#rbd_route_timeline_container').html('');
                })
                .always(() => {
                    manifestTable.draw();
                    updateBookingCounter(manifestTable.rows().count());
                });
            }

            function renderRouteInfo(details, staff) {
                let staffHtml = '<ul class="rbd_staff_list">';
                if (staff && staff.length > 0) {
                    staff.forEach(s => {
                        staffHtml += `<li><strong>${htmlspecialchars(s.role)}:</strong> ${htmlspecialchars(s.name)}</li>`;
                    });
                } else {
                    staffHtml += `<li>Not Assigned</li>`;
                }
                staffHtml += '</ul>';

                $('#rbd_route_info_content').html(`
                    <div class="rbd_detail_grid">
                        <div class="rbd_detail_item"><i class="fas fa-bus icon"></i><div><span class="label">Bus / Reg #:</span><span class="value">${htmlspecialchars(details.bus_name)} (${htmlspecialchars(details.registration_number)})</span></div></div>
                        <div class="rbd_detail_item"><i class="fas fa-couch icon"></i><div><span class="label">Bus Type:</span><span class="value">${htmlspecialchars(details.bus_type)}</span></div></div>
                        <div class="rbd_detail_item"><i class="far fa-clock icon"></i><div><span class="label">Departure:</span><span class="value">${formatTime(details.departure_time)}</span></div></div>
                        <div class="rbd_detail_item"><i class="fas fa-users-cog icon"></i><div><span class="label">Assigned Staff:</span><span class="value">${staffHtml}</span></div></div>
                    </div>`
                );
            }

            function renderTimelineVisual(timeline) {
                let timelineHtml = '<div class="rbd_timeline_section">';
                if (timeline && timeline.length > 0) {
                    timeline.forEach(item => {
                        let iconClass = 'stop-node fas fa-map-marker-alt';
                        if (item.type === 'start') iconClass = 'start-node fas fa-play';
                        else if (item.type === 'end') iconClass = 'end-node fas fa-flag-checkered';
                        
                        timelineHtml += `
                        <div class="rbd_timeline_entry">
                            <div class="rbd_timeline_marker ${iconClass}"></div>
                            <div class="rbd_timeline_info">
                                <strong class="rbd_stop_name">${htmlspecialchars(item.name)}</strong>
                                <div class="rbd_details">
                                    <span class="rbd_time_info"><i class="far fa-clock"></i>&nbsp;${htmlspecialchars(item.time)}</span>
                                    ${item.duration_from_prev > 0 ? `<span class="rbd_duration_tag">+${htmlspecialchars(item.duration_from_prev)} mins</span>` : ''}
                                </div>
                            </div>
                        </div>`;
                    });
                } else {
                    timelineHtml += `<p class="text-muted text-center py-3">No detailed timeline is available for this route.</p>`;
                }
                timelineHtml += '</div>';
                $('#rbd_route_timeline_container').html(timelineHtml);
            }

            function renderBookingsData(bookings) {
                if (bookings && bookings.length > 0) {
                    const tableData = bookings.map(b => {
                        const deleteBtn = userCanDelete ? 
                            `<button class="btn btn-sm btn-outline-danger rbd_action_delete_booking" data-booking-id="${b.booking_id}" data-ticket-no="${htmlspecialchars(b.ticket_no)}" title="Delete Booking"><i class="fas fa-trash-alt"></i></button>` : '';
                        
                        return {
                            ticket_no: `<strong>${htmlspecialchars(b.ticket_no)}</strong>`,
                            journey: `${htmlspecialchars(b.origin)} → ${htmlspecialchars(b.destination)}`,
                            passenger_names: htmlspecialchars(b.passenger_names),
                            seat_codes: b.seat_codes.split(', ').map(seat => `<span class="badge">${htmlspecialchars(seat)}</span>`).join(' '),
                            total_fare: `₹${parseFloat(b.total_fare).toFixed(2)}`,
                            booking_status: `<span class="badge bg-${b.payment_status === 'PAID' ? 'success' : 'warning'}">${htmlspecialchars(b.booking_status)}</span>`,
                            actions: `<a href="generate_ticket.php?booking_id=${b.booking_id}" target="_blank" class="btn btn-sm btn-outline-primary" title="View Ticket"><i class="fas fa-eye"></i></a> ${deleteBtn}`
                        };
                    });
                    manifestTable.rows.add(tableData);
                }
            }

            function updateBookingCounter(count) {
                $('#rbd_total_bookings_display').text(`(${count} Total Bookings)`);
            }

            // --- DELETE HANDLER (Event Delegation) ---
            $('#rbd_manifest_table tbody').on('click', '.rbd_action_delete_booking', function() {
                const bookingId = $(this).data('booking-id');
                const ticketNo = $(this).data('ticket-no');
                const row = $(this).closest('tr');

                Swal.fire({
                    title: `Delete Booking #${ticketNo}?`,
                    text: "This action cannot be undone. The booking details will be logged for reporting.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.post('function/backend/booking_actions.php', { action: 'delete_booking', booking_id: bookingId }, 'json')
                        .done(response => {
                            if (response.status === 'success') {
                                Swal.fire('Deleted!', response.message, 'success');
                                manifestTable.row(row).remove().draw();
                                updateBookingCounter(manifestTable.rows().count());
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        })
                        .fail(() => Swal.fire('Error!', 'Could not connect to the server.', 'error'));
                    }
                });
            });
             
            if ($('#rbd_route_selector').val()) {
                loadDashboardData();
            }

        });
    </script>
</body>
</html>