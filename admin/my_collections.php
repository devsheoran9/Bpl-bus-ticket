<?php
// my_collections.php
include_once('function/_db.php');
session_security_check();
check_permission('can_view_own_collections');

// Get the ID and name of the currently logged-in employee
$employee_id = $_SESSION['user']['id'];
$employee_name = $_SESSION['user']['name'];

// Filtering logic (remains the same)
$date_from_filter = filter_input(INPUT_GET, 'date_from');
$date_to_filter = filter_input(INPUT_GET, 'date_to');

try {
    // Build the main query to fetch bookings for THIS employee
    $sql = "
        SELECT
            b.booking_id, b.ticket_no, b.travel_date, b.total_fare,
            r.route_name, bu.bus_name,
            CASE WHEN t.transaction_id IS NOT NULL AND t.payment_status = 'CAPTURED' THEN 'ONLINE' ELSE 'CASH' END as payment_method,
            (ccl.collection_id IS NOT NULL) AS is_collected
        FROM bookings b
        JOIN routes r ON b.route_id = r.route_id
        JOIN buses bu ON b.bus_id = bu.bus_id
        LEFT JOIN transactions t ON b.booking_id = t.booking_id
        LEFT JOIN cash_collections_log ccl ON b.booking_id = ccl.booking_id
        WHERE b.booked_by_employee_id = ?
    ";

    $params = [$employee_id];
    if ($date_from_filter) { $sql .= " AND b.travel_date >= ?"; $params[] = $date_from_filter; }
    if ($date_to_filter) { $sql .= " AND b.travel_date <= ?"; $params[] = $date_to_filter; }
    $sql .= " ORDER BY b.travel_date DESC, b.created_at DESC";

    $stmt = $_conn_db->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Aggregate Calculations (remains the same)
    $total_cash_sales = 0;
    $cash_collected = 0;
    $cash_due = 0;
    foreach ($bookings as $booking) {
        if ($booking['payment_method'] === 'CASH') {
            $total_cash_sales += $booking['total_fare'];
            if ($booking['is_collected']) {
                $cash_collected += $booking['total_fare'];
            } else {
                $cash_due += $booking['total_fare'];
            }
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
    <title>My Cash Report</title>
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        .summary-card {
            border-left: 5px solid;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .summary-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1); }
        .card-total-cash { border-left-color: #0d6efd; }
        .card-cash-collected { border-left-color: #198754; }
        .card-cash-due { border-left-color: #dc3545; }
        .summary-card .display-5 { font-weight: 700; }
        .dataTables_wrapper .dt-buttons .btn { margin-right: 5px; }

        /* Custom styles for improved mobile booking cards */
        .booking-card-sm {
            border-radius: .5rem; /* Slightly rounded corners */
            box-shadow: 0 4px 10px rgba(0,0,0,.08); /* Subtle shadow for depth */
        }
        .booking-card-sm.pending-cash {
            border: 2px solid var(--bs-warning); /* Stronger warning border */
            background-color: var(--bs-warning-bg-subtle);
            box-shadow: 0 4px 12px rgba(255,193,7,0.2); /* Warning specific shadow */
        }
        .booking-card-sm .card-header {
            background-color: var(--bs-primary); /* Primary color for header */
            color: white; /* White text for header */
            padding: .75rem 1rem; /* Better header padding */
            border-bottom: none; /* Remove bottom border if header has distinct color */
            border-top-left-radius: calc(.5rem - 1px); /* Match card border radius */
            border-top-right-radius: calc(.5rem - 1px);
        }
        .booking-card-sm .card-header h5,
        .booking-card-sm .card-header h6 {
            font-size: 1rem; /* Adjust font size for mobile header */
            margin-bottom: 0;
        }
        .booking-card-sm .card-body {
            padding: 1rem; /* Consistent body padding */
        }
        .booking-card-sm .card-body .detail-label {
            font-size: .8rem; /* Smaller, muted label */
            color: var(--bs-secondary-color);
            margin-bottom: .2rem;
            display: block;
        }
        .booking-card-sm .card-body .detail-value {
            font-size: .95rem; /* Slightly larger value */
            font-weight: 600; /* Semi-bold */
        }
        .booking-card-sm .badge {
            font-size: .85rem; /* Adjust badge font size */
            padding: .4em .6em;
        }
        .booking-card-sm .status-text {
            font-weight: 600; /* Semi-bold status text */
            font-size: .9rem;
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">My Cash Report - <?php echo htmlspecialchars($employee_name); ?></h2>

            <!-- Filter Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-5 col-sm-12">
                            <label for="date_from" class="form-label fw-bold">Date From</label>
                            <input type="text" id="date_from" name="date_from" class="form-control"
                                   value="<?php echo htmlspecialchars($date_from_filter ?? ''); ?>" placeholder="Start Date">
                        </div>
                        <div class="col-md-5 col-sm-12">
                            <label for="date_to" class="form-label fw-bold">Date To</label>
                            <input type="text" id="date_to" name="date_to" class="form-control"
                                   value="<?php echo htmlspecialchars($date_to_filter ?? ''); ?>" placeholder="End Date">
                        </div>
                        <div class="col-md-2 col-sm-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                            <a href="my_collections.php" class="btn btn-light border w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4 col-sm-12">
                    <div class="card summary-card card-total-cash">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Total Cash Sales</h6>
                            <p class="display-5 mb-0">₹<?php echo number_format($total_cash_sales, 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                     <div class="card summary-card card-cash-collected">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Cash Submitted to Admin</h6>
                            <p class="display-5 mb-0">₹<?php echo number_format($cash_collected, 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="card summary-card card-cash-due">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Cash Due / To Be Submitted</h6>
                            <p class="display-5 mb-0">₹<?php echo number_format($cash_due, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings List Data Table (Visible on medium screens and up) -->
            <div class="card d-none d-md-block">
                <div class="card-header">
                    <h5>My Booking History</h5>
                </div>
                <div class="card-body">
                    <table id="bookings-table" class="display table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Travel Date</th>
                                <th>Route</th>
                                <th>Bus</th>
                                <th>Payment</th>
                                <th>Collection Status</th>
                                <th class="text-end">Fare</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr class="<?php echo ($booking['payment_method'] === 'CASH' && !$booking['is_collected']) ? 'table-warning' : ''; ?>">
                                    <td><strong><?php echo htmlspecialchars($booking['ticket_no']); ?></strong></td>
                                    <td><?php echo date('d M, Y', strtotime($booking['travel_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($booking['route_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['bus_name']); ?></td>
                                    <td>
                                        <span class="badge fs-6 <?php echo $booking['payment_method'] === 'CASH' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-info-subtle text-info-emphasis'; ?>">
                                            <?php echo $booking['payment_method']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($booking['payment_method'] === 'ONLINE'): ?>
                                            <span class="text-info fw-bold"><i class="fas fa-credit-card me-1"></i> Paid Online</span>
                                        <?php elseif ($booking['is_collected']): ?>
                                            <span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Submitted</span>
                                        <?php else: ?>
                                            <span class="text-danger fw-bold"><i class="fas fa-hourglass-half me-1"></i> Pending Submission</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-bold">₹<?php echo number_format($booking['total_fare'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Booking Cards (Visible on small screens only) -->
            <div class="d-md-none mt-4">
                <h5 class="mb-3">My Booking History</h5>
                <?php if (empty($bookings)): ?>
                    <div class="alert alert-info text-center mt-3" role="alert">
                        No bookings found for the selected criteria.
                    </div>
                <?php else: ?>
                    <div class="row row-cols-1 g-3">
                        <?php foreach ($bookings as $booking):
                            $is_pending_cash = ($booking['payment_method'] === 'CASH' && !$booking['is_collected']);
                        ?>
                            <div class="col">
                                <div class="card booking-card-sm <?php echo $is_pending_cash ? 'pending-cash' : ''; ?>">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Ticket # <span class="fw-bold"><?php echo htmlspecialchars($booking['ticket_no']); ?></span></h6>
                                        <h5 class="mb-0 fw-bold">₹<?php echo number_format($booking['total_fare'], 2); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <small class="detail-label">Travel Date</small>
                                                <span class="detail-value"><?php echo date('d M, Y', strtotime($booking['travel_date'])); ?></span>
                                            </div>
                                            <div class="col-6">
                                                <small class="detail-label">Bus</small>
                                                <span class="detail-value"><?php echo htmlspecialchars($booking['bus_name']); ?></span>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <small class="detail-label">Route</small>
                                                <span class="detail-value"><?php echo htmlspecialchars($booking['route_name']); ?></span>
                                            </div>
                                        </div>

                                        <hr class="my-2">

                                        <div class="d-flex flex-wrap justify-content-between align-items-center pt-2">
                                            <div>
                                                <span class="badge <?php echo $booking['payment_method'] === 'CASH' ? 'bg-warning text-dark' : 'bg-info text-dark'; ?>">
                                                    <i class="fas <?php echo $booking['payment_method'] === 'CASH' ? 'fa-money-bill-wave' : 'fa-credit-card'; ?> me-1"></i>
                                                    <?php echo $booking['payment_method']; ?>
                                                </span>
                                            </div>
                                            <div>
                                                <?php if ($booking['payment_method'] === 'ONLINE'): ?>
                                                    <span class="status-text text-info"><i class="fas fa-check-circle me-1"></i> Paid Online</span>
                                                <?php elseif ($booking['is_collected']): ?>
                                                    <span class="status-text text-success"><i class="fas fa-check-circle me-1"></i> Submitted</span>
                                                <?php else: ?>
                                                    <span class="status-text text-danger"><i class="fas fa-hourglass-half me-1"></i> Pending</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
<?php include "foot.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
$(document).ready(function() {
    // Initialize Date Pickers
    const fromPicker = flatpickr("#date_from", { dateFormat: "Y-m-d" });
    const toPicker = flatpickr("#date_to", { dateFormat: "Y-m-d" });

    // Initialize DataTables only if the table element is visible (or exists, it handles display:none gracefully)
    // The d-none d-md-block makes sure it's not rendered on small screens
    // DataTables will initialize but remain hidden until the d-md-block class applies
    if ($('#bookings-table').length) { // Check if the table element exists in the DOM
        $('#bookings-table').DataTable({
            "dom": 'Bfrtip', // This enables Buttons, Filtering (Search), etc.
            "buttons": [
                {
                    extend: 'copyHtml5',
                    title: 'My Cash Report - <?php echo addslashes($employee_name); ?>'
                },
                {
                    extend: 'csvHtml5',
                    title: 'My Cash Report - <?php echo addslashes($employee_name); ?>',
                    filename: 'Cash-Report-<?php echo str_replace(' ', '-', $employee_name); ?>-<?php echo date('Y-m-d'); ?>'
                },
                {
                    extend: 'excelHtml5',
                    title: 'My Cash Report - <?php echo addslashes($employee_name); ?>',
                    filename: 'Cash-Report-<?php echo str_replace(' ', '-', $employee_name); ?>-<?php echo date('Y-m-d'); ?>'
                },
                {
                    extend: 'pdfHtml5',
                    title: 'My Cash Report - <?php echo addslashes($employee_name); ?>',
                    filename: 'Cash-Report-<?php echo str_replace(' ', '-', $employee_name); ?>-<?php echo date('Y-m-d'); ?>'
                },
                {
                    extend: 'print',
                    title: 'My Cash Report - <?php echo addslashes($employee_name); ?>',
                    messageTop: 'A summary of cash and online bookings.'
                }
            ],
            "pageLength": 10,
            "order": [[ 1, "desc" ]] // Default sort by Travel Date descending
        });
    }
});
</script>
</body>
</html>