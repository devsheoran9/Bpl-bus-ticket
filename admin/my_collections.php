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
                        <div class="col-md-5">
                            <label for="date_from" class="form-label fw-bold">Date From</label>
                            <input type="text" id="date_from" name="date_from" class="form-control" 
                                   value="<?php echo htmlspecialchars($date_from_filter ?? ''); ?>" placeholder="Start Date">
                        </div>
                        <div class="col-md-5">
                            <label for="date_to" class="form-label fw-bold">Date To</label>
                            <input type="text" id="date_to" name="date_to" class="form-control" 
                                   value="<?php echo htmlspecialchars($date_to_filter ?? ''); ?>" placeholder="End Date">
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                            <a href="my_collections.php" class="btn btn-light border w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card summary-card card-total-cash">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Total Cash Sales</h6>
                            <p class="display-5 mb-0">₹<?php echo number_format($total_cash_sales, 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                     <div class="card summary-card card-cash-collected">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Cash Submitted to Admin</h6>
                            <p class="display-5 mb-0">₹<?php echo number_format($cash_collected, 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card summary-card card-cash-due">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Cash Due / To Be Submitted</h6>
                            <p class="display-5 mb-0">₹<?php echo number_format($cash_due, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bookings List Data Table -->
            <div class="card">
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

    // Initialize DataTables
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
});
</script>
</body>
</html>