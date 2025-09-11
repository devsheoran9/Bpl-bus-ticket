<?php
include 'includes/header.php';

// --- 1. Security & Input Validation ---
$booking_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$booking_id) {
    die("Error: A valid Booking ID is required.");
}

$is_new_user = isset($_GET['new_user']) && $_GET['new_user'] === 'true';

if (!$is_new_user) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    $logged_in_user_id = $_SESSION['user_id'];
}

// Initialize variables
$booking_details = null;
$passengers = [];
$transaction_details = null; // Variable to hold transaction data

try {
    // --- 2. Database Fetching (Using PDO from header) ---
    if ($is_new_user) {
        // For a new user, we don't check the user_id, as they aren't logged in yet.
        $sql = "SELECT b.*, bu.bus_name, bu.registration_number, rs.departure_time, r.starting_point, r.ending_point
                FROM bookings b
                JOIN buses bu ON b.bus_id = bu.bus_id
                JOIN routes r ON b.route_id = r.route_id
                LEFT JOIN route_schedules rs ON b.route_id = rs.route_id AND rs.operating_day = DATE_FORMAT(b.travel_date, '%a')
                WHERE b.booking_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$booking_id]);
    } else {
        // For a logged-in user, we MUST ensure the ticket belongs to them.
        $sql = "SELECT b.*, bu.bus_name, bu.registration_number, rs.departure_time, r.starting_point, r.ending_point
                FROM bookings b
                JOIN buses bu ON b.bus_id = bu.bus_id
                JOIN routes r ON b.route_id = r.route_id
                LEFT JOIN route_schedules rs ON b.route_id = rs.route_id AND rs.operating_day = DATE_FORMAT(b.travel_date, '%a')
                WHERE b.booking_id = ? AND b.user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$booking_id, $logged_in_user_id]);
    }

    $booking_details = $stmt->fetch();

    if (!$booking_details) {
        die("Booking not found or you do not have permission to view this confirmation.");
    }

    $passengersStmt = $pdo->prepare("SELECT passenger_name, seat_code, passenger_age, passenger_gender FROM passengers WHERE booking_id = ?");
    $passengersStmt->execute([$booking_id]);
    $passengers = $passengersStmt->fetchAll();

    // Fetch transaction details to get the payment ID
    $transStmt = $pdo->prepare("SELECT gateway_payment_id FROM transactions WHERE booking_id = ? ORDER BY transaction_id DESC LIMIT 1");
    $transStmt->execute([$booking_id]);
    $transaction_details = $transStmt->fetch();

    // --- 3. Calculate Real Boarding/Dropping Times ---
    $origin_minutes = 0;
    if ($booking_details['origin'] != $booking_details['starting_point']) {
        $stmt_origin = $pdo->prepare("SELECT duration_from_start_minutes FROM route_stops WHERE route_id = ? AND stop_name = ?");
        $stmt_origin->execute([$booking_details['route_id'], $booking_details['origin']]);
        $origin_result = $stmt_origin->fetch();
        if ($origin_result) {
            $origin_minutes = (int)$origin_result['duration_from_start_minutes'];
        }
    }

    $stmt_dest = $pdo->prepare("SELECT MAX(duration_from_start_minutes) as duration FROM route_stops WHERE route_id = ?");
    $stmt_dest->execute([$booking_details['route_id']]);
    $destination_minutes_total_route = (int)$stmt_dest->fetch()['duration'];

    $destination_minutes = 0;
    if ($booking_details['destination'] == $booking_details['ending_point']) {
        $destination_minutes = $destination_minutes_total_route;
    } else {
        $stmt_dest_stop = $pdo->prepare("SELECT duration_from_start_minutes FROM route_stops WHERE route_id = ? AND stop_name = ?");
        $stmt_dest_stop->execute([$booking_details['route_id'], $booking_details['destination']]);
        $destination_result_stop = $stmt_dest_stop->fetch();
        $destination_minutes = $destination_result_stop ? (int)$destination_result_stop['duration_from_start_minutes'] : $destination_minutes_total_route;
    }

    $route_departure_datetime_str = $booking_details['travel_date'] . ' ' . ($booking_details['departure_time'] ?? '00:00');
    $route_departure_datetime = new DateTime($route_departure_datetime_str);
    $actual_departure_datetime = (clone $route_departure_datetime)->modify("+$origin_minutes minutes");
    $actual_arrival_datetime = (clone $route_departure_datetime)->modify("+$destination_minutes minutes");
} catch (PDOException $e) {
    die("An error occurred while fetching booking details: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation - <?php echo htmlspecialchars($booking_details['ticket_no']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .confirmation-card {
            max-width: 850px;
            margin: 40px auto;
            border-radius: 12px;
            border: none;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.25rem;
        }

        .details-grid dt {
            font-weight: 500;
            color: #6c757d;
        }

        .details-grid dd {
            font-weight: 600;
            color: #212529;
            margin-bottom: 0;
        }

        .journey-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .new-account-info {
            background-color: #e3eeff;
            border: 1px solid #b8d6fb;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .password-field-wrapper {
            position: relative;
        }

        .password-field-wrapper input {
            padding-right: 40px;
        }

        .password-field-wrapper .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <main class="container my-5 pt-5">
        <div class="card shadow-lg confirmation-card">
            <div class="card-body p-lg-5">
                <div class="text-center mb-4">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h1 class="h3"><?php echo $is_new_user ? 'Booking Successful & Account Created!' : 'Booking Confirmed!'; ?></h1>
                    <p class="text-muted">Congratulations! Your ticket has been successfully confirmed. Have a safe journey!</p>
                </div>

                <?php if ($is_new_user): ?>
                    <div class="new-account-info text-center">
                        <h5 class="fw-bold">Your Account Has Been Created!</h5>
                        <p class="mb-3">Use these details to log in and manage all your bookings in one place.</p>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <dl class="row text-start">
                                    <dt class="col-sm-4">Username:</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($booking_details['contact_email']); ?></dd>
                                    <dt class="col-sm-4">Password:</dt>
                                    <dd class="col-sm-8">
                                        <div class="password-field-wrapper">
                                            <input type="password" id="default-password" class="form-control" value="<?php echo htmlspecialchars($booking_details['contact_mobile']); ?>" readonly>
                                            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                                        </div>
                                        <small class="text-muted">(Your mobile number is your default password.)</small>
                                    </dd>
                                </dl>
                                <a href="login.php" class="btn btn-primary mt-3">Login to Your Account</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-header bg-light text-dark">
                        <h5 class="mb-0">Journey Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="details-grid">
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
                            <?php if ($transaction_details && !empty($transaction_details['gateway_payment_id'])): ?>
                                <div class="grid-item">
                                    <dt>Payment ID</dt>
                                    <dd><?php echo htmlspecialchars($transaction_details['gateway_payment_id']); ?></dd>
                                </div>
                            <?php endif; ?>
                        </div>
                        <hr>
                        <div class="row align-items-center">
                            <div class="col-md-5 text-md-start text-center">
                                <dt>Boarding From</dt>
                                <dd class="fs-5"><?php echo htmlspecialchars($booking_details['origin']); ?></dd>
                                <small class="text-muted"><?php echo $actual_departure_datetime->format('h:i A'); ?></small>
                            </div>
                            <div class="col-md-2 text-center journey-arrow d-none d-md-block"><i class="fas fa-long-arrow-alt-right fa-2x text-muted"></i></div>
                            <div class="col-md-5 text-md-end text-center mt-3 mt-md-0">
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
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
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
                </div>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small">A copy of the ticket has also been sent to your registered email address.</p>
                    <a href="view_ticket.php?id=<?php echo htmlspecialchars($booking_id); ?>&pnr=<?php echo htmlspecialchars($booking_details['ticket_no']); ?>" class="btn btn-primary btn-lg me-2" target="_blank"><i class="fas fa-ticket-alt"></i> View/Print Ticket</a>
                    <a href="index.php" class="btn btn-outline-secondary btn-lg"><i class="fas fa-bus"></i> Book Another Ticket</a>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#default-password');
            if (togglePassword) {
                togglePassword.addEventListener('click', function(e) {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    this.classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>
    <?php include 'includes/footer.php'; ?>
</body>

</html>