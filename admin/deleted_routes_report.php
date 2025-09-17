<?php
// deleted_routes_report.php
include_once('function/_db.php');
session_security_check();
// You can create a new permission like 'can_view_deleted_reports' if needed
check_permission('main_admin'); 

try {
    // This query fetches routes deleted in the last 30 days.
    // It also joins with the 'admin' and 'buses' tables to get readable names.
    $stmt = $_conn_db->query("
        SELECT 
            dr.*,
            a.name as deleted_by_name,
            b.bus_name
        FROM deleted_routes dr
        LEFT JOIN admin a ON dr.deleted_by_employee_id = a.id
        LEFT JOIN buses b ON dr.bus_id = b.bus_id
        WHERE dr.deleted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY dr.deleted_at DESC
    ");
    $deleted_routes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Deleted Routes Log (Last 30 Days)</title>
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
            <h2 class="my-4">Deleted Routes Log</h2>
            <p class="text-muted">Showing all routes that have been archived and deleted in the last 30 days.</p>

            <div class="card">
                <div class="card-header">
                    <h5>Archived Route Records</h5>
                </div>
                <div class="card-body">
                    <table id="deleted-routes-table" class="display table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Route Name</th>
                                <th>Journey</th>
                                <th>Assigned Bus</th>
                                <th>Deleted By</th>
                                <th>Deleted At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deleted_routes as $route): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($route['route_name']); ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($route['starting_point']); ?> → <?php echo htmlspecialchars($route['ending_point']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($route['bus_name'] ?? 'Bus ID: ' . $route['bus_id']); ?></td>
                                    <td><?php echo htmlspecialchars($route['deleted_by_name'] ?? 'Unknown User'); ?></td>
                                    <td><?php echo date('d M Y, h:i A', strtotime($route['deleted_at'])); ?></td>
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
    $('#deleted-routes-table').DataTable({
        "dom": 'Bfrtip', // Enable Buttons, Filtering, Pagination, etc.
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        "pageLength": 25,
        "order": [[ 4, "desc" ]], // Sort by "Deleted At" date descending by default
        "language": {
            "emptyTable": "No routes have been deleted in the last 30 days."
        }
    });
});
</script>
</body>
</html>