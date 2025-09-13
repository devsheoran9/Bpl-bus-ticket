<?php 
include 'includes/header.php';
echo user_login("page");
// Initialize variables
$booking_details = null;
$passengers = [];
$cancellation_allowed = false;
$error_message = $_SESSION['error_message'] ?? null;
$success_message = $_SESSION['success_message'] ?? null;

// Clear session messages after displaying them
unset($_SESSION['error_message'], $_SESSION['success_message']);

// This part only handles the GET request to LOOKUP a ticket
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ticket_no']) && isset($_GET['email'])) {
    $ticket_no = trim($_GET['ticket_no']);
    $email = trim($_GET['email']);

    if (!empty($ticket_no) && !empty($email)) {
        try {
            // Updated query to use route_id and travel_date to find the schedule
            $stmt = $pdo->prepare("
                SELECT
                    b.booking_id, b.ticket_no, b.travel_date, b.origin, b.destination, b.booking_status,
                    p.passenger_id, p.passenger_name, p.seat_code, p.passenger_status,
                    rs.departure_time
                FROM bookings b
                JOIN passengers p ON b.booking_id = p.booking_id
                JOIN route_schedules rs ON b.route_id = rs.route_id AND rs.operating_day = DATE_FORMAT(b.travel_date, '%a')
                WHERE b.ticket_no = ? AND b.contact_email = ?
            ");
            $stmt->execute([$ticket_no, $email]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $booking_details = $results[0];
                $passengers = $results;

                $departure_datetime_str = $booking_details['travel_date'] . ' ' . $booking_details['departure_time'];
                $departure_timestamp = strtotime($departure_datetime_str);
                $cancellation_deadline = $departure_timestamp - (30 * 60);

                if (time() <= $cancellation_deadline) {
                    $cancellation_allowed = true;
                } else {
                    $error_message = "The cancellation period for this ticket has expired. Tickets can only be cancelled up to 30 minutes before departure.";
                }
            } else {
                $error_message = "No booking found with the provided Ticket No. and Email Address.";
            }
        } catch (PDOException $e) {
            $error_message = "A database error occurred. Please try again later.";
            error_log("Cancellation lookup error: " . $e->getMessage());
        }
    }
}
?>

