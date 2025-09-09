<?php
// ticket_view.php
include_once('function/_db.php');
session_security_check();

$booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
if (!$booking_id) {
    die("Error: Invalid Booking ID provided.");
}

// --- Comprehensive query to fetch all necessary details in one go ---
try {
    $stmt = $_conn_db->prepare("
        SELECT 
            b.ticket_no, b.travel_date, b.total_fare, b.booking_status, b.contact_email, b.contact_mobile,
            r.route_name, r.starting_point,
            sch.departure_time,
            bu.bus_name, bu.registration_number,
            op.operator_name, op.contact_phone AS conductor_mobile
        FROM bookings b
        JOIN routes r ON b.route_id = r.route_id
        JOIN buses bu ON b.bus_id = bu.bus_id
        JOIN operators op ON bu.operator_id = op.operator_id
        LEFT JOIN route_schedules sch ON r.route_id = sch.route_id AND sch.operating_day = DATE_FORMAT(b.travel_date, '%a')
        WHERE b.booking_id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        die("Error: Booking details not found for the given ID.");
    }

} catch (PDOException $e) {
    // For production, you might want to log the error instead of showing it
    // error_log($e->getMessage());
    die("Database error. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <!-- FIX: Use null coalescing operator (??) to prevent errors if ticket_no is null -->
    <title>Ticket #<?php echo htmlspecialchars($booking['ticket_no'] ?? 'N/A'); ?> - Booking Details</title>
    <style>
        body { background-color: #f4f7f6; }
        .page-header { border-bottom: 1px solid #dee2e6; padding-bottom: 1rem; }
        .ticket-id { font-size: 1.1rem; font-weight: 600; color: #6c757d; }
        .ticket-id .badge { font-size: 1rem; }
        
        .details-card { background: #fff; border-radius: 12px; box-shadow: 0 5px 25px rgba(0,0,0,0.07); }
        .details-card .card-header { background-color: #0d6efd; color: white; font-weight: 600; border-top-left-radius: 12px; border-top-right-radius: 12px; }
        
        .detail-item { display: flex; align-items: flex-start; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0; }
        .detail-item:last-child { border-bottom: none; }
        .detail-icon { flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%; background-color: #e9ecef; color: #0d6efd; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-right: 1rem; }
        .detail-content { flex-grow: 1; }
        .detail-content .label { font-size: 0.85em; color: #6c757d; display: block; }
        .detail-content .value { font-size: 1em; font-weight: 500; color: #212529; }

        .control-panel { background-color: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 5px 25px rgba(0,0,0,0.08); border: 1px solid #e9ecef; }
        .pdf-viewer-container { background-color: #fff; border-radius: 12px; padding: 1rem; box-shadow: 0 5px 25px rgba(0,0,0,0.08); border: 1px solid #e9ecef; }
        .pdf-viewer { width: 100%; height: 85vh; border: none; border-radius: 8px; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <div class="page-header my-4">
                <h2>Booking Details</h2>
                <!-- FIX: Use null coalescing operator (??) to prevent errors if ticket_no is null -->
                <span class="ticket-id">Ticket No: <span class="badge bg-secondary"><?php echo htmlspecialchars($booking['ticket_no'] ?? 'N/A'); ?></span></span>
            </div>
            
            <div class="row">
                <div class="col-lg-4 order-lg-2 mb-4 mb-lg-0">
                    <div class="control-panel">
                         <h5 class="mb-3">Share & Manage Ticket</h5>
                         <div class="d-grid gap-2 mb-4">
                             <a href="generate_ticket.php?booking_id=<?php echo $booking_id; ?>" target="_blank" class="btn btn-dark"><i class="fas fa-print me-2"></i>Print / Download PDF</a>
                         </div>
                         <div class="mb-3">
                            <label class="form-label">WhatsApp Number</label>
                            <!-- FIX: Use null coalescing operator for optional values -->
                            <div class="input-group"><input type="tel" class="form-control" id="whatsapp-number" value="<?php echo htmlspecialchars($booking['contact_mobile'] ?? ''); ?>" placeholder="Customer WhatsApp"><button class="btn btn-outline-success" id="send-whatsapp-btn" type="button"><i class="fab fa-whatsapp"></i></button></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <!-- FIX: Use null coalescing operator for optional values -->
                            <div class="input-group"><input type="email" class="form-control" id="email-address" value="<?php echo htmlspecialchars($booking['contact_email'] ?? ''); ?>" placeholder="Customer Email"><button class="btn btn-outline-primary" id="send-email-btn" type="button"><i class="fas fa-paper-plane"></i></button></div>
                        </div>
                        <hr>
                        <a href="book_ticket.php" class="btn btn-light border w-100"><i class="fas fa-plus me-2"></i>Book Another Ticket</a>
                    </div>
                </div>
                <div class="col-lg-8 order-lg-1">
                    <div class="card details-card">
                        <div class="card-header"><i class="fas fa-info-circle me-2"></i>Booking & Journey Information</div>
                        <div class="card-body p-4">
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fas fa-route"></i></div>
                                <div class="detail-content"><span class="label">Route</span><span class="value"><?php echo htmlspecialchars($booking['route_name'] ?? 'N/A'); ?></span></div>
                            </div>
                             <div class="detail-item">
                                <div class="detail-icon"><i class="fas fa-bus"></i></div>
                                <div class="detail-content"><span class="label">Bus Details</span><span class="value"><?php echo htmlspecialchars($booking['bus_name'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($booking['registration_number'] ?? 'N/A'); ?>)</span></div>
                            </div>
                             <div class="detail-item">
                                <div class="detail-icon"><i class="fas fa-user-tie"></i></div>
                                <div class="detail-content"><span class="label">Operator / Conductor Contact</span><span class="value"><?php echo htmlspecialchars($booking['operator_name'] ?? 'N/A'); ?> - <?php echo htmlspecialchars($booking['conductor_mobile'] ?? 'N/A'); ?></span></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fas fa-calendar-alt"></i></div>
                                <div class="detail-content"><span class="label">Travel Date</span><span class="value"><?php echo date('l, d F Y', strtotime($booking['travel_date'])); ?></span></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fas fa-clock"></i></div>
                                <div class="detail-content"><span class="label">Departure from <?php echo htmlspecialchars($booking['starting_point'] ?? 'N/A'); ?></span><span class="value"><?php echo date('h:i A', strtotime($booking['departure_time'])); ?></span></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fas fa-money-bill-wave"></i></div>
                                <div class="detail-content"><span class="label">Total Fare Paid</span><span class="value fs-5 fw-bold">₹ <?php echo number_format($booking['total_fare'] ?? 0, 2); ?></span></div>
                            </div>
                        </div>
                    </div>
                     <div class="pdf-viewer-container mt-4">
                        <iframe class="pdf-viewer" src="generate_ticket.php?booking_id=<?php echo $booking_id; ?>" title="Ticket Preview"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "foot.php"; ?>
<script>
$(document).ready(function() {
    $('#send-whatsapp-btn').on('click', function() {
        const number = $('#whatsapp-number').val().trim();
        if (!number) {
            Swal.fire('Error', 'Please enter a valid WhatsApp number (including country code).', 'error');
            return;
        }
        
        const ticketText = 
`*Bus Ticket Confirmed!*

Hello,
Thank you for booking with BPL Tickets. Your e-ticket is confirmed.

*Ticket No:* <?php echo htmlspecialchars($booking['ticket_no'] ?? 'N/A'); ?>
*Route:* <?php echo htmlspecialchars(addslashes($booking['route_name'] ?? 'N/A')); ?>
*Date:* <?php echo date('d M Y', strtotime($booking['travel_date'])); ?>

You can download your ticket here:
${window.location.origin}${window.location.pathname.replace('ticket_view.php', '')}generate_ticket.php?booking_id=<?php echo $booking_id; ?>

Safe travels!`;
        
        const encodedText = encodeURIComponent(ticketText);
        const whatsappUrl = `https://wa.me/${number}?text=${encodedText}`;
        window.open(whatsappUrl, '_blank');
    });

    $('#send-email-btn').on('click', function() {
        const email = $('#email-address').val().trim();
        if (!email) {
            Swal.fire('Error', 'Please enter a valid email address.', 'error');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: 'function/backend/email_ticket.php', // This backend file is required
            type: 'POST',
            data: {
                booking_id: <?php echo $booking_id; ?>,
                email: email
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire('Success!', 'The ticket has been sent to the email address.', 'success');
                } else {
                    Swal.fire('Error', response.message || 'Could not send the email.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'A server error occurred. Please ensure email settings are correct.', 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send');
            }
        });
    });
});
</script>
</body>
</html>