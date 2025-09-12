<?php
// payment_process.php
include_once('function/_db.php');
session_security_check(); // Or your relevant login check

// --- 1. Get and Validate Booking ID ---
$booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
if (!$booking_id) {
    die("Error: Invalid or missing booking ID.");
}

// --- 2. Fetch Booking Details from Database ---
try {
    $stmt = $_conn_db->prepare("
        SELECT b.*, r.route_name, bs.bus_name 
        FROM bookings b
        JOIN routes r ON b.route_id = r.route_id
        JOIN buses bs ON b.bus_id = bs.bus_id
        WHERE b.booking_id = ? AND b.booking_status = 'PENDING'
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        die("Error: Booking not found or has already been processed.");
    }

    // Fetch passenger details for this booking
    $stmt_passengers = $_conn_db->prepare("SELECT * FROM passengers WHERE booking_id = ?");
    $stmt_passengers->execute([$booking_id]);
    $passengers = $stmt_passengers->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Payment Process Error (Fetch): " . $e->getMessage());
    die("Database error while fetching booking details.");
}


// --- 3. Simulate a Successful Payment & Create Dummy Data ---
// In a real scenario, this is where Razorpay's response would be handled.
// Here, we just create dummy data.
$dummy_payment_id = 'pay_' . bin2hex(random_bytes(10));
$dummy_order_id = 'order_' . bin2hex(random_bytes(10));
$payment_method = 'upi'; // Simulate a UPI payment


// --- 4. Update Database (The most important part) ---
$_conn_db->beginTransaction();
try {
    // a) Update the booking to CONFIRMED and PAID
    $stmt_update_booking = $_conn_db->prepare("
        UPDATE bookings 
        SET booking_status = 'CONFIRMED'
        WHERE booking_id = ?
    ");
    $stmt_update_booking->execute([$booking_id]);

    // b) Insert a record into the transactions table
    $stmt_transaction = $_conn_db->prepare("
        INSERT INTO transactions 
            (booking_id, user_id, employee_id, gateway_payment_id, gateway_order_id, amount, payment_status, method) 
        VALUES (?, ?, ?, ?, ?, ?, 'CAPTURED', ?)
    ");
    $stmt_transaction->execute([
        $booking_id,
        $booking['user_id'], // This will be NULL for employee bookings, which is correct
        $booking['booked_by_employee_id'],
        $dummy_payment_id,
        $dummy_order_id,
        $booking['total_fare'],
        $payment_method
    ]);

    // c) If everything is successful, commit the changes
    $_conn_db->commit();

} catch (PDOException $e) {
    $_conn_db->rollBack();
    error_log("Payment Process Error (Update): " . $e->getMessage());
    die("A critical error occurred while confirming your payment. Please contact support.");
}

// --- 5. Display a Success Page to the User ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Payment Successful</title>
    <style>
        body { background-color: #f0f9f5; }
        .success-card { max-width: 600px; margin: 50px auto; background: white; border-left: 5px solid #198754; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .success-icon { font-size: 4rem; color: #198754; }
        .ticket-details dt { font-weight: 600; color: #6c757d; }
        .ticket-details dd { font-weight: 500; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <div class="card success-card">
                <div class="card-body text-center p-4 p-md-5">
                    <div class="success-icon mb-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="card-title">Booking Confirmed!</h2>
                    <p class="text-muted">Your ticket has been successfully booked. A confirmation has been sent to your contact details.</p>
                    <hr class="my-4">
                    
                    <h5 class="text-start mb-3">Booking Summary</h5>
                    <dl class="row text-start ticket-details">
                        <dt class="col-sm-4">Booking ID:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($booking['booking_id']); ?></dd>
                        
                        <dt class="col-sm-4">Route:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($booking['route_name']); ?></dd>
                        
                        <dt class="col-sm-4">Bus:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($booking['bus_name']); ?></dd>
                        
                        <dt class="col-sm-4">Travel Date:</dt>
                        <dd class="col-sm-8"><?php echo date('D, d M Y', strtotime($booking['travel_date'])); ?></dd>

                        <dt class="col-sm-4">Passengers:</dt>
                        <dd class="col-sm-8">
                            <?php foreach($passengers as $p): ?>
                                <?php echo htmlspecialchars($p['passenger_name']); ?> (Seat: <?php echo htmlspecialchars($p['seat_code']); ?>)<br>
                            <?php endforeach; ?>
                        </dd>
                        
                        <dt class="col-sm-4">Total Fare:</dt>
                        <dd class="col-sm-8 fw-bold fs-5">₹<?php echo htmlspecialchars(number_format($booking['total_fare'], 2)); ?></dd>
                        
                        <dt class="col-sm-4">Payment ID:</dt>
                        <dd class="col-sm-8"><small class="text-muted"><?php echo htmlspecialchars($dummy_payment_id); ?></small></dd>
                    </dl>
                    
                    <div class="mt-4">
                        <a href="book_ticket.php" class="btn btn-primary"><i class="fas fa-plus"></i> Book Another Ticket</a>
                        <a href="view_bookings.php" class="btn btn-outline-secondary">View All Bookings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "foot.php"; ?>
</body>
</html>