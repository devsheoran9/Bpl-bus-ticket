<?php
global $_conn_db;
include_once('function/_db.php');
check_user_login();

$route_to_edit = null;
$stops_to_edit = [];

// --- ACTION HANDLER (DELETE & EDIT) ---
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $route_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($action == 'edit' && $route_id) {
        $stmt = $_conn_db->prepare("SELECT * FROM routes WHERE route_id = ?");
        $stmt->execute([$route_id]);
        $route_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt_stops = $_conn_db->prepare("SELECT * FROM route_stops WHERE route_id = ? ORDER BY stop_order ASC");
        $stmt_stops->execute([$route_id]);
        $stops_to_edit = $stmt_stops->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($action == 'delete' && $route_id) {
        $stmt = $_conn_db->prepare("DELETE FROM routes WHERE route_id = ?");
        $stmt->execute([$route_id]);
        $_SESSION['notif_type'] = 'info'; $_SESSION['notif_title'] = 'Deleted'; $_SESSION['notif_desc'] = 'The route has been deleted.';
        header("Location: add_route.php");
        exit();
    }
}

// --- FORM SUBMISSION LOGIC (ADD & UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Main route details
    $bus_id = $_POST['bus_id'];
    $route_name = trim($_POST['route_name']);
    $starting_point = trim($_POST['starting_point']);
    $departure_time = $_POST['departure_time'];
    $status = $_POST['status'];
    
    // Arrays for stops
    $stop_names = $_POST['stop_name'] ?? [];
    $arrival_times = $_POST['arrival_time'] ?? [];
    $departure_times = $_POST['departure_time_stop'] ?? [];
    $prices_sl = $_POST['price_sl'] ?? []; $prices_su = $_POST['price_su'] ?? [];
    $prices_ll = $_POST['price_ll'] ?? []; $prices_lu = $_POST['price_lu'] ?? [];
    
    $ending_point = !empty($stop_names) ? end($stop_names) : $starting_point;
    $action_type = $_POST['action_type'];
    $route_id = filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT);

    $_conn_db->beginTransaction();
    try {
        if ($action_type == 'update' && $route_id) {
            $sql = "UPDATE routes SET bus_id = ?, route_name = ?, starting_point = ?, ending_point = ?, departure_time = ?, status = ? WHERE route_id = ?";
            $stmt = $_conn_db->prepare($sql);
            $stmt->execute([$bus_id, $route_name, $starting_point, $ending_point, $departure_time, $status, $route_id]);
            
            $stmt_delete = $_conn_db->prepare("DELETE FROM route_stops WHERE route_id = ?");
            $stmt_delete->execute([$route_id]);
            $current_route_id = $route_id;
        } else {
            $sql = "INSERT INTO routes (bus_id, route_name, starting_point, ending_point, departure_time, status) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $_conn_db->prepare($sql);
            $stmt->execute([$bus_id, $route_name, $starting_point, $ending_point, $departure_time, $status]);
            $current_route_id = $_conn_db->lastInsertId();
        }

        if (!empty($stop_names)) {
            $sql_stop = "INSERT INTO route_stops (route_id, stop_name, stop_order, arrival_time, departure_time, price_seater_lower, price_seater_upper, price_sleeper_lower, price_sleeper_upper) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_stop = $_conn_db->prepare($sql_stop);
            foreach ($stop_names as $index => $stop_name) {
                if (!empty(trim($stop_name))) {
                    $stmt_stop->execute([
                        $current_route_id, trim($stop_name), $index + 1,
                        !empty($arrival_times[$index]) ? $arrival_times[$index] : null,
                        !empty($departure_times[$index]) ? $departure_times[$index] : null,
                        !empty($prices_sl[$index]) ? $prices_sl[$index] : null,
                        !empty($prices_su[$index]) ? $prices_su[$index] : null,
                        !empty($prices_ll[$index]) ? $prices_ll[$index] : null,
                        !empty($prices_lu[$index]) ? $prices_lu[$index] : null,
                    ]);
                }
            }
        }
        $_conn_db->commit();
        $_SESSION['notif_type'] = 'success'; $_SESSION['notif_title'] = 'Success'; $_SESSION['notif_desc'] = 'Route has been saved successfully.';
        
        // Store the ID of the saved route in the session to display it
        $_SESSION['last_saved_route_id'] = $current_route_id;

    } catch (Exception $e) {
        $_conn_db->rollBack();
        $_SESSION['notif_type'] = 'error'; $_SESSION['notif_title'] = 'Error'; $_SESSION['notif_desc'] = 'Could not save the route. ' . $e->getMessage();
    }
    header("Location: add_route.php");
    exit();
}

