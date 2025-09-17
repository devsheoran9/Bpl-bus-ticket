<?php
// ticket_view.php
include_once('function/_db.php');
session_security_check();

$booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
if (!$booking_id) {
    die("Error: Invalid Booking ID provided.");
}

try {
    $stmt = $_conn_db->prepare("
        SELECT 
            b.*, r.route_name, r.route_id, r.starting_point, sch.departure_time,
            bu.bus_name, bu.registration_number
        FROM bookings b
        JOIN routes r ON b.route_id = r.route_id
        JOIN buses bu ON b.bus_id = bu.bus_id
        LEFT JOIN route_schedules sch ON r.route_id = sch.route_id AND sch.operating_day = DATE_FORMAT(b.travel_date, '%a')
        WHERE b.booking_id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) die("Error: Booking details not found.");
    
    $staff_stmt = $_conn_db->prepare("
        SELECT s.name, s.mobile FROM route_staff_assignments rsa
        JOIN staff s ON rsa.staff_id = s.staff_id
        WHERE rsa.route_id = ? AND rsa.role = 'Conductor' LIMIT 1
    ");
    $staff_stmt->execute([$booking['route_id']]);
    $conductor_info = $staff_stmt->fetch(PDO::FETCH_ASSOC);
    $conductor_name = $conductor_info['name'] ?? 'N/A';
    $conductor_phone = $conductor_info['mobile'] ?? 'N/A';

    $tokenStmt = $_conn_db->prepare("SELECT token FROM ticket_access_tokens WHERE booking_id = ?");
    $tokenStmt->execute([$booking_id]);
    $token = $tokenStmt->fetchColumn();
    if (!$token) {
        $token = bin2hex(random_bytes(16));
        $_conn_db->prepare("INSERT INTO ticket_access_tokens (booking_id, token) VALUES (?, ?)")->execute([$booking_id, $token]);
    }
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
   
    $publicTicketUrl = $protocol . $host . '/ticket_public_view?token=' . $token;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Manage Ticket #<?php echo htmlspecialchars($booking['ticket_no'] ?? 'N/A'); ?></title>
    <style>
        body { background-color: #f4f7f6; }
        .details-card, .control-panel {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
        }
        .details-card .card-header {
            background-color: #0d6efd;
            color: white;
            font-weight: 600;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        .detail-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-item:last-child { border-bottom: none; }
        .detail-icon {
            flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%;
            background-color: #eef2ff; color: #0d6efd; display: flex;
            align-items: center; justify-content: center; font-size: 1.2rem; margin-right: 1rem;
        }
        .detail-content .label { font-size: 0.85em; color: #6c757d; display: block; }
        .detail-content .value { font-size: 1em; font-weight: 500; color: #212529; }
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
                    <h2 class="mb-0">Manage Ticket</h2>
                    <p class="text-muted mb-0">Ticket No: <span class="fw-bold text-primary"><?php echo htmlspecialchars($booking['ticket_no'] ?? 'N/A'); ?></span></p>
                </div>
                <a href="book_ticket.php" class="btn btn-light border"><i class="fas fa-plus me-2"></i>New Booking</a>
            </div>
            
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card details-card">
                        <div class="card-header"><i class="fas fa-info-circle me-2"></i>Booking & Journey Information</div>
                        <div class="card-body p-4">
                            <div class="detail-item"><div class="detail-icon"><i class="fas fa-route"></i></div><div class="detail-content"><span class="label">Route</span><span class="value"><?php echo htmlspecialchars($booking['route_name'] ?? 'N/A'); ?></span></div></div>
                            <div class="detail-item"><div class="detail-icon"><i class="fas fa-bus"></i></div><div class="detail-content"><span class="label">Bus Details</span><span class="value"><?php echo htmlspecialchars($booking['bus_name'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($booking['registration_number'] ?? 'N/A'); ?>)</span></div></div>
                            <div class="detail-item"><div class="detail-icon"><i class="fas fa-user-tie"></i></div><div class="detail-content"><span class="label">Conductor Contact</span><span class="value"><?php echo htmlspecialchars($conductor_name); ?> - <?php echo htmlspecialchars($conductor_phone); ?></span></div></div>
                            <div class="detail-item"><div class="detail-icon"><i class="fas fa-calendar-alt"></i></div><div class="detail-content"><span class="label">Travel Date</span><span class="value"><?php echo date('l, d F Y', strtotime($booking['travel_date'])); ?></span></div></div>
                            <div class="detail-item"><div class="detail-icon"><i class="fas fa-clock"></i></div><div class="detail-content"><span class="label">Departure from <?php echo htmlspecialchars($booking['starting_point'] ?? 'N/A'); ?></span><span class="value"><?php echo date('h:i A', strtotime($booking['departure_time'] ?? '')); ?></span></div></div>
                            <div class="detail-item"><div class="detail-icon"><i class="fas fa-money-bill-wave"></i></div><div class="detail-content"><span class="label">Total Fare Paid</span><span class="value fs-5 fw-bold text-success">₹ <?php echo number_format($booking['total_fare'] ?? 0, 2); ?></span></div></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="control-panel p-4">
                         <h5 class="mb-3">Actions & Sharing</h5>
                         <div class="d-grid gap-2 mb-4">
                             <a href="generate_ticket.php?booking_id=<?php echo $booking_id; ?>" target="_blank" class="btn btn-primary btn-lg"><i class="fas fa-ticket-alt me-2"></i>View & Download Ticket</a>
                         </div>
                         <div class="mb-3">
                            <label class="form-label">Share via WhatsApp</label>
                            <div class="input-group">
                                <input type="tel" class="form-control" id="whatsapp-number" value="<?php echo htmlspecialchars($booking['contact_mobile'] ?? ''); ?>" placeholder="Enter WhatsApp No.">
                                <button class="btn btn-outline-success" id="send-whatsapp-btn" type="button"><i class="fab fa-whatsapp"></i> Send</button>
                            </div>
                        </div>
                       
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
            Swal.fire('Error', 'Please enter a valid WhatsApp number.', 'error');
            return;
        }
        
        // Use PHP to generate the text and pass it safely to JavaScript
        const ticketText = <?php echo json_encode(
            "*Bus Ticket Confirmed!*\n\n" .
            "Hello,\n" .
            "Your e-ticket for the journey from *" . ($booking['origin']) . "* to *" . ($booking['destination']) . "* is confirmed.\n\n" .
            "*Ticket No:* " . ($booking['ticket_no'] ?? 'N/A') . "\n" .
            "*Travel Date:* " . date('d M Y', strtotime($booking['travel_date'])) . "\n\n" .
            "You can view and download your ticket from this secure link:\n" .
            $publicTicketUrl . "\n\n" .
            "We wish you a safe and pleasant journey!\n" .
            "- BPL Bus Service"
        ); ?>;
        
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
            url: 'function/backend/email_ticket.php',
            type: 'POST', 
            data: { booking_id: <?php echo $booking_id; ?>, email: email }, 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire('Success!', 'The ticket has been sent to the email address.', 'success');
                } else {
                    Swal.fire('Error', response.message || 'Could not send the email.', 'error');
                }
            },
            error: function() { Swal.fire('Error', 'A server error occurred.', 'error'); },
            complete: function() { btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send'); }
        });
    });
});
</script>
</body>
</html>