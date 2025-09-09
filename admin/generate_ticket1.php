<?php
// generate_ticket_html.php (ULTIMATE PREMIUM DESIGN)
include_once('function/_db.php');
session_security_check();

$booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
if (!$booking_id) die("Invalid Booking ID.");

try {
    // --- Step 1: The most comprehensive query to fetch EVERYTHING ---
    $stmt = $_conn_db->prepare("
        SELECT 
            b.booking_id, b.ticket_no, b.travel_date, b.total_fare, b.origin, b.destination,
            b.payment_status, b.booking_status,
            r.route_id, r.route_name, r.starting_point,
            sch.departure_time,
            bu.bus_name, bu.registration_number, bu.bus_type,
            op.operator_name, op.contact_phone AS operator_phone
        FROM bookings b
        JOIN routes r ON b.route_id = r.route_id
        JOIN buses bu ON b.bus_id = bu.bus_id
        JOIN operators op ON bu.operator_id = op.operator_id
        LEFT JOIN route_schedules sch ON r.route_id = sch.route_id AND sch.operating_day = DATE_FORMAT(b.travel_date, '%a')
        WHERE b.booking_id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking_details = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$booking_details) die("Booking details not found.");

    $passengersStmt = $_conn_db->prepare("SELECT passenger_name, seat_code, passenger_age, passenger_gender FROM passengers WHERE booking_id = ?");
    $passengersStmt->execute([$booking_id]);
    $passengers = $passengersStmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Step 2: Get/Create secure token for QR Code ---
    $tokenStmt = $_conn_db->prepare("SELECT token FROM ticket_access_tokens WHERE booking_id = ?");
    $tokenStmt->execute([$booking_id]);
    $token = $tokenStmt->fetchColumn();
    if (!$token) {
        $token = bin2hex(random_bytes(16));
        $insertStmt = $_conn_db->prepare("INSERT INTO ticket_access_tokens (booking_id, token) VALUES (?, ?)");
        $insertStmt->execute([$booking_id, $token]);
    }
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $publicTicketUrl = $baseUrl . $scriptPath . '/view_public_ticket.php?token=' . $token;
    
} catch (PDOException $e) { die("Database error: " . $e->getMessage()); }