<main class="container my-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4 p-md-5">
                    <h2 class="card-title text-center mb-4">Cancel Your Ticket</h2>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>
                    <?php if ($error_message && !$booking_details): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>

                    <form action="cancel_ticket" method="GET" class="mb-5">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label for="ticket_no" class="form-label">Ticket No. (PNR)</label>
                                <input type="text" class="form-control" id="ticket_no" name="ticket_no" required value="<?php echo htmlspecialchars($_GET['ticket_no'] ?? ''); ?>">
                            </div>
                            <div class="col-md-5">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Find</button>
                            </div>
                        </div>
                    </form>

                    <?php if ($booking_details): ?>
                        <hr>
                        <h4 class="mb-3">Booking Details</h4>

                        <?php if ($error_message && $booking_details): ?>
                            <div class="alert alert-warning"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>

                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <p><strong>Ticket No:</strong> <?php echo htmlspecialchars($booking_details['ticket_no']); ?></p>
                                <p><strong>Journey:</strong> <?php echo htmlspecialchars($booking_details['origin']); ?> to <?php echo htmlspecialchars($booking_details['destination']); ?></p>
                                <p class="mb-0"><strong>Travel Date:</strong> <?php echo date('d M Y', strtotime($booking_details['travel_date'])); ?> at <?php echo date('h:i A', strtotime($booking_details['departure_time'])); ?></p>
                            </div>
                        </div>

                        <!-- The action now points to our new processing file -->
                        <form action="cancel_ticket_process" method="POST" id="cancellation-form">
                            <input type="hidden" name="booking_id" value="<?php echo $booking_details['booking_id']; ?>">
                            <input type="hidden" name="ticket_no" value="<?php echo htmlspecialchars($booking_details['ticket_no']); ?>">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">

                            <h5 class="mb-3">Select Passengers to Cancel</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;"><input type="checkbox" id="select-all" <?php if (!$cancellation_allowed) echo 'disabled'; ?>></th>
                                            <th>Passenger Name</th>
                                            <th>Seat No.</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $active_passengers_exist = false;
                                        foreach ($passengers as $passenger):
                                            $is_cancelled = ($passenger['passenger_status'] === 'CANCELLED');
                                            if (!$is_cancelled) $active_passengers_exist = true;
                                        ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="passenger-checkbox" name="passengers_to_cancel[]" value="<?php echo $passenger['passenger_id']; ?>" <?php if ($is_cancelled || !$cancellation_allowed) echo 'disabled'; ?>>
                                                </td>
                                                <td><?php echo htmlspecialchars($passenger['passenger_name']); ?></td>
                                                <td><?php echo htmlspecialchars($passenger['seat_code']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $is_cancelled ? 'bg-danger' : 'bg-success'; ?>">
                                                        <?php echo htmlspecialchars(ucfirst(strtolower($passenger['passenger_status']))); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($cancellation_allowed && $active_passengers_exist): ?>
                                <!-- This button now triggers the modal instead of submitting -->
                                <button type="button" id="cancel-trigger-btn" class="btn btn-danger w-100 mt-3">Cancel Selected Tickets</button>
                            <?php elseif (!$active_passengers_exist): ?>
                                <p class="text-center text-muted mt-3">All tickets for this booking have already been cancelled.</p>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- NEW: Cancellation Reason Modal -->
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
                    <label for="cancellation_reason_select">Reason</label>
                    <select class="form-select" id="cancellation_reason_select">
                        <option value="Change of Plans">Change of Plans</option>
                        <option value="Booked by Mistake">Booked by Mistake</option>
                        <option value="Medical Emergency">Medical Emergency</option>
                        <option value="Found a better option">Found a better option</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group mt-3 d-none" id="other_reason_div">
                    <label for="other_reason_text">Please specify</label>
                    <textarea class="form-control" id="other_reason_text" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="confirm-cancel-btn" class="btn btn-danger">Confirm Cancellation</button>
            </div>
        </div>
    </div>
</div>


<?php include 'includes/footer.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        const passengerCheckboxes = document.querySelectorAll('.passenger-checkbox:not(:disabled)');
        const cancellationForm = document.getElementById('cancellation-form');
        const cancelTriggerBtn = document.getElementById('cancel-trigger-btn');

        // NEW: Modal related elements
        const reasonModalElement = document.getElementById('reasonModal');
        if (reasonModalElement) {
            const reasonModal = new bootstrap.Modal(reasonModalElement);
            const confirmCancelBtn = document.getElementById('confirm-cancel-btn');
            const reasonSelect = document.getElementById('cancellation_reason_select');
            const otherReasonDiv = document.getElementById('other_reason_div');
            const otherReasonText = document.getElementById('other_reason_text');

            // Show/hide the 'Other' textarea based on selection
            reasonSelect.addEventListener('change', function() {
                if (this.value === 'Other') {
                    otherReasonDiv.classList.remove('d-none');
                } else {
                    otherReasonDiv.classList.add('d-none');
                }
            });

            // When the main cancel button is clicked...
            cancelTriggerBtn?.addEventListener('click', function() {
                const selectedCount = document.querySelectorAll('.passenger-checkbox:checked').length;
                if (selectedCount === 0) {
                    alert('Please select at least one passenger to cancel.');
                    return;
                }
                // ...show the modal instead of submitting the form.
                reasonModal.show();
            });

            // When the final confirmation button inside the modal is clicked...
            confirmCancelBtn?.addEventListener('click', function() {
                let reason = reasonSelect.value;
                if (reason === 'Other') {
                    reason = otherReasonText.value.trim();
                    if (reason === '') {
                        alert('Please specify a reason for cancellation.');
                        return;
                    }
                }

                // Create a hidden input field to hold the reason
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'cancellation_reason';
                hiddenInput.value = reason;

                // Add it to the form and submit
                cancellationForm.appendChild(hiddenInput);
                cancellationForm.submit();
            });
        }

        // Existing "Select All" logic
        selectAllCheckbox?.addEventListener('change', function() {
            passengerCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    });
</script>

</body>
</html>
