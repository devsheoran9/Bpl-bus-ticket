<?php
// ticket_view.php
include_once('function/_db.php');
session_security_check();

// Permissions for this page
check_permission('can_view_bookings'); // User must be able to view bookings to access this page
$can_delete_bookings = user_has_permission('can_delete_bookings'); // Specific permission for delete action

$booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
if (!$booking_id) {
    die("Error: Invalid Booking ID provided.");
}

try {
    // --- 1. Fetch main booking details ---
    $stmt = $_conn_db->prepare("
        SELECT 
            b.*, r.route_name, r.route_id, r.starting_point, r.ending_point, sch.departure_time,
            bu.bus_name, bu.registration_number, bu.bus_type
        FROM bookings b
        JOIN routes r ON b.route_id = r.route_id
        JOIN buses bu ON b.bus_id = bu.bus_id
        LEFT JOIN route_schedules sch ON r.route_id = sch.route_id AND sch.operating_day = DATE_FORMAT(b.travel_date, '%a')
        WHERE b.booking_id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) die("Error: Booking details not found.");

    // --- 2. Fetch Conductor info ---
    $staff_stmt = $_conn_db->prepare("
        SELECT s.name, s.mobile FROM route_staff_assignments rsa
        JOIN staff s ON rsa.staff_id = s.staff_id
        WHERE rsa.route_id = ? AND rsa.role = 'Conductor' LIMIT 1
    ");
    $staff_stmt->execute([$booking['route_id']]);
    $conductor_info = $staff_stmt->fetch(PDO::FETCH_ASSOC);
    $conductor_name = $conductor_info['name'] ?? 'N/A';
    $conductor_phone = $conductor_info['mobile'] ?? 'N/A';

    // --- 3. Get/Generate secure token for public ticket URL ---
    $tokenStmt = $_conn_db->prepare("SELECT token FROM ticket_access_tokens WHERE booking_id = ?");
    $tokenStmt->execute([$booking_id]);
    $token = $tokenStmt->fetchColumn();
    if (!$token) {
        $token = bin2hex(random_bytes(20));
        $_conn_db->prepare("INSERT INTO ticket_access_tokens (booking_id, token) VALUES (?, ?)")->execute([$booking_id, $token]);
    }
    $publicTicketUrl = BASE_URLL . '?token=' . urlencode($token);

    // --- 4. Fetch Route Stops for detailed timeline ---
    $stmt_stops = $_conn_db->prepare("SELECT * FROM route_stops WHERE route_id = ? ORDER BY stop_order ASC");
    $stmt_stops->execute([$booking['route_id']]);
    $all_route_stops = $stmt_stops->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// --- Dynamic Timings Calculation for Route Timeline (PHP Helper) ---
$route_timeline = [];
if (!empty($booking['departure_time'])) {
    try {
        $mainDepartureTime = new DateTime($booking['travel_date'] . ' ' . $booking['departure_time']);
        $previousStopTime = clone $mainDepartureTime;

        // Starting Point
        $route_timeline[] = [
            'type' => 'start',
            'name' => $booking['starting_point'],
            'time' => $mainDepartureTime->format('h:i A'),
            'duration_from_prev' => 0,
            'is_boarding' => ($booking['starting_point'] == $booking['origin']),
            'is_dropping' => ($booking['starting_point'] == $booking['destination'])
        ];

        // Intermediate Stops
        foreach ($all_route_stops as $stop) {
            $arrivalTime = clone $mainDepartureTime;
            $arrivalTime->modify('+' . $stop['duration_from_start_minutes'] . ' minutes');
            $interval = $previousStopTime->diff($arrivalTime);
            $durationBetween = ($interval->h * 60) + $interval->i;

            $route_timeline[] = [
                'type' => 'stop',
                'name' => $stop['stop_name'],
                'time' => $arrivalTime->format('h:i A'),
                'duration_from_prev' => $durationBetween,
                'is_boarding' => ($stop['stop_name'] == $booking['origin']),
                'is_dropping' => ($stop['stop_name'] == $booking['destination'])
            ];
            $previousStopTime = clone $arrivalTime;
        }

        // Ending Point
        $total_route_duration_stmt = $_conn_db->prepare("SELECT MAX(duration_from_start_minutes) FROM route_stops WHERE route_id = ?");
        $total_route_duration_stmt->execute([$booking['route_id']]);
        $max_duration_overall = (int)$total_route_duration_stmt->fetchColumn();

        $finalArrivalTime = clone $mainDepartureTime;
        $finalArrivalTime->modify('+' . $max_duration_overall . ' minutes');
        $interval = $previousStopTime->diff($finalArrivalTime);
        $durationBetween = ($interval->h * 60) + $interval->i;

        $route_timeline[] = [
            'type' => 'end',
            'name' => $booking['ending_point'],
            'time' => $finalArrivalTime->format('h:i A'),
            'duration_from_prev' => $durationBetween,
            'is_boarding' => ($booking['ending_point'] == $booking['origin']),
            'is_dropping' => ($booking['ending_point'] == $booking['destination'])
        ];
    } catch (Exception $e) {
        // Log or handle timeline calculation errors
        error_log("Timeline calculation error for booking ID {$booking_id}: " . $e->getMessage());
        $route_timeline = []; // Clear timeline on error
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "head.php"; ?>
    <title>Ticket Details - #<?php echo htmlspecialchars($booking['ticket_no'] ?? 'N/A'); ?></title>
    <style>
        /* --- General Layout --- */
        body {
            background-color: #f4f7f6;
            font-family: 'Inter', sans-serif;
            color: #343a40;
            line-height: 1.5;
        }

        .container-fluid {
            padding-top: 1.5rem;
            padding-bottom: 2rem;
        }

        h2.mb-0 {
            font-weight: 700;
            color: #212529;
            font-size: 1.8rem;
        }

        p.text-muted {
            font-size: 0.95rem;
        }

        /* --- Card Styles --- */
        .panel-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            margin-bottom: 1.5rem;
            /* Consistent spacing */
            transition: all 0.2s ease-in-out;
        }

        .panel-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .panel-card .card-header {
            background-color: #0d6efd;
            /* Primary blue header */
            color: white;
            font-weight: 600;
            border-radius: 12px 12px 0 0;
            /* Match card rounding */
            padding: 1.25rem 1.5rem;
            font-size: 1.1rem;
        }

        .panel-card .card-body {
            padding: 1.5rem;
        }

        /* --- Detail Item (Key-Value) Styling --- */
        .detail-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 0;
            /* Compact padding */
            border-bottom: 1px solid #f0f0f0;
            gap: 1rem;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-icon {
            flex-shrink: 0;
            width: 45px;
            /* Slightly larger icon circle */
            height: 45px;
            border-radius: 50%;
            background-color: #eef2ff;
            /* Very light blue background */
            color: #0d6efd;
            /* Primary blue icon */
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            /* Larger icon */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .detail-content .label {
            font-size: 0.8em;
            color: #6c757d;
            /* Muted grey label */
            display: block;
            margin-bottom: 0.1rem;
            font-weight: 500;
        }

        .detail-content .value {
            font-size: 1em;
            /* Standard text size */
            font-weight: 600;
            /* Semi-bold value */
            color: #212529;
            /* Darker value */
            line-height: 1.2;
        }

        .detail-content .value.text-success {
            color: #198754 !important;
        }

        /* --- Actions & Sharing Panel --- */
        .control-panel .card-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .control-panel h5 {
            font-weight: 700;
            color: #212529;
            font-size: 1.15rem;
        }

        .control-panel .btn-lg {
            padding: 0.8rem 1.5rem;
            font-size: 1.1rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }

        .control-panel .input-group .form-control {
            border-radius: 0.5rem 0 0 0.5rem;
            font-size: 0.95rem;
            padding: 0.65rem 1rem;
        }

        .control-panel .input-group .btn {
            border-radius: 0 0.5rem 0.5rem 0;
            font-size: 0.95rem;
            padding: 0.65rem 1rem;
        }

        /* --- Route Timeline Section --- */
        .route-timeline-section {
            border-left: 4px solid #f0f0f0;
            margin-left: 1rem;
        }

        /* Light grey line */
        .route-timeline-item {
            display: flex;
            align-items: flex-start;
            position: relative;
            padding-bottom: 1.5rem;
            /* Space between items */
            margin-left: 0.5rem;
            /* Adjust for line placement */
        }

        /* Vertical line between items */
        .route-timeline-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 14px;
            /* Align with icon center */
            top: 30px;
            /* Start below icon */
            bottom: -5px;
            /* Extend below this item for continuity */
            width: 2px;
            background-color: #dee2e6;
            /* Grey line */
            z-index: 0;
        }

        .timeline-icon {
            flex-shrink: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            z-index: 1;
            /* Above the line */
            font-size: 1rem;
            box-shadow: 0 0 0 4px #f4f7f6;
            /* White border to lift it */
            margin-right: 1rem;
        }

        .timeline-icon.icon-start {
            background-color: #0d6efd;
        }

        /* Blue */
        .timeline-icon.icon-stop {
            background-color: #6c757d;
        }

        /* Grey */
        .timeline-icon.icon-end {
            background-color: #198754;
        }

        /* Green */
        .timeline-icon.icon-passenger {
            background-color: #fca311;
        }

        /* Orange for Boarding/Dropping */


        .timeline-content {
            flex-grow: 1;
        }

        .timeline-content strong.stop-name {
            display: block;
            font-size: 1.05rem;
            font-weight: 700;
            color: #212529;
            line-height: 1.2;
        }

        .timeline-content .details-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.2rem;
            font-size: 0.85rem;
            color: #6c757d;
        }

        .time-info {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .duration-pill {
            background-color: #e9ecef;
            color: #495057;
            padding: 0.1rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .pass-tag {
            background-color: #cfe2ff;
            color: #084298;
            padding: 0.1rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* --- Responsive Adjustments --- */
        @media (max-width: 767.98px) {
            h2.mb-0 {
                font-size: 1.5rem;
            }

            .d-flex.justify-content-between.align-items-center.my-4 {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                margin-top: 1rem !important;
                margin-bottom: 1rem !important;
            }

            .d-flex.justify-content-between.align-items-center.my-4 .btn {
                width: 100%;
            }

            .panel-card .card-body {
                padding: 1rem;
            }

            .detail-item {
                flex-wrap: wrap;
                gap: 0.5rem;
                padding: 0.6rem 0;
            }

            .detail-icon {
                margin-right: 0.5rem;
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }

            .detail-content .label {
                font-size: 0.75em;
            }

            .detail-content .value {
                font-size: 0.95em;
            }

            .control-panel .card-body {
                padding: 1rem;
                gap: 1rem;
            }

            .control-panel .btn-lg {
                padding: 0.6rem 1rem;
                font-size: 1rem;
            }

            .control-panel .input-group {
                flex-wrap: wrap;
            }

            .control-panel .input-group .form-control,
            .control-panel .input-group .btn {
                width: 100%;
                border-radius: 0.5rem;
                margin-top: 0.5rem;
            }

            .control-panel .input-group .form-control {
                order: 1;
            }

            .control-panel .input-group .btn {
                order: 2;
                margin-left: 0 !important;
            }

            .route-timeline-item {
                margin-left: 0;
            }

            .route-timeline-item:not(:last-child)::before {
                left: 10px;
                top: 25px;
            }

            .timeline-icon {
                width: 25px;
                height: 25px;
                font-size: 0.9rem;
                margin-right: 0.8rem;
            }

            .timeline-content strong.stop-name {
                font-size: 0.95rem;
            }

            .timeline-content .details-row {
                gap: 0.5rem;
                font-size: 0.8em;
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
                <div class="d-flex justify-content-between align-items-center my-4">
                    <div>
                        <h2 class="mb-0">Ticket Details</h2>
                        <p class="text-muted mb-0">Ticket No: <span class="fw-bold text-primary"><?php echo htmlspecialchars($booking['ticket_no'] ?? 'N/A'); ?></span></p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="book_ticket.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New Booking</a>
                        <?php if ($can_delete_bookings): // Show delete button if permission is granted 
                        ?>
                            <button class="btn btn-danger delete-ticket-btn" data-booking-id="<?php echo htmlspecialchars($booking_id); ?>"><i class="fas fa-trash-alt me-2"></i>Delete Ticket</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <!-- Booking & Journey Information Card -->
                        <div class="panel-card">
                            <div class="card-header"><i class="fas fa-info-circle me-2"></i>Booking & Journey Information</div>
                            <div class="card-body">
                                <div class="detail-item">
                                    <div class="detail-icon"><i class="fas fa-route"></i></div>
                                    <div class="detail-content"><span class="label">Route</span><span class="value"><?php echo htmlspecialchars($booking['route_name'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($booking['starting_point']); ?> to <?php echo htmlspecialchars($booking['ending_point']); ?>)</span></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-icon"><i class="fas fa-bus"></i></div>
                                    <div class="detail-content"><span class="label">Bus Details</span><span class="value"><?php echo htmlspecialchars($booking['bus_name'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($booking['registration_number'] ?? 'N/A'); ?>) - <?php echo htmlspecialchars($booking['bus_type'] ?? 'N/A'); ?></span></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-icon"><i class="fas fa-user-tie"></i></div>
                                    <div class="detail-content"><span class="label">Conductor Contact</span><span class="value"><?php echo htmlspecialchars($conductor_name); ?> - <?php echo htmlspecialchars($conductor_phone); ?></span></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-icon"><i class="fas fa-calendar-alt"></i></div>
                                    <div class="detail-content"><span class="label">Travel Date</span><span class="value"><?php echo date('l, d F Y', strtotime($booking['travel_date'])); ?></span></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-icon"><i class="fas fa-clock"></i></div>
                                    <div class="detail-content"><span class="label">Scheduled Departure</span><span class="value"><?php echo date('h:i A', strtotime($booking['departure_time'] ?? '')); ?> from <?php echo htmlspecialchars($booking['starting_point'] ?? 'N/A'); ?></span></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-icon"><i class="fas fa-money-bill-wave"></i></div>
                                    <div class="detail-content"><span class="label">Total Fare Paid</span><span class="value fs-5 fw-bold text-success">₹ <?php echo number_format($booking['total_fare'] ?? 0, 2); ?></span></div>
                                </div>
                            </div>
                        </div>

                        <!-- NEW: Route Timeline Card -->
                        <?php if (!empty($route_timeline)) : ?>
                            <div class="panel-card">
                                <div class="card-header"><i class="fas fa-map-marker-alt me-2"></i>Complete Route Timeline</div>
                                <div class="card-body">
                                    <div class="route-timeline-section">
                                        <?php foreach ($route_timeline as $timeline_item) :
                                            $icon_class = '';
                                            if ($timeline_item['type'] == 'start') $icon_class = 'fas fa-play';
                                            elseif ($timeline_item['type'] == 'end') $icon_class = 'fas fa-flag-checkered';
                                            else $icon_class = 'fas fa-map-marker-alt';

                                            $boarding_tag = $timeline_item['is_boarding'] ? '<span class="pass-tag bg-info text-dark">Boarding Point</span>' : '';
                                            $dropping_tag = $timeline_item['is_dropping'] ? '<span class="pass-tag bg-primary">Dropping Point</span>' : '';
                                            $passenger_tags = '';
                                            if ($boarding_tag && $dropping_tag) $passenger_tags = $boarding_tag . ' ' . $dropping_tag;
                                            else if ($boarding_tag) $passenger_tags = $boarding_tag;
                                            else if ($dropping_tag) $passenger_tags = $dropping_tag;
                                        ?>
                                            <div class="route-timeline-item">
                                                <div class="timeline-icon <?php echo ($timeline_item['is_boarding'] || $timeline_item['is_dropping']) ? 'icon-passenger' : ''; ?>"
                                                    style="<?php echo ($timeline_item['is_boarding'] || $timeline_item['is_dropping']) ? 'background-color:#fca311;' : ''; // Orange for passenger related points 
                                                            ?>">
                                                    <i class="fas <?php echo $icon_class; ?>"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <strong class="stop-name"><?php echo htmlspecialchars($timeline_item['name']); ?></strong>
                                                    <div class="details-row">
                                                        <span class="time-info"><i class="fas fa-clock"></i><?php echo htmlspecialchars($timeline_item['time']); ?></span>
                                                        <?php if ($timeline_item['duration_from_prev'] > 0) : ?>
                                                            <span class="duration-pill">+<?php echo htmlspecialchars($timeline_item['duration_from_prev']); ?> mins</span>
                                                        <?php endif; ?>
                                                        <?php echo $passenger_tags; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <!-- End NEW: Route Timeline Card -->
                    </div>

                    <div class="col-lg-4">
                        <div class="panel-card control-panel">
                            <div class="card-header"><i class="fas fa-share-alt me-2"></i>Actions & Sharing</div>
                            <div class="card-body">
                                <div class="d-grid gap-2 mb-3">
                                    <a href="generate_ticket.php?booking_id=<?php echo htmlspecialchars($booking_id); ?>" target="_blank" class="btn btn-primary btn-lg"><i class="fas fa-ticket-alt me-2"></i>View & Download Ticket</a>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Share via WhatsApp</label>
                                    <div class="input-group">
                                        <input type="tel" class="form-control" id="whatsapp-number" value="+91<?php echo htmlspecialchars($booking['contact_mobile'] ?? ''); ?>" placeholder="Enter WhatsApp No.">
                                        <button class="btn btn-outline-success" id="send-whatsapp-btn" type="button"><i class="fab fa-whatsapp"></i> Send</button>
                                    </div>
                                </div>
                                <!-- <div class="mb-3">
                                    <label class="form-label small text-muted">Send via Email</label>
                                    <div class="input-group">
                                        <input type="email" class="form-control" id="email-address" value="<?php echo htmlspecialchars($booking['contact_email'] ?? ''); ?>" placeholder="Enter Email Address">
                                        <button class="btn btn-outline-info" id="send-email-btn" type="button"><i class="fas fa-envelope"></i> Send</button>
                                    </div>
                                </div> -->
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

                const ticketText = <?php echo json_encode(
                                        "*Bus Ticket Confirmed!*\n\n" .
                                            "Hello,\n" .
                                            "Your e-ticket for the journey from *" . ($booking['origin']) . "* to *" . ($booking['destination']) . "* is confirmed.\n\n" .
                                            "*Ticket No:* " . ($booking['ticket_no'] ?? 'N/A') . "\n" .
                                            "*Travel Date:* " . date('d M Y', strtotime($booking['travel_date'])) . "\n" .
                                            "*Departure Time:* " . date('h:i A', strtotime($booking['departure_time'] ?? '')) . " from " . ($booking['starting_point'] ?? 'N/A') . "\n\n" .
                                            "You can view and download your ticket from this secure link:\n" .
                                            $publicTicketUrl . "\n\n" .
                                            "We wish you a safe and pleasant journey!\n" .
                                            "- BPL Bus Service"
                                    ); ?>;

                const encodedText = encodeURIComponent(ticketText);
                const whatsappUrl = `https://wa.me/${number}?text=${encodedText}`;
                window.open(whatsappUrl, '_blank');
            });

            // --- Email Send Functionality ---
            $('#send-email-btn').on('click', function() {
                const email = $('#email-address').val().trim();
                if (!email) {
                    Swal.fire('Error', 'Please enter a valid email address.', 'error');
                    return;
                }
                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Sending...');
                $.ajax({
                    url: 'function/backend/email_ticket.php', // Assuming this backend action exists for sending
                    type: 'POST',
                    data: {
                        booking_id: <?php echo htmlspecialchars($booking_id); ?>,
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
                        Swal.fire('Error', 'A server error occurred.', 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-envelope"></i> Send');
                    }
                });
            });

            // --- Delete Ticket Functionality ---
            $(document).on('click', '.delete-ticket-btn', function() {
                const bookingId = $(this).data('booking-id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will permanently delete the booking and related data (archived). This cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'function/backend/booking_actions.php', // Assuming this backend action handles archiving & deleting
                            type: 'POST',
                            data: {
                                action: 'delete_booking',
                                booking_id: bookingId,
                                // You could add a reason here via Swal.fire if desired
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire('Deleted!', response.message, 'success').then(() => {
                                        // Redirect to view all bookings or a confirmation page
                                        window.location.href = 'view_bookings.php';
                                    });
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            },
                            error: function() {
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