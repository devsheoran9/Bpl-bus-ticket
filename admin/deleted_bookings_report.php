<?php
// deleted_bookings_report.php
include_once('function/_db.php');
session_security_check();
check_permission('can_view_reports'); 

try {
    // --- QUERY HAS BEEN MODIFIED ---
    $stmt = $_conn_db->query("
        SELECT 
            db.*,
            deleter.name as deleted_by_name,
            COALESCE(booker_admin.name, booker_user.username) as booked_by_name,
            CASE 
                WHEN db.booked_by_employee_id IS NOT NULL THEN 'Employee'
                WHEN db.user_id IS NOT NULL THEN 'Online User'
                ELSE 'Guest' 
            END as booker_type
        FROM 
            deleted_bookings db
            LEFT JOIN admin deleter ON db.deleted_by_employee_id = deleter.id
            LEFT JOIN admin booker_admin ON db.booked_by_employee_id = booker_admin.id
            LEFT JOIN users booker_user ON db.user_id = booker_user.id
        -- THIS IS THE NEW LINE THAT FILTERS THE DATA --
        WHERE db.deleted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY 
            db.deleted_at DESC
    ");
    $deleted_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Deleted Bookings Log (Last 30 Days)</title>
    <style>
        .table th { text-transform: uppercase; font-size: 0.8rem; }
        .dataTables_wrapper .dt-buttons { margin-bottom: 1rem; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Deleted Bookings Log</h2>
            <p class="text-muted">Showing all bookings archived and deleted in the last 30 days.</p>

            <div class="card">
                <div class="card-header">
                    <h5>Archived Booking Records</h5>
                </div>
                <div class="card-body">
                    <table id="deleted-bookings-table" class="display table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Journey</th>
                                <th class="text-end">Fare</th>
                                <th>Originally Booked By</th>
                                <th>Deleted By</th>
                                <th>Deleted At</th> 
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deleted_bookings as $booking): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($booking['ticket_no']); ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['origin']); ?> → <?php echo htmlspecialchars($booking['destination']); ?>
                                        <br>
                                        <small class="text-muted"><?php echo date('d M, Y', strtotime($booking['travel_date'])); ?></small>
                                    </td>
                                    <td class="text-end fw-bold">₹<?php echo number_format($booking['total_fare'], 2); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['booked_by_name'] ?? 'N/A'); ?>
                                        <br>
                                        <small class="text-muted">(<?php echo $booking['booker_type']; ?>)</small>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['deleted_by_name'] ?? 'Unknown User'); ?></td>
                                    <td><?php echo date('d M Y, h:i A', strtotime($booking['deleted_at'])); ?></td>
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
    $('#deleted-bookings-table').DataTable({
        "dom": 'Bfrtip',
        "buttons": [ 'copy', 'csv', 'excel', 'pdf', 'print' ],
        "pageLength": 25,
        "order": [[ 5, "desc" ]],
        "language": {
            "emptyTable": "No bookings have been deleted in the last 30 days."
        }
    });
});
</script>
</body>
</html>