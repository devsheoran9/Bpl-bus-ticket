<?php
// The header file now includes your PDO database connection and auth checks.
include 'includes/header.php';
echo user_login('page');

// Redirect user to login page if they are not logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$bookings = [];
$error_message = null;

// Get the selected month from the URL, or default to the current month.
$selected_month = $_GET['month'] ?? date('Y-m');

try {
    // --- DATABASE LOGIC CONVERTED FROM MYSQLi TO PDO ---

    // The SQL query remains the same.
    $sql = "SELECT 
                booking_id, 
                ticket_no, 
                origin, 
                destination, 
                travel_date, 
                total_fare, 
                booking_status 
            FROM bookings 
            WHERE user_id = ? 
            AND DATE_FORMAT(travel_date, '%Y-%m') = ?
            ORDER BY travel_date DESC";

    // Prepare the statement using the $pdo object from your connection file.
    $stmt = $pdo->prepare($sql);

    // Execute the statement by passing the parameters as an array.
    $stmt->execute([$user_id, $selected_month]);

    // Fetch all results directly into the $bookings array.
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    // Catch PDO-specific exceptions for better error handling.
    $error_message = "An error occurred while fetching your bookings: " . $e->getMessage();
} catch (Exception $e) {
    // Catch any other general exceptions.
    $error_message = $e->getMessage();
}

?>

<style>
    /* This style is fine and has been kept */
    .filter-card {
        background-color: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
</style>

<body>

    <main class="container my-5 pt-5">
        <div class="bookings-container">
            <h2 class="text-center mb-4">My Bookings</h2>

            <!-- Filter Form -->
            <div class="filter-card">
                <form action="bookings.php" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <label for="month-filter" class="form-label fw-bold">Select Month to View:</label>
                    </div>
                    <div class="col-md-5">
                        <input
                            type="month"
                            class="form-control"
                            id="month-filter"
                            name="month"
                            value="<?php echo htmlspecialchars($selected_month); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-danger w-100">Filter</button>
                    </div>
                </form>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($bookings)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Ticket No</th>
                                <th>Journey</th>
                                <th>Date</th>
                                <th>Fare</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($booking['ticket_no']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($booking['origin']); ?> &rarr; <?php echo htmlspecialchars($booking['destination']); ?></td>
                                    <td><?php echo date('d M, Y', strtotime($booking['travel_date'])); ?></td>
                                    <td>₹<?php echo number_format($booking['total_fare'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-success"><?php echo htmlspecialchars(ucfirst(strtolower($booking['booking_status']))); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="view_ticket.php?id=<?php echo $booking['booking_id']; ?>" class="view-btn" title="View Ticket" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-bookings-card">
                    <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                    <h4>No Bookings Found</h4>
                    <p class="text-muted">You have no bookings for the selected month of <?php echo date('F Y', strtotime($selected_month . '-01')); ?>.</p>
                    <a href="index.php" class="btn btn-danger mt-2">Book a New Ticket</a>
                </div>
            <?php endif; ?>

            <hr >

            <div class="booking-info-text">
                <h2 class="text-center mb-4">Your Trusted Bus Ticket Booking Partner</h2>
                <p>Welcome to your personal booking dashboard. Here, you can easily manage and view all your past and upcoming journeys. Our goal is to make your online bus booking experience seamless, secure, and convenient. Whether you are planning a business trip, a family vacation, or a quick weekend getaway, we have you covered with our extensive network of bus operators across the country.</p>

                <h3 class="mt-4 mb-3">Why Book With Us?</h3>
                <p>Booking a bus ticket has never been easier. We eliminate the hassle of waiting in long queues at the bus station. With just a few clicks, you can book your bus tickets from the comfort of your home or on the go.</p>
                <ul>
                    <li><strong>Extensive Route Network:</strong> We connect thousands of destinations with a wide choice of bus operators, ensuring you can always find a bus for your desired route.</li>
                    <li><strong>Best Price Guarantee:</strong> We work directly with operators to bring you the best prices and exclusive deals on your bus tickets.</li>
                    <li><strong>Secure Online Payments:</strong> Your security is our priority. We use industry-standard encryption to protect your payment details, offering multiple payment options for your convenience.</li>
                    <li><strong>24/7 Customer Support:</strong> Have a question or need assistance with your booking? Our dedicated customer support team is available around the clock to help you.</li>
                </ul>

                <h3 class="mt-4 mb-3">Travel with Confidence</h3>
                <p>All your confirmed tickets are accessible here at any time. Simply click the "View Ticket" icon to see the details of your journey, including boarding points, bus information, and passenger details. We recommend keeping a digital copy of your ticket on your mobile device for a paperless and smooth boarding process. Thank you for choosing us for your travel needs. We wish you a safe and pleasant journey!</p>
            </div>
        </div>
    </main>

    <?php
    include 'includes/footer.php';
    ?>
</body>

</html>