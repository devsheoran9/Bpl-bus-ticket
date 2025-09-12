<?php
// booking_details.php
include_once('function/_db.php');
session_security_check(); 
check_permission('main_admin');

$booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
if (!$booking_id) die("Error: Invalid Booking ID.");

try {
    // A comprehensive query to get all details related to a booking
    $stmt = $_conn_db->prepare("
        SELECT 
            b.booking_id, b.ticket_no, b.travel_date, b.total_fare, b.booking_status, b.origin, b.destination,
            COALESCE(a.name, u.username) as booker_name,
            CASE WHEN b.booked_by_employee_id IS NOT NULL THEN 'Employee' ELSE 'Online User' END as booker_type,
            r.route_name,
            sch.departure_time,
            bu.bus_name, bu.registration_number, bu.bus_type,
            op.operator_name, op.contact_person AS conductor_name, op.contact_phone AS conductor_mobile,
            b.payment_status, -- Fetched directly from bookings table
            t.method AS payment_method, t.gateway_payment_id
        FROM bookings b
        LEFT JOIN admin a ON b.booked_by_employee_id = a.id
        LEFT JOIN users u ON b.user_id = u.id
        JOIN routes r ON b.route_id = r.route_id
        JOIN buses bu ON b.bus_id = bu.bus_id
        JOIN operators op ON bu.operator_id = op.operator_id
        LEFT JOIN route_schedules sch ON r.route_id = sch.route_id AND sch.operating_day = DATE_FORMAT(b.travel_date, '%a')
        LEFT JOIN transactions t ON b.booking_id = t.booking_id
        WHERE b.booking_id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) die("Booking not found.");

    // --- CORRECTED: Ensure we fetch passenger_mobile ---
    $passengersStmt = $_conn_db->prepare("SELECT passenger_name, passenger_mobile, passenger_age, passenger_gender, seat_code, fare FROM passengers WHERE booking_id = ? ORDER BY passenger_name ASC");
    $passengersStmt->execute([$booking_id]);
    $passengers = $passengersStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Booking Details - #<?php echo htmlspecialchars($booking['ticket_no']); ?></title>
    <style>
        body { background-color: #f8f9fa; }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        .detail-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
        }
        .detail-card-header {
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-card-header i { color: #0d6efd; }
        .detail-card-body { padding: 1.5rem; }
        .info-row { display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid #f0f0f0; }
        .info-row:last-child { border-bottom: none; }
        .info-row .label { color: #6c757d; }
        .info-row .value { font-weight: 500; text-align: right; }
        .passenger-table td, .passenger-table th { font-size: 0.9em; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center my-4">
                <div>
                    <h2 class="mb-0">Booking Details</h2>
                    <p class="text-muted">Ticket No: <b><?php echo htmlspecialchars($booking['ticket_no']); ?></b></p>
                </div>
                <a href="generate_ticket.php?booking_id=<?php echo $booking_id; ?>" target="_blank" class="btn btn-primary"><i class="fas fa-print me-2"></i>View E-Ticket PDF</a>
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
                <!-- Bus & Operator Details Card -->
                <div class="detail-card">
                    <div class="card-header"><i class="fas fa-bus me-2"></i>Bus & Operator Details</div>
                    <div class="card-body">
                        <div class="info-row"><span class="label">Operator</span><span class="value"><?php echo htmlspecialchars($booking['operator_name']); ?></span></div>
                        <div class="info-row"><span class="label">Conductor Name</span><span class="value"><?php echo htmlspecialchars($booking['conductor_name'] ?: 'N/A'); ?></span></div>
                        <div class="info-row"><span class="label">Conductor Mobile</span><span class="value"><?php echo htmlspecialchars($booking['conductor_mobile'] ?: 'N/A'); ?></span></div>
                        <div class="info-row"><span class="label">Bus Name</span><span class="value"><?php echo htmlspecialchars($booking['bus_name']); ?></span></div>
                        <div class="info-row"><span class="label">Registration No.</span><span class="value"><?php echo htmlspecialchars($booking['registration_number']); ?></span></div>
                    </div>
                </div>
                <!-- Booking & Payment Details Card -->
                <div class="detail-card">
                    <div class="card-header"><i class="fas fa-file-invoice-dollar me-2"></i>Booking & Payment</div>
                    <div class="card-body">
                        <div class="info-row"><span class="label">Booked By</span><span class="value"><?php echo htmlspecialchars($booking['booker_name']); ?> (<?php echo $booking['booker_type']; ?>)</span></div>
                        <div class="info-row"><span class="label">Booking Status</span><span class="value"><span class="badge bg-success"><?php echo htmlspecialchars($booking['booking_status']); ?></span></span></div>
                        <div class="info-row"><span class="label">Payment Method</span><span class="value"><?php echo htmlspecialchars($booking['payment_method'] ?: 'CASH'); ?></span></div>
                        <div class="info-row"><span class="label">Payment Status</span><span class="value"><?php echo htmlspecialchars($booking['payment_status'] ?: 'PAID'); ?></span></div>
                        <div class="info-row"><span class="label">Total Fare</span><span class="value fs-5 fw-bold text-success">₹<?php echo number_format($booking['total_fare'], 2); ?></span></div>
                    </div>
                </div>
            </div>

            <!-- Passenger Manifest Card -->
            <div class="card detail-card mt-4">
                <div class="card-header"><i class="fas fa-users me-2"></i>Passenger Manifest</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table passenger-table">
                            <!-- --- CORRECTED TABLE HEADER --- -->
                            <thead><tr><th>Name</th><th>Mobile</th><th>Age</th><th>Gender</th><th>Seat No.</th><th>Fare</th></tr></thead>
                            <tbody>
                                <?php foreach($passengers as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['passenger_name']); ?></td>
                                    <!-- --- ADDED MOBILE NUMBER CELL --- -->
                                    <td><?php echo htmlspecialchars($p['passenger_mobile']); ?></td>
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
</div>
<?php include "foot.php"; ?>
</body>
</html>