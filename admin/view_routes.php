<?php
// view_routes.php

global $_conn_db;
include_once('function/_db.php');
session_security_check();  
$can_manage_routes = user_has_permission('can_manage_routes'); // Consolidated for page and general actions
$can_edit_routes = user_has_permission('can_edit_routes');
$can_delete_routes = user_has_permission('can_delete_routes');
$can_charter_bus = user_has_permission('can_charter_bus');
$can_toggle_popular_route = user_has_permission('can_toggle_popular_route');
$can_view_reports = user_has_permission('can_view_reports'); // Added for deleted routes report link

// Page level permission check
check_permission('can_manage_routes'); // Any user with route management permissions can access this page

$routes_list = [];
$all_stops = [];
$all_schedules = [];
$chartered_dates_by_route = []; 

try {
    $routes_list = $_conn_db->query(
        "SELECT r.route_id, r.route_name, r.starting_point, r.ending_point, r.status, r.is_popular, r.is_chartered, b.bus_name 
         FROM routes r 
         JOIN buses b ON r.bus_id = b.bus_id 
         ORDER BY r.route_id DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($routes_list)) {
        $route_ids = array_column($routes_list, 'route_id');
        $in_clause = implode(',', array_fill(0, count($route_ids), '?'));
        
        $all_stops = [];
        $stmt_stops = $_conn_db->prepare("SELECT * FROM route_stops WHERE route_id IN ($in_clause) ORDER BY route_id, stop_order ASC");
        $stmt_stops->execute($route_ids);
        foreach ($stmt_stops->fetchAll(PDO::FETCH_ASSOC) as $stop) {
            $all_stops[$stop['route_id']][] = $stop;
        }

        $all_schedules = [];
        $stmt_schedules = $_conn_db->prepare("SELECT * FROM route_schedules WHERE route_id IN ($in_clause)");
        $stmt_schedules->execute($route_ids);
        foreach ($stmt_schedules->fetchAll(PDO::FETCH_ASSOC) as $schedule) {
            $all_schedules[$schedule['route_id']][] = $schedule;
        }

        $stmt_chartered_dates = $_conn_db->prepare("
            SELECT route_id, travel_date, customer_name, customer_mobile
            FROM charter_bookings
            WHERE route_id IN ($in_clause) AND travel_date >= CURDATE()
            ORDER BY travel_date ASC
        ");
        $stmt_chartered_dates->execute($route_ids);
        foreach ($stmt_chartered_dates->fetchAll(PDO::FETCH_ASSOC) as $charter_booking) {
            $chartered_dates_by_route[$charter_booking['route_id']][] = $charter_booking; 
        }
    }

} catch (PDOException $e) {
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
        /* --- Global & Base Styles --- */
        body {
            background-color: #f8f9fa; /* Very light grey background */
            font-family: 'Inter', sans-serif; /* Modern, clean font */
            color: #343a40; /* Darker text for readability */
            line-height: 1.5;
        }

        .page-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding-bottom: 0.75rem; /* Slightly reduced */
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 1.5rem; /* More space below header */
        }

        h2 {
            font-weight: 700;
            color: #212529; /* Darker heading */
            font-size: 1.8rem; /* Slightly larger */
        }

        /* --- Route Grid Container --- */
        .route-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); /* Min-width 350px, bit smaller for compactness */
            gap: 1.5rem; /* Reduced gap between cards */
        }

        /* --- Individual Route Card Styling --- */
        .route-card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0; /* Lighter border */
            border-left-width: 5px;
            border-radius: 0.75rem; /* Slightly more rounded corners */
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); /* Subtler shadow */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Ensure rounded corners are respected */
        }

        .route-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08); /* Enhanced hover effect */
        }

        /* Status Colors for Left Border */
        .route-card.status-Active { border-left-color: #1dd1a1; } /* More vibrant green */
        .route-card.status-Inactive { border-left-color: #ff6b6b; } /* Soft red/warning */
        /* You can define more statuses like this if needed */
        .route-card.status-Upcoming { border-left-color: #feca57; } 

        .route-card-body {
            padding: 1rem 1.25rem; /* Optimized padding, slightly less top/bottom */
            flex-grow: 1;
        }

        .route-card-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem; /* Slightly reduced margin */
            line-height: 1.2;
        }

        .route-card-title h5 {
            margin: 0;
            font-weight: 700; /* Bolder title */
            font-size: 1.15rem; /* Consistent size */
            color: #212529;
        }

        .route-card-title .star-container i {
            font-size: 0.9em; /* Smaller star icon */
            margin-right: 0.5rem;
        }

        .route-status-badge {
            font-size: 0.75rem; /* Smaller badge */
            padding: 0.3em 0.6em;
            border-radius: 12px; /* More pill-like */
            font-weight: 600;
        }
        .route-status-badge.bg-success-subtle { background-color: #d1e7dd !important; color: #0f5132 !important; border: 1px solid #badbcc; }
        .route-status-badge.bg-secondary-subtle { background-color: #e2e3e5 !important; color: #495057 !important; border: 1px solid #d3d6db; }

        .route-details-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0.5rem 1rem; /* More compact grid */
            align-items: center;
            font-size: 0.9rem; /* Smaller detail text */
        }
        .route-details-grid i {
            color: #4a69bd; /* Primary color for icons */
            width: 20px;
            text-align: center;
            font-size: 1em; /* Ensure icons match text size */
        }
        .route-details-grid span {
            font-weight: 500;
            color: #495057; /* Darker grey for details */
        }

        /* --- Route Card Footer & Actions --- */
        .route-card-footer {
            background-color: #f8f9fa; /* Lighter background for footer */
            padding: 0.75rem 1.25rem; /* Optimized padding */
            border-top: 1px solid #e9ecef;
            border-radius: 0 0 0.75rem 0.75rem; /* Match card border radius */
            display: flex;
            justify-content: space-between; /* Space out action buttons and toggle */
            align-items: center;
            gap: 0.75rem; /* Gap between button group and toggle */
            flex-wrap: wrap; /* Allow wrapping on small screens */
        }
        .route-card-actions { /* New div to group buttons */
            display: flex;
            gap: 0.5rem; /* Gap between buttons */
            flex-wrap: wrap;
        }
        .route-card-actions .btn {
            padding: 0.4rem 0.8rem; /* Smaller buttons */
            font-size: 0.8rem;
            border-radius: 0.4rem; /* More rounded */
            font-weight: 500;
        }
        .form-check.form-switch { /* Popular toggle */
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem; 
        }

        /* --- Charter Controls Section --- */
        .charter-controls {
            padding: 1rem 1.25rem; 
            background-color: #fcfcfc; 
            border-top: 1px dashed #e0e0e0; 
            border-radius: 0 0 0.75rem 0.75rem; 
            position: relative; 
            z-index: 1; 
        }

        .charter-controls .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            display: block; 
        }

        .charter-controls .input-group .form-control {
            border-radius: 0.5rem 0 0 0.5rem; 
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem; 
        }
        .charter-controls .input-group .btn {
            border-radius: 0 0.5rem 0.5rem 0; 
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
        }
        /* Custom secondary button for check status */
        .charter-controls .input-group .btn-secondary {
            background-color: #6c757d; /* Standard grey for secondary */
            border-color: #6c757d;
            color: white;
        }
        .charter-controls .input-group .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }


        /* Chartered Dates List - NEW ENHANCED STYLING */
        .charter-dates-info {
            margin-top: 1rem;
            padding-top: 0.75rem;
            border-top: 1px solid #eee; /* Light separator for this section */
        }
        .charter-dates-info p.small {
            font-size: 0.8em; 
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .charter-dates-list {
            list-style: none;
            padding: 0.5rem; 
            margin: 0;
            max-height: 90px; 
            overflow-y: auto; 
            font-size: 0.75em; 
            border-radius: 6px;
            background-color: #f4f8ff; 
            border: 1px solid #cfe2ff; 
            display: flex; 
            flex-wrap: wrap; 
            gap: 0.4rem; 
        }

        .charter-dates-list li {
            background-color: #e0f2fe; 
            color: #0d6efd; 
            padding: 0.2rem 0.6rem; 
            border-radius: 12px; 
            font-weight: 500;
            flex-shrink: 0; 
            cursor: pointer; 
            transition: background-color 0.2s ease, transform 0.1s ease;
        }
        .charter-dates-list li:hover {
            background-color: #c9e6ff; 
            transform: translateY(-1px); 
        }

        .charter-dates-list p.mb-0 { 
            font-size: 0.8em; 
            color: #7a7a7a;
            font-style: italic;
            text-align: center;
            width: 100%; 
            padding: 0.5rem;
        }

        /* --- Modal Styles (Schedule View) --- */
        .modal-header {
            background-color: #4a69bd; 
            color: white;
            border-bottom: none;
            border-radius: 0.75rem 0.75rem 0 0; 
            padding: 1.25rem 1.5rem;
        }
        .modal-title {
            font-weight: 700;
            font-size: 1.35rem;
        }
        .modal-body {
            padding: 1.5rem;
        }

        .schedule-day-block {
            background-color: #fcfcfc;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 1.25rem; 
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        .schedule-day-block h6 {
            font-weight: 700;
            font-size: 1.1rem;
            color: #212529;
            margin-bottom: 1rem;
        }
        .schedule-timeline-item {
            display: flex; align-items: flex-start; position: relative; padding-bottom: 20px;
        }
        .schedule-timeline-item:not(:last-child)::before {
            content: ''; position: absolute; left: 11px; top: 30px; bottom: 0; width: 2px;
            background-image: linear-gradient(to bottom, #ced4da 60%, transparent 40%);
            background-size: 1px 10px;
        }
        .schedule-timeline-icon {
            width: 25px; height: 25px; font-size: 0.9rem;
            border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; z-index: 1;
            box-shadow: 0 0 0 3px #f8f9fa;
        }.schedule-timeline-icon i{
           color:white; /* Changed to white for consistency with other icon-styles */
        }
        .icon-start { background-color: #0d6efd; } /* Re-applying these specific colors */
        .icon-stop { background-color: #6c757d; }
        .icon-end { background-color: #198754; }

        .schedule-timeline-content {
            margin-left: 15px;
        }
        .schedule-timeline-content strong {
            font-size: 0.95rem; font-weight: 600;
        }
        .time-info {
            font-size: 0.8em; color: #6c757d;
        }
        .duration-pill {
            font-size: 0.7em; font-weight: 600; color: #6c757d;
            background-color: #e9ecef; padding: 1px 6px; border-radius: 50px;
        }


        /* --- Responsive Adjustments --- */
        @media (max-width: 767.98px) {
            .page-header { flex-direction: column; align-items: flex-start; margin-bottom: 1rem; gap: 0.5rem; }
            h2 { font-size: 1.6rem; }
            .page-header .button-13 { width: 100%; margin-bottom: 0.5rem; } 

            .route-grid { grid-template-columns: 1fr; gap: 1rem; } 
            .route-card { border-radius: 0.75rem; } 
            .route-card-footer {
                flex-direction: column;
                align-items: stretch;
                padding: 0.75rem 1rem; 
            }
            .route-card-actions {
                justify-content: center; 
                gap: 0.5rem; 
                margin-bottom: 0.75rem; 
                width: 100%; 
            }
            .route-card-actions .btn {
                flex-grow: 1; 
                max-width: 150px; 
            }
            .form-check.form-switch { 
                align-self: center; 
                width: 100%; 
                justify-content: center; 
                margin-bottom: 0; 
            }

            .charter-controls { padding: 1rem; } 
            .charter-controls .input-group { 
                flex-wrap: wrap; 
                margin-bottom: 0.5rem; 
            }
            .charter-controls .input-group .form-control,
            .charter-controls .input-group .btn {
                border-radius: 0.5rem; 
                width: 100%; 
                margin-top: 0.5rem; 
            }
            .charter-controls .input-group .form-control { order: 1; } 
            .charter-controls .input-group .btn { order: 2; margin-left: 0 !important; } 

            .charter-dates-list { padding: 0.4rem; } 
        }
    </style>
     <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <div class="page-header my-1">
               
                <?php if ($can_view_reports): ?>
                <a href="deleted_routes_report" class="button-13 bg-danger text-light">View Deleted Routes</a>    
                <?php endif; ?>

                <?php if ($can_manage_routes): // Permission check for Add New Route ?>
                <a href="add_route.php" class="button-13 bg-secondary text-light"><i class="fas fa-plus me-2"></i>Add New Route</a> 
                <?php endif; ?>
            </div>
            <div class="page-header ">
              
                <h2>All Saved Routes</h2>
                
             
            </div>

            <?php if (empty($routes_list)): ?>
                <div class="alert alert-info text-center"><h3>No routes found.</h3><p>Why not <a href="add_route.php" class="alert-link">add one now</a> to get started?</p></div>
            <?php else: ?>
                <div class="route-grid">
                    <?php foreach ($routes_list as $route): ?>
                        <div class="route-card status-<?php echo htmlspecialchars($route['status']); ?>">
                            <div class="route-card-body">
                                <div class="route-card-title">
                                    <h5 class="mb-0">
                                        <span class="star-container">
                                            <?php if ($route['is_popular']): ?>
                                                <i class="fas fa-star text-warning me-2" title="Popular Route"></i>
                                            <?php endif; ?>
                                        </span>
                                        <?php echo htmlspecialchars($route['route_name']); ?>
                                    </h5>
                                    <span class="badge route-status-badge <?php echo $route['status'] == 'Active' ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'; ?>"><?php echo htmlspecialchars($route['status']); ?></span>
                                </div>
                                <div class="route-details-grid mt-3">
                                    <i class="fas fa-bus fa-fw"></i><span><?php echo htmlspecialchars($route['bus_name']); ?></span>
                                    <i class="fas fa-route fa-fw"></i><span><?php echo htmlspecialchars($route['starting_point']); ?> → <?php echo htmlspecialchars($route['ending_point']); ?></span>
                                </div>
                            </div>
                            <div class="route-card-footer">
                                <div class="route-card-actions">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal<?php echo htmlspecialchars($route['route_id']); ?>"><i class="fas fa-calendar-alt me-1"></i>Schedule</button>
                                    <?php if ($can_edit_routes): ?>
                                        <a href="add_route.php?action=edit&id=<?php echo htmlspecialchars($route['route_id']); ?>" class="btn btn-sm btn-info"><i class="fas fa-edit me-1"></i>Edit</a>
                                    <?php endif; ?>
                                    <?php if ($can_delete_routes): ?>
                                        <button type="button" class="btn btn-sm btn-danger delete-route-btn" data-route-id="<?php echo htmlspecialchars($route['route_id']); ?>"><i class="fas fa-trash me-1"></i>Delete</button>
                                    <?php endif; ?>
                                </div>
                                <?php if ($can_toggle_popular_route): // Permission check for Popular Toggle ?>
                                    <div class="form-check form-switch" title="Toggle Popular Status">
                                        <input class="form-check-input popular-toggle" type="checkbox" role="switch" data-route-id="<?php echo htmlspecialchars($route['route_id']); ?>" <?php echo $route['is_popular'] ? 'checked' : ''; ?>>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($can_charter_bus): // Permission check for entire Charter section ?>
                            <div class="charter-controls">
                                <label class="form-label fw-bold small mb-2">Full Bus Booking (Charter)</label>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control charter-date-picker" placeholder="Select date to book/unbook...">
                                    <button class="btn btn-sm btn-secondary check-charter-status-btn" data-route-id="<?php echo htmlspecialchars($route['route_id']); ?>" type="button">Check/Update Status</button>
                                </div>
                                
                                <!-- Display upcoming chartered dates -->
                                <div class="charter-dates-info">
                                    <p class="small text-muted mb-1">Upcoming Chartered Dates:</p>
                                    <ul class="charter-dates-list">
                                        <?php if (!empty($chartered_dates_by_route[$route['route_id']])): ?>
                                            <?php foreach ($chartered_dates_by_route[$route['route_id']] as $charter_booking_data): ?>
                                                <li data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo htmlspecialchars('Booked by: ' . $charter_booking_data['customer_name'] . ' (' . $charter_booking_data['customer_mobile'] . ')'); ?>"><?php echo date('D, d M Y', strtotime($charter_booking_data['travel_date'])); ?></li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="mb-0">No upcoming charters.</p>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div> 
 
                        <div class="modal fade" id="scheduleModal<?php echo htmlspecialchars($route['route_id']); ?>" tabindex="-1">
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> 

<script>
$(document).ready(function() {
    flatpickr(".charter-date-picker", {
        minDate: "today",
        dateFormat: "Y-m-d",
    });

    // Initialize Bootstrap Tooltips for chartered dates
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

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

    
    $('.check-charter-status-btn').on('click', function() {
        const button = $(this);
        const routeId = button.data('route-id');
        const datePicker = button.siblings('.charter-date-picker');
        const selectedDate = datePicker.val();

        if (!selectedDate) {
            Swal.fire('Input Needed', 'Please select a date first.', 'info');
            return;
        }

        $.ajax({
            url: 'function/backend/route_actions.php',
            type: "POST", 
            data: { action: 'get_charter_status', route_id: routeId, travel_date: selectedDate },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const customerName = response.customer_name || '';
                    const customerMobile = response.customer_mobile || '';
                    promptForCharterAction(response.is_chartered, routeId, selectedDate, customerName, customerMobile);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: () => Swal.fire('Error', 'Could not connect to the server.', 'error')
        });
    });

    function promptForCharterAction(isCurrentlyChartered, routeId, travelDate, customerName = '', customerMobile = '') {
        let title, htmlContent, confirmButtonColor, confirmButtonText;

        if (isCurrentlyChartered) {
            title = 'Bus is Already Chartered!';
            htmlContent = `<p>This route is currently chartered for <strong>${travelDate}</strong>.</p>`;
            if (customerName) {
                 htmlContent += `<p class="mb-1"><strong>Booked by:</strong> ${customerName}</p>`;
                 htmlContent += `<p><strong>Contact:</strong> ${customerMobile}</p>`;
            }
            htmlContent += `<p class="mt-3">Do you want to make it <strong>AVAILABLE</strong> for regular booking?</p>`;
            confirmButtonColor = '#dc3545'; 
            confirmButtonText = 'Yes, Make Available!';
        } else {
            title = 'Bus is Available for Charter';
            htmlContent = `<p>Do you want to book this entire route for a <strong>private charter</strong> on ${travelDate}?</p>
                   <input id="swal-customer-name" class="swal2-input" placeholder="Customer Name (Required)">
                   <input id="swal-customer-mobile" class="swal2-input" placeholder="Customer Mobile (Required)">`;
            confirmButtonColor = '#198754'; 
            confirmButtonText = 'Yes, Book It!';
        }

        const swalConfig = {
            title: title,
            html: htmlContent,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: confirmButtonColor,
            confirmButtonText: confirmButtonText,
            focusConfirm: false,
            preConfirm: () => {
                if (!isCurrentlyChartered) { 
                    const name = document.getElementById('swal-customer-name').value;
                    const mobile = document.getElementById('swal-customer-mobile').value;
                    if (!name || !mobile) {
                        Swal.showValidationMessage('Customer Name and Mobile are required.');
                        return false;
                    }
                    return { name: name, mobile: mobile };
                }
                return {}; 
            }
        };

        Swal.fire(swalConfig).then((result) => {
            if (result.isConfirmed) {
                const postData = {
                    action: 'toggle_charter_booking',
                    route_id: routeId,
                    travel_date: travelDate,
                    customer_name: result.value.name || null, 
                    customer_mobile: result.value.mobile || null
                };

                $.ajax({
                    url: 'function/backend/route_actions.php',
                    type: 'POST',
                    data: postData,
                    dataType: 'json',
                    success: (response) => {
                        if (response.res === 'true') {
                            Swal.fire('Success!', response.notif_desc, 'success').then(() => {
                                location.reload(); 
                            });
                        } else {
                            Swal.fire('Error!', response.notif_desc, 'error');
                        }
                    },
                    error: () => Swal.fire('Error', 'Could not connect to the server.', 'error')
                });
            }
        });
    }
});
</script>
</body>
</html>