// --- Logic to fetch ONLY the last saved route for display ---
$last_route_details = null;
$last_route_stops = [];
if (isset($_SESSION['last_saved_route_id'])) {
    $last_route_id = $_SESSION['last_saved_route_id'];
    
    $stmt_route = $_conn_db->prepare("SELECT r.*, b.bus_name FROM routes r JOIN buses b ON r.bus_id = b.bus_id WHERE r.route_id = ?");
    $stmt_route->execute([$last_route_id]);
    $last_route_details = $stmt_route->fetch(PDO::FETCH_ASSOC);

    $stmt_stops = $_conn_db->prepare("SELECT * FROM route_stops WHERE route_id = ? ORDER BY stop_order ASC");
    $stmt_stops->execute([$last_route_id]);
    $last_route_stops = $stmt_stops->fetchAll(PDO::FETCH_ASSOC);

    // Unset the session variable so it only shows once per save
    unset($_SESSION['last_saved_route_id']);
}

// Data fetching for form dropdowns
try {
    $buses = $_conn_db->query("SELECT bus_id, bus_name FROM buses WHERE status = 'Active'")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $buses = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <style>
        /* Form Timeline Styles */
        .card-header { border-top: 3px solid #0d6efd; }
        .timeline-item { position: relative; padding-left: 40px; padding-bottom: 20px; }
        .timeline-item::before { content: ''; position: absolute; left: 0; top: 5px; bottom: -20px; width: 2px; background-color: #e9ecef; }
        .timeline-item::after { content: ''; position: absolute; left: -8px; top: 5px; width: 18px; height: 18px; border-radius: 50%; background-color: #fff; border: 3px solid #0d6efd; z-index: 1; }
        .timeline-item:last-child::before { display: none; }
        .timeline-item-content { background-color: #f8f9fa; border-radius: 8px; padding: 20px; border: 1px solid #e9ecef; }
        .timeline-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .timeline-header h5 { margin: 0; color: #0d6efd; }
        .field-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; }
        #add-stop-btn-container { position: relative; padding-left: 40px; }
        

        /* Beautiful Result Card Styles */
        .result-card { border-left: 5px solid #198754; animation: fadeIn 0.5s ease-in-out; }
        .result-header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; padding-bottom: 1rem; margin-bottom: 1rem; border-bottom: 1px solid #e9ecef; }
        .result-header h4 { margin: 0; }
        .result-details-grid { display: grid; grid-template-columns: auto 1fr; gap: 5px 15px; font-size: 0.95em; }
        .result-details-grid dt { font-weight: 600; color: #6c757d; }
        .result-timeline { margin-top: 1.5rem; }
        .result-timeline-item { display: flex; align-items: flex-start; position: relative; padding-bottom: 25px; }
        .result-timeline-item:not(:last-child)::before { content: ''; position: absolute; left: 12px; top: 30px; bottom: 0; width: 2px; background-color: #ced4da; }
        .result-timeline-icon { flex-shrink: 0; width: 26px; height: 26px; border-radius: 50%; background-color: #0d6efd; color: white; display: flex; align-items: center; justify-content: center; z-index: 1; box-shadow: 0 0 0 4px #fff; }
        .result-timeline-content { margin-left: 20px; }
        .result-timeline-content strong { display: block; font-size: 1.1em; }
        .result-timeline-content span { font-size: 0.9em; color: #6c757d; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Add & Manage Routes</h2>
            <div class="card">
                <div class="card-header"><?php echo $route_to_edit ? 'Edit Route' : 'Add New Route'; ?></div>
                <div class="card-body">
                    <form action="add_route.php" method="POST">
                        <input type="hidden" name="action_type" value="<?php echo $route_to_edit ? 'update' : 'add'; ?>">
                        <input type="hidden" name="route_id" value="<?php echo $route_to_edit['route_id'] ?? ''; ?>">
                        
                        <h5>1. Basic Route Details</h5>
                        <div class="row bg-light p-3 rounded mb-4">
                            <div class="col-md-4 mb-3"><label class="form-label">Route Name</label><input type="text" class="form-control" name="route_name" value="<?php echo htmlspecialchars($route_to_edit['route_name'] ?? ''); ?>" required></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Assign Bus</label><select class="form-select" name="bus_id" required><option value="">-- Select --</option><?php foreach ($buses as $bus): ?><option value="<?php echo $bus['bus_id']; ?>" <?php echo (isset($route_to_edit['bus_id']) && $route_to_edit['bus_id'] == $bus['bus_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($bus['bus_name']); ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="Active" <?php echo (isset($route_to_edit['status']) && $route_to_edit['status'] == 'Active') ? 'selected' : ''; ?>>Active</option><option value="Inactive" <?php echo (isset($route_to_edit['status']) && $route_to_edit['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option></select></div>
                        </div>

                        <h5>2. Route Path & Schedule</h5>
                        <div class="route-timeline">
                            <div class="timeline-item">
                                <div class="timeline-item-content">
                                    <div class="timeline-header"><h5>Starting Point</h5></div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label class="form-label">Location Name</label><input type="text" class="form-control" name="starting_point" value="<?php echo htmlspecialchars($route_to_edit['starting_point'] ?? ''); ?>" required></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Departure Time</label><input type="time" class="form-control" name="departure_time" value="<?php echo htmlspecialchars($route_to_edit['departure_time'] ?? ''); ?>" required></div>
                                    </div>
                                </div>
                            </div>
                            <div id="stops-container">
                                <?php if (!empty($stops_to_edit)): foreach ($stops_to_edit as $stop): ?>
                                    <div class="timeline-item stop-item">
                                        <div class="timeline-item-content">
                                            <div class="timeline-header"><h5>Intermediate Stop</h5><button type="button" class="btn btn-sm btn-outline-danger remove-stop-btn">&times; Remove</button></div>
                                            <div class="mb-3"><label class="form-label small">Stop Name</label><input type="text" class="form-control" name="stop_name[]" value="<?php echo htmlspecialchars($stop['stop_name']); ?>" required></div>
                                            <div class="field-grid mb-3">
                                                <div><label class="form-label small">Arrival Time</label><input type="time" class="form-control" name="arrival_time[]" value="<?php echo htmlspecialchars($stop['arrival_time'] ?? ''); ?>"></div>
                                                <div><label class="form-label small">Departure Time</label><input type="time" class="form-control" name="departure_time_stop[]" value="<?php echo htmlspecialchars($stop['departure_time'] ?? ''); ?>"></div>
                                            </div>
                                            <label class="form-label small">Price from Starting Point</label>
                                            <div class="field-grid">
                                                <input type="number" class="form-control" name="price_sl[]" placeholder="Seater Lower" value="<?php echo $stop['price_seater_lower'] ?? ''; ?>">
                                                <input type="number" class="form-control" name="price_su[]" placeholder="Seater Upper" value="<?php echo $stop['price_seater_upper'] ?? ''; ?>">
                                                <input type="number" class="form-control" name="price_ll[]" placeholder="Sleeper Lower" value="<?php echo $stop['price_sleeper_lower'] ?? ''; ?>">
                                                <input type="number" class="form-control" name="price_lu[]" placeholder="Sleeper Upper" value="<?php echo $stop['price_sleeper_upper'] ?? ''; ?>">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; endif; ?>
                            </div>
                            <div id="add-stop-btn-container">
                                <button type="button" id="add-stop-btn" class="btn btn-secondary"><i class="fas fa-plus"></i> Add Intermediate Stop</button>
                            </div>
                        </div>
                        <hr class="mt-4"><div class="mt-3"><button type="submit" class="btn btn-primary btn-lg"><?php echo $route_to_edit ? 'Update Route' : 'Save New Route'; ?></button></div>
                    </form>
                </div>
            </div>
            
            <?php if ($last_route_details): ?>
            <div class="card mt-5 result-card" id="result-display">
                <div class="card-header bg-success text-white"><i class="fas fa-check-circle"></i> Route Saved Successfully!</div>
                <div class="card-body">
                    <div class="result-header">
                        <h4><?php echo htmlspecialchars($last_route_details['route_name']); ?></h4>
                        <div class="btn-group"><a href="add_route.php?action=edit&id=<?php echo $last_route_details['route_id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i> Edit Again</a><a href="add_route.php" class="btn btn-sm btn-light"><i class="fas fa-plus"></i> Add Another New Route</a></div>
                    </div>
                    <div class="result-details-grid mb-3">
                        <dt>Bus:</dt> <dd><?php echo htmlspecialchars($last_route_details['bus_name']); ?></dd>
                        <dt>Status:</dt> <dd><span class="badge <?php echo $last_route_details['status'] == 'Active' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $last_route_details['status']; ?></span></dd>
                    </div>
                    <div class="result-timeline">
                        <div class="result-timeline-item">
                            <div class="result-timeline-icon"><i class="fas fa-play"></i></div>
                            <div class="result-timeline-content"><strong><?php echo htmlspecialchars($last_route_details['starting_point']); ?></strong><span>Departure: <?php echo date('h:i A', strtotime($last_route_details['departure_time'])); ?></span></div>
                        </div>
                        <?php foreach($last_route_stops as $stop): ?>
                        <div class="result-timeline-item">
                            <div class="result-timeline-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="result-timeline-content"><strong><?php echo htmlspecialchars($stop['stop_name']); ?></strong><span>Arrival: <?php echo $stop['arrival_time'] ? date('h:i A', strtotime($stop['arrival_time'])) : 'N/A'; ?> | Departure: <?php echo $stop['departure_time'] ? date('h:i A', strtotime($stop['departure_time'])) : 'N/A'; ?></span></div>
                        </div>
                        <?php endforeach; ?>
                        <div class="result-timeline-item">
                             <div class="result-timeline-icon bg-success"><i class="fas fa-flag-checkered"></i></div>
                             <div class="result-timeline-content"><strong><?php echo htmlspecialchars($last_route_details['ending_point']); ?></strong><span>Destination</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="stop-row-template" style="display: none;">
    <div class="timeline-item stop-item">
        <div class="timeline-item-content">
            <div class="timeline-header"><h5>Intermediate Stop</h5><button type="button" class="btn btn-sm btn-outline-danger remove-stop-btn">&times; Remove</button></div>
            <div class="mb-3"><label class="form-label small">Stop Name</label><input type="text" class="form-control" name="stop_name[]" required></div>
            <div class="field-grid mb-3">
                <div><label class="form-label small">Arrival Time</label><input type="time" class="form-control" name="arrival_time[]"></div>
                <div><label class="form-label small">Departure Time</label><input type="time" class="form-control" name="departure_time_stop[]"></div>
            </div>
            <label class="form-label small">Price from Starting Point</label>
            <div class="field-grid">
                <input type="number" class="form-control" name="price_sl[]" placeholder="Seater Lower" min="0"><input type="number" class="form-control" name="price_su[]" placeholder="Seater Upper" min="0">
                <input type="number" class="form-control" name="price_ll[]" placeholder="Sleeper Lower" min="0"><input type="number" class="form-control" name="price_lu[]" placeholder="Sleeper Upper" min="0">
            </div>
        </div>
    </div>
</div>

<?php include "foot.php"; ?>
<script>
$(document).ready(function() {
    $('#add-stop-btn').on('click', function() {
        $('#stops-container').append($('#stop-row-template').html());
    });
    $('#stops-container').on('click', '.remove-stop-btn', function() {
        $(this).closest('.stop-item').remove();
    });
    if ($('#result-display').length) {
        $('html, body').animate({ scrollTop: $('#result-display').offset().top - 80 }, 800);
    }
});
</script>
</body>
</html>