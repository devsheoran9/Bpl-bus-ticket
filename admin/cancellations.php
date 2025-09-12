<?php
// We assume the admin header includes the session start and database connection ($pdo).
include 'header.php';


// --- FETCH CANCELLATION DATA ---
$cancellations = [];
try {
    // This is a comprehensive query that joins all necessary tables to get a full report.
    $stmt = $pdo->query("
        SELECT
            c.cancellation_id,
            c.amount_refunded,
            c.cancellation_reason,
            c.status AS refund_status,
            c.created_at AS cancelled_on,
            b.ticket_no,
            b.travel_date,
            b.contact_name,
            b.contact_email,
            p.passenger_name,
            p.seat_code,
            p.fare AS original_passenger_fare,
            t.gateway_payment_id
        FROM cancellations AS c
        JOIN bookings AS b ON c.booking_id = b.booking_id
        JOIN passengers AS p ON c.passenger_id = p.passenger_id
        LEFT JOIN (
            -- Subquery to get only the latest successful transaction for each booking
            SELECT booking_id, gateway_payment_id
            FROM transactions
            WHERE payment_status = 'CAPTURED'
            ORDER BY transaction_id DESC
        ) AS t ON c.booking_id = t.booking_id
        ORDER BY c.created_at DESC
    ");
    $cancellations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Store error message to display in the UI
    $error_message = "Database error: " . $e->getMessage();
    error_log($error_message); // Log the detailed error for debugging
}

?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0">Cancellation & Refund Report</h5>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger mx-4"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>

                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ticket / Passenger</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Journey Date</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Amounts</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Refund Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cancelled On</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cancellations)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <p class="text-muted">No cancellations have been recorded yet.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cancellations as $c): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($c['passenger_name']); ?></h6>
                                                        <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($c['ticket_no']); ?> | Seat: <?php echo htmlspecialchars($c['seat_code']); ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0"><?php echo date('d M Y', strtotime($c['travel_date'])); ?></p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <p class="mb-0">Paid: <strong class="text-dark">₹<?php echo htmlspecialchars(number_format($c['original_passenger_fare'], 2)); ?></strong></p>
                                                <p class="mb-0">Refund: <strong class="text-danger">₹<?php echo htmlspecialchars(number_format($c['amount_refunded'], 2)); ?></strong></p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <?php
                                                    $status = $c['refund_status'];
                                                    $badge_class = 'bg-secondary'; // Default
                                                    if ($status === 'COMPLETED') $badge_class = 'bg-success';
                                                    if ($status === 'PENDING') $badge_class = 'bg-warning';
                                                    if ($status === 'FAILED') $badge_class = 'bg-danger';
                                                ?>
                                                <span class="badge badge-sm <?php echo $badge_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold"><?php echo date('d M Y, h:i A', strtotime($c['cancelled_on'])); ?></span>
                                            </td>
                                            <td class="align-middle">
                                                <!-- Optional: Link to view more details -->
                                                <a href="javascript:;" class="text-secondary font-weight-bold text-xs" data-bs-toggle="tooltip" data-bs-original-title="View details for cancellation #<?php echo $c['cancellation_id']; ?>">
                                                    Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>