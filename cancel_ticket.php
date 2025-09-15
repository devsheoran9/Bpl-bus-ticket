<?php
include 'includes/header.php';

// Enforce login.
user_login("page");
$session_email = $_SESSION['email'] ?? null;

// Initialize variables
$booking_details = null;
$passengers = [];
$cancellation_allowed = false;
$pending_cancellation_pids = [];
$error_message = $_SESSION['error_message'] ?? null;
$success_message = $_SESSION['success_message'] ?? null;

// Clear session messages after retrieving them
unset($_SESSION['error_message'], $_SESSION['success_message']);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ticket_no'])) {
    $ticket_no = trim($_GET['ticket_no']);

    if (!empty($ticket_no) && !empty($session_email)) {
        try {
            // Step 1: Fetch the core booking and passenger details
            $stmt = $pdo->prepare("
                SELECT
                    b.booking_id, b.ticket_no, b.travel_date, b.origin, b.destination, b.booking_status,
                    p.passenger_id, p.passenger_name, p.seat_code, p.passenger_status,
                    rs.departure_time
                FROM bookings b
                JOIN passengers p ON b.booking_id = p.booking_id
                JOIN route_schedules rs ON b.route_id = rs.route_id
                WHERE
                    b.ticket_no = ? AND b.contact_email = ?
                    AND rs.operating_day LIKE CONCAT('%', DATE_FORMAT(b.travel_date, '%a'), '%')
                GROUP BY p.passenger_id
            ");
            $stmt->execute([$ticket_no, $session_email]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $booking_details = $results[0];
                $passengers = $results;

                // Step 2: Check the cancellation deadline
                $departure_datetime_str = $booking_details['travel_date'] . ' ' . $booking_details['departure_time'];
                $departure_timestamp = strtotime($departure_datetime_str);
                $cancellation_deadline = $departure_timestamp - (12 * 60 * 60); // 12-hour rule

                if (time() <= $cancellation_deadline) {
                    $cancellation_allowed = true;
                } else {
                    $error_message = "The cancellation period for this ticket has expired (must be 12 hours before departure).";
                }

                // Step 3: Check for existing PENDING cancellation requests for this booking
                $pending_stmt = $pdo->prepare("SELECT passenger_id FROM cancellations WHERE booking_id = ? AND status = 'PENDING'");
                $pending_stmt->execute([$booking_details['booking_id']]);
                $pending_cancellation_pids = $pending_stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            } else {
                $error_message = "The entered Ticket No. (PNR) was not found for your account.";
            }
        } catch (PDOException $e) {
            $error_message = "A critical database error occurred. Please contact support.";
            error_log("Cancellation lookup PDOException: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Ticket</title>
    <!-- Your CSS from header.php -->
</head>

<body>

    <main class="container my-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card" style="border: none; border-radius: 15px; box-shadow: 0 8px 30px rgba(0,0,0,0.07); overflow: hidden; position: relative;">
                    <div style="position: absolute; left: -50px; top: -40px; font-size: 200px; color: #e9ecef; opacity: 0.5; z-index: 1; transform: rotate(15deg);"><i class="bi bi-journal-x"></i></div>
                    <div class="card-body p-4 p-md-5" style="position: relative; z-index: 2;">
                        <h2 class="card-title text-center mb-4" style="font-weight: 700; color: #343a40;">Request Ticket Cancellation</h2>

                        <?php if ($success_message): ?>
                            <div class="alert alert-info"><?php echo htmlspecialchars($success_message); ?></div>
                        <?php endif; ?>
                        <?php if ($error_message && !$booking_details): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>

                        <div class="p-4 rounded" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">
                            <form action="cancel_ticket.php" method="GET" class="mb-0">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-5">
                                        <label for="ticket_no" class="form-label" style="font-weight: 500;">Ticket No. (PNR)</label>
                                        <div style="position: relative;">
                                            <i class="bi bi-ticket-perforated" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #adb5bd;"></i>
                                            <input type="text" class="form-control" id="ticket_no" name="ticket_no" required value="<?php echo htmlspecialchars($_GET['ticket_no'] ?? ''); ?>" style="padding-left: 40px;">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="email" class="form-label" style="font-weight: 500;">Your Email Address</label>
                                        <div style="position: relative;">
                                            <i class="bi bi-envelope" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #adb5bd;"></i>
                                            <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($session_email ?? ''); ?>" readonly style="padding-left: 40px; background-color: #e9ecef;">
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-grid">
                                        <button type="submit" class="btn btn-primary w-100">Find Ticket</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <?php if ($booking_details): ?>
                            <hr class="my-5">
                            <h4 class="mb-3" style="font-weight: 600;">Booking Details</h4>

                            <?php if ($error_message && $booking_details): ?>
                                <div class="alert alert-warning"><?php echo htmlspecialchars($error_message); ?></div>
                            <?php endif; ?>

                            <div class="card mb-4" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">
                                <div class="card-body">
                                    <p><strong>Ticket No:</strong> <?php echo htmlspecialchars($booking_details['ticket_no']); ?></p>
                                    <p><strong>Journey:</strong> <?php echo htmlspecialchars($booking_details['origin']); ?> to <?php echo htmlspecialchars($booking_details['destination']); ?></p>
                                    <p class="mb-0"><strong>Travel Date:</strong> <?php echo date('d M Y', strtotime($booking_details['travel_date'])); ?> at <?php echo date('h:i A', strtotime($booking_details['departure_time'])); ?></p>
                                </div>
                            </div>

                            <form action="cancel_ticket_process.php" method="POST" id="cancellation-form">
                                <input type="hidden" name="booking_id" value="<?php echo $booking_details['booking_id']; ?>">
                                <input type="hidden" name="ticket_no" value="<?php echo htmlspecialchars($booking_details['ticket_no']); ?>">

                                <h5 class="mb-3" style="font-weight: 600;">Select Passengers to Cancel</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead style="background-color: #f8f9fa;">
                                            <tr>
                                                <th style="width: 50px;" class="text-center"><input type="checkbox" class="form-check-input" id="select-all" <?php if (!$cancellation_allowed) echo 'disabled'; ?>></th>
                                                <th>Passenger Name</th>
                                                <th>Seat No.</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $active_passengers_exist = false;
                                            foreach ($passengers as $passenger):
                                                $status = strtoupper($passenger['passenger_status']);
                                                $is_pending = in_array($passenger['passenger_id'], $pending_cancellation_pids);
                                                $is_cancellable = ($status === 'CONFIRMED');
                                                if ($is_cancellable && !$is_pending) $active_passengers_exist = true;
                                            ?>
                                                <tr style="vertical-align: middle;">
                                                    <td class="text-center">
                                                        <input type="checkbox" class="form-check-input passenger-checkbox" name="passengers_to_cancel[]" value="<?php echo $passenger['passenger_id']; ?>" <?php if (!$is_cancellable || $is_pending || !$cancellation_allowed) echo 'disabled'; ?>>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($passenger['passenger_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($passenger['seat_code']); ?></td>
                                                    <td>
                                                        <?php
                                                        $badge_class = 'bg-success';
                                                        $display_status = $status;
                                                        if ($status === 'CANCELLED') {
                                                            $badge_class = 'bg-danger';
                                                        } elseif ($is_pending) {
                                                            $badge_class = 'bg-warning text-dark';
                                                            $display_status = 'PENDING CANCELLATION';
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $badge_class; ?> rounded-pill">
                                                            <?php echo htmlspecialchars(ucwords(strtolower(str_replace("_", " ", $display_status)))); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if ($cancellation_allowed && $active_passengers_exist): ?>
                                    <div class="d-grid">
                                        <button type="button" id="cancel-trigger-btn" class="btn btn-danger btn-lg mt-3">Request Cancellation for Selected</button>
                                    </div>
                                <?php elseif (!$active_passengers_exist && $cancellation_allowed): ?>
                                    <p class="text-center text-muted mt-3">All passengers on this booking have been cancelled or are pending cancellation.</p>
                                <?php endif; ?>
                            </form>

                            <div class="mt-5 p-4 rounded" style="background-color: #f1f3f5; border: 1px solid #dee2e6;">
                                <h5 class="mb-3" style="font-weight: 600;">Cancellation Terms & Conditions</h5>
                                <ul class="list-unstyled" style="font-size: 0.9rem; color: #495057;">
                                    <li class="mb-2 d-flex"><i class="bi bi-check-circle-fill text-success me-2" style="margin-top: 3px;"></i><span>Tickets can be cancelled up to <strong>12 hours</strong> before the scheduled departure time.</span></li>
                                    <li class="mb-2 d-flex"><i class="bi bi-check-circle-fill text-success me-2" style="margin-top: 3px;"></i><span>Cancellation requests are final and cannot be undone once submitted.</span></li>
                                    <li class="mb-2 d-flex"><i class="bi bi-check-circle-fill text-success me-2" style="margin-top: 3px;"></i><span>All cancellation requests are subject to approval. You will be notified via email about the status of your request.</span></li>
                                    <li class="mb-2 d-flex"><i class="bi bi-check-circle-fill text-success me-2" style="margin-top: 3px;"></i><span>Upon approval, the refund amount will be credited to your original payment source within 24-48 working hours.</span></li>
                                    <li class="d-flex"><i class="bi bi-x-circle-fill text-danger me-2" style="margin-top: 3px;"></i><span>Partial cancellation of a round-trip ticket is not permitted.</span></li>
                                </ul>
                            </div>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="reasonModal" tabindex="-1" aria-labelledby="reasonModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reasonModalLabel">Reason for Cancellation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Please select a reason for cancelling the selected ticket(s).</p>
                    <div class="form-group">
                        <label for="cancellation_reason_select" class="form-label">Reason</label>
                        <select class="form-select" id="cancellation_reason_select">
                            <option value="Change of Plans">Change of Plans</option>
                            <option value="Booked by Mistake">Booked by Mistake</option>
                            <option value="Medical Emergency">Medical Emergency</option>
                            <option value="Found a better option">Found a better option</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group mt-3 d-none" id="other_reason_div">
                        <label for="other_reason_text" class="form-label">Please specify</label>
                        <textarea class="form-control" id="other_reason_text" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="confirm-cancel-btn" class="btn btn-danger">Confirm Cancellation Request</button>
                </div>
            </div>
        </div>
    </div>

    <br><br><br><br><br>
    <?php include 'includes/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const passengerCheckboxes = document.querySelectorAll('.passenger-checkbox:not(:disabled)');
            const cancellationForm = document.getElementById('cancellation-form');
            const cancelTriggerBtn = document.getElementById('cancel-trigger-btn');
            const reasonModalElement = document.getElementById('reasonModal');

            if (reasonModalElement) {
                const reasonModal = new bootstrap.Modal(reasonModalElement);
                const confirmCancelBtn = document.getElementById('confirm-cancel-btn');
                const reasonSelect = document.getElementById('cancellation_reason_select');
                const otherReasonDiv = document.getElementById('other_reason_div');
                const otherReasonText = document.getElementById('other_reason_text');

                reasonSelect.addEventListener('change', function() {
                    otherReasonDiv.classList.toggle('d-none', this.value !== 'Other');
                });

                cancelTriggerBtn?.addEventListener('click', function() {
                    const selectedCount = document.querySelectorAll('.passenger-checkbox:checked').length;
                    if (selectedCount === 0) {
                        alert('Please select at least one passenger to cancel.');
                        return;
                    }
                    reasonModal.show();
                });

                confirmCancelBtn?.addEventListener('click', function() {
                    let reason = reasonSelect.value;
                    if (reason === 'Other') {
                        reason = otherReasonText.value.trim();
                        if (reason === '') {
                            alert('Please specify a reason for cancellation.');
                            return;
                        }
                    }

                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'cancellation_reason';
                    hiddenInput.value = reason;

                    cancellationForm.appendChild(hiddenInput);
                    cancellationForm.submit();
                });
            }

            selectAllCheckbox?.addEventListener('change', function() {
                passengerCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        });
    </script>
</body>

</html>