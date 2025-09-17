<?php
// view_bookings.php
include_once('function/_db.php');
session_security_check();
check_permission('can_view_bookings');
$route_id_from_url = filter_input(INPUT_GET, 'route_id', FILTER_VALIDATE_INT);
$date_from_url = $_GET['date'] ?? null; // Keep as string for the date picker
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
    $routes = [];
}
$user_can_delete = user_has_permission('can_delete_bookings');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "head.php"; ?>
    <title>Route Dashboard & Bookings</title>
    <!-- Viewport meta tag is crucial for responsiveness -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> 
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        :root {
            --table-header-bg: #f8f9fa;
            --table-border-color: #dee2e6;
            --text-dark: #212529;
            --text-light: #6c757d;
            --primary-color: #0d6efd; /* Bootstrap primary blue */
            --success-color: #198754; /* Bootstrap success green */
            --warning-color: #ffc107; /* Bootstrap warning yellow */
            --danger-color: #dc3545; /* Bootstrap danger red */
            --secondary-color: #6c757d; /* Bootstrap secondary grey */
        }

        /* General styles for the overall layout */
        .filter-card {
            background-color: var(--table-header-bg);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        #details-panel {
            display: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        /* --- Custom Table Styling for Larger Screens (Default) --- */
        .custom-table-wrapper {
            background: #fff;
            border: 1px solid var(--table-border-color);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden; /* Ensures rounded corners are visible */
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table thead th {
            background-color: var(--table-header-bg);
            border-bottom: 2px solid var(--table-border-color);
            color: var(--text-light);
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .custom-table tbody tr {
            border-bottom: 1px solid var(--table-border-color);
            transition: background-color 0.2s ease;
        }

        .custom-table tbody tr:last-child {
            border-bottom: none;
        }

        .custom-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .custom-table td {
            padding: 0.75rem 1.25rem;
            vertical-align: middle;
            font-size: 0.9rem;
        }

        /* Specific styles for text within cells */
        .ticket-no-val {
            font-weight: 600;
            color: var(--primary-color);
        }

        .journey-val {
            font-weight: 500;
        }

        .passengers-val, .seat-codes-val {
            max-width: 200px; /* Limits the visible width, text-overflow then adds ellipsis */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap; /* Keep on one line for larger screens */
        }

        .fare-val {
            font-weight: 700;
            color: var(--success-color);
        }

        .actions-cell {
            text-align: right;
            white-space: nowrap; /* Keep action buttons on one line */
        }

        .actions-cell .btn {
            margin-left: 5px;
        }

        /* Basic badge styling */
        .badge {
            display: inline-block;
            padding: .35em .65em;
            font-size: .75em;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25rem;
        }

        .badge.bg-success { background-color: var(--success-color) !important; }
        .badge.bg-warning { background-color: var(--warning-color) !important; color: #000 !important; }
        .badge.bg-secondary { background-color: var(--secondary-color) !important; }

        /* --- Responsive Adjustments for Filter Card & Details Panel (using Bootstrap grid for stacking) --- */
        @media (max-width: 767.98px) {
            .filter-card .col-md-4,
            .filter-card .col-md-3,
            .filter-card .col-md-2 {
                margin-bottom: 1rem; /* Add space between stacked filter items */
            }
            .filter-card .col-md-2 button {
                width: 100% !important; /* Ensure clear button takes full width on small screens */
            }
            .details-content .col-md-6 {
                margin-bottom: 0.5rem; /* Space between details items if they stack */
            }
            /* Make sure .row in filter/details respects spacing */
            .card-body .row {
                --bs-gutter-x: 1.5rem; /* Default Bootstrap gutter */
                --bs-gutter-y: 1rem;
            }
        }

        /* --- Custom Table Responsive "Card" Layout for Small Screens --- */
        /* Only apply these rules if the screen is 767px or narrower */
        @media screen and (max-width: 767px) {
            .custom-table-wrapper {
                overflow-x: hidden; /* Hide default scroll, as rows are block elements */
                box-shadow: none; /* Remove outer shadow, rows will have their own */
                border: none; /* Remove outer border */
                background: transparent; /* Transparent background, cards will have their own */
            }
            .custom-table {
                border: none;
                width: 100%;
                table-layout: fixed; /* Ensures td widths behave predictably */
            }

            .custom-table thead {
                /* Keep the header visible, but style it for mobile */
                display: none; /* Force block display */
                border-bottom: 2px solid var(--table-border-color);
                margin-bottom: 1rem; /* Space below header */
                background-color: var(--table-header-bg); /* Header background */
                border-radius: 8px 8px 0 0; /* Rounded top corners */
            }
            .custom-table thead tr {
                display: block !important; /* Force block display */
            }
            .custom-table thead th {
                display: block !important; /* Each th takes full width of its parent (thead) */
                text-align: left; /* Keep text left-aligned */
                font-size: 0.9rem;
                padding: 0.75rem 1rem;
                border-bottom: none; /* Remove bottom border from individual th */
            }
            /* Visually group header items if there are few, otherwise they'll stack */
            .custom-table thead th:not(:last-child) {
                border-bottom: 1px solid var(--table-border-color);
            }
            .custom-table thead th.no-export {
                /* Adjust for actions column if needed */
                text-align: center; /* Center action header */
                padding: 0.75rem 1rem;
            }


            .custom-table tbody,
            .custom-table tr {
                display: block !important; /* Force block display */
                width: 100%;
            }

            .custom-table tr {
                margin-bottom: 1.5rem; /* Space between card-like rows */
                border: 1px solid var(--table-border-color); /* Card border */
                border-radius: 8px; /* Card rounded corners */
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); /* Card shadow */
                background-color: #fff; /* Ensure card background is white */
                padding: 0; /* Remove padding from tr, td will have it */
            }

            .custom-table td {
                display: flex !important; /* Use flexbox to align label and value side-by-side */
                justify-content: space-between; /* Push label to left, value to right */
                align-items: center; /* Vertically align items */
                padding: 0.75rem 1rem; /* Padding within each cell */
                border-bottom: 1px solid var(--table-border-color); /* Separator between fields */
                white-space: normal; /* Allow text to wrap within the cell */
                overflow: visible; /* Override ellipsis */
                text-overflow: clip; /* Override ellipsis */
            }

            .custom-table td:last-of-type {
                border-bottom: none; /* No border for the last field in the card */
            }

            /* Add a pseudo-element for the label part of each table cell */
            .custom-table td::before {
                content: attr(data-label); /* Get label from data-label attribute */
                font-weight: 600;
                color: var(--text-dark);
                text-align: left;
                flex-shrink: 0; /* Prevent label from shrinking */
                margin-right: 1rem; /* Space between label and value */
                width: 40%; /* Adjust as needed, or let flex handle it */
            }
            
            /* Ensure the first item (Ticket #) still has a clear label even if its class is specific */
            .custom-table td.ticket-no-val::before {
                content: "Ticket #"; 
            }

            /* Specific styling for the actions cell */
            .custom-table td.actions-cell {
                text-align: center; /* Center actions in the card */
                padding-top: 1rem;
                justify-content: center; /* Center buttons horizontally */
                border-top: 1px solid var(--table-border-color); /* Separator above actions */
                margin-top: 1rem; /* Space above action buttons */
            }
            .custom-table td.actions-cell::before {
                display: none !important; /* Hide the label for the actions column */
            }
            /* Adjust button size for mobile if desired */
            .custom-table td.actions-cell .btn {
                padding: .4rem .6rem;
                font-size: .85rem;
            }

            /* Ensure badges and passenger lists wrap correctly */
            .passengers-val, .seat-codes-val {
                white-space: normal; /* Allow text to wrap for better readability in cards */
                max-width: none; /* No max-width constraint in card view */
                overflow: visible;
                text-overflow: clip;
            }
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <?php include_once('sidebar.php'); ?>
        <div class="main-content">
            <?php include_once('header.php'); ?>
            <div class="container-fluid">
                <h2 class="my-4">Route Dashboard & Bookings</h2>
                <a href="deleted_bookings_report">View Deleted Booking</a>

                <!-- Display a message if no routes are available for this employee -->
                <?php if (empty($routes)): ?>
                    <div class="alert alert-warning text-center">
                        <h4><i class="fas fa-exclamation-triangle"></i> No Routes Assigned</h4>
                        <p class="mb-0">You can only view bookings for routes you are assigned to. Please contact an administrator if you believe this is an error.</p>
                    </div>
                <?php else: ?>
                    <!-- Filter Section -->
                    <div class="card filter-card mb-4">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4 col-12"> <!-- Added col-12 for full width on small screens -->
                                    <label for="route-filter" class="form-label fw-bold">Select Your Assigned Route</label>
                                    <select id="route-filter" class="form-select">
                                        <option value="">-- Choose a Route --</option>
                                        <?php foreach ($routes as $route): ?>
                                            <option value="<?php echo $route['route_id']; ?>" <?php if ($route_id_from_url == $route['route_id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($route['route_name']); ?> (Bus: <?php echo htmlspecialchars($route['bus_name']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 col-12"> <!-- Added col-12 for full width on small screens -->
                                    <label for="date-filter" class="form-label fw-bold">Select Travel Date</label>
                                    <input type="text" id="date-filter" class="form-control" placeholder="Select Date">
                                </div>
                                <div class="col-md-2 col-12"> <!-- Added col-12 for full width on small screens -->
                                    <button id="clear-filter-btn" class="btn btn-outline-secondary w-100">Clear</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Panel (populated by AJAX) -->
                    <div id="details-panel" class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Route & Bus Details</h5>
                        </div>
                        <div class="card-body">
                            <div id="details-content" class="row">
                                <!-- Content populated by JS -->
                            </div>
                            <hr>
                            <h6><i class="fas fa-map-signs me-2"></i>Complete Route Timeline</h6>
                            <ul id="timeline-content" class="timeline">
                                <!-- Content populated by JS -->
                            </ul>
                        </div>
                    </div>

                    <!-- Bookings List Panel -->
                    <div class="card ">
                        <div class="card-body custom-table-wrapper">
                            <table class="display custom-table" id="bookings-table" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Journey</th>
                                        <th>Passengers</th>
                                        <th>Seats</th>
                                        <th>Fare</th>
                                        <th>Status</th>
                                        <th class="no-export actions-cell">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
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
    <script>
        // Pass the PHP permission check result to JavaScript for secure UI rendering
        const userCanDelete = <?php echo json_encode($user_can_delete); ?>;
        const initialDate = <?php echo json_encode($date_from_url); ?> || new Date().toISOString().slice(0, 10);

        $(document).ready(function() {
            // --- DATATABLE INITIALIZATION ---
            // Initialize the table as a DataTable ONCE when the page loads.
            let bookingTable = $('#bookings-table').DataTable({
                "dom": 'Bfrtip', // This enables the Buttons (B), filtering/search (f), etc.
                "buttons": [{
                        extend: 'copyHtml5',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        },
                        title: () => `${$('#route-filter option:selected').text()} - ${$('#date-filter').val()}`
                    },
                    {
                        extend: 'csvHtml5',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        },
                        title: () => `${$('#route-filter option:selected').text()} - ${$('#date-filter').val()}`
                    },
                    {
                        extend: 'excelHtml5',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        },
                        title: () => `${$('#route-filter option:selected').text()} - ${$('#date-filter').val()}`
                    },
                    {
                        extend: 'pdfHtml5',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        },
                        title: () => `${$('#route-filter option:selected').text()} - ${$('#date-filter').val()}`
                    },
                    {
                        extend: 'print',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        },
                        title: () => `Booking Report for ${$('#route-filter option:selected').text()}`,
                        messageTop: () => `Travel Date: ${$('#date-filter').val()}`
                    }
                ],
                "language": {
                    "emptyTable": "Please select a route and date to see bookings.",
                    // This message is shown by DataTables when `processing: true` is set during internal operations
                    "processing": '<div class="d-flex justify-content-center align-items-center"><div class="spinner-border text-primary" role="status"></div><span class="ms-2">Loading...</span></div>'
                },
                "processing": true, // Enable the automatic processing indicator (it will show during redraws)
                "serverSide": false, // We are handling data client-side (loading all data at once)
                "columns": [{
                        "data": "ticket_no",
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).attr('data-label', 'Ticket #');
                            $(td).addClass('ticket-no-val');
                        }
                    },
                    {
                        "data": "journey",
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).attr('data-label', 'Journey');
                            $(td).addClass('journey-val');
                        }
                    },
                    {
                        "data": "passenger_names",
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).attr('data-label', 'Passengers');
                            $(td).addClass('passengers-val');
                        }
                    },
                    {
                        "data": "seat_codes",
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).attr('data-label', 'Seats');
                            $(td).addClass('seat-codes-val');
                        }
                    },
                    {
                        "data": "total_fare",
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).attr('data-label', 'Fare');
                            $(td).addClass('fare-val');
                        }
                    },
                    {
                        "data": "booking_status",
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).attr('data-label', 'Status');
                        }
                    },
                    {
                        "data": "actions",
                        "orderable": false,
                        "searchable": false,
                        "className": "no-export actions-cell" // Ensures actions-cell class is applied
                        // No data-label needed for actions as per your existing mobile design,
                        // which hides the label for this column using `td.actions-cell::before { display: none; }`
                    }
                ]
            });

            // --- FILTER EVENT LISTENERS ---
            const datePicker = flatpickr("#date-filter", {
                dateFormat: "Y-m-d",
                defaultDate: initialDate, // Set the date from URL or today's date
                onChange: () => loadDashboardData()
            });

            $('#route-filter').on('change', () => loadDashboardData());

            $('#clear-filter-btn').on('click', () => {
                $('#route-filter').val('');
                datePicker.setDate(new Date());
                $('#details-panel').slideUp();
                bookingTable.clear().draw(); // This will show DataTables' processing indicator briefly
                updateBookingCount(0);
            });

            // --- MAIN DATA LOADING FUNCTION ---
            function loadDashboardData() {
                const routeId = $('#route-filter').val();
                const travelDate = $('#date-filter').val();

                if (!routeId || !travelDate) {
                    bookingTable.clear().draw();
                    updateBookingCount(0);
                    return;
                }

                // Show details panel and its own loading indicator
                $('#details-panel').slideDown();
                $('#details-content').html('<div class="d-flex justify-content-center p-3"><div class="spinner-border text-primary"></div><span class="ms-2">Loading Details...</span></div>');
                
                // Clear the table and immediately redraw.
                // Because 'processing: true' is set in DataTable initialization,
                // the "Processing..." message will show within the table while it's empty,
                // and remain there until the next draw call.
                bookingTable.clear().draw();  
                
                $.getJSON('function/backend/booking_actions.php', {
                        action: 'get_route_dashboard_details',
                        route_id: routeId,
                        travel_date: travelDate
                    })
                    .done(response => {
                        if (response.status === 'success') {
                            const {
                                details,
                                staff,
                                bookings
                            } = response;

                            // Populate the details panel
                            let staffHtml = '<p class="mb-2"><span class="label">Staff:</span> Not Assigned</p>';
                            if (staff && staff.length > 0) {
                                staffHtml = staff.map(s => `<p class="mb-2"><span class="label">${s.role}:</span> ${s.name}</p>`).join('');
                            }
                            $('#details-content').html(
                                `<div class="col-md-6"><p class="mb-2"><span class="label">Bus:</span> ${details.bus_name} (${details.registration_number})</p></div>
                     <div class="col-md-6">${staffHtml}</div>`
                            );

                            // Populate the DataTable
                            if (bookings && bookings.length > 0) {
                                const tableData = bookings.map(booking => {
                                    let deleteButtonHtml = '';
                                    if (userCanDelete) {
                                        deleteButtonHtml = `<button class="btn btn-sm btn-outline-danger delete-booking-btn" data-booking-id="${booking.booking_id}" data-ticket-no="${booking.ticket_no || 'N/A'}" title="Delete Booking"><i class="fas fa-trash-alt"></i></button>`;
                                    }

                                    return {
                                        ticket_no: `<strong class="ticket-no-val">${booking.ticket_no || 'N/A'}</strong>`,
                                        journey: `${booking.origin} → ${booking.destination}`,
                                        passenger_names: booking.passenger_names,
                                        seat_codes: booking.seat_codes.split(', ').map(seat => `<span class="badge bg-secondary me-1">${seat}</span>`).join(''),
                                        total_fare: `₹${parseFloat(booking.total_fare).toFixed(2)}`,
                                        booking_status: `<span class="badge bg-${booking.payment_status === 'PAID' ? 'success' : 'warning'}">${booking.booking_status}</span>`,
                                        actions: `<a href="generate_ticket.php?booking_id=${booking.booking_id}" target="_blank" class="btn btn-sm btn-outline-primary" title="View Ticket"><i class="fas fa-eye"></i></a> ${deleteButtonHtml}`
                                    };
                                });

                                bookingTable.rows.add(tableData);
                            }
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    })
                    .fail(() => {
                        Swal.fire('Error', 'Failed to load data from the server. Please check your connection.', 'error');
                    })
                    .always(() => {
                        bookingTable.draw(); // Redraw with new data; DataTables' processing indicator will hide automatically here.
                        updateBookingCount(bookingTable.rows().count());
                    });
            }

            // Function to update booking count (if you have an element with id="booking-count" in your HTML)
            function updateBookingCount(count) {
                // If you add an element like <span id="booking-count"></span>, you can uncomment the line below:
                // $('#booking-count').text(`${count} ${count === 1 ? 'Booking' : 'Bookings'}`); 
                console.log(`Booking count: ${count} ${count === 1 ? 'Booking' : 'Bookings'}`);
            }

            // --- DELETE HANDLER (Event Delegation for the table body) ---
            $('#bookings-table tbody').on('click', '.delete-booking-btn', function() {
                const bookingId = $(this).data('booking-id');
                const ticketNo = $(this).data('ticket-no');
                const row = $(this).closest('tr');

                Swal.fire({
                    title: `Delete Booking #${ticketNo}?`,
                    text: "This action is permanent and cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'function/backend/booking_actions.php',
                            type: 'POST',
                            data: {
                                action: 'delete_booking',
                                booking_id: bookingId
                            },
                            dataType: 'json',
                            success: response => {
                                if (response.status === 'success') {
                                    Swal.fire('Deleted!', response.message, 'success');
                                    // Use the DataTables API to remove the row and redraw
                                    bookingTable.row(row).remove().draw();
                                    updateBookingCount(bookingTable.rows().count());
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            },
                            error: () => {
                                Swal.fire('Error!', 'Could not connect to the server.', 'error');
                            }
                        });
                    }
                });
            });
            
            // Initial load if a route is already selected from URL or date is set
            if ($('#route-filter').val() && $('#date-filter').val()) {
                loadDashboardData();
            } else if (!initialDate && !$('#route-filter').val()) {
                // If neither route nor date is set, ensure table is empty initially
                bookingTable.clear().draw();
            }
        });
    </script>
</body>

</html>