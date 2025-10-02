<?php
// booking_details.php
include_once('function/_db.php');
session_security_check();
check_permission('main_admin'); // This page is restricted to the main admin

$booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
if (!$booking_id) {
    $_SESSION['notif_type'] = 'error';
    $_SESSION['notif_title'] = 'Invalid Request';
    $_SESSION['notif_desc'] = 'No valid Booking ID was provided.';
    header('Location: employee_bookings.php');
    exit();
}

try {
    // --- QUERY 1: Get main booking details (ADDED contact_mobile) ---
    $stmt = $_conn_db->prepare("
        SELECT 
            b.booking_id, b.ticket_no, b.travel_date, b.total_fare, b.booking_status, b.origin, b.destination,
            b.contact_mobile, /* <<<--- NEW: Fetched the main contact mobile number ---<<< */
            COALESCE(a.name, u.username, 'Online User') as booker_name,
            CASE WHEN b.booked_by_employee_id IS NOT NULL THEN 'Employee' ELSE 'Online User' END as booker_type,
            r.route_name, r.route_id,
            sch.departure_time,
            bu.bus_name, bu.registration_number, bu.bus_type,
            b.payment_status,
            t.method AS payment_method, t.gateway_payment_id
        FROM bookings b
        LEFT JOIN admin a ON b.booked_by_employee_id = a.id
        LEFT JOIN users u ON b.user_id = u.id
        JOIN routes r ON b.route_id = r.route_id
        JOIN buses bu ON b.bus_id = bu.bus_id
        LEFT JOIN route_schedules sch ON r.route_id = sch.route_id AND sch.operating_day = DATE_FORMAT(b.travel_date, '%a')
        LEFT JOIN transactions t ON b.booking_id = t.booking_id
        WHERE b.booking_id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        $_SESSION['notif_type'] = 'warning';
        $_SESSION['notif_title'] = 'Not Found';
        $_SESSION['notif_desc'] = 'The requested booking could not be found.';
        header('Location: employee_bookings.php');
        exit();
    }

    // --- QUERY 2: Fetch assigned staff ---
    $assigned_staff = [
        'Driver' => [], 'Co-Driver' => [], 'Conductor' => [], 'Co-Conductor' => [], 'Helper' => []
    ];
    $staff_stmt = $_conn_db->prepare("
        SELECT s.name, s.mobile, rsa.role
        FROM route_staff_assignments rsa JOIN staff s ON rsa.staff_id = s.staff_id
        WHERE rsa.route_id = ?
        ORDER BY FIELD(rsa.role, 'Driver', 'Co-Driver', 'Conductor', 'Co-Conductor', 'Helper'), s.name
    ");
    $staff_stmt->execute([$booking['route_id']]);
    $all_staff_on_route = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($all_staff_on_route as $staff_member) {
        if (array_key_exists($staff_member['role'], $assigned_staff)) {
            $assigned_staff[$staff_member['role']][] = $staff_member;
        }
    }

    // --- QUERY 3: Fetch passengers (REMOVED passenger_mobile) ---
    // We will now use the main contact number for all passengers
    $passengersStmt = $_conn_db->prepare("SELECT passenger_name, passenger_age, passenger_gender, seat_code, fare FROM passengers WHERE booking_id = ? ORDER BY passenger_name ASC");
    $passengersStmt->execute([$booking_id]);
    $passengers = $passengersStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Booking Details Error: " . $e->getMessage());
    die("A critical database error occurred. Please check the server logs.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Booking Details - #<?php echo htmlspecialchars($booking['ticket_no']); ?></title>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.13.6/r-2.5.0/datatables.min.css"/>
    <style>
        body { background-color: #f8f9fa; }
        #wrapper { display: block; }
        .details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; }
        .detail-card { background-color: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e9ecef; }
        .detail-card .card-header { padding: 1rem 1.5rem; font-size: 1.1rem; font-weight: 600; border-bottom: 1px solid #e9ecef; background-color: transparent; }
        .detail-card .card-header i { color: #0d6efd; }
        .detail-card .card-body { padding: 1.5rem; }
        .info-row { display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid #f0f0f0; }
        .info-row:last-child { border-bottom: none; }
        .info-row .label { color: #6c757d; }
        .info-row .value { font-weight: 500; text-align: right; }
        .dataTables_filter input { border-radius: 0.5rem; border: 1px solid #ced4da; padding: 0.5rem 0.75rem; margin-left: 0.5em; }
    </style>
</head>
<body>
    <div id="wrapper">
        <?php include_once('sidebar.php'); ?>
        <div class="main-content">
            <?php include_once('header.php'); ?>
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center my-4 flex-wrap">
                    <div>
                        <h2 class="mb-0">Booking Details</h2>
                        <p class="text-muted mb-0">Ticket No: <b><?php echo htmlspecialchars($booking['ticket_no']); ?></b></p>
                    </div>
                    <a href="generate_ticket.php?booking_id=<?php echo $booking_id; ?>" target="_blank" class="btn btn-primary mt-2 mt-md-0"><i class="fas fa-print me-2"></i>View E-Ticket</a>
                </div>

                <div class="details-grid">
                    <!-- Journey Details Card -->
                    <div class="detail-card">
                        <div class="card-header"><i class="fas fa-route me-2"></i>Journey Details</div>
                        <div class="card-body">
                            <div class="info-row"><span class="label">Route Name</span><span class="value"><?php echo htmlspecialchars($booking['route_name']); ?></span></div>
                            <div class="info-row"><span class="label">From</span><span class="value fw-bold"><?php echo htmlspecialchars($booking['origin']); ?></span></div>
                            <div class="info-row"><span class="label">To</span><span class="value fw-bold"><?php echo htmlspecialchars($booking['destination']); ?></span></div>
                            <div class="info-row"><span class="label">Travel Date</span><span class="value"><?php echo date('l, d F Y', strtotime($booking['travel_date'])); ?></span></div>
                            <div class="info-row"><span class="label">Departure Time</span><span class="value"><?php echo date('h:i A', strtotime($booking['departure_time'])); ?></span></div>
                        </div>
                    </div>

                    <!-- Bus & Staff Details Card -->
                    <div class="detail-card">
                        <div class="card-header"><i class="fas fa-bus me-2"></i>Bus & Assigned Staff</div>
                        <div class="card-body">
                            <div class="info-row"><span class="label">Bus Name</span><span class="value"><?php echo htmlspecialchars($booking['bus_name']); ?></span></div>
                            <div class="info-row"><span class="label">Registration No.</span><span class="value"><?php echo htmlspecialchars($booking['registration_number']); ?></span></div>
                            <?php foreach ($assigned_staff as $role => $staff_list): ?>
                                <?php if (!empty($staff_list)): ?>
                                    <?php $staff_names = array_column($staff_list, 'name'); $display_text = htmlspecialchars(implode(', ', $staff_names)); ?>
                                    <div class="info-row"><span class="label"><?php echo htmlspecialchars(str_replace('_', '-', $role)); ?></span><span class="value"><?php echo $display_text; ?></span></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Booking & Payment Details Card -->
                    <div class="detail-card">
                        <div class="card-header"><i class="fas fa-file-invoice-dollar me-2"></i>Booking & Payment</div>
                        <div class="card-body">
                            <div class="info-row"><span class="label">Booked By</span><span class="value"><?php echo htmlspecialchars($booking['booker_name']); ?> (<?php echo $booking['booker_type']; ?>)</span></div>
                            <!-- NEW: Displaying the main contact number -->
                            <div class="info-row"><span class="label">Contact Mobile</span><span class="value fw-bold"><?php echo htmlspecialchars($booking['contact_mobile'] ?: 'N/A'); ?></span></div>
                            <div class="info-row"><span class="label">Booking Status</span><span class="value"><span class="badge bg-success"><?php echo htmlspecialchars($booking['booking_status']); ?></span></span></div>
                            <div class="info-row"><span class="label">Payment Method</span><span class="value"><?php echo htmlspecialchars($booking['payment_method'] ?: 'CASH'); ?></span></div>
                            <div class="info-row"><span class="label">Payment Status</span><span class="value"><span class="badge bg-<?php echo $booking['payment_status'] === 'PAID' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($booking['payment_status'] ?: 'PAID'); ?></span></span></div>
                            <div class="info-row"><span class="label">Total Fare</span><span class="value fs-5 fw-bold text-success">₹<?php echo number_format($booking['total_fare'], 2); ?></span></div>
                        </div>
                    </div>
                </div>

                <!-- Passenger Manifest Card -->
                <div class="card detail-card mt-4">
                    <div class="card-header"><i class="fas fa-users me-2"></i>Passenger Manifest</div>
                    <div class="card-body">
                        <table id="passenger_manifest_table" class="table table-hover dt-responsive nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <!-- REMOVED: Mobile column from here -->
                                    <th>Age</th>
                                    <th>Gender</th>
                                    <th>Seat No.</th>
                                    <th>Fare</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($passengers as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['passenger_name']); ?></td>
                                        <!-- REMOVED: Mobile cell from here -->
                                        <td><?php echo htmlspecialchars($p['passenger_age'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($p['passenger_gender']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($p['seat_code']); ?></span></td>
                                        <td>₹<?php echo number_format($p['fare'], 2); ?></td>
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

    <script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.13.6/r-2.5.0/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#passenger_manifest_table').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search passengers..."
                }
            });
        });
    </script>
</body>
</html>