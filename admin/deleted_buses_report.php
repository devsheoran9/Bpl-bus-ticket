<?php
// deleted_buses_report.php
include_once('function/_db.php');
session_security_check();
// You might want a specific permission here, but main_admin is also fine.
check_permission('main_admin');

try {
    // This query fetches buses deleted in the last 30 days and joins with the admin table
    // to get the name of the employee who deleted it.
    $stmt = $_conn_db->query("
        SELECT 
            db.*,
            a.name as deleted_by_name
        FROM deleted_buses db
        LEFT JOIN admin a ON db.deleted_by_employee_id = a.id
        WHERE db.deleted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY db.deleted_at DESC
    ");
    $deleted_buses = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Deleted Buses Log (Last 30 Days)</title>
    <style>
        .table th {
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        .bus-info {
            font-weight: 500;
        }
        .bus-info .reg-no {
            font-weight: 400;
            font-size: 0.9em;
            color: #6c757d;
        }
        .dataTables_wrapper .dt-buttons {
             margin-bottom: 1rem;
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Deleted Buses Log</h2>
            <p class="text-muted">Showing all buses that have been deleted in the last 30 days.</p>

            <div class="card">
                <div class="card-header">
                    <h5>Archived Bus Records</h5>
                </div>
                <div class="card-body">
                    <table id="deleted-buses-table" class="display table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Bus Details</th>
                                <th>Registration No.</th>
                                <th>Engine No.</th>
                                <th>Chassis No.</th>
                                <th>Deleted By</th>
                                <th>Deleted At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deleted_buses as $bus): ?>
                                <tr>
                                    <td>
                                        <div class="bus-info">
                                            <?php echo htmlspecialchars($bus['bus_name']); ?>
                                            <div class="reg-no"><?php echo htmlspecialchars($bus['bus_type']); ?></div>
                                        </div>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($bus['registration_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($bus['engine_no'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($bus['chassis_no'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($bus['deleted_by_name'] ?? 'Unknown User'); ?></td>
                                    <td><?php echo date('d M Y, h:i A', strtotime($bus['deleted_at'])); ?></td>
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
    // Initialize DataTables on the report table
    $('#deleted-buses-table').DataTable({
        "dom": 'Bfrtip', // Enable Buttons, Filtering, etc.
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        "pageLength": 25,
        "order": [[ 5, "desc" ]], // Sort by "Deleted At" date descending by default
        "language": {
            "emptyTable": "No buses have been deleted in the last 30 days."
        }
    });
});
</script>
</body>
</html>