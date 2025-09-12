<?php
// view_buses.php (Redesigned with More Details in Custom Table)
global $_conn_db;
include_once('function/_db.php');
session_security_check(); 

// A single, powerful query to get all bus details at once
try {
    $sql = "
        SELECT 
            b.*,
            (SELECT COUNT(*) FROM seats s WHERE s.bus_id = b.bus_id AND s.seat_type = 'SEATER' AND s.is_bookable = 1) as seater_count,
            (SELECT COUNT(*) FROM seats s WHERE s.bus_id = b.bus_id AND s.seat_type = 'SLEEPER' AND s.is_bookable = 1) as sleeper_count,
            (SELECT GROUP_CONCAT(r.route_name SEPARATOR '</li><li>') FROM routes r WHERE r.bus_id = b.bus_id) as assigned_routes,
            (SELECT GROUP_CONCAT(c.category_name SEPARATOR ',') 
             FROM bus_category_map map
             JOIN bus_categories c ON map.category_id = c.category_id
             WHERE map.bus_id = b.bus_id) as categories
        FROM buses b
        ORDER BY b.bus_id DESC
    ";
    $stmt = $_conn_db->query($sql);
    $buses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // For debugging: error_log($e->getMessage());
    $buses = []; // Gracefully handle DB error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('head.php');?>
    <title>Manage Buses</title>
    
    <style>
        body { background-color: #f8f9fa; }
        .page-header {
            display: flex; flex-wrap: wrap; justify-content: space-between;
            align-items: center; gap: 1rem; margin-bottom: 1.5rem;
        }
        .search-wrapper { position: relative; width: 100%; max-width: 350px; }
        .search-wrapper .form-control { padding-left: 2.5rem; }
        .search-wrapper .fa-search { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #6c757d; }
        .card { box-shadow: 0 8px 30px rgba(0,0,0,0.07); border: none; border-radius: 1rem; }
        .card-header { background-color: #fff; font-weight: 600; padding: 1.25rem 1.5rem; }
        
        /* Table Styling */
        .table thead th {
            background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; color: #495057;
            font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px;
        }
        .table tbody tr { transition: background-color 0.2s ease-in-out; }
        .table td { border-top: 1px solid #dee2e6; vertical-align: middle; }
        
        /* Custom Cell Styling */
        .bus-info-cell { display: flex; align-items: center; gap: 1rem; }
        .bus-icon-circle {
            width: 50px; height: 50px; border-radius: 50%; background-color: #e9ecef; color: #0d6efd;
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;
        }
        .bus-name-table { font-weight: 600; color: #212529; display: block; }
        .bus-reg-table { font-size: 0.85rem; color: #6c757d; display: block; }
        .action-buttons { display: flex; gap: 0.5rem; justify-content: flex-end; }
        .action-buttons .btn { padding: 0.3rem 0.7rem; }

        /* New Styles for Details */
        .category-badge {
            font-size: 0.75rem; font-weight: 500;
            padding: 0.3em 0.6em; margin: 0 2px 2px 0; display: inline-block;
        }
        .route-list { list-style: none; padding: 0; margin: 0; font-size: 0.9em; }
        .route-list li { padding-bottom: 4px; }
        .route-list li:last-child { padding-bottom: 0; }
    </style>
</head>
<body>

<div id="wrapper">
    <?php include_once('sidebar.php');?>
    <div class="main-content">
        <?php include_once('header.php');?>
        <div class="container-fluid">
            
            <div class="page-header mt-4">
                <h2 class="mb-0">Manage Buses</h2>
                <div class="d-flex align-items-center gap-1">
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="bus-search-input" class="form-control" placeholder="Search buses...">
                    </div>
                    <div class="d-block " style="width:180px; ">
                    <a href="add_bus.php" class=" btn btn-primary" style="padding:5px 10px;"><i class="fas fa-plus"></i> Add Bus</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>All Buses</span>
                    <span class="badge bg-primary rounded-pill"><?php echo count($buses); ?> Total</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="padding-left: 1.5rem;">Bus Details</th>
                                    <th>Categories</th>
                                    <th>Seat Layout</th>
                                    <th>Assigned Routes</th>
                                    <th>Status</th>
                                    <th class="text-end" style="padding-right: 1.5rem;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="buses-table-body">
                                <?php if (empty($buses)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="fas fa-bus fa-2x mb-2"></i>
                                            <p class="mb-0">No buses found. Please add a new bus.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($buses as $bus):
                                        $status_badge = 'bg-secondary';
                                        if ($bus['status'] == 'Active') $status_badge = 'bg-success';
                                        elseif ($bus['status'] == 'Inactive') $status_badge = 'bg-warning text-dark';
                                    ?>
                                        <tr id="bus-row-<?php echo $bus['bus_id']; ?>">
                                            <td>
                                                <div class="bus-info-cell">
                                                    <div class="bus-icon-circle"><i class="fas fa-bus-alt"></i></div>
                                                    <div>
                                                        <span class="bus-name-table"><?php echo htmlspecialchars($bus['bus_name']); ?></span>
                                                        <span class="bus-reg-table"><?php echo htmlspecialchars($bus['registration_number']); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($bus['categories'])): 
                                                    $categories = explode(',', $bus['categories']);
                                                    foreach($categories as $category): ?>
                                                        <span class="badge bg-light text-dark border category-badge"><?php echo htmlspecialchars(trim($category)); ?></span>
                                                    <?php endforeach; 
                                                else: ?>
                                                    <span class="text-muted small">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="me-2"><i class="fas fa-chair text-success"></i> <?php echo $bus['seater_count']; ?></span>
                                                <span><i class="fas fa-bed text-info"></i> <?php echo $bus['sleeper_count']; ?></span>
                                            </td>
                                            <td>
                                                <?php if (!empty($bus['assigned_routes'])): ?>
                                                    <ul class="route-list">
                                                        <li><?php echo $bus['assigned_routes']; ?></li>
                                                    </ul>
                                                <?php else: ?>
                                                    <span class="text-muted small">No routes assigned</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge <?php echo $status_badge; ?>"><?php echo htmlspecialchars($bus['status']); ?></span></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="edit_bus.php?bus_id=<?php echo $bus['bus_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                                    <a href="manage_seats.php?bus_id=<?php echo $bus['bus_id']; ?>" class="btn btn-sm btn-outline-info" title="Manage Seats"><i class="fas fa-chair"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-bus-btn" 
                                                            data-bus-id="<?php echo $bus['bus_id']; ?>" 
                                                            data-bus-name="<?php echo htmlspecialchars($bus['bus_name']); ?>"
                                                            title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <tr id="no-results-row" style="display: none;">
                                    <td colspan="6" class="text-center text-muted py-4">No buses match your search.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('foot.php');?>

<script>
// Your existing AJAX form submission script can remain here if needed.

$(document).ready(function() {
    // --- Live Search Functionality ---
    $('#bus-search-input').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        const tableBody = $('#buses-table-body');
        let visibleCount = 0;

        tableBody.find('tr:not(#no-results-row)').each(function() {
            const row = $(this);
            const rowText = row.text().toLowerCase();
            if (rowText.includes(searchTerm)) {
                row.show();
                visibleCount++;
            } else {
                row.hide();
            }
        });

        if (visibleCount === 0) {
            tableBody.find('#no-results-row').show();
        } else {
            tableBody.find('#no-results-row').hide();
        }
    });

    // --- Delete Functionality ---
    $('#buses-table-body').on('click', '.delete-bus-btn', function() {
        const busId = $(this).data('bus-id');
        const busName = $(this).data('bus-name');
        const row = $('#bus-row-' + busId);

        Swal.fire({
            title: `Delete "${busName}"?`,
            text: "All related data (seats, routes, etc.) will be affected. This cannot be undone!",
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
                            row.css('background-color', '#ffdddd').fadeOut(600, function() {
                                $(this).remove();
                            });
                        } else {
                            Swal.fire('Error!', response.notif_desc, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Could not connect to the server.', 'error');
                    }
                });
            }
        });
    });
});
</script>

</body>
</html>
<?php pdo_close_conn($_conn_db); ?>