<?php
// generate_ticket.php (ULTRA-CLEAN & SCALABLE DESIGN)
include_once('function/_db.php');
session_security_check();

$booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
if (!$booking_id) die("Invalid Booking ID.");

try {
    // --- Step 1: Query to fetch all booking details ---
    // This query no longer joins with the 'operators' table.
    $stmt = $_conn_db->prepare("
        SELECT 
            b.*, 
            r.route_id, r.route_name, r.starting_point, 
            sch.departure_time,
            bu.bus_name, bu.registration_number, bu.bus_type
        FROM bookings b
        JOIN routes r ON b.route_id = r.route_id
        JOIN buses bu ON b.bus_id = bu.bus_id
        LEFT JOIN route_schedules sch ON r.route_id = sch.route_id AND sch.operating_day = DATE_FORMAT(b.travel_date, '%a')
        WHERE b.booking_id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking_details = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$booking_details) die("Booking details not found.");

    // --- Step 2: Fetch assigned Conductor's name ---
    $conductor_name = 'Bus Staff'; // A sensible default if no conductor is assigned
    $staff_stmt = $_conn_db->prepare("
        SELECT s.name 
        FROM route_staff_assignments rsa
        JOIN staff s ON rsa.staff_id = s.staff_id
        WHERE rsa.route_id = ? AND rsa.role = 'Conductor'
        LIMIT 1
    ");
    $staff_stmt->execute([$booking_details['route_id']]);
    $conductor_info = $staff_stmt->fetch(PDO::FETCH_ASSOC);
    if ($conductor_info) {
        $conductor_name = $conductor_info['name'];
    }

    // --- Step 3: Fetch all passengers for this booking ---
    $passengersStmt = $_conn_db->prepare("SELECT passenger_name, seat_code, passenger_age, passenger_gender FROM passengers WHERE booking_id = ?");
    $passengersStmt->execute([$booking_id]);
    $passengers = $passengersStmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Step 4: Get/Create secure token for QR Code ---
    $tokenStmt = $_conn_db->prepare("SELECT token FROM ticket_access_tokens WHERE booking_id = ?");
    $tokenStmt->execute([$booking_id]);
    $token = $tokenStmt->fetchColumn();
    if (!$token) {
        $token = bin2hex(random_bytes(16));
        $insertStmt = $_conn_db->prepare("INSERT INTO ticket_access_tokens (booking_id, token) VALUES (?, ?)");
        $insertStmt->execute([$booking_id, $token]);
    }
    // Determine the base URL dynamically
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $script_path = dirname($_SERVER['PHP_SELF']);
    $projectBaseUrl = 'http://localhost/bpl-bus-ticket';
    $publicTicketUrl = $projectBaseUrl . '/ticket_public_view?token=' . $token;

    // --- Step 5: Calculate real departure/arrival timings ---
    $route_departure_datetime_str = $booking_details['travel_date'] . ' ' . ($booking_details['departure_time'] ?? '00:00');
    $route_departure_datetime = new DateTime($route_departure_datetime_str);
    
    // Get duration for the passenger's origin stop
    $origin_duration_stmt = $_conn_db->prepare("SELECT duration_from_start_minutes FROM route_stops WHERE route_id = ? AND stop_name = ? UNION SELECT 0 WHERE ? = ? LIMIT 1");
    $origin_duration_stmt->execute([$booking_details['route_id'], $booking_details['origin'], $booking_details['origin'], $booking_details['starting_point']]);
    $origin_minutes = (int)$origin_duration_stmt->fetchColumn();
    
    // Get duration for the passenger's destination stop
    $destination_duration_stmt = $_conn_db->prepare("SELECT duration_from_start_minutes FROM route_stops WHERE route_id = ? AND stop_name = ?");
    $destination_duration_stmt->execute([$booking_details['route_id'], $booking_details['destination']]);
    $destination_minutes = (int)$destination_duration_stmt->fetchColumn();

    $actual_departure_datetime = (clone $route_departure_datetime)->modify("+$origin_minutes minutes");
    $actual_arrival_datetime = (clone $route_departure_datetime)->modify("+$destination_minutes minutes");
    
} catch (PDOException $e) { 
    die("Database error: " . $e->getMessage()); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> 
    <title>Bus Ticket - <?php echo htmlspecialchars($booking_details['ticket_no'] ?? 'N/A'); ?></title>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

    <style>
        :root {
            --brand-blue: #0052CC; --text-dark: #172B4D; --text-light: #6B778C;
            --bg-main: #F4F5F7; --bg-card: #FFFFFF; --border-color: #DFE1E6;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-main); margin: 0; padding: 20px; display: flex; flex-direction: column; align-items: center; }
        .ticket-viewport { width: 100%; max-width: 840px; margin: 0 auto; }
        #ticket-wrapper { background: var(--bg-card); padding: 20px; border-radius: 16px; box-shadow: 0 10px 40px -10px rgba(0,82,204,0.2); transform-origin: top left; }
        .bus-ticket { width: 800px; height: auto; display: flex; border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; }
        .main-panel { width: 75%; padding: 25px; box-sizing: border-box; }
        .stub-panel { width: 25%; box-sizing: border-box; border-left: 2px dashed var(--border-color); text-align: center; display: flex; flex-direction: column; padding: 20px; justify-content: space-between; }
        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 1px solid var(--border-color); }
        .brand .operator { font-size: 20px; font-weight: 700; color: var(--text-dark); }
        .brand .bus-info { font-size: 13px; color: var(--text-light); }
        .ticket-no .value { font-size: 16px; font-weight: 600; color: var(--brand-blue); }
        .journey-details { display: flex; justify-content: space-between; align-items: center; margin: 20px 0; }
        .city .name { font-size: 24px; font-weight: 600; color: var(--text-dark); }
        .city .time { font-size: 16px; color: var(--text-light); }
        .path-icon i { font-size: 20px; color: var(--border-color); }
        .passengers-section { margin-top: 20px; }
        .passenger-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border-color); }
        .passenger-row:last-child { border-bottom: none; }
        .passenger-info .name { font-weight: 600; font-size: 15px; color: var(--text-dark); }
        .passenger-info .details { font-size: 12px; color: var(--text-light); }
        .passenger-seat .seat { font-size: 18px; font-weight: 700; color: var(--brand-blue); }
        .stub-panel .brand { font-size: 14px; font-weight: 600; color: var(--text-dark); }
        .stub-panel #qrcode { margin: 15px auto; }
        .download-button { margin-top: 20px; padding: 15px 30px; background-color: var(--brand-blue); color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="ticket-viewport">
        <div id="ticket-wrapper">
            <div class="bus-ticket">
                <div class="main-panel">
                    <div class="header">
                        <div class="brand">
                            <div class="operator"><?php echo htmlspecialchars($booking_details['bus_name']); ?> </div>
                            <div class="bus-info"> <?php echo htmlspecialchars($booking_details['registration_number']); ?> • <?php echo htmlspecialchars($booking_details['bus_type']); ?></div>
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
                                <div class="details">Age: <?php echo htmlspecialchars($p['passenger_age']); ?>, Gender: <?php echo htmlspecialchars($p['passenger_gender']); ?></div>
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
                    <div class="brand">BPL Bus Service</div>
                    <div id="qrcode"></div>
                    <div style="text-align:center;">
                        <div class="label" style="font-size:11px;">Date</div>
                        <div class="value" style="font-size:14px;"><?php echo $actual_departure_datetime->format('D, d M Y'); ?></div>
                    </div>
                    <div style="text-align:center;">
                        <div class="label" style="font-size:11px;">Total Fare</div>
                        <div class="value" style="font-size:18px;">₹<?php echo number_format($booking_details['total_fare'], 2); ?></div>
                    </div>
                    <div style="font-size:9px; color:var(--text-light); margin-top:10px;">Scan QR for details. Have a safe journey!</div>
                </div>
            </div>
        </div>
    </div>

    <button id="download-btn" class="download-button">Download Ticket PDF</button>

    <script>
        // Scaling script
        function scaleTicket() {
            const viewport = document.querySelector('.ticket-viewport');
            const ticket = document.querySelector('#ticket-wrapper');
            const ticketWidth = 840;
            const scale = Math.min(viewport.offsetWidth / ticketWidth, 1);
            ticket.style.transform = `scale(${scale})`;
            viewport.style.height = `${ticket.offsetHeight * scale}px`;
        }
        window.addEventListener('load', scaleTicket);
        window.addEventListener('resize', scaleTicket);

        // QR Code generation script
        (function() {
            const qrData = "<?php echo $publicTicketUrl; ?>";
            const qr = qrcode(0, 'M');
            qr.addData(qrData);
            qr.make();
            document.getElementById('qrcode').innerHTML = qr.createSvgTag({ cellSize: 4, margin: 0 });
            document.querySelector('#qrcode svg').setAttribute('style', 'width:120px; height:120px; border-radius:8px;');
        })();

        // PDF download script
        window.jsPDF = window.jspdf.jsPDF;
        document.getElementById('download-btn').addEventListener('click', function () {
            const btn = this;
            btn.textContent = 'Generating...';
            btn.disabled = true;
            const ticketWrapper = document.getElementById('ticket-wrapper');
            const ticketNo = "<?php echo htmlspecialchars($booking_details['ticket_no'] ?? 'ticket'); ?>";
            
            ticketWrapper.style.transform = 'scale(1)';

            html2canvas(ticketWrapper, { scale: 3, useCORS: true, backgroundColor: '#FFFFFF' }).then(canvas => {
                const imgData = canvas.toDataURL('image/jpeg', 0.95);
                const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
                const margin = 15;
                const pdfWidth = pdf.internal.pageSize.getWidth() - (margin * 2);
                const pdfHeight = pdf.internal.pageSize.getHeight() - (margin * 2);
                const canvasWidth = canvas.width;
                const canvasHeight = canvas.height;
                const ratio = canvasWidth / canvasHeight;

                let imgWidth = pdfWidth;
                let imgHeight = imgWidth / ratio;

                if (imgHeight > pdfHeight) {
                    imgHeight = pdfHeight;
                    imgWidth = imgHeight * ratio;
                }

                const x = (pdf.internal.pageSize.getWidth() - imgWidth) / 2;
                const y = margin;

                pdf.addImage(imgData, 'JPEG', x, y, imgWidth, imgHeight);
                pdf.save(`Bus-Ticket-${ticketNo}.pdf`);
                
                scaleTicket();

                setTimeout(() => { btn.textContent = 'Download Ticket PDF'; btn.disabled = false; }, 1000);
            });
        });
    </script>
</body>
</html>