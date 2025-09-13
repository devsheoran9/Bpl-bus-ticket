<?php
include 'includes/header.php';
// This function should handle session checks and redirects if the user is not logged in.
echo user_login('page');

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
    // The SQL query is correct and uses PDO prepared statements.
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

    // Prepare and execute the statement using the $pdo object.
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $selected_month]);

    // Fetch all results.
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Bookings Page Error: " . $e->getMessage()); // Log error for debugging
    $error_message = "An error occurred while fetching your bookings. Please try again later.";
}

?>

<style>
    .filter-card {
        background-color: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .no-bookings-card {
        text-align: center;
        padding: 3rem;
        background-color: #f8f9fa;
        border-radius: 8px;
    }

    .view-btn {
        color: #0d6efd;
        font-size: 1.2rem;
        text-decoration: none;
    }

    .view-btn:hover {
        color: #0a58ca;
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

            <?php if (empty($bookings) && !$error_message): ?>
                <div class="no-bookings-card">
                    <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                    <h4>No Bookings Found</h4>
                    <p class="text-muted">You have no bookings for the selected month of <?php echo date('F Y', strtotime($selected_month . '-01')); ?>.</p>
                    <a href="index.php" class="btn btn-danger mt-2">Book a New Ticket</a>
                </div>
            <?php elseif (!empty($bookings)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 bg-white">
                        <thead class="bg-light">
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
                                        <span class="badge bg-success rounded-pill"><?php echo htmlspecialchars(ucfirst(strtolower($booking['booking_status']))); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <!-- === FIX APPLIED HERE === -->
                                        <!-- The link now includes both the booking ID and the PNR (ticket_no) -->
                                        <a href="view_ticket.php?id=<?php echo $booking['booking_id']; ?>&pnr=<?php echo urlencode($booking['ticket_no']); ?>" class="view-btn" title="View Ticket" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <hr class="my-5">

            <div class="booking-info-text">
                <h2 class="text-center mb-4">Your Trusted Bus Ticket Booking Partner</h2>
                <p>Welcome to your personal booking dashboard. Here, you can easily manage and view all your past and upcoming journeys. Our goal is to make your online bus booking experience seamless, secure, and convenient.</p>
                <h3 class="mt-4 mb-3">Why Book With Us?</h3>
                <ul>
                    <li><strong>Extensive Route Network:</strong> We connect thousands of destinations, ensuring you can always find a bus for your desired route.</li>
                    <li><strong>Best Price Guarantee:</strong> We work directly with operators to bring you the best prices and exclusive deals.</li>
                    <li><strong>Secure Online Payments:</strong> Your security is our priority. We use industry-standard encryption to protect your payment details.</li>
                    <li><strong>24/7 Customer Support:</strong> Our dedicated customer support team is available around the clock to help you.</li>
                </ul>
            </div>
        </div>
    </main>

    <?php
    include 'includes/footer.php';
    ?>
</body>

</html>