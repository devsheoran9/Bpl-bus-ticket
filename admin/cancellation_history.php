<?php
// cancellation_history.php
include_once('function/_db.php');
session_security_check();
check_permission('can_manage_cancellations');

try {
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
    <!-- DataTables CSS (only for larger screens) -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.11.3/b-2.1.1/b-html5-2.1.1/b-print-2.1.1/datatables.min.css"/>
    <style>
        /* General White Theme & Layout */
        body { background-color: #f8f9fa; color: #343a40; }
        .container-fluid { padding-top: 2rem; padding-bottom: 2rem; }
        h2.my-4 { font-weight: 700; color: #212529; }
        .card { border-radius: 1rem; box-shadow: 0 8px 30px rgba(0,0,0,0.07); border: none; background-color: #ffffff; }
        .card-header {
            background-color: #ffffff; font-weight: 600; padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #dee2e6; font-size: 1.1rem;
            display: flex; justify-content: space-between; align-items: center;
        }

        /* DataTable Specific Styles (for larger screens) */
        .table thead th {
            text-transform: uppercase; font-size: 0.8rem; background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600;
        }
        .table td { vertical-align: middle; padding: 0.75rem 1rem; }
        .fare-column .original-fare { font-weight: 500; color: #6c757d; font-size: 0.9rem; }
        .fare-column .refund-amount { font-weight: bold; color: #dc3545; font-size: 1rem; }
        .status-badge-table { font-size: 0.8em; padding: 0.4em 0.7em; border-radius: 15px; }
        .status-badge-table.bg-success { background-color: #d1e7dd !important; color: #0f5132 !important; border: 1px solid #badbcc; }
        .status-badge-table.bg-danger { background-color: #f8d7da !important; color: #842029 !important; border: 1px solid #f5c2c7; }
        .dataTables_wrapper .dataTables_filter { margin-bottom: 1rem; float: right; }
        .dataTables_wrapper .dt-buttons { margin-bottom: 1rem; float: left; }
        .dataTables_wrapper .dataTables_filter input { border-radius: 0.5rem; border: 1px solid #ced4da; padding: 0.5rem 0.75rem; }
        .dataTables_wrapper .dt-buttons .btn {
            margin-right: 0.5rem; border-radius: 0.5rem; font-size: 0.85rem; padding: 0.4rem 0.8rem;
            background-color: #6c757d; color: white; border-color: #6c757d;
        }

        /* --- CUSTOM COMPACT CARD STYLING (for smaller screens) --- */
        .history-card-sm {
            background-color: white;
            border: 1px solid #e0e0e0;
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); /* Subtler shadow */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 1rem; /* Space between cards */
        }
        .history-card-sm:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        .history-card-sm .card-body {
            padding: 0.75rem 1rem; /* Reduced padding */
        }

        /* Card Header section */
        .card-header-compact-history {
            display: flex; justify-content: space-between; align-items: flex-start;
            padding-bottom: 0.75rem; border-bottom: 1px dashed #dee2e6; margin-bottom: 0.75rem;
        }
        .history-passenger-info { flex-grow: 1; }
        .history-passenger-name {
            font-size: 1rem; font-weight: 600; margin-bottom: 0.1rem; color: #212529; line-height: 1.2;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .history-ticket-no {
            font-size: 0.75rem; color: #6c757d; line-height: 1.2;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .history-passenger-name .fas, .history-ticket-no .fas { font-size: 0.85rem; color: #0d6efd; }

        .status-badge-card-history {
            font-size: 0.75rem; padding: 0.3em 0.6em; border-radius: 15px;
            white-space: nowrap; line-height: 1;
        }
        .status-badge-card-history.bg-success { background-color: #d1e7dd !important; color: #0f5132 !important; border: 1px solid #badbcc; }
        .status-badge-card-history.bg-danger { background-color: #f8d7da !important; color: #842029 !important; border: 1px solid #f5c2c7; }

        /* Details grid for main info */
        .history-details-grid {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin-bottom: 0.75rem;
        }
        .detail-item-compact-history { display: flex; flex-direction: column; }
        .detail-label-compact-history { font-size: 0.65rem; color: #6c757d; margin-bottom: 0.1rem; line-height: 1; }
        .detail-value-compact-history {
            font-size: 0.85rem; font-weight: 500; color: #212529;
            display: flex; align-items: center; gap: 0.3rem; line-height: 1.2;
        }
        .detail-value-compact-history .fas { font-size: 0.7rem; }
        .detail-value-original-fare { color: #0d6efd; font-weight: 600; }
        .detail-value-refund-amount { color: #dc3545; font-weight: 600; }

        /* Reason/Refund ID and Processed On */
        .history-reason-id { margin-bottom: 0.75rem; }
        .detail-value-compact-history-text { font-size: 0.85rem; color: #495057; line-height: 1.3; }
        .processed-on-date { font-size: 0.7rem; color: #6c757d; font-weight: 500; }

        /* No results for cards */
        .history-list-cards .alert {
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-radius: 0.75rem;
            margin-bottom: 0;
        }

        /* Responsive adjustments for overall layout */
        @media (max-width: 767.98px) {
            .container-fluid { padding-top: 1rem; padding-bottom: 1rem; }
            h2.my-4 { font-size: 1.5rem; margin-top: 1rem; margin-bottom: 1rem; }

            .card-header { padding: 1rem; font-size: 1rem; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Cancellation & Refund History</h2>
            
            <!-- History Table (Visible on medium screens and up) -->
            <div class="card d-none d-md-block">
                <div class="card-header">
                    <h5>Complete Log of All Processed Cancellations</h5>
                </div>
                <div class="card-body">
                    <table id="history-table" class="display table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Passenger / Ticket</th>
                                <th>Journey Date</th>
                                <th class="text-end">Fare / Refunded</th>
                                <th class="text-center">Final Status</th>
                                <th>Reason / Refund ID</th>
                                <th>Processed On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_cancellations)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                                        <p class="mb-0">No cancellation requests found.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($all_cancellations as $c): 
                                    $status = $c['refund_status'];
                                    $badge_class = ($status === 'COMPLETED') ? 'status-badge-table bg-success' : 'status-badge-table bg-danger';
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($c['passenger_name'] ?? '[Deleted Passenger]'); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($c['ticket_no']); ?></small>
                                        </td>
                                        <td><?php echo date('d M, Y', strtotime($c['travel_date'])); ?></td>
                                        
                                        <td class="text-end fare-column">
                                            <?php if ($status === 'FAILED'): ?>
                                                <div class="original-fare" title="Original Ticket Price">
                                                    Paid: ₹<?php echo number_format($c['original_passenger_fare'] ?? 0, 2); ?>
                                                </div>
                                            <?php elseif ($status === 'COMPLETED'): ?>
                                                <div class="refund-amount" title="Amount Refunded">
                                                    Refunded: ₹<?php echo number_format($c['amount_refunded'], 2); ?>
                                                </div>
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
                <div class="card-header">
                    <h5>Complete Log of All Processed Cancellations</h5>
                </div>
                <div class="card-body pt-3 pb-0"> <!-- Added top padding for first card if any -->
                    <?php if (empty($all_cancellations)): ?>
                        <div class="alert alert-info text-center py-4 mb-0" role="alert">
                            <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                            <p class="mb-0">No cancellation requests found.</p>
                        </div>
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
                                                    <h6 class="mb-0 history-passenger-name"><i class="fas fa-user-circle"></i><?php echo htmlspecialchars($c['passenger_name'] ?? '[Deleted Passenger]'); ?></h6>
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
                                                    <small class="detail-label-compact-history">
                                                        <?php echo ($status === 'FAILED') ? 'Original Fare' : 'Refunded Amount'; ?>
                                                    </small>
                                                    <span class="detail-value-compact-history <?php echo ($status === 'FAILED') ? 'detail-value-original-fare' : 'detail-value-refund-amount'; ?>">
                                                        <i class="fas fa-rupee-sign"></i>
                                                        <?php 
                                                            echo number_format(
                                                                ($status === 'FAILED' ? ($c['original_passenger_fare'] ?? 0) : $c['amount_refunded']), 
                                                                2
                                                            ); 
                                                        ?>
                                                    </span>
                                                </div>
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
<!-- DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.11.3/b-2.1.1/b-html5-2.1.1/b-print-2.1.1/datatables.min.js"></script>
<script>
$(document).ready(function() {
    // --- INITIALIZE DATATABLES (only for table on larger screens) ---
    // Check if the DataTable element is visible before initializing.
    if ($('#history-table').is(':visible')) {
        $('#history-table').DataTable({
            "dom": 'Bfrtip',
            "buttons": [
                { extend: 'copyHtml5', exportOptions: { columns: ':not(.no-export)' }, title: 'Cancellation History Report' },
                { extend: 'csvHtml5', exportOptions: { columns: ':not(.no-export)' }, filename: `Cancellation-History-${new Date().toISOString().slice(0,10)}` },
                { extend: 'excelHtml5', exportOptions: { columns: ':not(.no-export)' }, filename: `Cancellation-History-${new Date().toISOString().slice(0,10)}` },
                { extend: 'pdfHtml5', exportOptions: { columns: ':not(.no-export)' }, filename: `Cancellation-History-${new Date().toISOString().slice(0,10)}` },
                { extend: 'print', exportOptions: { columns: ':not(.no-export)' }, title: 'Cancellation History Report' }
            ],
            "pageLength": 25,
            "order": [[ 5, "desc" ]], // Sort by "Processed On" date
            "language": {
                "emptyTable": "No cancellation history found."
            }
        });
    }
    // No action buttons here, so no action-btn JS needed for cards.
});
</script>
</body>
</html>