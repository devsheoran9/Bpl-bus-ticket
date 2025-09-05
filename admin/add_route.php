 
 



<?php
global $_conn_db;
include_once('function/_db.php');
check_user_login();

$route_to_edit = null;
$stops_to_edit = [];
$schedules_to_edit = [];

// --- ACTION HANDLER (ONLY for pre-filling the EDIT form) ---
if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    $route_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($route_id) {
        $stmt_route = $_conn_db->prepare("SELECT * FROM routes WHERE route_id = ?");
        $stmt_route->execute([$route_id]);
        $route_to_edit = $stmt_route->fetch(PDO::FETCH_ASSOC);

        $stmt_stops = $_conn_db->prepare("SELECT * FROM route_stops WHERE route_id = ? ORDER BY stop_order ASC");
        $stmt_stops->execute([$route_id]);
        $stops_to_edit = $stmt_stops->fetchAll(PDO::FETCH_ASSOC);

        $stmt_schedules = $_conn_db->prepare("SELECT * FROM route_schedules WHERE route_id = ?");
        $stmt_schedules->execute([$route_id]);
        foreach($stmt_schedules->fetchAll(PDO::FETCH_ASSOC) as $schedule) {
            $schedules_to_edit[$schedule['operating_day']] = $schedule['departure_time'];
        }
    }
}

