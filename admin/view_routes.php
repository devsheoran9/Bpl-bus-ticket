<?php
// view_routes.php

global $_conn_db;
include_once('function/_db.php');
session_security_check(); // सुनिश्चित करता है कि उपयोगकर्ता लॉग इन है और सत्र मान्य है

// --- डेटा प्राप्त करने का तर्क ---
$routes_list = [];
$all_stops = [];
$all_schedules = [];

try {
    // बसों के साथ सभी मुख्य मार्गों को प्राप्त करें
    $routes_list = $_conn_db->query(
        "SELECT r.route_id, r.route_name, r.starting_point, r.ending_point, r.status, r.is_popular, b.bus_name 
         FROM routes r 
         JOIN buses b ON r.bus_id = b.bus_id 
         ORDER BY r.route_id DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($routes_list)) {
        $route_ids = array_column($routes_list, 'route_id');
        $in_clause = implode(',', array_fill(0, count($route_ids), '?'));
        
        // दक्षता के लिए एक ही बार में सभी मार्गों के लिए सभी स्टॉप प्राप्त करें
        $stmt_stops = $_conn_db->prepare("SELECT * FROM route_stops WHERE route_id IN ($in_clause) ORDER BY route_id, stop_order ASC");
        $stmt_stops->execute($route_ids);
        foreach ($stmt_stops->fetchAll(PDO::FETCH_ASSOC) as $stop) {
            $all_stops[$stop['route_id']][] = $stop;
        }

        // दक्षता के लिए एक ही बार में सभी मार्गों के लिए सभी शेड्यूल प्राप्त करें
        $stmt_schedules = $_conn_db->prepare("SELECT * FROM route_schedules WHERE route_id IN ($in_clause)");
        $stmt_schedules->execute($route_ids);
        foreach ($stmt_schedules->fetchAll(PDO::FETCH_ASSOC) as $schedule) {
            $all_schedules[$schedule['route_id']][] = $schedule;
        }
    }

} catch (PDOException $e) {
    // संभावित डेटाबेस त्रुटियों को शालीनता से संभालें
    $_SESSION['notif_type'] = 'error';
    $_SESSION['notif_title'] = 'Error';
    $_SESSION['notif_desc'] = 'Could not fetch route data. ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>View All Routes</title>
    <style>
        body { background-color: #f8f9fa; }
        .page-header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #dee2e6; }
        .route-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 25px; }
        .route-card { background-color: #fff; border: 1px solid #e9ecef; border-left-width: 5px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: all 0.2s ease-in-out; display: flex; flex-direction: column; }
        .route-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .route-card.status-Active { border-left-color: #198754; }
        .route-card.status-Inactive { border-left-color: #6c757d; }
        .route-card-body { padding: 20px; flex-grow: 1; }
        .route-card-title { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
        .route-card-title h5 { margin: 0; font-weight: 600; line-height: 1.3; }
        .route-details-grid { display: grid; grid-template-columns: auto 1fr; gap: 8px 15px; align-items: center; }
        .route-details-grid i { color: #0d6efd; width: 20px; text-align: center; }
        .route-details-grid span { font-weight: 500; }
        .route-card-footer { background-color: #f8f9fa; padding: 15px 20px; border-top: 1px solid #e9ecef; border-radius: 0 0 8px 8px; display: flex; gap: 10px; }
        .modal-header { background-color: #0d6efd; color: white; }
        .modal-header .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
        .schedule-day-block { border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 15px; }
        .schedule-day-block h6 { font-weight: 600; margin-bottom: 1rem; }
        .schedule-timeline-item { display: flex; align-items: flex-start; position: relative; padding-bottom: 20px; }
        .schedule-timeline-item:not(:last-child)::before { content: ''; position: absolute; left: 14px; top: 35px; bottom: 0; width: 2px; background-image: linear-gradient(to bottom, #ced4da 60%, transparent 40%); background-size: 1px 10px; }
        .schedule-timeline-icon { flex-shrink: 0; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; z-index: 1; box-shadow: 0 0 0 4px #f8f9fa; }
        .schedule-timeline-content { margin-left: 20px; }
        .schedule-timeline-content strong { display: block; }
        .details-wrapper { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .time-info { font-size: 0.9em; color: #6c757d; }
        .duration-pill { font-size: 0.75em; font-weight: 600; color: #6c757d; background-color: #e9ecef; padding: 2px 8px; border-radius: 50px; }
        .icon-start { background-color: #0d6efd; }
        .icon-stop { background-color: #6c757d; }
        .icon-end { background-color: #198754; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <div class="page-header my-4">
                <h2>All Saved Routes</h2>
                <a href="add_route.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add New Route</a>
            </div>

            <?php if (empty($routes_list)): ?>
                <div class="alert alert-info text-center"><h3>No routes found.</h3><p>Why not <a href="add_route.php" class="alert-link">add one now</a> to get started?</p></div>
            <?php else: ?>
                <div class="route-grid">
                    <?php foreach ($routes_list as $route): ?>
                        <div class="route-card status-<?php echo $route['status']; ?>">
                            <div class="route-card-body">
                                <div class="route-card-title">
                                    <div class="d-flex align-items-center">
                                        <h5 class="mb-0">
                                            <span class="star-container">
                                                <?php if ($route['is_popular']): ?>
                                                    <i class="fas fa-star text-warning me-2" title="Popular Route"></i>
                                                <?php endif; ?>
                                            </span>
                                            <?php echo htmlspecialchars($route['route_name']); ?>
                                        </h5>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check form-switch" title="Toggle Popular Status">
                                            <input class="form-check-input popular-toggle" type="checkbox" role="switch" 
                                                   data-route-id="<?php echo $route['route_id']; ?>" 
                                                   <?php echo $route['is_popular'] ? 'checked' : ''; ?>>
                                        </div>
                                        <span class="badge fs-6 <?php echo $route['status'] == 'Active' ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'; ?>"><?php echo $route['status']; ?></span>
                                    </div>
                                </div>
                                <div class="route-details-grid mt-3">
                                    <i class="fas fa-bus fa-fw"></i><span><?php echo htmlspecialchars($route['bus_name']); ?></span>
                                    <i class="fas fa-route fa-fw"></i><span><?php echo htmlspecialchars($route['starting_point']); ?> → <?php echo htmlspecialchars($route['ending_point']); ?></span>
                                </div>
                            </div>
                            <div class="route-card-footer">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal<?php echo $route['route_id']; ?>">
                                    <i class="fas fa-calendar-alt me-1"></i>View Schedule
                                </button>
                                <a href="add_route.php?action=edit&id=<?php echo $route['route_id']; ?>" class="btn btn-sm btn-info ms-auto"><i class="fas fa-edit me-1"></i>Edit</a>
                                <button type="button" class="btn btn-sm btn-danger delete-route-btn" data-route-id="<?php echo $route['route_id']; ?>">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </button>
                            </div>
                        </div>

                        <!-- Schedule Modal for each route -->
                        <div class="modal fade" id="scheduleModal<?php echo $route['route_id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Full Schedule: <?php echo htmlspecialchars($route['route_name']); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php if(empty($all_schedules[$route['route_id']])): ?>
                                            <div class="alert alert-warning">No daily schedules have been set for this route.</div>
                                        <?php else: ?>
                                            <?php foreach($all_schedules[$route['route_id']] as $schedule): ?>
                                                <div class="schedule-day-block">
                                                    <h6><i class="fas fa-calendar-day me-2"></i><?php echo htmlspecialchars($schedule['operating_day']); ?> Schedule</h6>
                                                    <div class="schedule-timeline">
                                                        <?php
                                                        try {
                                                            $mainDepartureTime = new DateTime($schedule['departure_time']);
                                                            $previousStopTime = clone $mainDepartureTime;
                                                            echo '<div class="schedule-timeline-item"><div class="schedule-timeline-icon icon-start"><i class="fas fa-play"></i></div><div class="schedule-timeline-content"><strong>' . htmlspecialchars($route['starting_point']) . '</strong><div class="details-wrapper"><span class="time-info">Departure: ' . $mainDepartureTime->format('h:i A') . '</span></div></div></div>';
                                                            if (!empty($all_stops[$route['route_id']])) {
                                                                foreach($all_stops[$route['route_id']] as $stop) {
                                                                    $arrivalTime = clone $mainDepartureTime;
                                                                    $arrivalTime->modify('+' . $stop['duration_from_start_minutes'] . ' minutes');
                                                                    $interval = $previousStopTime->diff($arrivalTime);
                                                                    $durationBetween = $interval->h * 60 + $interval->i;
                                                                    echo '<div class="schedule-timeline-item"><div class="schedule-timeline-icon icon-stop"><i class="fas fa-map-marker-alt"></i></div><div class="schedule-timeline-content"><strong>' . htmlspecialchars($stop['stop_name']) . '</strong><div class="details-wrapper"><span class="time-info">Arrival: ' . $arrivalTime->format('h:i A') . '</span><span class="duration-pill">+' . $durationBetween . ' mins</span></div></div></div>';
                                                                    $previousStopTime = clone $arrivalTime;
                                                                }
                                                            }
                                                            echo '<div class="schedule-timeline-item"><div class="schedule-timeline-icon icon-end"><i class="fas fa-flag-checkered"></i></div><div class="schedule-timeline-content"><strong>' . htmlspecialchars($route['ending_point']) . '</strong><span class="time-info">Final Destination</span></div></div>';
                                                        } catch (Exception $e) { echo "<p class='text-danger'>Error calculating schedule.</p>"; }
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "foot.php"; ?>
<script>
$(document).ready(function() {
    // Handle Popular Status Toggle
    $('.popular-toggle').on('change', function() {
        var checkbox = $(this);
        var routeId = checkbox.data('route-id');
        var isPopular = checkbox.is(':checked') ? 1 : 0;
        var starContainer = checkbox.closest('.route-card-title').find('.star-container');

        $.ajax({
            type: "POST",
            url: "function/backend/route_actions.php",
            data: {
                action: 'toggle_popular',
                route_id: routeId,
                is_popular: isPopular
            },
            dataType: "json",
            success: function(data) {
                $.notify({ title: data.notif_title, message: data.notif_desc }, { type: data.notif_type });
                if (data.res === 'true') {
                    if (isPopular) {
                        starContainer.html('<i class="fas fa-star text-warning me-2" title="Popular Route"></i>');
                    } else {
                        starContainer.empty();
                    }
                } else {
                    checkbox.prop('checked', !isPopular);
                }
            },
            error: function() {
                $.notify({ title: 'Error', message: 'Could not connect to the server.' }, { type: 'danger' });
                checkbox.prop('checked', !isPopular);
            }
        });
    });

    // Handle Delete Route Button
    $('.delete-route-btn').on('click', function() {
        var routeId = $(this).data('route-id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the route and all its schedules. You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "function/backend/route_actions.php",
                    data: { action: 'delete_route', route_id: routeId },
                    dataType: "json",
                    success: function(data) {
                        $.notify({ title: data.notif_title, message: data.notif_desc }, { type: data.notif_type });
                        if (data.res === 'true' && data.goTo !== '') {
                            setTimeout(() => window.location.href = data.goTo, 1000);
                        }
                    },
                    error: () => Swal.fire('Error', 'Could not connect to the server.', 'error')
                });
            }
        });
    });
});
</script>
</body>
</html>