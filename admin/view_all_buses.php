<?php
global $_conn_db;
include_once('function/_db.php');
// check_user_login();
session_security_check(); 

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('head.php');?>
    <!-- DataTables CSS (agar head.php mein pehle se nahi hai) -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .actions-btn-group .btn {
            margin-right: 5px;
        }
    </style>
</head>
<body>

<div id="wrapper">
    <?php include_once('sidebar.php');?>
    <div class="main-content">
        <?php include_once('header.php');?>
        <div class="container-fluid">
            
            <div class="page-header mt-4">
                <h2 class="mb-0">All Buses</h2>
                <a href="add_bus.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Bus</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="busesTable" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Bus Name</th>
                                    <th>Reg. Number</th>
                                    <th>Operator</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                try {
                                    $sql = "SELECT b.*, o.operator_name 
                                            FROM buses b
                                            LEFT JOIN operators o ON b.operator_id = o.operator_id
                                            ORDER BY b.bus_id DESC";
                                    $stmt = $_conn_db->query($sql);
                                    
                                    while ($bus = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $status_badge = 'bg-secondary'; // Default
                                        if ($bus['status'] == 'Active') $status_badge = 'bg-success';
                                        elseif ($bus['status'] == 'Inactive') $status_badge = 'bg-warning text-dark';
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($bus['bus_id']); ?></td>
                                        <td><?php echo htmlspecialchars($bus['bus_name']); ?></td>
                                        <td><?php echo htmlspecialchars($bus['registration_number']); ?></td>
                                        <td><?php echo htmlspecialchars($bus['operator_name'] ?? 'N/A'); ?></td>
                                        <td><span class="badge <?php echo $status_badge; ?>"><?php echo htmlspecialchars($bus['status']); ?></span></td>
                                        <td class="actions-btn-group">
                                            <a href="edit_bus.php?bus_id=<?php echo $bus['bus_id']; ?>" class="btn btn-sm btn-warning" title="Edit Bus">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="manage_seats.php?bus_id=<?php echo $bus['bus_id']; ?>" class="btn btn-sm btn-info" title="Manage Seats">
                                                <i class="fas fa-chair"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger delete-bus-btn" 
                                                    data-bus-id="<?php echo $bus['bus_id']; ?>" 
                                                    data-bus-name="<?php echo htmlspecialchars($bus['bus_name']); ?>"
                                                    title="Delete Bus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php } } catch (PDOException $e) { /* Error handling */ } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include_once('foot.php');?>
<!-- DataTables & SweetAlert2 JS (agar foot.php mein nahi hain) -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    const busesTable = $('#busesTable').DataTable({ "order": [[0, "desc"]] });

    $('#busesTable').on('click', '.delete-bus-btn', function() {
        const busId = $(this).data('bus-id');
        const busName = $(this).data('bus-name');
        const row = $(this).closest('tr');

        Swal.fire({
            title: `Delete "${busName}"?`,
            text: "This action cannot be undone! All associated seats will also be deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'function/backend/bus_actions.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { action: 'delete_bus', bus_id: busId },
                    success: function(response) {
                        if (response.res === 'true') {
                            $.notify({ title: response.notif_title, message: response.notif_desc }, { type: 'success' });
                            busesTable.row(row).remove().draw(false);
                        } else {
                            Swal.fire('Error!', response.notif_desc, 'error');
                        }
                    },
                    error: function() { Swal.fire('Error!', 'Could not connect to the server.', 'error'); }
                });
            }
        });
    });
});
</script>

</body>
</html>
<?php pdo_close_conn($_conn_db); ?>