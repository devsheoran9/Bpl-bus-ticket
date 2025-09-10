<?php
// Always start the header - it should include the session and db_connect.php
// We assume db_connect.php also starts the session. If not, add session_start();
include 'db_connect.php';

// --- 1. Security & Input Validation ---

// First, check if a user is logged in at all.
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php"); // Make sure you have a login.php page
    exit();
}
$logged_in_user_id = $_SESSION['user_id'];

// Get the booking_id from the URL and validate it's a number.
$booking_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$booking_id) {
    die("Error: A valid Booking ID is required.");
}

// Initialize variables
$booking_details = null;
$passengers = [];

try {
    // --- 2. Database Fetching ---

    // Step A: Fetch the main booking details and route info.
    $stmt = $conn->prepare("
        SELECT 
            b.booking_id, b.ticket_no, b.travel_date, b.origin, b.destination, b.total_fare,
            b.route_id, b.bus_id,
            bu.bus_name, bu.registration_number,
            rs.departure_time,
            r.starting_point,
            r.ending_point
        FROM bookings b
        JOIN buses bu ON b.bus_id = bu.bus_id
        JOIN routes r ON b.route_id = r.route_id
        LEFT JOIN route_schedules rs ON b.route_id = rs.route_id AND rs.operating_day = DATE_FORMAT(b.travel_date, '%a')
        WHERE b.booking_id = ? AND b.user_id = ?
    ");
    if ($stmt === false) throw new Exception("SQL Prepare Error: " . $conn->error);

    $stmt->bind_param("ii", $booking_id, $logged_in_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking_details = $result->fetch_assoc();
    $stmt->close();

    // If no booking is found for this user, deny access.
    if (!$booking_details) {
        die("Booking not found or you do not have permission to view this ticket.");
    }

    // Step B: Fetch all passengers for this booking.
    $passengersStmt = $conn->prepare("SELECT passenger_name, seat_code, passenger_age, passenger_gender FROM passengers WHERE booking_id = ?");
    $passengersStmt->bind_param("i", $booking_id);
    $passengersStmt->execute();
    $passengers = $passengersStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $passengersStmt->close();

    // --- 3. Calculate Real Boarding/Dropping Times ---

    $origin_minutes = 0;
    if ($booking_details['origin'] != $booking_details['starting_point']) {
        $stmt_origin = $conn->prepare("SELECT duration_from_start_minutes FROM route_stops WHERE route_id = ? AND stop_name = ?");
        $stmt_origin->bind_param("is", $booking_details['route_id'], $booking_details['origin']);
        $stmt_origin->execute();
        $origin_result = $stmt_origin->get_result()->fetch_assoc();
        $stmt_origin->close();
        if ($origin_result) {
            $origin_minutes = (int)$origin_result['duration_from_start_minutes'];
        }
    }

    $destination_minutes = 0;
    if ($booking_details['destination'] == $booking_details['ending_point']) {
        $stmt_dest = $conn->prepare("SELECT MAX(duration_from_start_minutes) as max_duration FROM route_stops WHERE route_id = ?");
        $stmt_dest->bind_param("i", $booking_details['route_id']);
    } else {
        $stmt_dest = $conn->prepare("SELECT duration_from_start_minutes FROM route_stops WHERE route_id = ? AND stop_name = ?");
        $stmt_dest->bind_param("is", $booking_details['route_id'], $booking_details['destination']);
    }
    $stmt_dest->execute();
    $destination_result = $stmt_dest->get_result()->fetch_assoc();
    $stmt_dest->close();
    if ($destination_result) {
        $destination_minutes = isset($destination_result['max_duration'])
            ? (int)$destination_result['max_duration']
            : (int)$destination_result['duration_from_start_minutes'];
    }

    $route_departure_datetime_str = $booking_details['travel_date'] . ' ' . ($booking_details['departure_time'] ?? '00:00');
    $route_departure_datetime = new DateTime($route_departure_datetime_str);

    $actual_departure_datetime = (clone $route_departure_datetime)->modify("+$origin_minutes minutes");
    $actual_arrival_datetime = (clone $route_departure_datetime)->modify("+$destination_minutes minutes");
} catch (Exception $e) {
    die("An error occurred: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bus Ticket - <?php echo htmlspecialchars($booking_details['ticket_no']); ?></title>
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

    <style>
        :root {
            --brand-blue: #0052CC;
            --text-dark: #172B4D;
            --text-light: #6B778C;
            --bg-main: #F4F5F7;
            --bg-card: #FFFFFF;
            --border-color: #DFE1E6;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-main);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
            /* FIX: Prevent the main body from having a horizontal scrollbar */
            overflow-x: hidden;
        }

        /* The main outer wrapper that centers the content */
        #ticket-wrapper {
            background: var(--bg-card);
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 40px -10px rgba(0, 82, 204, 0.2);
            width: 100%;
            max-width: 900px;
        }

        /* FIX: New container to handle horizontal scrolling on small screens */
        #ticket-scroll-container {
            width: 100%;
            overflow-x: auto; /* This creates the horizontal scrollbar when needed */
            -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
        }

        /* FIX: The ticket itself now has a fixed MINIMUM width. */
        /* It will never get narrower than this, forcing the scrollbar to appear. */
        .bus-ticket {
            min-width: 800px;
            height: auto;
            display: flex; /* This will always be flex-direction: row */
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
        }

        .main-panel {
            width: 75%;
            padding: 25px;
            box-sizing: border-box;
        }

        .stub-panel {
            width: 25%;
            box-sizing: border-box;
            border-left: 2px dashed var(--border-color);
            text-align: center;
            display: flex;
            flex-direction: column;
            padding: 20px;
            justify-content: space-between;
            background-color: #fafbfc;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .brand .operator { font-size: 20px; font-weight: 700; color: var(--text-dark); }
        .brand .bus-info { font-size: 13px; color: var(--text-light); }
        .ticket-no .value { font-size: 16px; font-weight: 600; color: var(--brand-blue); }

        .journey-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
        }

        .city .name { font-size: 18px; font-weight: 600; color: var(--text-dark); }
        .city .time { font-size: 16px; color: var(--text-light); }
        .path-icon i { font-size: 20px; color: var(--border-color); margin: 0 20px; }

        .passengers-section { margin-top: 20px; }
        .passenger-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .passenger-row:last-child { border-bottom: none; }
        .passenger-info .name { font-weight: 600; font-size: 15px; color: var(--text-dark); }
        .passenger-info .details { font-size: 12px; color: var(--text-light); }
        .passenger-seat .seat { font-size: 18px; font-weight: 700; color: var(--brand-blue); }

        .stub-panel .brand { font-size: 14px; font-weight: 600; color: var(--text-dark); }
        .stub-panel .qr-code { width: 120px; height: 120px; margin: 15px auto; border-radius: 8px; }

        .download-button {
            margin-top: 30px;
            padding: 15px 30px;
            background-color: var(--brand-blue);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .download-button:hover { background-color: #0041a3; }
        .download-button:disabled { background-color: #a5adba; cursor: not-allowed; }

        /* FIX: The media query that caused stacking has been REMOVED. */
        /* The layout will now be consistent across all screen sizes, */
        /* relying on the scroll container for small screens. */
    </style>
</head>

<body>
    <!-- FIX: The ticket is now wrapped in the scroll container -->
    <div id="ticket-wrapper">
        <div id="ticket-scroll-container">
            <div class="bus-ticket">
                <div class="main-panel">
                    <div class="header">
                        <div class="brand">
                            <div class="operator">Your Bus Operator</div>
                            <div class="bus-info"><?php echo htmlspecialchars($booking_details['bus_name']); ?> • <?php echo htmlspecialchars($booking_details['registration_number']); ?></div>
                        </div>
                        <div class="ticket-no">
                            <div class="label" style="font-size:11px; color:var(--text-light);">Ticket No.</div>
                            <div class="value"><?php echo htmlspecialchars($booking_details['ticket_no']); ?></div>
                        </div>
                    </div>

                    <div class="journey-details">
                        <div class="city">
                            <div class="name"><?php echo htmlspecialchars($booking_details['origin']); ?></div>
                            <div class="time"><?php echo $actual_departure_datetime->format('h:i A'); ?></div>
                        </div>
                        <div class="path-icon"><i class="fas fa-long-arrow-alt-right"></i></div>
                        <div class="city" style="text-align:right;">
                            <div class="name"><?php echo htmlspecialchars($booking_details['destination']); ?></div>
                            <div class="time">Est. <?php echo $actual_arrival_datetime->format('h:i A'); ?></div>
                        </div>
                    </div>

                    <div class="passengers-section">
                        <?php foreach ($passengers as $p): ?>
                            <div class="passenger-row">
                                <div class="passenger-info">
                                    <div class="name"><?php echo htmlspecialchars($p['passenger_name']); ?></div>
                                    <div class="details">Age: <?php echo htmlspecialchars($p['passenger_age']); ?>, Gender: <?php echo htmlspecialchars(ucfirst(strtolower($p['passenger_gender']))); ?></div>
                                </div>
                                <div class="passenger-seat">
                                    <div class="label" style="font-size:11px; color:var(--text-light); text-align:right;">Seat</div>
                                    <div class="seat"><?php echo htmlspecialchars($p['seat_code']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="stub-panel">
                    <div class="brand">YourBus Ticket</div>
                    <img class="qr-code" src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?php echo urlencode('Ticket No: ' . $booking_details['ticket_no']); ?>" alt="QR Code">
                    <div style="text-align:center;">
                        <div class="label" style="font-size:11px;">Date</div>
                        <div class="value" style="font-size:14px; font-weight: 600;"><?php echo $actual_departure_datetime->format('D, d M Y'); ?></div>
                    </div>
                    <div style="text-align:center;">
                        <div class="label" style="font-size:11px;">Ticket Price:
                        <span class="value" style="font-size:14px; font-weight: 700;">₹<?php echo number_format($booking_details['total_fare'], 2); ?></span></div>
                    </div>
                    <div style="font-size:9px; color:var(--text-light); margin-top:10px;">Scan QR for details. Have a safe journey!</div>
                </div>
            </div>
        </div>
    </div>

    <button id="download-btn" class="download-button">Download Ticket PDF</button>

    <script>
        window.jsPDF = window.jspdf.jsPDF;
        document.getElementById('download-btn').addEventListener('click', function() {
            const btn = this;
            btn.textContent = 'Generating...';
            btn.disabled = true;

            // IMPORTANT: We now target the '.bus-ticket' itself, not the outer wrapper.
            // This ensures we only capture the ticket content, which always has a fixed min-width.
            const ticketElement = document.querySelector('.bus-ticket');
            const ticketNo = "<?php echo htmlspecialchars($booking_details['ticket_no'] ?? 'ticket'); ?>";

            html2canvas(ticketElement, {
                scale: 3, // High scale for better image quality
                useCORS: true,
                // Since the ticket might be scrolled, we need to capture its full width
                width: ticketElement.scrollWidth,
                height: ticketElement.scrollHeight
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/jpeg', 0.95);

                // --- A4 Sizing and Scaling Logic ---
                // Create a new PDF in A4 landscape orientation (297mm wide x 210mm high)
                const pdf = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: 'a4'
                });

                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const canvasAspectRatio = canvas.width / canvas.height;

                // Calculate the optimal image dimensions to fit within the A4 page
                // while maintaining the aspect ratio.
                let imgWidth = pdfWidth - 20; // A4 width with 10mm margin on each side
                let imgHeight = imgWidth / canvasAspectRatio;

                // If the calculated height is too big for the page, scale by height instead
                if (imgHeight > pdfHeight - 20) { // A4 height with 10mm margin
                    imgHeight = pdfHeight - 20;
                    imgWidth = imgHeight * canvasAspectRatio;
                }

                // Center the image on the A4 page
                const x = (pdfWidth - imgWidth) / 2;
                const y = (pdfHeight - imgHeight) / 2;

                // Add the captured image to the PDF
                pdf.addImage(imgData, 'JPEG', x, y, imgWidth, imgHeight);
                pdf.save(`Bus-Ticket-${ticketNo}.pdf`);

                setTimeout(() => {
                    btn.textContent = 'Download Ticket PDF';
                    btn.disabled = false;
                }, 1000);
            });
        });
    </script>
</body>

</html>