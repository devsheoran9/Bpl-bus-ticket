<?php
// employee_bookings.php - Redesigned with DataTables Integration
include_once('function/_db.php');
session_security_check();
check_permission('main_admin');

// --- ADVANCED FILTERING LOGIC ---
$employee_id_filter = filter_input(INPUT_GET, 'employee_id', FILTER_VALIDATE_INT);
$bus_id_filter = filter_input(INPUT_GET, 'bus_id', FILTER_VALIDATE_INT);
$route_id_filter = filter_input(INPUT_GET, 'route_id', FILTER_VALIDATE_INT);
$date_from_filter = filter_input(INPUT_GET, 'date_from');
$date_to_filter = filter_input(INPUT_GET, 'date_to');

try {
    // Fetch data for filter dropdowns
    $employees = $_conn_db->query("SELECT id, name FROM admin WHERE type = 'employee' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $buses = $_conn_db->query("SELECT bus_id, bus_name, registration_number FROM buses ORDER BY bus_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $routes = $_conn_db->query("SELECT route_id, route_name FROM routes ORDER BY route_name ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Build the main query to fetch both employee and user bookings
    $sql = "
        SELECT 
            b.booking_id, b.ticket_no, b.travel_date, b.total_fare,
            COALESCE(a.name, u.username, 'Online User') as booker_name,
            CASE WHEN b.booked_by_employee_id IS NOT NULL THEN 'Employee' ELSE 'User' END as booker_type,
            b.booked_by_employee_id,
            r.route_name, bu.bus_name,
            CASE WHEN t.transaction_id IS NOT NULL AND t.payment_status = 'CAPTURED' THEN 'ONLINE' ELSE 'CASH' END as payment_method,
            (ccl.collection_id IS NOT NULL OR (t.transaction_id IS NOT NULL AND t.payment_status = 'CAPTURED')) AS is_collected
        FROM bookings b
        LEFT JOIN admin a ON b.booked_by_employee_id = a.id
        LEFT JOIN users u ON b.user_id = u.id
        JOIN routes r ON b.route_id = r.route_id
        JOIN buses bu ON b.bus_id = bu.bus_id
        LEFT JOIN transactions t ON b.booking_id = t.booking_id
        LEFT JOIN cash_collections_log ccl ON b.booking_id = ccl.booking_id
        WHERE 1=1
    ";

    $params = [];
    if ($employee_id_filter) { $sql .= " AND b.booked_by_employee_id = ?"; $params[] = $employee_id_filter; }
    if ($bus_id_filter) { $sql .= " AND b.bus_id = ?"; $params[] = $bus_id_filter; }
    if ($route_id_filter) { $sql .= " AND b.route_id = ?"; $params[] = $route_id_filter; }
    if ($date_from_filter) { $sql .= " AND b.travel_date >= ?"; $params[] = $date_from_filter; }
    if ($date_to_filter) { $sql .= " AND b.travel_date <= ?"; $params[] = $date_to_filter; }

    $sql .= " ORDER BY b.travel_date DESC, booker_name ASC";

    $stmt = $_conn_db->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- AGGREGATE CALCULATIONS ---
    $employee_report = [];
    $user_report = [
        'booker_name' => 'Online Customers',
        'total_bookings' => 0,
        'total_online' => 0,
        'bookings' => []
    ];

    foreach ($bookings as $booking) {
        if ($booking['booker_type'] === 'Employee') {
            $emp_id = $booking['booked_by_employee_id'];
            if (!isset($employee_report[$emp_id])) {
                $employee_report[$emp_id] = [
                    'employee_name' => $booking['booker_name'],
                    'total_bookings' => 0, 'total_online' => 0, 'total_cash' => 0,
                    'cash_collected' => 0, 'cash_due' => 0, 'bookings' => []
                ];
            }
            $employee_report[$emp_id]['total_bookings']++;
            if ($booking['payment_method'] === 'ONLINE') {
                $employee_report[$emp_id]['total_online'] += $booking['total_fare'];
            } else {
                $employee_report[$emp_id]['total_cash'] += $booking['total_fare'];
                if ($booking['is_collected']) {
                    $employee_report[$emp_id]['cash_collected'] += $booking['total_fare'];
                } else {
                    $employee_report[$emp_id]['cash_due'] += $booking['total_fare'];
                }
            }
            $employee_report[$emp_id]['bookings'][] = $booking;
        } else {
            $user_report['total_bookings']++;
            $user_report['total_online'] += $booking['total_fare'];
            $user_report['bookings'][] = $booking;
        }
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Complete Booking Report</title>
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        .filter-card { background-color: #fff; border-radius: 0.75rem; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none; }
        .report-card { background-color: #fff; border-radius: 0.75rem; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.07); margin-bottom: 2rem; border: 1px solid #e9ecef; overflow: hidden; }
        .report-header { display: flex; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e9ecef; background-color: #f8f9fa; }
        .avatar { width: 50px; height: 50px; border-radius: 50%; color: white; display: inline-flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.5rem; margin-right: 1rem; flex-shrink: 0; }
        .avatar-employee { background-color: #0d6efd; }
        .avatar-user { background-color: #198754; }
        .report-info h4 { margin: 0; font-size: 1.25rem; font-weight: 600; }
        .report-info p { margin: 0; color: #6c757d; }
        .cash-due-box { text-align: right; margin-left: auto; }
        .cash-due-box .label { font-size: 0.9em; color: #6c757d; font-weight: 500;}
        .cash-due-box .amount { font-size: 2rem; font-weight: 700; color: #dc3545; line-height: 1.1; }
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1.5rem; padding: 1.5rem; }
        .summary-box .label { font-size: 0.85em; color: #6c757d; text-transform: uppercase; font-weight: 600; }
        .summary-box .value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; color: #212529;}
        .text-cash-due { color: #dc3545 !important; }
        .text-cash-collected { color: #198754 !important; }
        .text-online { color: #0dcaf0 !important; }
        .text-total { color: #0d6efd !important; }
        .report-footer { padding: 1.25rem 1.5rem; background-color: #f8f9fa; border-top: 1px solid #e9ecef;}
        .details-table th { font-weight: 600; font-size: 0.9em; background-color: #f8f9fa;}
        .dataTables_wrapper .dataTables_filter { margin-bottom: 1rem; }
        .dataTables_wrapper .dt-buttons .btn { margin-right: 5px; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Complete Bookings Report</h2>

            <div class="card filter-card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label fw-bold">Employee</label>
                            <select name="employee_id" class="form-select">
                                <option value="">All</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>" <?php echo ($employee_id_filter == $emp['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($emp['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label fw-bold">Bus</label>
                            <select name="bus_id" class="form-select">
                                <option value="">All</option>
                                <?php foreach ($buses as $bus): ?>
                                    <option value="<?php echo $bus['bus_id']; ?>" <?php echo ($bus_id_filter == $bus['bus_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($bus['bus_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label fw-bold">Route</label>
                            <select name="route_id" class="form-select">
                                <option value="">All</option>
                                <?php foreach ($routes as $route): ?>
                                    <option value="<?php echo $route['route_id']; ?>" <?php echo ($route_id_filter == $route['route_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($route['route_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- NEW: Separate Date Inputs -->
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label for="date_from" class="form-label fw-bold">Date From</label>
                            <input type="text" id="date_from" name="date_from" class="form-control date-picker" 
                                   value="<?php echo htmlspecialchars($date_from_filter ?? ''); ?>" placeholder="Start Date">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label for="date_to" class="form-label fw-bold">Date To</label>
                            <input type="text" id="date_to" name="date_to" class="form-control date-picker" 
                                   value="<?php echo htmlspecialchars($date_to_filter ?? ''); ?>" placeholder="End Date">
                        </div>
                        
                        <div class="col-12 col-lg-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                            <a href="employee_bookings.php" class="btn btn-light border w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (empty($employee_report) && $user_report['total_bookings'] == 0): ?>
                <div class="alert alert-info text-center py-4">
                    <h4 class="alert-heading"><i class="fas fa-info-circle fa-2x mb-3"></i></h4>
                    <h4>No Bookings Found</h4>
                    <p class="mb-0">Please adjust your filters or check back later.</p>
                </div>
            <?php endif; ?>

            <?php if ($user_report['total_bookings'] > 0): ?>
                <div class="report-card">
                    <div class="report-header">
                        <div class="avatar avatar-user"><i class="fas fa-globe"></i></div>
                        <div class="report-info"><h4>Online Customer Bookings</h4><p>Bookings made by customers via website/app.</p></div>
                    </div>
                    <div class="summary-grid">
                        <div class="summary-box"><span class="label">Total Bookings</span><div class="value text-total"><?php echo $user_report['total_bookings']; ?></div></div>
                        <div class="summary-box"><span class="label">Total Online Sales</span><div class="value text-cash-collected">₹<?php echo number_format($user_report['total_online'], 2); ?></div></div>
                    </div>
                    <div class="report-footer">
                        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#detailsModalUser"><i class="fas fa-list-ul me-2"></i>View Booking Details (<?php echo count($user_report['bookings']); ?>)</button>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($employee_report as $employee_id => $data): ?>
                <div class="report-card">
                    <div class="report-header">
                        <div class="avatar avatar-employee"><?php echo strtoupper(substr(strtok($data['employee_name'], ' '), 0, 1) . substr(strrchr($data['employee_name'], ' '), 1, 1)); ?></div>
                        <div class="report-info"><h4><?php echo htmlspecialchars($data['employee_name']); ?></h4><p>Sales report based on selected filters.</p></div>
                        <?php if ($data['cash_due'] > 0): ?>
                        <div class="cash-due-box"><span class="label">Cash to be Collected</span><div class="amount">₹<?php echo number_format($data['cash_due'], 2); ?></div></div>
                        <?php endif; ?>
                    </div>
                    <div class="summary-grid">
                        <div class="summary-box"><span class="label">Total Bookings</span><div class="value text-total"><?php echo $data['total_bookings']; ?></div></div>
                        <div class="summary-box"><span class="label">Online Sales</span><div class="value text-online">₹<?php echo number_format($data['total_online'], 2); ?></div></div>
                        <div class="summary-box"><span class="label">Total Cash Sales</span><div class="value">₹<?php echo number_format($data['total_cash'], 2); ?></div></div>
                        <div class="summary-box"><span class="label">Cash Collected</span><div class="value text-cash-collected">₹<?php echo number_format($data['cash_collected'], 2); ?></div></div>
                        <div class="summary-box"><span class="label">Cash Due</span><div class="value text-cash-due">₹<?php echo number_format($data['cash_due'], 2); ?></div></div>
                    </div>
                    <div class="report-footer d-flex gap-2 flex-wrap">
                        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $employee_id; ?>"><i class="fas fa-list-ul me-2"></i>View Details (<?php echo count($data['bookings']); ?>)</button>
                        <?php if ($data['cash_due'] > 0): ?>
                            <button class="btn btn-success collect-all-cash-btn" data-employee-id="<?php echo $employee_id; ?>" data-employee-name="<?php echo htmlspecialchars($data['employee_name']); ?>" data-amount="<?php echo $data['cash_due']; ?>"><i class="fas fa-check-circle me-2"></i>Collect All Due Cash</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Details Modal for Employee -->
                <div class="modal fade" id="detailsModal<?php echo $employee_id; ?>" tabindex="-1">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">Booking Manifest for <?php echo htmlspecialchars($data['employee_name']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body p-3">
                                <table class="table table-striped table-hover details-table datatable display" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Ticket #</th><th>Travel Date</th><th>Route</th><th>Bus</th><th>Payment</th>
                                            <th>Status</th><th class="text-end">Fare</th><th class="no-export">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['bookings'] as $booking): ?>
                                        <tr>
                                            <td><b><?php echo htmlspecialchars($booking['ticket_no']); ?></b></td>
                                            <td><?php echo date('d-M-Y', strtotime($booking['travel_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($booking['route_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['bus_name']); ?></td>
                                            <td><span class="badge fs-6 <?php echo $booking['payment_method'] === 'CASH' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-info-subtle text-info-emphasis'; ?>"><?php echo $booking['payment_method']; ?></span></td>
                                            <td><?php echo $booking['is_collected'] ? '<span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i>Collected</span>' : ($booking['payment_method'] === 'CASH' ? '<span class="text-danger fw-bold"><i class="fas fa-hourglass-half me-1"></i>Pending</span>' : '<span class="text-info fw-bold"><i class="fas fa-credit-card me-1"></i>Paid Online</span>'); ?></td>
                                            <td class="fw-bold text-end">₹<?php echo number_format($booking['total_fare'], 2); ?></td>
                                            <td><a href="generate_ticket.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fas fa-eye"></i></a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Details Modal for Users -->
            <div class="modal fade" id="detailsModalUser" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Booking Manifest for Online Customers</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body p-3">
                            <table class="table table-striped table-hover details-table datatable display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th><th>Booked By</th><th>Travel Date</th><th>Route</th>
                                        <th>Bus</th><th class="text-end">Fare</th><th class="no-export">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($user_report['bookings'] as $booking): ?>
                                    <tr>
                                        <td><b><?php echo htmlspecialchars($booking['ticket_no']); ?></b></td>
                                        <td><?php echo htmlspecialchars($booking['booker_name']); ?></td>
                                        <td><?php echo date('d-M-Y', strtotime($booking['travel_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($booking['route_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['bus_name']); ?></td>
                                        <td class="fw-bold text-end">₹<?php echo number_format($booking['total_fare'], 2); ?></td>
                                        <td><a href="generate_ticket.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fas fa-eye"></i></a></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php include "foot.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
$(document).ready(function() {
     // Initialize the "Date From" picker
     const fromPicker = flatpickr("#date_from", {
        dateFormat: "Y-m-d",
        // When the 'from' date is changed, set it as the minimum date for the 'to' picker
        onChange: function(selectedDates, dateStr, instance) {
            if (toPicker) {
                toPicker.set('minDate', dateStr);
            }
        }
    });

    // Initialize the "Date To" picker
    const toPicker = flatpickr("#date_to", {
        dateFormat: "Y-m-d",
        // When the 'to' date is changed, set it as the maximum date for the 'from' picker
        onChange: function(selectedDates, dateStr, instance) {
            if (fromPicker) {
                fromPicker.set('maxDate', dateStr);
            }
        }
    });

    $('.collect-all-cash-btn').on('click', function() {
        const btn = $(this);
        const employeeId = btn.data('employee-id');
        const employeeName = btn.data('employee-name');
        const amount = btn.data('amount');
        
        Swal.fire({
            title: 'Confirm Cash Collection',
            html: `Are you sure you have collected <strong>₹${parseFloat(amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong> from <strong>${employeeName}</strong>? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Yes, Mark as Collected!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');
                $.ajax({
                    url: 'function/backend/report_actions.php',
                    type: 'POST',
                    data: {
                        action: 'collect_all_cash',
                        employee_id: employeeId,
                        bus_id: '<?php echo $bus_id_filter; ?>',
                        route_id: '<?php echo $route_id_filter; ?>',
                        date_from: '<?php echo $date_from_filter; ?>',
                        date_to: '<?php echo $date_to_filter; ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Success!', response.message, 'success').then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message || 'An unknown error occurred.', 'error');
                            btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>Collect All Due Cash');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Could not connect to the server.', 'error');
                        btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>Collect All Due Cash');
                    }
                });
            }
        });
    });

    $('.datatable').DataTable({
        "dom": 'Bfrtip',
        "buttons": [
            { 
                extend: 'copyHtml5', 
                exportOptions: { columns: ':not(.no-export)' },
                title: function() { return $('.modal.show .modal-title').text(); }
            },
            { 
                extend: 'csvHtml5', 
                exportOptions: { columns: ':not(.no-export)' },
                filename: function() { return $('.modal.show .modal-title').text().replace(/\s+/g, '-') + '-' + new Date().toISOString().slice(0,10); }
            },
            { 
                extend: 'excelHtml5', 
                exportOptions: { columns: ':not(.no-export)' },
                filename: function() { return $('.modal.show .modal-title').text().replace(/\s+/g, '-') + '-' + new Date().toISOString().slice(0,10); }
            },
            { 
                extend: 'pdfHtml5', 
                exportOptions: { columns: ':not(.no-export)' },
                filename: function() { return $('.modal.show .modal-title').text().replace(/\s+/g, '-') + '-' + new Date().toISOString().slice(0,10); }
            },
            { 
                extend: 'print', 
                exportOptions: { columns: ':not(.no-export)' },
                title: function() { return $('.modal.show .modal-title').text(); }
            }
        ],
        "pageLength": 10,
        "order": [[ 1, "desc" ]]
    });
});
</script>
</body>
</html>