<?php
// view_bookings.php
include_once('function/_db.php');
session_security_check();
check_permission('can_view_bookings');

// --- Pre-load data from URL ---
$route_id_from_url = filter_input(INPUT_GET, 'route_id', FILTER_VALIDATE_INT);
$date_from_url = null;
if (isset($_GET['date'])) {
    if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $_GET['date'])) {
        $date_from_url = $_GET['date'];
    }
}

$routes = [];
try {
    $allowed_route_ids = get_assigned_route_ids_for_employee($_SESSION['user']['id']);
    if (!empty($allowed_route_ids)) {
        $placeholders = implode(',', array_fill(0, count($allowed_route_ids), '?'));
        $routes_query = $_conn_db->prepare("SELECT r.route_id, r.route_name, b.bus_name FROM routes r JOIN buses b ON r.bus_id = b.bus_id WHERE r.status = 'Active' ORDER BY r.route_name");
        $routes_query->execute();
        $routes = $routes_query->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $routes = [];
}
$user_can_delete = user_has_permission('can_delete_bookings');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "head.php"; ?>
    <title>Route Mission Control</title>
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.13.6/b-2.4.1/b-html5-2.4.1/b-print-2.4.1/r-2.5.0/datatables.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" />

    <style>
        :root {
            --primary-color: #4A90E2;
            --secondary-color: #50E3C2;
            --text-dark: #2c3e50;
            --text-light: #8492a6;
            --bg-light: #f8f9fa;
            --bg-white: #ffffff;
            --border-color: #e2e8f0;
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 0.75rem;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
        }

        #wrapper {
            display: block;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-weight: 800;
            font-size: 2rem;
            letter-spacing: -0.5px;
            color: var(--text-dark);
        }

        /* --- Card Styles --- */
        .card-custom {
            background-color: var(--bg-white);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card-custom:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-4px);
        }

        .card-header-custom {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-white);
            color: var(--primary-color);
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-body-custom {
            padding: 1.5rem;
        }

        /* --- Filter Panel --- */
        .filters-panel .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .filters-panel .form-control,
        .filters-panel .form-select {
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .filters-panel .form-control:focus,
        .filters-panel .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.2);
        }

        /* --- Details Panel --- */
        #route-info-panel {
            display: none;
        }

        .details-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .details-button:hover {
            background-color: #357ABD;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
        }

        /* --- Route Details Modal Styles --- */
        .modal-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .modal-title {
            font-weight: 600;
        }

        .modal-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .modal-detail-item {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 1.25rem;
            border-left: 4px solid var(--primary-color);
        }

        .modal-detail-item .icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
            display: block;
        }

        .modal-detail-item .label {
            font-size: 0.9rem;
            color: var(--text-light);
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .modal-detail-item .value {
            font-size: 1.1rem;
            color: var(--text-dark);
            font-weight: 700;
            line-height: 1.3;
        }

        .modal-staff-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .modal-staff-list li {
            background-color: white;
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            border-left: 3px solid var(--secondary-color);
            font-size: 0.95rem;
        }

        .modal-staff-list li strong {
            color: var(--secondary-color);
            display: inline-block;
            min-width: 90px;
        }

        /* --- Horizontal Timeline --- */
        .timeline-section {
            display: flex;
            overflow-x: auto;
            padding-bottom: 1.5rem;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) var(--border-color);
        }

        .timeline-section::-webkit-scrollbar {
            height: 8px;
        }

        .timeline-section::-webkit-scrollbar-track {
            background: var(--bg-light);
            border-radius: 10px;
        }

        .timeline-section::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 10px;
        }

        .timeline-entry {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 0 0 180px;
            text-align: center;
        }

        .timeline-entry:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 15px;
            left: 50%;
            width: 100%;
            height: 3px;
            background-color: var(--border-color);
            z-index: 0;
        }

        .timeline-marker {
            position: relative;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            z-index: 1;
            font-size: 1rem;
            box-shadow: 0 0 0 5px var(--bg-white);
            margin-bottom: 0.75rem;
            border: 2px solid;
        }

        .timeline-marker.start-node {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .timeline-marker.end-node {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .timeline-marker.stop-node {
            background-color: #adb5bd;
            border-color: #adb5bd;
        }

        .timeline-info .stop-name {
            font-size: 1rem;
            font-weight: 700;
            word-break: break-word;
        }

        .timeline-info .details {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .duration-tag {
            background-color: var(--bg-light);
            color: var(--text-light);
            padding: 0.2rem 0.6rem;
            border-radius: 1rem;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* --- DataTables Customizations --- */
        .dataTables_wrapper .dt-buttons .btn {
            font-size: 0.4rem;
        }

        table.dataTable {
            border-collapse: collapse !important;
            width: 100% !important;
        }

        .table-header th {
            background-color: var(--bg-light);
            border-bottom: 2px solid var(--border-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .table-body td {
            vertical-align: middle;
            font-size: 0.9rem;
            border-top: 1px solid var(--border-color);
        }

        .ticket-cell {
            font-weight: 600;
            color: var(--primary-color);
        }

        .seat-cell .badge {
            background-color: var(--bg-light);
            color: var(--text-light);
            border: 1px solid var(--border-color);
        }

        .fare-cell {
            font-weight: 700;
            color: #28a745;
        }

        .actions-cell .btn {
            margin-left: 5px;
            border-radius: 0.4rem;
        }

        .status-badge {
            padding: 0.3rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }

        .status-badge.bg-success {
            background-color: rgba(40, 167, 69, 0.1) !important;
            color: #1e7e34 !important;
        }

        .status-badge.bg-warning {
            background-color: rgba(255, 193, 7, 0.1) !important;
            color: #b98b04 !important;
        }
        div.dataTables_wrapper {
    overflow-x: auto;
}
    </style>
</head>

<body>
    <div id="wrapper">
        <?php include_once('sidebar.php'); ?>
        <div class="main-content">
            <?php include_once('header.php'); ?>
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center flex-wrap page-header">
                    <h2 class="page-title"><i class="fas fa-rocket text-primary me-2"></i>Route Mission Control</h2>
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
                    <div class="card-custom filters-panel p-1">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-5 col-md-12">
                                <label for="route-selector" class="form-label">Select Your Assigned Route</label>
                                <select id="route-selector" class="form-select">
                                    <option value="">-- Choose a Route --</option>
                                    <?php foreach ($routes as $route) : ?>
                                        <option value="<?php echo htmlspecialchars($route['route_id']); ?>" <?php if ($route_id_from_url == $route['route_id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($route['route_name']); ?> (Bus: <?php echo htmlspecialchars($route['bus_name']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <label for="date-picker" class="form-label">Select Travel Date</label>
                                <input type="text" id="date-picker" class="form-control" placeholder="Select Date">
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <button id="clear-filters-button" class="btn btn-outline-secondary w-100">Clear</button>
                            </div>
                        </div>
                    </div>

                    <div id="route-info-panel" class="card-custom">
                        <div class="card-header-custom">
                            <span><i class="fas fa-map-signs me-2"></i>Complete Route Timeline</span>
                            <button id="details-button" class="details-button">
                                <i class="fas fa-info-circle me-1"></i> View Bus & Crew Details
                            </button>
                        </div>
                        <div class="card-body-custom">
                            <div id="route-timeline-container">
                            </div>
                        </div>
                    </div>

                    <!-- Route Details Modal -->
                    <div class="modal fade" id="route-details-modal" tabindex="-1" aria-labelledby="route-details-modal-label" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="route-details-modal-label"><i class="fas fa-route me-2"></i>Complete Route & Bus Information</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="modal-route-info-content">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bookings List Panel -->
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <span><i class="fas fa-ticket-alt me-2"></i>Bookings Manifest</span>
                            <span id="total-bookings-display" class="fw-normal fs-6 text-white-50"></span>
                        </div>
                        <div class="card-body-custom">
                            <table class="table table-hover dt-responsive nowrap" id="manifest-table">
                                <thead class="table-header">
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
                                <tbody class="table-body">
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.13.6/b-2.4.1/b-html5-2.4.1/b-print-2.4.1/r-2.5.0/datatables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>

    <script>
        const userCanDelete = <?php echo json_encode($user_can_delete); ?>;
        const initialDateFromUrl = <?php echo json_encode($date_from_url); ?>;
        const initialDate = initialDateFromUrl || new Date().toISOString().slice(0, 10);

        let currentRouteDetails = null;
        let currentRouteStaff = null;

        function htmlspecialchars(str) {
            if (typeof str !== 'string') return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return str.replace(/[&<>"']/g, m => map[m]);
        }

        function formatTime(time24) {
            if (!time24) return 'N/A';
            try {
                const [h, m] = time24.split(':');
                const d = new Date(0);
                d.setUTCHours(parseInt(h), parseInt(m));
                return d.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            } catch (e) {
                return time24;
            }
        }

        $(document).ready(function() {
            let manifestTable = $('#manifest-table').DataTable({
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'].map(type => ({
                    extend: type,
                    exportOptions: {
                        columns: ':not(.no-export)'
                    },
                    title: () => `${$('#route-selector option:selected').text().trim()} - ${$('#date-picker').val()}`
                })),
                language: {
                    emptyTable: "Please select a route and date to view bookings.",
                    processing: '<div class="d-flex justify-content-center align-items-center"><div class="spinner-border text-primary"></div><span class="ms-2">Loading Bookings...</span></div>'
                },
                processing: true,
                serverSide: false,
                responsive: {
                    details: {
                        type: 'column',
                        target: 'tr'
                    }
                },
                columns: [{
                        data: "ticket_no",
                        className: "ticket-cell"
                    },
                    {
                        data: "journey"
                    },
                    {
                        data: "total_fare",
                        className: "fare-cell"
                    },
                    {
                        data: "booking_status"
                    },
                    {
                        data: "passenger_names"
                    },
                    {
                        data: "seat_codes",
                        className: "seat-cell"
                    },
                    {
                        data: "actions",
                        orderable: false,
                        searchable: false,
                        className: "no-export actions-cell", 
                    }
                ],
                responsive: {
                    details: {
                        type: 'column',
                        target: 'tr'
                    }
                },
                 
            });

            const datePicker = flatpickr("#date-picker", {
                dateFormat: "Y-m-d",
                defaultDate: initialDate,
                onChange: loadDashboardData
            });
            $('#route-selector').on('change', loadDashboardData);
            $('#clear-filters-button').on('click', () => {
                $('#route-selector').val('');
                datePicker.setDate(new Date());
                $('#route-info-panel').slideUp();
                manifestTable.clear().draw();
                updateBookingCounter(0);
                currentRouteDetails = currentRouteStaff = null;
            });
            $('#details-button').on('click', () => {
                if (currentRouteDetails && currentRouteStaff) {
                    showRouteDetailsModal(currentRouteDetails, currentRouteStaff);
                }
            });

            function loadDashboardData() {
                const routeId = $('#route-selector').val();
                const travelDate = $('#date-picker').val();
                if (!routeId || !travelDate) {
                    $('#route-info-panel').slideUp();
                    manifestTable.clear().draw();
                    updateBookingCounter(0);
                    currentRouteDetails = currentRouteStaff = null;
                    return;
                }
                $('#route-info-panel').slideDown();
                const loaderHtml = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Loading...</p></div>';
                $('#route-timeline-container').html(loaderHtml);
                manifestTable.clear().draw();

                $.getJSON('function/backend/booking_actions.php', {
                        action: 'get_route_dashboard_details',
                        route_id: routeId,
                        travel_date: travelDate
                    })
                    .done(response => {
                        if (response.status === 'success') {
                            currentRouteDetails = response.details;
                            currentRouteStaff = response.staff;
                            renderTimelineVisual(response.timeline);
                            renderBookingsData(response.bookings);
                        } else {
                            const errorHtml = `<div class="alert alert-danger text-center">${htmlspecialchars(response.message)}</div>`;
                            $('#route-timeline-container').html(errorHtml);
                            currentRouteDetails = currentRouteStaff = null;
                        }
                    })
                    .fail(() => {
                        const errorHtml = `<div class="alert alert-danger text-center">Failed to load data. Please try again.</div>`;
                        $('#route-timeline-container').html(errorHtml);
                        currentRouteDetails = currentRouteStaff = null;
                    })
                    .always(() => {
                        updateBookingCounter(manifestTable.rows().count());
                    });
            }

            function showRouteDetailsModal(details, staff) {
                let staffHtml = '<ul class="modal-staff-list">';
                if (staff && staff.length > 0) {
                    staff.forEach(s => {
                        staffHtml += `<li><strong>${htmlspecialchars(s.role)}:</strong> ${htmlspecialchars(s.name)}</li>`;
                    });
                } else {
                    staffHtml += `<li style="font-style: italic;">No staff assigned</li>`;
                }
                staffHtml += '</ul>';

                const modalContent = `
                    <div class="modal-detail-grid">
                        <div class="modal-detail-item"><i class="fas fa-bus icon"></i><div><div class="label">Bus Name</div><div class="value">${htmlspecialchars(details.bus_name)}</div></div></div>
                        <div class="modal-detail-item"><i class="fas fa-id-card icon"></i><div><div class="label">Registration No.</div><div class="value">${htmlspecialchars(details.registration_number)}</div></div></div>
                        <div class="modal-detail-item"><i class="fas fa-couch icon"></i><div><div class="label">Bus Type</div><div class="value">${htmlspecialchars(details.bus_type)}</div></div></div>
                      
                        <div class="modal-detail-item"><i class="fas fa-map-marker-alt icon"></i><div><div class="label">Origin</div><div class="value">${htmlspecialchars(details.starting_point)}</div></div></div>
                        <div class="modal-detail-item"><i class="fas fa-flag-checkered icon"></i><div><div class="label">Destination</div><div class="value">${htmlspecialchars(details.ending_point)}</div></div></div>
                    </div>
                    <div class="modal-detail-item"><i class="fas fa-users-cog icon"></i><div><div class="label">Assigned Crew</div><div class="value">${staffHtml}</div></div></div>`;

                $('#modal-route-info-content').html(modalContent);
                $('#route-details-modal').modal('show');
            }

            function renderTimelineVisual(timeline) {
                let timelineHtml = '<div class="timeline-section">';
                if (timeline && timeline.length > 0) {
                    timeline.forEach(item => {
                        let icon = 'fa-map-marker-alt';
                        if (item.type === 'start') icon = 'fa-play';
                        else if (item.type === 'end') icon = 'fa-flag-checkered';
                        timelineHtml += `
                        <div class="timeline-entry">
                            <div class="timeline-marker ${item.type}-node"><i class="fas ${icon}"></i></div>
                            <div class="timeline-info">
                                <div class="stop-name">${htmlspecialchars(item.name)}</div>
                                <div class="details">${htmlspecialchars(item.time)}</div>
                                ${item.duration_from_prev > 0 ? `<div class="duration-tag mt-1">+${item.duration_from_prev} mins</div>` : ''}
                            </div>
                        </div>`;
                    });
                } else {
                    timelineHtml += `<p class="text-muted text-center py-3 w-100">No timeline available.</p>`;
                }
                timelineHtml += '</div>';
                $('#route-timeline-container').html(timelineHtml);
            }

            function renderBookingsData(bookings) {
                manifestTable.clear();
                if (bookings && bookings.length > 0) {
                    const tableData = bookings.map(b => {
                        const deleteBtn = userCanDelete ? `<button class="btn btn-sm btn-outline-danger rbd_action_delete_booking" data-booking-id="${b.booking_id}" data-ticket-no="${htmlspecialchars(b.ticket_no)}" title="Delete Booking"><i class="fas fa-trash-alt"></i></button>` : '';
                        return {
                            ticket_no: htmlspecialchars(b.ticket_no),
                            journey: `${htmlspecialchars(b.origin)} → ${htmlspecialchars(b.destination)}`,
                            passenger_names: htmlspecialchars(b.passenger_names),
                            seat_codes: b.seat_codes.split(', ').map(s => `<span class="badge">${htmlspecialchars(s)}</span>`).join(' '),
                            total_fare: `₹${parseFloat(b.total_fare).toFixed(2)}`,
                            booking_status: `<span class="status-badge bg-${b.payment_status === 'PAID' ? 'success' : 'warning'}">${htmlspecialchars(b.booking_status)}</span>`,
                            actions: `<a href="generate_ticket.php?booking_id=${b.booking_id}" target="_blank" class="btn btn-sm btn-outline-primary" title="View Ticket"><i class="fas fa-eye"></i></a> ${deleteBtn}`
                        };
                    });
                    manifestTable.rows.add(tableData);
                }
                manifestTable.draw();
            }

            function updateBookingCounter(count) {
                $('#total-bookings-display').text(`(${count} Total Bookings)`);
            }

            $('#manifest-table tbody').on('click', '.rbd_action_delete_booking', function() {
                const id = $(this).data('booking-id'),
                    ticket = $(this).data('ticket-no'),
                    row = $(this).closest('tr');
                Swal.fire({
                    title: `Delete Booking #${ticket}?`,
                    text: "This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.post('function/backend/booking_actions.php', {
                                action: 'delete_booking',
                                booking_id: id
                            }, 'json')
                            .done(res => {
                                if (res.status === 'success') {
                                    Swal.fire('Deleted!', res.message, 'success');
                                    manifestTable.row(row).remove().draw();
                                    updateBookingCounter(manifestTable.rows().count());
                                } else {
                                    Swal.fire('Error!', res.message, 'error');
                                }
                            })
                            .fail(() => Swal.fire('Error!', 'Could not connect to server.', 'error'));
                    }
                });
            });

            if ($('#route-selector').val()) {
                loadDashboardData();
            }
        });
    </script>
</body>

</html>