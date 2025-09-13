<?php
// Use your actual PDO connection file.
require 'admin/function/_db.php';

// --- 1. VALIDATE TOKEN AND FETCH ALL DATA ---

$access_token = trim($_GET['token'] ?? '');
if (empty($access_token)) {
    die("Error: A valid ticket token is required.");
}

try {
    // === FIX: REMOVED JOIN on 'operators' table ===
    $sql = "SELECT 
                b.*, 
                bu.bus_name, bu.registration_number, bu.bus_type,
                r.starting_point, r.ending_point,
                rs.departure_time
            FROM ticket_access_tokens tat
            JOIN bookings b ON tat.booking_id = b.booking_id
            JOIN buses bu ON b.bus_id = bu.bus_id
            JOIN routes r ON b.route_id = r.route_id
            LEFT JOIN route_schedules rs ON b.route_id = rs.route_id AND rs.operating_day = DATE_FORMAT(b.travel_date, '%a')
            WHERE tat.token = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$access_token]);
    $booking_details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking_details) {
        die("Ticket not found or the link has expired.");
    }

    $passengersStmt = $pdo->prepare("SELECT passenger_name, seat_code, passenger_age, passenger_gender FROM passengers WHERE booking_id = ?");
    $passengersStmt->execute([$booking_details['booking_id']]);
    $passengers = $passengersStmt->fetchAll(PDO::FETCH_ASSOC);

    // --- 2. CALCULATE REAL BOARDING/DROPPING TIMES ---

    $route_departure_datetime_str = $booking_details['travel_date'] . ' ' . ($booking_details['departure_time'] ?? '00:00');
    $route_departure_datetime = new DateTime($route_departure_datetime_str);

    $stmt_origin = $pdo->prepare("SELECT duration_from_start_minutes FROM route_stops WHERE route_id = ? AND stop_name = ?");
    $stmt_origin->execute([$booking_details['route_id'], $booking_details['origin']]);
    $origin_minutes = (int)$stmt_origin->fetchColumn();

    $stmt_dest = $pdo->prepare("SELECT duration_from_start_minutes FROM route_stops WHERE route_id = ? AND stop_name = ?");
    $stmt_dest->execute([$booking_details['route_id'], $booking_details['destination']]);
    $destination_minutes = (int)$stmt_dest->fetchColumn();

    $actual_departure_datetime = (clone $route_departure_datetime)->modify("+$origin_minutes minutes");
    $actual_arrival_datetime = (clone $route_departure_datetime)->modify("+$destination_minutes minutes");
} catch (PDOException $e) {
    error_log("Public Ticket View Error: " . $e->getMessage());
    die("An unexpected error occurred. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bus Ticket - <?php echo htmlspecialchars($booking_details['ticket_no']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

    <!-- The CSS is identical to the previous version -->
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
        }

        #ticket-wrapper {
            background: var(--bg-card);
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 40px -10px rgba(0, 82, 204, 0.2);
            width: 100%;
            max-width: 900px;
        }

        #ticket-scroll-container {
            width: 100%;
            overflow-x: auto;
        }

        .bus-ticket {
            min-width: 800px;
            height: auto;
            display: flex;
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

        .brand .operator {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .brand .bus-info {
            font-size: 13px;
            color: var(--text-light);
        }

        .ticket-no .value {
            font-size: 16px;
            font-weight: 600;
            color: var(--brand-blue);
        }

        .journey-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }

        .city .name {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .city .time {
            font-size: 16px;
            color: var(--text-light);
        }

        .path-icon i {
            font-size: 20px;
            color: var(--border-color);
        }

        .passengers-section {
            margin-top: 20px;
        }

        .passenger-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .passenger-row:last-child {
            border-bottom: none;
        }

        .passenger-info .name {
            font-weight: 600;
            font-size: 15px;
            color: var(--text-dark);
        }

        .passenger-info .details {
            font-size: 12px;
            color: var(--text-light);
        }

        .passenger-seat .seat {
            font-size: 18px;
            font-weight: 700;
            color: var(--brand-blue);
        }

        .stub-panel .brand {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .stub-panel #qrcode {
            margin: 15px auto;
        }

        .download-button {
            margin-top: 20px;
            padding: 12px 25px;
            background-color: var(--brand-blue);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .download-button:disabled {
            background-color: #a5adba;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <div id="ticket-wrapper">
        <div id="ticket-scroll-container">
            <div class="bus-ticket">
                <div class="main-panel">
                    <div class="header">
                        <div class="brand">
                            <!-- === FIX: Use bus_name instead of operator_name === -->
                            <div class="operator"><?php echo htmlspecialchars($booking_details['bus_name']); ?></div>
                            <div class="bus-info"><?php echo htmlspecialchars($booking_details['registration_number']); ?> • <?php echo htmlspecialchars($booking_details['bus_type']); ?></div>
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
                    <!-- === FIX: Use bus_name instead of operator_name === -->
                    <div class="brand"><?php echo htmlspecialchars($booking_details['bus_name']); ?></div>
                    <div id="qrcode"></div>
                    <div style="text-align:center;">
                        <div class="label" style="font-size:11px;">Date</div>
                        <div class="value" style="font-size:14px; font-weight: 600;"><?php echo $actual_departure_datetime->format('D, d M Y'); ?></div>
                    </div>
                    <div style="text-align:center;">
                        <div class="label" style="font-size:11px;">Total Fare</div>
                        <div class="value" style="font-size:18px; font-weight: 700;">₹<?php echo number_format($booking_details['total_fare'], 2); ?></div>
                    </div>
                    <div style="font-size:9px; color:var(--text-light); margin-top:10px;">Scan QR for ticket details.</div>
                </div>
            </div>
        </div>
    </div>
    <button id="download-btn" class="download-button"><i class="fas fa-download"></i> Download Ticket PDF</button>

    <script>
        // The JavaScript for QR generation and PDF download is identical
        (function() {
            const qrData = window.location.href; // QR code will simply link to the current page
            const qr = qrcode(0, 'M');
            qr.addData(qrData);
            qr.make();
            const qrCodeContainer = document.getElementById('qrcode');
            qrCodeContainer.innerHTML = qr.createSvgTag({
                cellSize: 4,
                margin: 0
            });
            const svgElement = qrCodeContainer.querySelector('svg');
            if (svgElement) {
                svgElement.setAttribute('style', 'width:120px; height:120px; border-radius:8px;');
            }
        })();

        (function() {
            window.jsPDF = window.jspdf.jsPDF;
            const downloadButton = document.getElementById('download-btn');
            const ticketElement = document.querySelector('.bus-ticket');
            const ticketNo = "<?php echo htmlspecialchars($booking_details['ticket_no'] ?? 'ticket'); ?>";

            function downloadTicket() {
                if (!ticketElement || downloadButton.disabled) return;
                downloadButton.textContent = 'Generating...';
                downloadButton.disabled = true;
                html2canvas(ticketElement, {
                        scale: 3,
                        useCORS: true,
                        width: ticketElement.scrollWidth,
                        height: ticketElement.scrollHeight
                    })
                    .then(canvas => {
                        const imgData = canvas.toDataURL('image/jpeg', 0.95);
                        const pdf = new jsPDF({
                            orientation: 'landscape',
                            unit: 'mm',
                            format: 'a4'
                        });
                        const pdfWidth = pdf.internal.pageSize.getWidth();
                        const pdfHeight = pdf.internal.pageSize.getHeight();
                        const canvasAspectRatio = canvas.width / canvas.height;
                        let imgWidth = pdfWidth - 20;
                        let imgHeight = imgWidth / canvasAspectRatio;
                        if (imgHeight > pdfHeight - 20) {
                            imgHeight = pdfHeight - 20;
                            imgWidth = imgHeight * canvasAspectRatio;
                        }
                        const x = (pdfWidth - imgWidth) / 2;
                        const y = (pdfHeight - imgHeight) / 2;
                        pdf.addImage(imgData, 'JPEG', x, y, imgWidth, imgHeight);
                        pdf.save(`Bus-Ticket-${ticketNo}.pdf`);
                        setTimeout(() => {
                            downloadButton.innerHTML = '<i class="fas fa-download"></i> Download Ticket PDF';
                            downloadButton.disabled = false;
                        }, 1000);
                    });
            }
            downloadButton.addEventListener('click', downloadTicket);
            document.addEventListener('DOMContentLoaded', function() {
                if (window.innerWidth <= 768) {
                    setTimeout(downloadTicket, 500);
                }
            });
        })();
    </script>
</body>

</html>