<?php
// view_bookings.php
include_once('function/_db.php');
session_security_check();
check_permission('can_view_bookings');

$routes = [];
try {
    $allowed_route_ids = get_assigned_route_ids_for_employee($_SESSION['user']['id']);
    if (!empty($allowed_route_ids)) {
        $placeholders = implode(',', array_fill(0, count($allowed_route_ids), '?'));
        $routes_query = $_conn_db->prepare("SELECT r.route_id, r.route_name, b.bus_name FROM routes r JOIN buses b ON r.bus_id = b.bus_id WHERE r.status = 'Active' AND r.route_id IN ($placeholders) ORDER BY r.route_name");
        $routes_query->execute($allowed_route_ids);
        $routes = $routes_query->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) { $routes = []; }
$user_can_delete = user_has_permission('can_delete_bookings');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Route Dashboard & Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        :root {
            --table-header-bg: #f8f9fa;
            --table-border-color: #dee2e6;
            --text-dark: #212529;
            --text-light: #6c757d;
        }
        .filter-card { background-color: #f8f9fa; }
        #details-panel { display: none; }
        
        /* Custom Table Styling */
        .custom-table-wrapper {
            background: #fff;
            border: 1px solid var(--table-border-color);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            overflow: hidden;
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
        .ticket-no-val { font-weight: 600; color: #0d6efd; }
        .journey-val { font-weight: 500; }
        .passengers-val { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .fare-val { font-weight: 700; color: #198754; }
        .actions-cell { text-align: right; }
        .actions-cell .btn { margin-left: 5px; }
        </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Route Dashboard & Bookings</h2>

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
                            <div class="col-md-4"><label for="route-filter" class="form-label fw-bold">Select Your Assigned Route</label><select id="route-filter" class="form-select"><option value="">-- Choose a Route --</option><?php foreach ($routes as $route): ?><option value="<?php echo $route['route_id']; ?>"><?php echo htmlspecialchars($route['route_name']); ?> (Bus: <?php echo htmlspecialchars($route['bus_name']); ?>)</option><?php endforeach; ?></select></div>
                            <div class="col-md-3"><label for="date-filter" class="form-label fw-bold">Select Travel Date</label><input type="text" id="date-filter" class="form-control" placeholder="Select Date"></div>
                            <!-- <div class="col-md-3"><label for="search-filter" class="form-label fw-bold">Search Bookings</label><input type="text" id="search-filter" class="form-control" placeholder="By Ticket No, Name, Seats..."></div> -->
                            <div class="col-md-2"><button id="clear-filter-btn" class="btn btn-outline-secondary w-100">Clear</button></div>
                        </div>
                    </div>
                </div>
                
                <!-- Details Panel (populated by AJAX) -->
                <div id="details-panel" class="card mb-4">
                    <div class="card-header bg-primary text-white"><h5 class="mb-0">Route & Bus Details</h5></div>
                    <div class="card-body">
                        <div id="details-content" class="row"></div><hr>
                        <h6><i class="fas fa-map-signs me-2"></i>Complete Route Timeline</h6>
                        <ul id="timeline-content" class="timeline"></ul>
                    </div>
                </div>
                
                <!-- Bookings List Panel -->
                <div class="card">
                    <div class="card-body">
                        <table class="display" id="bookings-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Journey</th>
                                    <th>Passengers</th>
                                    <th>Seats</th>
                                    <th>Fare</th>
                                    <th>Status</th>
                                    <th class="no-export">Actions</th> <!-- Add class to exclude from export -->
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

$(document).ready(function() {
    // --- DATATABLE INITIALIZATION ---
    // Initialize the table as a DataTable ONCE when the page loads.
    let bookingTable = $('#bookings-table').DataTable({
        "dom": 'Bfrtip', // This enables the Buttons (B), filtering/search (f), etc.
        "buttons": [
            { 
                extend: 'copyHtml5', 
                exportOptions: { columns: ':not(.no-export)' },
                title: () => `${$('#route-filter option:selected').text()} - ${$('#date-filter').val()}`
            },
            { 
                extend: 'csvHtml5', 
                exportOptions: { columns: ':not(.no-export)' },
                title: () => `${$('#route-filter option:selected').text()} - ${$('#date-filter').val()}`
            },
            { 
                extend: 'excelHtml5', 
                exportOptions: { columns: ':not(.no-export)' },
                title: () => `${$('#route-filter option:selected').text()} - ${$('#date-filter').val()}`
            },
            { 
                extend: 'pdfHtml5', 
                exportOptions: { columns: ':not(.no-export)' },
                title: () => `${$('#route-filter option:selected').text()} - ${$('#date-filter').val()}`
            },
            { 
                extend: 'print', 
                exportOptions: { columns: ':not(.no-export)' },
                title: () => `Booking Report for ${$('#route-filter option:selected').text()}`,
                messageTop: () => `Travel Date: ${$('#date-filter').val()}`
            }
        ],
        "language": {
            "emptyTable": "Please select a route and date to see bookings.",
            // This message is shown by DataTables when `processing: true` is set
            "processing": '<div class="d-flex justify-content-center align-items-center"><div class="spinner-border text-primary" role="status"></div><span class="ms-2">Loading...</span></div>'
        },
        "processing": true,  // **FIX**: Enable the automatic processing indicator
        "serverSide": false, // We are handling data client-side
        "columns": [
            { "data": "ticket_no" },
            { "data": "journey" },
            { "data": "passenger_names" },
            { "data": "seat_codes" },
            { "data": "total_fare" },
            { "data": "booking_status" },
            { "data": "actions", "orderable": false, "searchable": false, "className": "no-export" }
        ]
    });

    // --- FILTER EVENT LISTENERS ---
    const datePicker = flatpickr("#date-filter", {
        dateFormat: "Y-m-d", 
        defaultDate: "today",
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
            bookingTable.clear().draw();
            updateBookingCount(0);
            return; 
        }
        
        // Show details panel and clear the table to indicate loading
        $('#details-panel').slideDown();
        $('#details-content').html('<div class="d-flex justify-content-center p-3"><div class="spinner-border text-primary"></div><span class="ms-2">Loading Details...</span></div>');
        bookingTable.clear().draw(); 

        $.getJSON('function/backend/booking_actions.php', { action: 'get_route_dashboard_details', route_id: routeId, travel_date: travelDate })
        .done(response => {
            if (response.status === 'success') {
                const { details, staff, bookings } = response;
                
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
            // Redraw the table with the new data (or show "empty table" message)
            bookingTable.draw();
            updateBookingCount(bookingTable.rows().count());
        });
    }

    function updateBookingCount(count) {
        $('#booking-count').text(`${count} ${count === 1 ? 'Booking' : 'Bookings'}`);
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
                    data: { action: 'delete_booking', booking_id: bookingId },
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
});
</script>
</body>
</html>




 