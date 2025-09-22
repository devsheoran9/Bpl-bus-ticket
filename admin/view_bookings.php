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
        .filter-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            border-top: 5px solid #0d6efd; /* Blue accent */
        }

        .filter-card .form-label { font-weight: 600; font-size: 0.875rem; color: #495057; }
        .filter-card .form-control, .filter-card .form-select {
            border-radius: 0.5rem; border: 1px solid #ced4da; padding: 0.6rem 1rem;
            font-size: 0.95rem; transition: all 0.2s ease;
        }
        .filter-card .form-control:focus, .filter-card .form-select:focus {
            border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.2);
        }
        .filter-card .btn {
            border-radius: 0.5rem; font-weight: 600; padding: 0.6rem 1.2rem;
            font-size: 0.95rem; transition: all 0.2s ease-in-out;
        }

        /* --- Details Panel --- */
        #details-panel {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.06);
            border: 1px solid #e9ecef;
            margin-top: 2rem; /* Spacing from filters */
            display: none; /* Hidden by default */
            border-top: 5px solid #198754; /* Green accent */
        }
        #details-panel .card-header {
            background-color: transparent; border-bottom: 1px solid #e9ecef;
            color: #198754; font-weight: 700;
            padding: 1.25rem 1.5rem; font-size: 1.2rem;
        }
        #details-panel .card-body { padding: 1.5rem; }

        .detail-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .detail-info-item { display: flex; align-items: center; gap: 0.8rem; }
        .detail-info-item .icon { font-size: 1.4rem; color: #0d6efd; width: 30px; text-align: center;}
        .detail-info-item .label { font-size: 0.85em; color: #6c757d; font-weight: 500; }
        .detail-info-item .value { font-size: 0.95em; color: #212529; font-weight: 600; }
        .detail-info-item .staff-list { margin: 0; padding: 0; list-style: none;}
        .detail-info-item .staff-list li { font-size: 0.9em; line-height: 1.4; }
        .detail-info-item .staff-list li strong { color: #495057; }

        /* --- Route Timeline Section --- */
        .route-timeline-section { 
            position: relative;
            padding-left: 25px; /* Space for the line */
        }
        /* The main vertical line */
        .route-timeline-section::before {
            content: '';
            position: absolute;
            left: 13px; /* Centered on the icon width */
            top: 5px;
            bottom: 5px;
            width: 2px;
            background-color: #e9ecef;
        }
        .route-timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .route-timeline-item:last-child { margin-bottom: 0; }
        .timeline-icon {
            position: absolute;
            left: -14px; top: 0;
            width: 28px; height: 28px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; z-index: 1;
            font-size: 0.9rem;
            box-shadow: 0 0 0 4px #ffffff;
        }
        .timeline-icon.start-point { background-color: #0d6efd; } /* Blue */
        .timeline-icon.end-point { background-color: #198754; } /* Green */
        .timeline-icon.stop-point { background-color: #6c757d; } /* Grey */

        .timeline-content { padding-left: 25px; }
        .timeline-content strong.stop-name { font-size: 1.05rem; font-weight: 700; color: #212529; }
        .timeline-content .details-row { display: flex; align-items: center; gap: 0.75rem; margin-top: 0.25rem; font-size: 0.85rem; color: #6c757d;}
        .time-info { display: flex; align-items: center; gap: 0.3rem; }
        .duration-pill { background-color: #e9ecef; color: #495057; padding: 0.15rem 0.6rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600;}

        /* --- Bookings List Panel --- */
        .bookings-list-panel {
            background: #fff; border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.06);
            border: 1px solid #e9ecef;
            margin-top: 2rem;
            border-top: 5px solid #0d6efd; /* Blue accent */
        }
        .bookings-list-panel .card-header {
            background-color: transparent; border-bottom: 1px solid #e9ecef;
            color: #0d6efd; font-weight: 700;
            padding: 1.25rem 1.5rem; font-size: 1.2rem;
        }

        /* --- DataTables Customizations --- */
        .dataTables_wrapper { padding: 1.5rem; }
        .dataTables_wrapper .dt-buttons .btn { border-radius: 0.5rem; font-size: 0.85rem; background-color: #6c757d; color: white; border-color: #6c757d; }
        .dataTables_wrapper .dataTables_filter input { border-radius: 0.5rem; border: 1px solid #ced4da; padding: 0.5rem 0.75rem; }
        .dataTables_wrapper .pagination .page-item .page-link { border-radius: 0.5rem !important; margin: 0 2px; }
        .dataTables_wrapper .pagination .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; }

        table.dataTable { border-collapse: collapse !important; }
        .bookings-table-header th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; }
        .bookings-table-body td { vertical-align: middle; font-size: 0.9rem; border-top: 1px solid #e9ecef; }
        .ticket-no-val { font-weight: 600; color: #0d6efd; }
        .seat-codes-val .badge { background-color: #e9ecef; color: #495057; }
        .fare-val { font-weight: 700; color: #198754; }
        .actions-cell .btn { margin-left: 5px; border-radius: 0.4rem; }

        /* --- Responsive Adjustments --- */
        @media (max-width: 767.98px) {
            h2.my-4 { font-size: 1.6rem; }
            .filter-card { padding: 1.5rem; }
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
                    <div class="card filter-card">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-5 col-md-12">
                                <label for="route-filter" class="form-label">Select Your Assigned Route</label>
                                <select id="route-filter" class="form-select">
                                    <option value="">-- Choose a Route --</option>
                                    <?php foreach ($routes as $route) : ?>
                                        <option value="<?php echo htmlspecialchars($route['route_id']); ?>" <?php if ($route_id_from_url == $route['route_id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($route['route_name']); ?> (Bus: <?php echo htmlspecialchars($route['bus_name']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <label for="date-filter" class="form-label">Select Travel Date</label>
                                <input type="text" id="date-filter" class="form-control" placeholder="Select Date">
                            </div>
                            <div class="col-lg-3 col-md-6 d-flex gap-2">
                                <button id="clear-filter-btn" class="btn btn-outline-secondary w-100">Clear</button>
                            </div>
                        </div>
                    </div>

                    <!-- Details Panel (populated by AJAX) -->
                    <div id="details-panel" class="card">
                        <div class="card-header"><i class="fas fa-route me-2"></i>Route & Bus Details</div>
                        <div class="card-body">
                            <div id="details-content">
                                <div class="text-center py-4"><div class="spinner-border text-primary"></div><span class="ms-2">Loading Route Details...</span></div>
                            </div>
                            <hr class="my-4">
                            <h6 class="mb-3 fw-bold"><i class="fas fa-map-signs me-2"></i>Complete Route Timeline</h6>
                            <div id="timeline-content-container">
                                <div class="text-center py-4"><div class="spinner-border text-primary"></div><span class="ms-2">Loading Timeline...</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Bookings List Panel -->
                    <div class="card bookings-list-panel">
                        <div class="card-header"><i class="fas fa-ticket-alt me-2"></i>Bookings Manifest <span id="booking-count-display" class="fw-normal fs-6"></span></div>
                        <div class="card-body">
                            <table class="table table-hover dt-responsive nowrap" id="bookings-table" style="width:100%">
                                <thead class="bookings-table-header">
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Journey</th>
                                        <th>Fare</th>
                                        <th>Status</th>
                                        <th>Passengers</th>
                                        <th>Seats</th>
                                        <th class="no-export actions-cell">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bookings-table-body">
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
            let bookingTable = $('#bookings-table').DataTable({
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'].map(type => ({
                    extend: type,
                    exportOptions: { columns: ':not(.no-export)' },
                    title: () => `${$('#route-filter option:selected').text().trim()} - ${$('#date-filter').val()}`
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
                    { data: "ticket_no", className: "ticket-no-val" },
                    { data: "journey" },
                    { data: "total_fare", className: "fare-val" },
                    { data: "booking_status" },
                    { data: "passenger_names" },
                    { data: "seat_codes" },
                    { data: "actions", orderable: false, searchable: false, className: "no-export actions-cell" }
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
            const datePicker = flatpickr("#date-filter", {
                dateFormat: "Y-m-d",
                defaultDate: initialDate,
                onChange: () => loadDashboardData()
            });

            $('#route-filter').on('change', () => loadDashboardData());

            $('#clear-filter-btn').on('click', () => {
                $('#route-filter').val('');
                datePicker.setDate(new Date());
                $('#details-panel').slideUp();
                bookingTable.clear().draw();
                updateBookingCount(0);
            });

            // --- MAIN DATA LOADING FUNCTION ---
            function loadDashboardData() {
                const routeId = $('#route-filter').val();
                const travelDate = $('#date-filter').val();

                if (!routeId || !travelDate) {
                    $('#details-panel').slideUp();
                    bookingTable.clear().draw();
                    updateBookingCount(0);
                    return;
                }

                $('#details-panel').slideDown();
                const loaderHtml = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><span class="ms-2">Loading...</span></div>';
                $('#details-content').html(loaderHtml);
                $('#timeline-content-container').html(loaderHtml);
                bookingTable.clear().draw(); // Clear old data and show processing message

                $.getJSON('function/backend/booking_actions.php', {
                    action: 'get_route_dashboard_details',
                    route_id: routeId,
                    travel_date: travelDate
                })
                .done(response => {
                    if (response.status === 'success') {
                        const { details, staff, bookings, timeline } = response;
                        
                        renderDetailsPanel(details, staff);
                        renderTimelinePanel(timeline);
                        renderBookingsTable(bookings);

                    } else {
                        const errorHtml = `<div class="alert alert-danger text-center">${htmlspecialchars(response.message)}</div>`;
                        $('#details-content').html(errorHtml);
                        $('#timeline-content-container').html('');
                    }
                })
                .fail(() => {
                    const errorHtml = `<div class="alert alert-danger text-center">Failed to load data. Please check your connection and try again.</div>`;
                    $('#details-content').html(errorHtml);
                    $('#timeline-content-container').html('');
                })
                .always(() => {
                    bookingTable.draw();
                    updateBookingCount(bookingTable.rows().count());
                });
            }

            function renderDetailsPanel(details, staff) {
                let staffHtml = '<ul class="staff-list">';
                if (staff && staff.length > 0) {
                    staff.forEach(s => {
                        staffHtml += `<li><strong>${htmlspecialchars(s.role)}:</strong> ${htmlspecialchars(s.name)}</li>`;
                    });
                } else {
                    staffHtml += `<li>Not Assigned</li>`;
                }
                staffHtml += '</ul>';

                $('#details-content').html(`
                    <div class="detail-info-grid">
                        <div class="detail-info-item"><i class="fas fa-bus icon"></i><div><span class="label">Bus / Reg #:</span><span class="value">${htmlspecialchars(details.bus_name)} (${htmlspecialchars(details.registration_number)})</span></div></div>
                        <div class="detail-info-item"><i class="fas fa-couch icon"></i><div><span class="label">Bus Type:</span><span class="value">${htmlspecialchars(details.bus_type)}</span></div></div>
                        <div class="detail-info-item"><i class="far fa-clock icon"></i><div><span class="label">Departure:</span><span class="value">${formatTime(details.departure_time)}</span></div></div>
                        <div class="detail-info-item"><i class="fas fa-users-cog icon"></i><div><span class="label">Assigned Staff:</span><span class="value">${staffHtml}</span></div></div>
                    </div>`
                );
            }

            function renderTimelinePanel(timeline) {
                let timelineHtml = '<div class="route-timeline-section">';
                if (timeline && timeline.length > 0) {
                    timeline.forEach(item => {
                        let iconClass = 'stop-point fas fa-map-marker-alt';
                        if (item.type === 'start') iconClass = 'start-point fas fa-play';
                        else if (item.type === 'end') iconClass = 'end-point fas fa-flag-checkered';
                        
                        timelineHtml += `
                        <div class="route-timeline-item">
                            <div class="timeline-icon ${iconClass}"></div>
                            <div class="timeline-content">
                                <strong class="stop-name">${htmlspecialchars(item.name)}</strong>
                                <div class="details-row">
                                    <span class="time-info"><i class="far fa-clock"></i>&nbsp;${htmlspecialchars(item.time)}</span>
                                    ${item.duration_from_prev > 0 ? `<span class="duration-pill">+${htmlspecialchars(item.duration_from_prev)} mins</span>` : ''}
                                </div>
                            </div>
                        </div>`;
                    });
                } else {
                    timelineHtml += `<p class="text-muted text-center py-3">No detailed timeline is available for this route.</p>`;
                }
                timelineHtml += '</div>';
                $('#timeline-content-container').html(timelineHtml);
            }

            function renderBookingsTable(bookings) {
                if (bookings && bookings.length > 0) {
                    const tableData = bookings.map(b => {
                        const deleteBtn = userCanDelete ? 
                            `<button class="btn btn-sm btn-outline-danger delete-booking-btn" data-booking-id="${b.booking_id}" data-ticket-no="${htmlspecialchars(b.ticket_no)}" title="Delete Booking"><i class="fas fa-trash-alt"></i></button>` : '';
                        
                        return {
                            ticket_no: `<strong>${htmlspecialchars(b.ticket_no)}</strong>`,
                            journey: `${htmlspecialchars(b.origin)} → ${htmlspecialchars(b.destination)}`,
                            passenger_names: htmlspecialchars(b.passenger_names),
                            seat_codes: b.seat_codes.split(', ').map(seat => `<span class="badge seat-codes-val">${htmlspecialchars(seat)}</span>`).join(' '),
                            total_fare: `₹${parseFloat(b.total_fare).toFixed(2)}`,
                            booking_status: `<span class="badge bg-${b.payment_status === 'PAID' ? 'success' : 'warning'}">${htmlspecialchars(b.booking_status)}</span>`,
                            actions: `<a href="generate_ticket.php?booking_id=${b.booking_id}" target="_blank" class="btn btn-sm btn-outline-primary" title="View Ticket"><i class="fas fa-eye"></i></a> ${deleteBtn}`
                        };
                    });
                    bookingTable.rows.add(tableData);
                }
            }

            function updateBookingCount(count) {
                $('#booking-count-display').text(`(${count} Total Bookings)`);
            }

            // --- DELETE HANDLER (Event Delegation) ---
            $('#bookings-table tbody').on('click', '.delete-booking-btn', function() {
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
                                bookingTable.row(row).remove().draw();
                                updateBookingCount(bookingTable.rows().count());
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        })
                        .fail(() => Swal.fire('Error!', 'Could not connect to the server.', 'error'));
                    }
                });
            });
            
            // --- INITIAL LOAD ---
            if ($('#route-filter').val()) {
                loadDashboardData();
            }

        });
    </script>
</body>
</html>