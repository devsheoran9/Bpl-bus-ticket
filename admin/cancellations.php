<?php
// cancellations.php
include_once('function/_db.php');
session_security_check();
check_permission('can_manage_cancellations');

try {
    // --- NEW & IMPROVED QUERY ---
    // Fetches the original passenger fare separately from the refunded amount
    $stmt = $_conn_db->query("
        SELECT
            c.cancellation_id,
            c.amount_refunded,
            c.cancellation_reason,
            c.status AS refund_status,
            c.created_at AS cancelled_on,
            b.ticket_no,
            b.travel_date,
            p.passenger_name,
            p.seat_code,
            p.fare AS original_passenger_fare, -- Get the original fare from the passengers table
            t.gateway_payment_id
        FROM cancellations AS c
        JOIN bookings AS b ON c.booking_id = b.booking_id
        JOIN passengers AS p ON c.passenger_id = p.passenger_id
        LEFT JOIN transactions AS t ON c.booking_id = t.booking_id AND t.payment_status = 'CAPTURED'
        ORDER BY 
            FIELD(c.status, 'PENDING', 'COMPLETED', 'FAILED'), -- PENDING requests will always be on top
            c.created_at DESC
    ");
    $cancellations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Manage Cancellations & Refunds</title>
    <style>
        .table th { text-transform: uppercase; font-size: 0.8rem; }
        .actions-cell .btn { margin-left: 5px; }
        .dataTables_wrapper .dataTables_filter { margin-bottom: 1rem; float: right; }
        .dataTables_wrapper .dt-buttons { margin-bottom: 1rem; float: left; }
        .fare-column .original-fare { font-weight: 500; color: #6c757d; }
        .fare-column .refund-amount { font-weight: bold; color: #dc3545; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Cancellations & Refunds</h2>
            <div class="card">
                <div class="card-header">
                    <h5>All Cancellation Requests</h5>
                </div>
                <div class="card-body">
                    <table id="cancellations-table" class="display table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Passenger / Ticket</th>
                                <th>Journey Date</th>
                                <th class="text-end">Fare / Refund</th>
                                <th class="text-center">Status</th>
                                <th>Reason / Refund ID</th>
                                <th>Cancelled On</th>
                                <th class="no-export text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cancellations as $c): ?>
                                <tr id="cancellation-row-<?php echo $c['cancellation_id']; ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($c['passenger_name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($c['ticket_no']); ?></small>
                                    </td>
                                    <td><?php echo date('d M, Y', strtotime($c['travel_date'])); ?></td>
                                    
                                    <!-- NEW: Fare / Refund Column -->
                                    <td class="text-end fare-column">
                                        <div class="original-fare" title="Original Ticket Price">
                                            Pay: ₹<?php echo number_format($c['original_passenger_fare'], 2); ?>
                                        </div>
                                        <?php if ($c['refund_status'] === 'COMPLETED'): ?>
                                            <div class="refund-amount" title="Amount Refunded">
                                                Refunded: ₹<?php echo number_format($c['amount_refunded'], 2); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php
                                            $status = $c['refund_status'];
                                            $badge_class = 'bg-secondary';
                                            if ($status === 'COMPLETED') $badge_class = 'bg-success';
                                            if ($status === 'PENDING') $badge_class = 'bg-warning text-dark';
                                            if ($status === 'FAILED') $badge_class = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                    </td>

                                    <!-- NEW: Reason / Refund ID Column -->
                                    <td>
                                        <small>
                                            <?php 
                                            if ($status === 'COMPLETED' && !empty($c['gateway_refund_id'])) {
                                                echo 'Refund ID: <strong>' . htmlspecialchars($c['gateway_refund_id']) . '</strong>';
                                            } else {
                                                echo htmlspecialchars($c['cancellation_reason'] ?: 'N/A');
                                            }
                                            ?>
                                        </small>
                                    </td>

                                    <td><?php echo date('d M Y, h:i A', strtotime($c['cancelled_on'])); ?></td>

                                    <td class="text-end actions-cell">
                                        <?php if ($status === 'PENDING'): ?>
                                            <button class="btn btn-sm btn-success action-btn" data-action="mark_refunded" data-id="<?php echo $c['cancellation_id']; ?>" title="Mark as Refunded">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger action-btn" data-action="mark_failed" data-id="<?php echo $c['cancellation_id']; ?>" title="Mark as Failed">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "foot.php"; ?>
<script>
$(document).ready(function() {
    // --- INITIALIZE DATATABLES ---
    const cancellationTable = $('#cancellations-table').DataTable({
        "dom": 'Bfrtip', // Enable Buttons, Filtering, etc.
        "buttons": [
            { extend: 'copyHtml5', exportOptions: { columns: ':not(.no-export)' }, title: 'Cancellation Report' },
            { extend: 'csvHtml5', exportOptions: { columns: ':not(.no-export)' }, filename: `Cancellation-Report-${new Date().toISOString().slice(0,10)}` },
            { extend: 'excelHtml5', exportOptions: { columns: ':not(.no-export)' }, filename: `Cancellation-Report-${new Date().toISOString().slice(0,10)}` },
            { extend: 'pdfHtml5', exportOptions: { columns: ':not(.no-export)' }, filename: `Cancellation-Report-${new Date().toISOString().slice(0,10)}` },
            { extend: 'print', exportOptions: { columns: ':not(.no-export)' }, title: 'Cancellation Report' }
        ],
        "pageLength": 25,
        // --- THIS IS THE FIX ---
        // An empty array tells DataTables to respect the initial order from the HTML/PHP
        "order": [], 
        "language": {
            "emptyTable": "No cancellation requests found."
        }
    });

    // --- EVENT HANDLER FOR ACTION BUTTONS (This code is correct) ---
    $('#cancellations-table tbody').on('click', '.action-btn', async function() {
        const button = $(this);
        const action = button.data('action');
        const cancellationId = button.data('id');
        
        let swalConfig = {};

        if (action === 'mark_refunded') {
            swalConfig = {
                title: 'Mark as Refunded',
                html: `<p class="text-start mb-3">Enter the payment gateway's refund ID to confirm.</p><input id="swal-refund-id" class="swal2-input" placeholder="Refund Transaction ID (Optional)">`,
                confirmButtonText: 'Confirm Refund',
                confirmButtonColor: '#198754',
                showCancelButton: true,
                focusConfirm: false,
                preConfirm: () => {
                    return { gateway_refund_id: document.getElementById('swal-refund-id').value }
                }
            };
        } else if (action === 'mark_failed') {
            swalConfig = {
                title: 'Mark as Failed',
                html: `
                    <p class="text-start mb-3">Select or enter a reason for the failure.</p>
                    <select id="swal-reason-select" class="swal2-select">
                        <option value="Technical error during processing.">Technical error</option>
                        <option value="Cancellation policy violation.">Policy violation</option>
                        <option value="Invalid bank details provided.">Invalid bank details</option>
                        <option value="custom">Other (Specify below)</option>
                    </select>
                    <input id="swal-reason-custom" class="swal2-input" placeholder="Custom reason" style="display:none; margin-top:10px;">
                `,
                confirmButtonText: 'Confirm Failure',
                confirmButtonColor: '#dc3545',
                showCancelButton: true,
                focusConfirm: false,
                didOpen: () => {
                    const select = document.getElementById('swal-reason-select');
                    const customInput = document.getElementById('swal-reason-custom');
                    select.addEventListener('change', () => {
                        if (select.value === 'custom') {
                            customInput.style.display = 'block';
                            customInput.focus();
                        } else {
                            customInput.style.display = 'none';
                        }
                    });
                },
                preConfirm: () => {
                    const select = document.getElementById('swal-reason-select');
                    const customInput = document.getElementById('swal-reason-custom');
                    let reason = select.value;
                    if (reason === 'custom') {
                        reason = customInput.value.trim();
                        if (!reason) {
                            Swal.showValidationMessage('Please enter a custom reason.');
                            return false;
                        }
                    }
                    return { reason: reason };
                }
            };
        }

        const { value: formValues } = await Swal.fire(swalConfig);

        if (formValues) {
            button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            
            const postData = {
                action: action,
                cancellation_id: cancellationId,
                ...formValues 
            };

            $.ajax({
                url: 'function/backend/cancellation_actions.php',
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                        button.prop('disabled', false).html(action === 'mark_refunded' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Could not connect to the server.', 'error');
                    button.prop('disabled', false).html(action === 'mark_refunded' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>');
                }
            });
        }
    });
});
</script>
</body>
</html>