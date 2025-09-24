<?php
// cancellations.php
include_once('function/_db.php');
session_security_check();
check_permission('can_manage_cancellations');

try {
    // 1. CONSULTA SQL ACTUALIZADA para incluir datos de contacto de la reserva
    $stmt = $_conn_db->query("
        SELECT
            c.cancellation_id,
            c.amount_refunded,
            c.cancellation_reason,
            c.status AS refund_status,
            c.created_at AS cancelled_on,
            b.ticket_no,
            b.travel_date,
            b.contact_name,      -- AÑADIDO
            b.contact_email,     -- AÑADIDO
            b.contact_mobile,    -- AÑADIDO
            p.passenger_name,
            p.seat_code,
            p.fare AS original_passenger_fare,
            t.gateway_payment_id AS gateway_refund_id
        FROM cancellations AS c
        JOIN bookings AS b ON c.booking_id = b.booking_id
        LEFT JOIN passengers AS p ON c.passenger_id = p.passenger_id -- LEFT JOIN es más seguro aquí
        LEFT JOIN transactions AS t ON c.booking_id = t.booking_id AND t.payment_status = 'CAPTURED'
        WHERE c.status = 'PENDING'
        ORDER BY c.created_at DESC
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
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.11.3/b-2.1.1/b-html5-2.1.1/b-print-2.1.1/datatables.min.css"/>
    <style>
        /* General Theme */
        body { background-color: #f8f9fa; color: #343a40; }
        .container-fluid { padding-top: 2rem; padding-bottom: 2rem; }
        h2.my-4 { font-weight: 700; color: #212529; }
        .card { border-radius: 1rem; box-shadow: 0 8px 30px rgba(0,0,0,0.07); border: none; background-color: #ffffff; }
        .card-header {
            background-color: #ffffff; font-weight: 600; padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #dee2e6; font-size: 1.1rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .card-header a { font-size: 0.85rem; }

        /* DataTable Styles */
        .table thead th {
            text-transform: uppercase; font-size: 0.8rem; background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600;
        }
        .table td { vertical-align: middle; }
        .actions-cell .btn { margin-left: 5px; }
        .fare-column .original-fare { font-weight: 500; color: #6c757d; font-size: 0.9rem; }
        .status-badge-table { font-size: 0.8em; padding: 0.4em 0.7em; border-radius: 15px; }
        .status-badge-table.bg-warning { background-color: #fff3cd !important; color: #664d03 !important; border: 1px solid #ffecb5; }
        .booking-contact-info { font-size: 0.8rem; color: #555; }
        .booking-contact-info .fas { color: #0d6efd; margin-right: 5px; }

        /* Compact Card Styling */
        .cancellation-card-sm {
            border: 1px solid #e0e0e0; border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 1rem;
        }
        .cancellation-card-sm .card-body { padding: 0.75rem 1rem; }
        .card-header-compact-cancellation {
            display: flex; justify-content: space-between; align-items: flex-start;
            padding-bottom: 0.75rem; border-bottom: 1px dashed #dee2e6; margin-bottom: 0.75rem;
        }
        .cancellation-passenger-name { font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }
        .cancellation-ticket-no { font-size: 0.75rem; color: #6c757d; display: flex; align-items: center; gap: 0.5rem; }
        .cancellation-passenger-name .fas, .cancellation-ticket-no .fas { font-size: 0.85rem; color: #0d6efd; }
        .status-badge-card.bg-warning { background-color: #fff3cd !important; color: #664d03 !important; border: 1px solid #ffecb5; }
        .cancellation-details-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin-bottom: 0.75rem; }
        .detail-label-compact { font-size: 0.65rem; color: #6c757d; }
        .detail-value-compact { font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 0.3rem; }
        .detail-value-compact .fas { font-size: 0.7rem; }
        .detail-value-compact-fare { color: #0d6efd; font-weight: 600; }
        .cancellation-reason-id, .cancellation-booking-contact { margin-bottom: 0.75rem; } /* Added */
        .detail-value-compact-text { font-size: 0.85rem; color: #495057; line-height: 1.3; }
        .detail-value-compact-text .fas { margin-right: 5px; color: #0d6efd; } /* Added */
        .cancelled-on-date { font-size: 0.7rem; color: #6c757d; font-weight: 500; }
        .cancellation-card-sm .card-footer {
            background-color: #f8f9fa; padding: 0.75rem 1rem;
            display: flex; justify-content: flex-end; gap: 0.5rem;
        }
        .cancellation-card-sm .card-footer .btn { font-size: 0.75rem; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Cancellations & Refunds</h2>
            
            <div class="card d-none d-md-block">
                <div class="card-header">
                    <h5>Pending Cancellation Requests</h5>
                    <a href="cancellation_history.php">View History</a>
                </div>
                <div class="card-body">
                    <table id="cancellations-table" class="display table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Passenger / Booking Info</th>
                                <th>Journey Date</th>
                                <th class="text-end">Fare / Refund</th>
                                <th class="text-center">Status</th>
                                <th>Reason</th>
                                <th>Requested On</th>
                                <th class="no-export text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cancellations)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-check-circle fa-2x mb-2 text-success"></i><p class="mb-0">No pending cancellation requests.</p></td></tr>
                            <?php else: ?>
                                <?php foreach ($cancellations as $c): ?>
                                    <tr id="cancellation-row-<?php echo $c['cancellation_id']; ?>">
                                        <td>
                                            <strong><?php echo htmlspecialchars($c['passenger_name'] ?? '[Deleted Passenger]'); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($c['ticket_no']); ?></small>
                                            
                                            <!-- 2. VISTA DE TABLA ACTUALIZADA CON DATOS DE CONTACTO -->
                                            <div class="mt-2 pt-2 border-top">
                                                <div class="booking-contact-info">
                                                    <strong>Booked By:</strong><br>
                                                    <?php if (!empty($c['contact_name'])): ?>
                                                        <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($c['contact_name']); ?></span><br>
                                                    <?php endif; ?>
                                                    <?php if (!empty($c['contact_mobile'])): ?>
                                                        <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($c['contact_mobile']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo date('d M, Y', strtotime($c['travel_date'])); ?></td>
                                        <td class="text-end fare-column">
                                            <div class="original-fare" title="Original Ticket Price">
                                                Pay: ₹<?php echo number_format($c['original_passenger_fare'], 2); ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge status-badge-table bg-warning text-dark"><?php echo htmlspecialchars($c['refund_status']); ?></span>
                                        </td>
                                        <td><small><?php echo htmlspecialchars($c['cancellation_reason'] ?: 'N/A'); ?></small></td>
                                        <td><?php echo date('d M Y, h:i A', strtotime($c['cancelled_on'])); ?></td>
                                        <td class="text-end actions-cell">
                                            <button class="btn btn-sm btn-success action-btn" data-action="mark_refunded" data-id="<?php echo $c['cancellation_id']; ?>" title="Mark as Refunded"><i class="fas fa-check-circle"></i></button>
                                            <button class="btn btn-sm btn-danger action-btn" data-action="mark_failed" data-id="<?php echo $c['cancellation_id']; ?>" title="Mark as Failed"><i class="fas fa-times-circle"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cancellation Cards (Visible on small screens only) -->
            <div class="d-md-none mt-4 cancellation-list-cards">
                <div class="card-header">
                    <h5>Pending Cancellation Requests</h5>
                    <a href="cancellation_history.php">View History</a>
                </div>
                <div class="card-body pt-3 pb-0">
                    <?php if (empty($cancellations)): ?>
                        <div class="alert alert-success text-center py-4 mb-0" role="alert"><i class="fas fa-check-circle fa-2x mb-2"></i><p class="mb-0">No pending cancellation requests.</p></div>
                    <?php else: ?>
                        <div class="row row-cols-1 g-3">
                            <?php foreach ($cancellations as $c): ?>
                                <div class="col" id="cancellation-card-<?php echo $c['cancellation_id']; ?>">
                                    <div class="card cancellation-card-sm">
                                        <div class="card-body">
                                            <div class="card-header-compact-cancellation">
                                                <div class="cancellation-passenger-info">
                                                    <h6 class="mb-0 cancellation-passenger-name"><i class="fas fa-user-circle"></i><?php echo htmlspecialchars($c['passenger_name'] ?? '[Deleted Passenger]'); ?></h6>
                                                    <small class="text-muted cancellation-ticket-no mt-1"><i class="fas fa-ticket-alt"></i><?php echo htmlspecialchars($c['ticket_no']); ?></small>
                                                </div>
                                                <span class="badge status-badge-card bg-warning text-dark"><?php echo htmlspecialchars($c['refund_status']); ?></span>
                                            </div>

                                            <div class="cancellation-details-grid">
                                                <div class="detail-item-compact">
                                                    <small class="detail-label-compact">Journey Date</small>
                                                    <span class="detail-value-compact"><i class="fas fa-calendar-alt"></i><?php echo date('d M, Y', strtotime($c['travel_date'])); ?></span>
                                                </div>
                                                <div class="detail-item-compact text-end">
                                                    <small class="detail-label-compact">Original Fare</small>
                                                    <span class="detail-value-compact detail-value-compact-fare justify-content-end"><i class="fas fa-rupee-sign"></i><?php echo number_format($c['original_passenger_fare'], 2); ?></span>
                                                </div>
                                            </div>
                                            
                                            <!-- 3. VISTA DE TARJETA MÓVIL ACTUALIZADA CON DATOS DE CONTACTO -->
                                            <div class="cancellation-booking-contact">
                                                <small class="detail-label-compact">Booking Contact</small>
                                                <p class="detail-value-compact-text mb-0">
                                                    <?php if (!empty($c['contact_name'])): ?>
                                                        <div><i class="fas fa-user-circle fa-fw"></i> <?php echo htmlspecialchars($c['contact_name']); ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($c['contact_mobile'])): ?>
                                                        <div><i class="fas fa-phone fa-fw"></i> <?php echo htmlspecialchars($c['contact_mobile']); ?></div>
                                                    <?php endif; ?>
                                                     <?php if (!empty($c['contact_email'])): ?>
                                                        <div><i class="fas fa-envelope fa-fw"></i> <?php echo htmlspecialchars($c['contact_email']); ?></div>
                                                    <?php endif; ?>
                                                </p>
                                            </div>

                                            <div class="cancellation-reason-id">
                                                <small class="detail-label-compact">Reason for Cancellation</small>
                                                <p class="detail-value-compact-text mb-0"><?php echo htmlspecialchars($c['cancellation_reason'] ?: 'N/A'); ?></p>
                                            </div>

                                            <div class="text-muted text-end mt-2">
                                                <small class="detail-label-compact d-block">Requested On</small>
                                                <small class="cancelled-on-date"><?php echo date('d M Y, h:i A', strtotime($c['cancelled_on'])); ?></small>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button class="btn btn-sm btn-success action-btn-card" data-action="mark_refunded" data-id="<?php echo $c['cancellation_id']; ?>" title="Mark as Refunded"><i class="fas fa-check-circle me-1"></i> Refund</button>
                                            <button class="btn btn-sm btn-danger action-btn-card" data-action="mark_failed" data-id="<?php echo $c['cancellation_id']; ?>" title="Mark as Failed"><i class="fas fa-times-circle me-1"></i> Fail</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "foot.php"; ?>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.11.3/b-2.1.1/b-html5-2.1.1/b-print-2.1.1/datatables.min.js"></script>
<script>
$(document).ready(function() {
    if ($('#cancellations-table').is(':visible')) {
        $('#cancellations-table').DataTable({
            "dom": 'Bfrtip',
            "buttons": ['copyHtml5', 'csvHtml5', 'excelHtml5', 'pdfHtml5', 'print'],
            "pageLength": 25,
            "order": [],
            "language": { "emptyTable": "No pending cancellation requests." }
        });
    }

    $(document).on('click', '.action-btn, .action-btn-card', async function() {
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
                preConfirm: () => ({ gateway_refund_id: document.getElementById('swal-refund-id').value })
            };
        } else if (action === 'mark_failed') {
            swalConfig = {
                title: 'Mark as Failed',
                html: `<p class="text-start mb-3">Select or enter a reason for the failure.</p>
                       <select id="swal-reason-select" class="swal2-select">
                           <option value="Technical error during processing.">Technical error</option>
                           <option value="Cancellation policy violation.">Policy violation</option>
                           <option value="Invalid bank details provided.">Invalid bank details</option>
                           <option value="custom">Other (Specify below)</option>
                       </select>
                       <input id="swal-reason-custom" class="swal2-input" placeholder="Custom reason" style="display:none; margin-top:10px;">`,
                confirmButtonText: 'Confirm Failure',
                confirmButtonColor: '#dc3545',
                showCancelButton: true,
                focusConfirm: false,
                didOpen: () => {
                    const select = document.getElementById('swal-reason-select');
                    const customInput = document.getElementById('swal-reason-custom');
                    select.addEventListener('change', () => {
                        customInput.style.display = (select.value === 'custom') ? 'block' : 'none';
                        if (select.value === 'custom') customInput.focus();
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
            const postData = { action: action, cancellation_id: cancellationId, ...formValues };

            $.ajax({
                url: 'function/backend/cancellation_actions.php',
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Success!', response.message, 'success').then(() => window.location.reload());
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