// Data fetching for form dropdowns
try {
    $buses = $_conn_db->query("SELECT bus_id, bus_name FROM buses WHERE status = 'Active'")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $buses = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <style>
        .card-header { border-top: 3px solid #0d6efd; }
        .days-selector { display: flex; flex-wrap: wrap; gap: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 8px; margin-bottom: 15px; }
        .days-selector .form-check-label { padding: 8px 15px; background-color: #fff; border-radius: 50px; cursor: pointer; border: 1px solid #ced4da; transition: all 0.2s; }
        .days-selector .form-check-input { display: none; }
        .days-selector .form-check-input:checked + .form-check-label { background-color: #0d6efd; color: white; border-color: #0d6efd; }
        
        .time-input-container { display: none; }
        .time-input-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .time-input-group { background-color: #f1f1f1; padding: 10px; border-radius: 5px; }
        .time-input-group label { font-weight: 600; }

        .stop-item { position: relative; padding-left: 20px; }
        .stop-item::before { content: '↓'; position: absolute; left: -5px; top: 40%; transform: translateY(-50%); font-size: 24px; color: #ced4da; }
        .stop-item-content { background-color: #f8f9fa; border-radius: 8px; padding: 20px; border: 1px solid #e9ecef; margin-bottom: 20px; }
        .stop-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .stop-header h6 { margin: 0; }
        .price-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Add & Manage Route Schedules</h2>
            <div class="card">
                <div class="card-header"><?php echo $route_to_edit ? 'Edit Route' : 'Add New Route'; ?></div>
                <div class="card-body">
                    <!-- --- CHANGED: Form now points to the backend file and has the data-form class --- -->
                    <form action="function/backend/route_actions.php" method="POST" class="data-form">
                        <input type="hidden" name="action" value="save_route">
                        <input type="hidden" name="action_type" value="<?php echo $route_to_edit ? 'update' : 'add'; ?>">
                        <input type="hidden" name="route_id" value="<?php echo $route_to_edit['route_id'] ?? ''; ?>">
                        
                        
                        <h5>1. Basic Route Details</h5>
                        <div class="row bg-light p-3 rounded mb-4">
                            <div class="col-lg-4 mb-3"><label class="form-label">Route Name</label><input type="text" class="form-control" name="route_name" value="<?php echo htmlspecialchars($route_to_edit['route_name'] ?? ''); ?>" required></div>
                            <div class="col-lg-4 mb-3"><label class="form-label">Assign Bus</label><select class="form-select" name="bus_id" required><option value="">-- Select --</option><?php foreach ($buses as $bus): ?><option value="<?php echo $bus['bus_id']; ?>" <?php echo (isset($route_to_edit['bus_id']) && $route_to_edit['bus_id'] == $bus['bus_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($bus['bus_name']); ?></option><?php endforeach; ?></select></div>
                            <div class="col-lg-4 mb-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="Active" <?php echo (isset($route_to_edit['status']) && $route_to_edit['status'] == 'Active') ? 'selected' : ''; ?>>Active</option><option value="Inactive" <?php echo (isset($route_to_edit['status']) && $route_to_edit['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option></select></div>
                        </div>

                        <h5>2. Select Operating Days & Departure Times</h5>
                        <?php $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']; $selected_days = array_keys($schedules_to_edit); ?>
                        <div class="days-selector">
                            <?php foreach($days as $day): ?>
                                <div class="form-check"><input class="form-check-input day-checkbox" type="checkbox" name="operating_days[]" value="<?php echo $day; ?>" id="day_<?php echo $day; ?>" <?php echo in_array($day, $selected_days) ? 'checked' : ''; ?>><label class="form-check-label" for="day_<?php echo $day; ?>"><?php echo $day; ?></label></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="time-input-grid mt-3">
                            <?php foreach($days as $day): ?>
                                <div class="time-input-container" id="time-input-container-<?php echo $day; ?>" style="<?php echo in_array($day, $selected_days) ? 'display: block;' : ''; ?>">
                                    <div class="time-input-group"><label>Departure on <?php echo $day; ?></label><input type="time" class="form-control" name="departure_time[<?php echo $day; ?>]" value="<?php echo $schedules_to_edit[$day] ?? ''; ?>"></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <hr class="my-4">

                        <h5>3. Route Path, Durations & Pricing</h5>
                        <div class="mb-3"><h6>Starting Point</h6><input type="text" class="form-control" name="starting_point" value="<?php echo htmlspecialchars($route_to_edit['starting_point'] ?? ''); ?>" required></div>
                        
                        <div id="stops-container">
                             <?php if (!empty($stops_to_edit)): foreach ($stops_to_edit as $stop): ?>
                                <div class="stop-item">
                                    <div class="stop-item-content">
                                        <div class="stop-header"><h6>Intermediate Stop</h6><button type="button" class="btn btn-sm btn-outline-danger remove-stop-btn">&times; Remove</button></div>
                                        <div class="row">
                                            <div class="col-md-8 mb-3"><label class="form-label small">Stop Name</label><input type="text" class="form-control" name="stop_name[]" value="<?php echo htmlspecialchars($stop['stop_name']); ?>" required></div>
                                            <div class="col-md-4 mb-3"><label class="form-label small">Duration from Start (mins)</label><input type="number" class="form-control" name="duration[]" value="<?php echo $stop['duration_from_start_minutes'] ?? '0'; ?>" min="0"></div>
                                        </div>
                                        <label class="form-label small">Price from Starting Point</label>
                                        <div class="price-grid">
                                            <input type="number" class="form-control" name="price_sl[]" placeholder="Seater Lower" value="<?php echo $stop['price_seater_lower'] ?? ''; ?>">
                                            <input type="number" class="form-control" name="price_su[]" placeholder="Seater Upper" value="<?php echo $stop['price_seater_upper'] ?? ''; ?>">
                                            <input type="number" class="form-control" name="price_ll[]" placeholder="Sleeper Lower" value="<?php echo $stop['price_sleeper_lower'] ?? ''; ?>">
                                            <input type="number" class="form-control" name="price_lu[]" placeholder="Sleeper Upper" value="<?php echo $stop['price_sleeper_upper'] ?? ''; ?>">
                                        </div>
                                    </div>
                                </div>
                             <?php endforeach; endif; ?>
                        </div>

                        <button type="button" id="add-stop-btn" class="btn btn-secondary mt-2"><i class="fas fa-plus"></i> Add Stop</button>
                        
                        <div class="mt-4"><button type="submit" class="btn btn-primary btn-lg submit-btn"><?php echo $route_to_edit ? 'Update Route' : 'Save New Route'; ?></button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="stop-row-template" style="display: none;">
    <div class="stop-item">
        <div class="stop-item-content">
            <div class="stop-header"><h6>New Intermediate Stop</h6><button type="button" class="btn btn-sm btn-outline-danger remove-stop-btn">&times; Remove</button></div>
            <div class="row">
                <div class="col-md-8 mb-3"><label class="form-label small">Stop Name</label><input type="text" class="form-control" name="stop_name[]" placeholder="e.g., Jaipur" required></div>
                <div class="col-md-4 mb-3"><label class="form-label small">Duration from Start (mins)</label><input type="number" class="form-control" name="duration[]" placeholder="e.g., 240" min="0"></div>
            </div>
            <label class="form-label small">Price from Starting Point</label>
            <div class="price-grid">
                <input type="number" class="form-control" name="price_sl[]" placeholder="Seater Lower" min="0">
                <input type="number" class="form-control" name="price_su[]" placeholder="Seater Upper" min="0">
                <input type="number" class="form-control" name="price_ll[]" placeholder="Sleeper Lower" min="0">
                <input type="number" class="form-control" name="price_lu[]" placeholder="Sleeper Upper" min="0">
            </div>
        </div>
    </div>
</div>

<?php include "foot.php"; ?>
<script>
$(document).ready(function() {
    $('.day-checkbox').on('change', function() {
        const day = $(this).val();
        const timeInputContainer = $('#time-input-container-' + day);
        if ($(this).is(':checked')) {
            timeInputContainer.slideDown();
        } else {
            timeInputContainer.slideUp();
            timeInputContainer.find('input[type="time"]').val('');
        }
    });

    $('#add-stop-btn').on('click', function() {
        $('#stops-container').append($('#stop-row-template').html());
    });

    $('#stops-container').on('click', '.remove-stop-btn', function() {
        $(this).closest('.stop-item').remove();
    });
});
</script>
</body>
</html>