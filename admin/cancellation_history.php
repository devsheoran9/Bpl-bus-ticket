<?php
// cancellation_history.php
include_once('function/_db.php');
session_security_check();
check_permission('can_manage_cancellations');

try {
    // 1. CONSULTA SQL ACTUALIZADA: Se añaden los campos de contacto de la tabla 'bookings'
    $stmt = $_conn_db->query("
        SELECT
            c.cancellation_id,
            c.amount_refunded,
            c.cancellation_reason,
            c.status AS refund_status,
            c.created_at AS cancelled_on,
            c.gateway_refund_id,
            b.ticket_no,
            b.travel_date,
            b.contact_name,      -- AÑADIDO
            b.contact_email,     -- AÑADIDO
            b.contact_mobile,    -- AÑADIDO
            p.passenger_name,
            p.seat_code,
            p.fare AS original_passenger_fare
        FROM cancellations AS c
        JOIN bookings AS b ON c.booking_id = b.booking_id
        LEFT JOIN passengers AS p ON c.passenger_id = p.passenger_id
        WHERE c.status IN ('COMPLETED', 'FAILED')
        ORDER BY c.created_at DESC
    ");
    $all_cancellations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: ". $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Cancellation & Refund History</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.11.3/b-2.1.1/b-html5-2.1.1/b-print-2.1.1/datatables.min.css"/>
    <style>
        /* General Theme & Layout */
        body { background-color: #f8f9fa; color: #343a40; }
        .container-fluid { padding-top: 2rem; padding-bottom: 2rem; }
        h2.my-4 { font-weight: 700; color: #212529; }
        .card { border-radius: 1rem; box-shadow: 0 8px 30px rgba(0,0,0,0.07); border: none; background-color: #ffffff; }
        .card-header {
            background-color: #ffffff; font-weight: 600; padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #dee2e6; font-size: 1.1rem;
            display: flex; justify-content: space-between; align-items: center;
        }

        /* DataTable Styles */
        .table thead th {
            text-transform: uppercase; font-size: 0.8rem; background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600;
        }
        .table td { vertical-align: middle; }
        .fare-column .original-fare { font-weight: 500; color: #6c757d; font-size: 0.9rem; }
        .fare-column .refund-amount { font-weight: bold; color: #dc3545; font-size: 1rem; }
        .status-badge-table { font-size: 0.8em; padding: 0.4em 0.7em; border-radius: 15px; }
        .status-badge-table.bg-success { background-color: #d1e7dd !important; color: #0f5132 !important; border: 1px solid #badbcc; }
        .status-badge-table.bg-danger { background-color: #f8d7da !important; color: #842029 !important; border: 1px solid #f5c2c7; }
        .booking-contact-info { font-size: 0.8rem; color: #555; }
        .booking-contact-info .fas { color: #0d6efd; margin-right: 5px; }

        /* Compact Card Styling */
        .history-card-sm {
            background-color: white; border: 1px solid #e0e0e0; border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 1rem;
        }
        .history-card-sm .card-body { padding: 0.75rem 1rem; }
        .card-header-compact-history {
            display: flex; justify-content: space-between; align-items: flex-start;
            padding-bottom: 0.75rem; border-bottom: 1px dashed #dee2e6; margin-bottom: 0.75rem;
        }
        .history-passenger-name { font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }
        .history-ticket-no { font-size: 0.75rem; color: #6c757d; display: flex; align-items: center; gap: 0.5rem; }
        .history-passenger-name .fas, .history-ticket-no .fas { font-size: 0.85rem; color: #0d6efd; }
        .status-badge-card-history { font-size: 0.75rem; padding: 0.3em 0.6em; border-radius: 15px; }
        .status-badge-card-history.bg-success { background-color: #d1e7dd !important; color: #0f5132 !important; border: 1px solid #badbcc; }
        .status-badge-card-history.bg-danger { background-color: #f8d7da !important; color: #842029 !important; border: 1px solid #f5c2c7; }
        .history-details-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin-bottom: 0.75rem; }
        .detail-label-compact-history { font-size: 0.65rem; color: #6c757d; }
        .detail-value-compact-history { font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 0.3rem; }
        .detail-value-compact-history .fas { font-size: 0.7rem; }
        .detail-value-original-fare { color: #0d6efd; font-weight: 600; }
        .detail-value-refund-amount { color: #dc3545; font-weight: 600; }
        .history-reason-id, .history-booking-contact { margin-bottom: 0.75rem; }
        .detail-value-compact-history-text { font-size: 0.85rem; color: #495057; line-height: 1.3; }
        .detail-value-compact-history-text .fas { margin-right: 5px; color: #0d6efd; }
        .processed-on-date { font-size: 0.7rem; color: #6c757d; font-weight: 500; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Cancellation & Refund History</h2>
            
            <div class="card d-none d-md-block">
                <div class="card-header">
                    <h5>Complete Log of All Processed Cancellations</h5>
                </div>
                <div class="card-body">
                    <table id="history-table" class="display table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Passenger / Booking Info</th>
                                <th>Journey Date</th>
                                <th class="text-end">Fare / Refunded</th>
                                <th class="text-center">Final Status</th>
                                <th>Reason / Refund ID</th>
                                <th>Processed On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_cancellations)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><p class="mb-0">No cancellation history found.</p></td></tr>
                            <?php else: ?>
                                <?php foreach ($all_cancellations as $c): 
                                    $status = $c['refund_status'];
                                    $badge_class = ($status === 'COMPLETED') ? 'status-badge-table bg-success' : 'status-badge-table bg-danger';
                                ?>
                                    <tr>
                                        <td>
                                            <!-- 2. CORREGIDO: Se usa isset() para más compatibilidad y se muestran datos del contacto -->
                                            <strong><?php echo isset($c['passenger_name']) ? htmlspecialchars($c['passenger_name']) : ''; ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($c['ticket_no']); ?></small>

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
                                            <?php if ($status === 'FAILED'): ?>
                                                <div class="original-fare" title="Original Ticket Price">User Paid: ₹<?php echo number_format($c['original_passenger_fare'] ?? 0, 2); ?></div>
                                            <?php elseif ($status === 'COMPLETED'): ?>
                                                <div class="refund-amount" title="Amount Refunded">Refunded: ₹<?php echo number_format($c['amount_refunded'], 2); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                        </td>
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
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cancellation History Cards (Visible on small screens only) -->
            <div class="d-md-none mt-4 history-list-cards">
                <div class="card-header"><h5>Complete Log of All Processed Cancellations</h5></div>
                <div class="card-body pt-3 pb-0">
                    <?php if (empty($all_cancellations)): ?>
                        <div class="alert alert-info text-center py-4 mb-0" role="alert"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><p class="mb-0">No cancellation history found.</p></div>
                    <?php else: ?>
                        <div class="row row-cols-1 g-3">
                            <?php foreach ($all_cancellations as $c): 
                                $status = $c['refund_status'];
                                $badge_class = ($status === 'COMPLETED') ? 'status-badge-card-history bg-success' : 'status-badge-card-history bg-danger';
                            ?>
                                <div class="col">
                                    <div class="card history-card-sm">
                                        <div class="card-body">
                                            <div class="card-header-compact-history">
                                                <div class="history-passenger-info">
                                                    <!-- 2. CORREGIDO: Se usa isset() para más compatibilidad -->
                                                    <h6 class="mb-0 history-passenger-name"><i class="fas fa-user-circle"></i><?php echo isset($c['passenger_name']) ? htmlspecialchars($c['passenger_name']) : ''; ?></h6>
                                                    <small class="text-muted history-ticket-no mt-1"><i class="fas fa-ticket-alt"></i><?php echo htmlspecialchars($c['ticket_no']); ?></small>
                                                </div>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                            </div>

                                            <div class="history-details-grid">
                                                <div class="detail-item-compact-history">
                                                    <small class="detail-label-compact-history">Journey Date</small>
                                                    <span class="detail-value-compact-history"><i class="fas fa-calendar-alt"></i><?php echo date('d M, Y', strtotime($c['travel_date'])); ?></span>
                                                </div>
                                                <div class="detail-item-compact-history text-end">
                                                    <small class="detail-label-compact-history"><?php echo ($status === 'FAILED') ? 'Original Fare' : 'Refunded Amount'; ?></small>
                                                    <span class="detail-value-compact-history justify-content-end <?php echo ($status === 'FAILED') ? 'detail-value-original-fare' : 'detail-value-refund-amount'; ?>">
                                                        <i class="fas fa-rupee-sign"></i>
                                                        <?php echo number_format(($status === 'FAILED' ? ($c['original_passenger_fare'] ?? 0) : $c['amount_refunded']), 2); ?>
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- 3. AÑADIDO: Bloque de información de contacto de la reserva -->
                                            <div class="history-booking-contact">
                                                <small class="detail-label-compact-history">Booking Contact</small>
                                                <p class="detail-value-compact-history-text mb-0">
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
                                            
                                            <div class="history-reason-id">
                                                <small class="detail-label-compact-history">Reason / Refund ID</small>
                                                <p class="detail-value-compact-history-text mb-0">
                                                    <?php 
                                                    if ($status === 'COMPLETED' && !empty($c['gateway_refund_id'])) {
                                                        echo 'Refund ID: <strong>' . htmlspecialchars($c['gateway_refund_id']) . '</strong>';
                                                    } else {
                                                        echo htmlspecialchars($c['cancellation_reason'] ?: 'N/A');
                                                    }
                                                    ?>
                                                </p>
                                            </div>

                                            <div class="text-muted text-end mt-2">
                                                <small class="detail-label-compact-history d-block">Processed On</small>
                                                <small class="processed-on-date"><?php echo date('d M Y, h:i A', strtotime($c['cancelled_on'])); ?></small>
                                            </div>
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
    if ($('#history-table').is(':visible')) {
        $('#history-table').DataTable({
            "dom": 'Bfrtip',
            "buttons": ['copyHtml5', 'csvHtml5', 'excelHtml5', 'pdfHtml5', 'print'],
            "pageLength": 25,
            "order": [[ 5, "desc" ]],
            "language": { "emptyTable": "No cancellation history found." }
        });
    }
});
</script>
</body>
</html>