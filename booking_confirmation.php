<?php
// We assume db_connect.php also starts the session.
include 'db_connect.php';

// --- 1. Security & Input Validation ---

// First, check if a user is logged in at all.
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}
$logged_in_user_id = $_SESSION['user_id'];

// Get the booking_id from the URL and validate it's a number.
$booking_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$booking_id) {
    die("Error: A valid Booking ID is required.");
}

// Initialize variables to hold fetched data
$booking_details = null;
$passengers = [];

try {
    // --- 2. Database Fetching (Same logic as view_ticket.php) ---

    // Step A: Fetch the main booking details and route info.
    // This query ensures the booking belongs to the logged-in user.
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
        die("Booking not found or you do not have permission to view this confirmation.");
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
    <title>Booking Confirmation - <?php echo htmlspecialchars($booking_details['ticket_no']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Using Bootstrap for consistent styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f7f6;
        }

        .confirmation-card {
            max-width: 800px;
            margin: 40px auto;
            border-radius: 10px;
        }

        .card-header {
            background-color: #28a745;
            color: white;
            font-size: 1.25rem;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .details-grid dt {
            font-weight: normal;
            color: #6c757d;
        }

        .details-grid dd {
            font-weight: 600;
            margin-bottom: 0;
        }

        @media (max-width: 576px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card shadow-lg confirmation-card">
            <div class="card-body p-lg-5">

                <div class="text-center mb-4">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h1 class="h3">Booking Confirmed!</h1>
                    <p class="text-muted">Congratulations! Your ticket has been successfully confirmed. Have a safe journey!</p>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-light text-dark">
                        <h5 class="mb-0">Journey Summary</h5>
                    </div>
                    <div class="card-body">
                        <dl class="details-grid">
                            <div>
                                <dt>Ticket No. (PNR)</dt>
                                <dd><?php echo htmlspecialchars($booking_details['ticket_no']); ?></dd>
                            </div>
                            <div>
                                <dt>Travel Date</dt>
                                <dd><?php echo $actual_departure_datetime->format('D, d M Y'); ?></dd>
                            </div>
                            <div>
                                <dt>Bus Details</dt>
                                <dd><?php echo htmlspecialchars($booking_details['bus_name']); ?> (<?php echo htmlspecialchars($booking_details['registration_number']); ?>)</dd>
                            </div>
                            <div>
                                <dt>Total Fare Paid</dt>
                                <dd><strong>₹<?php echo number_format($booking_details['total_fare'], 2); ?></strong></dd>
                            </div>
                        </dl>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-start">
                                <dt>Boarding From</dt>
                                <dd class="fs-5"><?php echo htmlspecialchars($booking_details['origin']); ?></dd>
                                <small class="text-muted"><?php echo $actual_departure_datetime->format('h:i A'); ?></small>
                            </div>
                            <div class="text-center px-2">
                                <i class="fas fa-long-arrow-alt-right fa-2x text-muted"></i>
                            </div>
                            <div class="text-end">
                                <dt>Dropping At</dt>
                                <dd class="fs-5"><?php echo htmlspecialchars($booking_details['destination']); ?></dd>
                                <small class="text-muted">Est. <?php echo $actual_arrival_datetime->format('h:i A'); ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-light text-dark">
                        <h5 class="mb-0">Passenger Details</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Passenger Name</th>
                                    <th>Age</th>
                                    <th>Gender</th>
                                    <th class="text-end">Seat No.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($passengers as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['passenger_name']); ?></td>
                                        <td><?php echo htmlspecialchars($p['passenger_age']); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst(strtolower($p['passenger_gender']))); ?></td>
                                        <td class="text-end fw-bold"><?php echo htmlspecialchars($p['seat_code']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small">A copy of the ticket has also been sent to your registered email address.</p>
                    <a href="view_ticket.php?id=<?php echo htmlspecialchars($booking_id); ?>" class="btn btn-primary btn-lg me-2">
                        <i class="fas fa-ticket-alt"></i> View Ticket
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-bus"></i> Book Another Ticket
                    </a>
                </div>

            </div>
        </div>
    </div>
</body>

</html>