// Calculate reporting time (30 mins before departure)
$departure_time = strtotime($booking_details['departure_time'] ?? '00:00');
$reporting_time = date('h:i A', $departure_time - 30 * 60);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Boarding Pass - <?php echo htmlspecialchars($booking_details['ticket_no'] ?? 'N/A'); ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

    <style>
        :root {
            --royal-blue: #0A2240;
            --gold: #D4AF37;
            --text-dark: #212529;
            --text-light: #8A95A5;
            --bg-main: #E9EFF5;
            --bg-card: #FFFFFF;
            --border-color: #DEE2E6;
        }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-main); display: flex; justify-content: center; align-items: center; flex-direction: column; padding: 40px 20px; }
        #ticket-wrapper { padding: 25px; background: var(--bg-card); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.2); border-radius: 20px; }
        .boarding-pass {
            width: 1000px;
            height: 400px;
            background: var(--bg-card);
            background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuLTAiIHg9IjAiIHk9IjAiIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZmlsbD0iI2YwZjRmOCIgZD0iTSAwIDIwIEwgMjAgMCBMIDIwIDEgTCAxIDIwIEwgMCAyMCBNIDIwIDIwIEwgMCAwIEwgMSAwIEwgMjAgMTkgTCAyMCAyMCBMIDIwIDIwIFoiLz48L3BhdHRlcm4+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNwYXR0ZXJuLTApIi8+PC9zdmc+');
            border-radius: 18px;
            display: flex;
        }
        .main-panel { width: 72%; padding: 25px; box-sizing: border-box; display: flex; flex-direction: column; }
        .stub-panel { width: 28%; background-color: #F8F9FA; box-sizing: border-box; border-left: 3px dashed var(--border-color); text-align: center; display: flex; flex-direction: column; padding: 20px; justify-content: center; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; }
        .brand .logo { font-size: 28px; color: var(--royal-blue); }
        .brand .operator { font-size: 22px; font-weight: 700; color: var(--royal-blue); margin-left: 12px; }
        .info-tag { text-align: right; }
        .info-tag .label { font-size: 11px; color: var(--text-light); text-transform: uppercase; }
        .info-tag .value { font-size: 16px; font-weight: 600; color: var(--royal-blue); }
        .journey-path { display: flex; align-items: center; margin: 25px 0; }
        .city .name { font-size: 36px; font-weight: 700; color: var(--text-dark); line-height: 1.1; }
        .path-icon { padding: 0 30px; text-align: center; }
        .path-icon i { font-size: 22px; color: var(--text-light); }
        .path-icon .bus-name { font-size: 11px; color: var(--text-light); }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; padding-top: 15px; border-top: 1px solid var(--border-color); }
        .info-item .label { font-size: 12px; color: var(--text-light); }
        .info-item .value { font-size: 15px; font-weight: 600; color: var(--text-dark); }
        .passengers-section { margin-top: auto; padding-top: 15px; }
        .passengers-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .passengers-table th, .passengers-table td { text-align: left; padding: 6px 0; }
        .passengers-table th { color: var(--text-light); font-weight: 500; border-bottom: 1px solid var(--border-color); font-size: 11px; }
        .stub-panel .brand { font-size: 18px; font-weight: 700; color: var(--royal-blue); margin-bottom: 10px; }
        .stub-panel .qr-code { width: 120px; height: 120px; margin: 15px auto; border: 5px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .stub-panel .barcode { width: 100%; height: 50px; }
        .download-button { margin-top: 30px; padding: 15px 30px; background-image: linear-gradient(to right, #0A2240 0%, #0d6efd 100%); color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 10px 20px -5px rgba(10, 61, 145, 0.4); }
        .download-button:hover { transform: translateY(-3px); box-shadow: 0 15px 25px -8px rgba(10, 61, 145, 0.5); }
    </style>
</head>
<body>
    <div id="ticket-wrapper">
        <div class="boarding-pass">
            <div class="main-panel">
                <div class="header">
                    <div class="brand" style="display: flex; align-items: center;">
                        <i class="fas fa-bus-alt logo"></i>
                        <span class="operator"><?php echo htmlspecialchars($booking_details['operator_name']); ?></span>
                    </div>
                    <div class="info-tag">
                        <div class="label">Boarding Pass</div>
                        <div class="value"><?php echo htmlspecialchars($booking_details['ticket_no']); ?></div>
                    </div>
                </div>
                <div class="journey-path">
                    <div class="city">
                        <div class="name"><?php echo htmlspecialchars($booking_details['origin']); ?></div>
                    </div>
                    <div class="path-icon">
                        <i class="fas fa-long-arrow-alt-right"></i>
                        <div class="bus-name"><?php echo htmlspecialchars($booking_details['bus_name']); ?></div>
                    </div>
                    <div class="city" style="text-align: right;">
                        <div class="name"><?php echo htmlspecialchars($booking_details['destination']); ?></div>
                    </div>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="label">Date</div>
                        <div class="value"><?php echo date('D, d M Y', strtotime($booking_details['travel_date'])); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Departure</div>
                        <div class="value"><?php echo date('h:i A', strtotime($booking_details['departure_time'] ?? '')); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Reporting Time</div>
                        <div class="value" style="color:#dc3545; font-weight:700;"><?php echo $reporting_time; ?></div>
                    </div>
                </div>
                <div class="passengers-section">
                    <table class="passengers-table">
                        <thead><tr><th>PASSENGER NAME</th><th>SEAT</th><th>AGE</th><th>GENDER</th></tr></thead>
                        <tbody>
                            <?php foreach ($passengers as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['passenger_name']); ?></td>
                                <td><b><?php echo htmlspecialchars($p['seat_code']); ?></b></td>
                                <td><?php echo htmlspecialchars($p['passenger_age']); ?></td>
                                <td><?php echo htmlspecialchars($p['passenger_gender']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="stub-panel">
                <div class="brand">BPL Premium Express</div>
                <div class="info-tag" style="text-align:center;">
                    <div class="label">From</div>
                    <div class="value"><?php echo htmlspecialchars($booking_details['origin']); ?></div>
                </div>
                <div style="font-size: 20px; color: var(--text-light); margin: 5px 0;">&#8595;</div>
                <div class="info-tag" style="text-align:center;">
                    <div class="label">To</div>
                    <div class="value"><?php echo htmlspecialchars($booking_details['destination']); ?></div>
                </div>
                <img class="qr-code" src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?php echo urlencode($publicTicketUrl); ?>&bgcolor=F8F9FA" alt="QR Code">
                <img class="barcode" src="https://barcode.tec-it.com/barcode.ashx?data=<?php echo htmlspecialchars($booking_details['ticket_no']); ?>&code=Code128&dpi=96&imagetype=gif&hidehrt=true&bgcolor=F8F9FA" alt="Barcode">
            </div>
        </div>
    </div>

    <button id="download-btn" class="download-button">Download PDF</button>

    <script>
        window.jsPDF = window.jspdf.jsPDF;
        document.getElementById('download-btn').addEventListener('click', function () {
            const btn = this;
            btn.textContent = 'Generating...';
            btn.disabled = true;

            const ticketWrapper = document.getElementById('ticket-wrapper');
            const ticketNo = "<?php echo htmlspecialchars($booking_details['ticket_no'] ?? 'ticket'); ?>";

            html2canvas(ticketWrapper, {
                scale: 3,
                useCORS: true,
                backgroundColor: null,
                logging: false
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/jpeg', 0.95);
                const pdf = new jsPDF({
                    orientation: 'landscape',
                    unit: 'px',
                    format: [canvas.width, canvas.height]
                });
                pdf.addImage(imgData, 'JPEG', 0, 0, canvas.width, canvas.height);
                pdf.save(`Boarding-Pass-${ticketNo}.pdf`);

                setTimeout(() => {
                    btn.textContent = 'Download PDF';
                    btn.disabled = false;
                }, 1000);
            });
        });
    </script>
</body>
</html>