<?php
include 'includes/header.php';

// --- HELPER FUNCTIONS (No changes needed) ---
function find_next_available_date($operating_days_str, $start_date_str)
{
    if (empty($operating_days_str)) return null;
    $day_map = ['Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6, 'Sun' => 7];
    $operating_days_arr = array_map('trim', explode(',', $operating_days_str));
    $operating_day_numbers = [];
    foreach ($operating_days_arr as $day) {
        if (isset($day_map[$day])) $operating_day_numbers[] = $day_map[$day];
    }
    if (empty($operating_day_numbers)) return null;

    $date = new DateTime($start_date_str);
    for ($i = 0; $i < 365; $i++) {
        if (in_array((int)$date->format('N'), $operating_day_numbers)) {
            return $date->format('Y-m-d');
        }
        $date->modify('+1 day');
    }
    return null;
}

function find_closest_location($input, $locations, $max_distance = 2)
{
    if (empty($input) || empty($locations)) return $input;
    $best_match = null;
    $shortest_distance = -1;

    foreach ($locations as $location) {
        $distance = levenshtein(strtolower($input), strtolower($location));
        if ($distance === 0) return $location;
        if ($shortest_distance < 0 || $distance < $shortest_distance) {
            $shortest_distance = $distance;
            $best_match = $location;
        }
    }
    return ($shortest_distance <= $max_distance) ? $best_match : $input;
}

// --- PHP DATA LOGIC ---
$all_locations = [];
$direct_matches = [];
$processed_routes = [];
$error_message = null;
$all_routes_for_js = [];

try {
    $stmt_locations = $_conn_db->query("(SELECT DISTINCT starting_point FROM routes WHERE status = 'Active') UNION (SELECT DISTINCT ending_point FROM routes WHERE status = 'Active') UNION (SELECT DISTINCT stop_name FROM route_stops) ORDER BY starting_point ASC");
    $all_locations = array_filter($stmt_locations->fetchAll(PDO::FETCH_COLUMN));

    $from_location_raw = $_GET['from'] ?? null;
    $to_location_raw = $_GET['to'] ?? null;
    $journey_date = $_GET['date'] ?? null;
    $is_search_performed = ($from_location_raw && $to_location_raw && $journey_date);

    if ($is_search_performed) {
        $from_location = find_closest_location($from_location_raw, $all_locations);
        $to_location = find_closest_location($to_location_raw, $all_locations);
        $day_of_week = date('D', strtotime($journey_date));

        $stmt = $_conn_db->prepare("
            SELECT
                b.bus_name, b.bus_id, b.bus_type,
                r.route_id, r.starting_point, r.ending_point,
                rsch.schedule_id, rsch.departure_time,
                (SELECT GROUP_CONCAT(DISTINCT rs.operating_day SEPARATOR ',') FROM route_schedules rs WHERE rs.route_id = r.route_id) AS operating_days_list,
                COUNT(DISTINCT s.seat_id) AS total_seats,
                COUNT(DISTINCT p.passenger_id) AS booked_seats,
                GROUP_CONCAT(DISTINCT bc.category_name SEPARATOR ', ') AS categories,
                MIN(rs_prices.price_seater_lower) AS journey_price,
                cb.charter_id IS NOT NULL AS is_chartered
            FROM route_schedules rsch
            JOIN routes r ON rsch.route_id = r.route_id
            JOIN buses b ON r.bus_id = b.bus_id
            LEFT JOIN seats s ON s.bus_id = b.bus_id AND s.is_bookable = 1
            LEFT JOIN bookings bk ON bk.route_id = r.route_id AND bk.travel_date = :journey_date AND bk.booking_status = 'CONFIRMED'
            LEFT JOIN passengers p ON p.booking_id = bk.booking_id AND p.passenger_status = 'CONFIRMED'
            LEFT JOIN bus_category_map bcm ON b.bus_id = bcm.bus_id
            LEFT JOIN bus_categories bc ON bcm.category_id = bc.category_id
            LEFT JOIN route_stops rs_prices ON rs_prices.route_id = r.route_id AND rs_prices.price_seater_lower > 0
            LEFT JOIN charter_bookings cb ON cb.route_id = r.route_id AND cb.travel_date = :journey_date_charter
            WHERE
                rsch.operating_day = :day_of_week
                AND r.status = 'Active'
                AND (
                    EXISTS (SELECT 1 FROM routes rt_from WHERE rt_from.route_id = r.route_id AND LOWER(rt_from.starting_point) = LOWER(:from_location_start_point))
                    OR
                    EXISTS (SELECT 1 FROM route_stops rs_from WHERE rs_from.route_id = r.route_id AND LOWER(rs_from.stop_name) = LOWER(:from_location_stop_name))
                )
                AND (
                    EXISTS (SELECT 1 FROM routes rt_to WHERE rt_to.route_id = r.route_id AND LOWER(rt_to.ending_point) = LOWER(:to_location_ending_point))
                    OR
                    EXISTS (SELECT 1 FROM route_stops rs_to WHERE rs_to.route_id = r.route_id AND LOWER(rs_to.stop_name) = LOWER(:to_location_stop_name))
                )
            GROUP BY r.route_id, rsch.schedule_id, cb.charter_id
            ORDER BY rsch.departure_time ASC
        ");

        $stmt->execute([
            ':journey_date' => $journey_date,
            ':journey_date_charter' => $journey_date,
            ':day_of_week' => $day_of_week,
            ':from_location_start_point' => $from_location,
            ':from_location_stop_name' => $from_location,
            ':to_location_ending_point' => $to_location,
            ':to_location_stop_name' => $to_location
        ]);
        $direct_matches_today = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $duration_stmt = $_conn_db->prepare("SELECT stop_name, duration_from_start_minutes FROM route_stops WHERE route_id = ? UNION SELECT starting_point, 0 FROM routes WHERE route_id = ?");

        foreach ($direct_matches_today as $bus) {
            if ($bus['is_chartered']) {
                $bus['available_seats'] = 0;
                $bus['chartered_status'] = true;
            } else {
                $duration_stmt->execute([$bus['route_id'], $bus['route_id']]);
                $durations = $duration_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                $from_offset = (int)($durations[$from_location] ?? 0);
                $to_offset = (int)($durations[$to_location] ?? 0);

                if ($to_location == $bus['ending_point'] && !isset($durations[$to_location])) {
                    $max_duration_stmt = $_conn_db->prepare("SELECT MAX(duration_from_start_minutes) FROM route_stops WHERE route_id = ?");
                    $max_duration_stmt->execute([$bus['route_id']]);
                    $to_offset = (int)$max_duration_stmt->fetchColumn();
                }

                if ($to_offset <= $from_offset) continue;
                $bus['available_seats'] = (int)$bus['total_seats'] - (int)$bus['booked_seats'];
            }

            $bus_base_time = strtotime($journey_date . ' ' . $bus['departure_time']);
            $bus['departure'] = date('H:i', $bus_base_time + (($durations[$from_location] ?? 0) * 60));

            if ($journey_date == date('Y-m-d') && strtotime($bus['departure']) < time() && !$bus['is_chartered']) continue;

            if (!$bus['is_chartered']) {
                $bus['arrival'] = date('H:i', $bus_base_time + ($to_offset * 60));
                $duration_minutes = $to_offset - ($durations[$from_location] ?? 0);
                $bus['duration'] = floor($duration_minutes / 60) . 'h ' . ($duration_minutes % 60) . 'm';
            } else {
                $bus['arrival'] = 'Chartered';
                $bus['duration'] = 'Full Day';
            }

            $bus['price'] = isset($bus['journey_price']) ? number_format($bus['journey_price'], 2) : 'N/A';
            $bus['link_params'] = http_build_query(['schedule_id' => $bus['schedule_id'], 'from' => $from_location, 'to' => $to_location, 'date' => $journey_date]);
            $direct_matches[] = $bus;
        }
    }

    if (empty($direct_matches)) {
        $all_schedules_stmt = $_conn_db->prepare("
            SELECT 
                b.bus_name, b.bus_id, b.bus_type, 
                r.route_id, r.starting_point, r.ending_point, 
                MIN(rs_prices.price_seater_lower) AS route_min_price,
                (SELECT GROUP_CONCAT(DISTINCT rs.operating_day SEPARATOR ',') FROM route_schedules rs WHERE rs.route_id = r.route_id) AS operating_days_list
            FROM routes r
            JOIN buses b ON r.bus_id = b.bus_id
            LEFT JOIN route_stops rs_prices ON rs_prices.route_id = r.route_id AND rs_prices.price_seater_lower > 0
            WHERE r.status = 'Active'
            GROUP BY r.route_id
            ORDER BY r.starting_point, r.ending_point
        ");
        $all_schedules_stmt->execute();
        $processed_routes = $all_schedules_stmt->fetchAll(PDO::FETCH_ASSOC);

        $charter_check_stmt_for_other_routes = $_conn_db->prepare("SELECT charter_id FROM charter_bookings WHERE route_id = ? AND travel_date = ?");
        $seats_total_stmt = $_conn_db->prepare("SELECT COUNT(s.seat_id) FROM seats s WHERE s.bus_id = ? AND s.is_bookable = 1");
        $seats_booked_stmt = $_conn_db->prepare("SELECT COUNT(p.passenger_id) FROM passengers p JOIN bookings bk ON p.booking_id = bk.booking_id WHERE bk.route_id = ? AND bk.travel_date = ? AND p.passenger_status = 'CONFIRMED'");
        $max_duration_stmt_other = $_conn_db->prepare("SELECT MAX(duration_from_start_minutes) FROM route_stops WHERE route_id = ?");
        $departure_time_stmt = $_conn_db->prepare("SELECT schedule_id, departure_time FROM route_schedules WHERE route_id = ? AND operating_day = ? LIMIT 1");
    }
} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
}
?>
<style>
    /* CSS remains unchanged */
    .bus-list-container {
        --brand-color: #7b003a;
        --brand-color-light: #fcebeb;
        --text-dark: #2d3748;
        --text-light: #718096;
        --bg-light: #f8fafc;
        --border-color: #e2e8f0;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        --card-shadow-hover: 0 8px 25px rgba(0, 0, 0, 0.1);
        --card-bg: #ffffff;
    }

    .bus-item-card {
        background-color: var(--card-bg);
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        margin-bottom: 1.5rem;
        transition: all 0.2s ease-in-out;
        border: 1px solid var(--border-color);
        overflow: hidden;
    }

    .bus-item-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--card-shadow-hover);
        border-color: var(--brand-color);
    }

    .card-content {
        padding: 1rem 1.25rem;
    }

    .bus-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 1px dashed var(--border-color);
        padding-bottom: 0.75rem;
        margin-bottom: 1rem;
    }

    .bus-header .bus-name {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--text-dark);
        margin-bottom: 0;
    }

    .bus-header .bus-type {
        font-size: 0.85rem;
        color: var(--text-light);
    }

    .bus-header .seats-available {
        text-align: right;
        font-size: 0.9rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .seats-available .value {
        font-size: 1.1rem;
    }

    .journey-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .journey-point {
        text-align: center;
        flex: 1;
    }

    .journey-point .time {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .journey-point .location {
        font-size: 0.8rem;
        color: var(--text-light);
        word-break: break-word;
    }

    .journey-arrow {
        flex: 0 0 auto;
        padding: 0 1rem;
        text-align: center;
    }

    .journey-arrow .arrow-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--brand-color-light);
        color: var(--brand-color);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .journey-arrow .duration {
        font-size: 0.75rem;
        color: var(--text-light);
        margin-top: 2px;
    }

    .card-footer-custom {
        background-color: var(--bg-light);
        padding: 0.75rem 1.25rem;
        /* display: flex; */
        /* justify-content: space-between; */
        /* align-items: center; */
        font-size: 0.85rem;
        /* flex-wrap: nowrap; */
        /* gap: 1rem; */
    }

    .footer-left .operating-days {
        font-weight: 500;
        color: var(--text-light);
    }

    .footer-left .operating-days strong {
        color: var(--success-color);
    }

    .footer-right {
        display: flex;
        justify-content: right;
        /* align-items: center; */
        /* gap: 1rem; */
        /* flex-shrink: 0; */
    }
    
    .footer-right span{
        display: flex;
        justify-content: center;
        align-items: center;
        /* align-items: center; */
        /* gap: 1rem; */
        /* flex-shrink: 0; */
    }

    .footer-right .price {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .footer-right .btn {
        background-color: var(--brand-color);
        border-color: var(--brand-color);
        color: white;
        font-weight: 600;
        border-radius: 50px;
        padding: 0.5rem 1.25rem;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .footer-right .btn:hover {
        background-color: #5a002a;
        transform: scale(1.05);
    }

    .footer-right .btn.disabled {
        background-color: #adb5bd;
        border-color: #adb5bd;
        cursor: not-allowed;
    }
    @media screen and (max-width: 767px) {
        .footer-right {
        display: flex;
        justify-content: space-between;
        /* align-items: center; */
        /* gap: 1rem; */
        /* flex-shrink: 0; */
    }
}
</style>

<body class="mt-5 pt-5">
    <div class="container my-3 pt-5">
        <div class="search-form-card">
            <!-- Search Form remains unchanged -->
            <form action="bus_list.php" method="GET" id="bus-search-form">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-4 col-md-12">
                        <div style="position: relative; width: 100%;">
                            <i class="fa-solid fa-bus" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#888; z-index:2;"></i>
                            <input type="text" class="form-control" name="from" id="from-city" placeholder="Leaving from" required autocomplete="off" value="<?php echo htmlspecialchars($from_location ?? ''); ?>" style="padding-left: 40px;">
                            <div class="suggestions-dropdown" id="from-suggestions"></div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div style="position: relative; width: 100%;">
                            <i class="fa-solid fa-location-dot" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#888; z-index:2;"></i>
                            <input type="text" class="form-control" name="to" id="to-city" placeholder="Going to" required autocomplete="off" value="<?php echo htmlspecialchars($to_location ?? ''); ?>" style="padding-left: 40px;">
                            <div class="suggestions-dropdown" id="to-suggestions"></div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <div style="position: relative; width: 100%;">
                            <i class="fa-solid fa-calendar-days" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#888; z-index:2;"></i>
                            <input type="date" class="form-control" name="date" id="date" value="<?php echo htmlspecialchars($journey_date ?? date('Y-m-d')); ?>" required min="<?php echo date('Y-m-d'); ?>" style="padding-left: 40px;">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6 d-grid">
                        <button type="submit" class="btn btn-danger">Search Buses</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="bus-list-container container mt-4">
        <div class="row">
            <aside class="col-lg-3">
                <!-- Filters remain unchanged -->
                <div class="filter-card sticky-top d-none d-lg-block" style="top: 100px;">
                    <h5>FILTER BY</h5>
                    <div id="bus-type-filters-desktop"></div>
                    <hr>
                    <div id="departure-time-filters-desktop"></div>
                </div>
                <div class="accordion d-lg-none mb-4" id="mobile-filter-accordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters">
                                <i class="bi bi-funnel-fill me-2"></i> Tap to Filter Buses
                            </button>
                        </h2>
                        <div id="collapseFilters" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#mobile-filter-accordion">
                            <div class="accordion-body">
                                <div id="bus-type-filters-mobile"></div>
                                <hr>
                                <div id="departure-time-filters-mobile"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="col-lg-9">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <div id="bus-listings-container">
                    <!-- DIRECT MATCHES (Search Results) -->
                    <?php if ($is_search_performed && !empty($direct_matches)): ?>
                        <h4 class="mb-3">Buses from <strong><?php echo htmlspecialchars($from_location); ?></strong> to <strong><?php echo htmlspecialchars($to_location); ?></strong></h4>
                        <?php foreach ($direct_matches as $bus): ?>
                            <?php
                            $is_bus_chartered = $bus['is_chartered'] ?? false;
                            $available_seats = $is_bus_chartered ? 0 : max(0, (int)$bus['available_seats']);
                            $seat_color = ($available_seats <= 5 && $available_seats > 0) ? 'text-danger' : 'text-success';

                            $day_order = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                            $days_arr = array_unique(array_map('trim', explode(',', $bus['operating_days_list'])));
                            usort($days_arr, fn($a, $b) => array_search($a, $day_order) - array_search($b, $day_order));
                            $display_days = (count($days_arr) >= 7) ? 'Daily' : implode(', ', $days_arr);
                            ?>
                            <div class="bus-item-card" data-bus-type="<?php echo htmlspecialchars($bus['bus_type']); ?>" data-departure-time="<?php echo htmlspecialchars($bus['departure']); ?>">
                                <div class="card-content">
                                    <div class="bus-header">
                                        <div>
                                            <h6 class="bus-name"><?php echo htmlspecialchars($bus['bus_name']); ?></h6>
                                            <p class="bus-type mb-0"><?php echo htmlspecialchars($bus['bus_type']); ?></p>
                                        </div>
                                        <div class="seats-available <?php echo $seat_color; ?>">
                                            <?php if ($is_bus_chartered): ?>
                                                <span class="text-danger fw-bold">Chartered</span>
                                            <?php else: ?>
                                                <span class="value"><?php echo $available_seats; ?></span> Seats Left
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="journey-info">
                                        <div class="journey-point">
                                            <div class="time"><?php echo htmlspecialchars($bus['departure']); ?></div>
                                            <div class="location"><?php echo htmlspecialchars($from_location); ?></div>
                                        </div>
                                        <div class="journey-arrow">
                                            <div class="arrow-icon"><i class="bi bi-arrow-right"></i></div>
                                            <div class="duration"><?php echo htmlspecialchars($bus['duration']); ?></div>
                                        </div>
                                        <div class="journey-point">
                                            <div class="time"><?php echo htmlspecialchars($bus['arrival']); ?></div>
                                            <div class="location"><?php echo htmlspecialchars($to_location); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer-custom row">
                                    <div class="footer-left col-md-6 col-12">
                                        <span class="operating-days"><strong>Runs On:</strong> <?php echo $display_days; ?></span>
                                    </div>
                                    <div class="footer-right col-md-6 col-12">
                                        <span class="price px-2">₹<?php echo htmlspecialchars($bus['price']); ?></span>
                                        <a href="select_seats.php?<?php echo htmlspecialchars($bus['link_params']); ?>" class="btn <?php if ($available_seats <= 0) echo 'disabled'; ?>">
                                            <?php echo ($available_seats > 0) ? 'View Seats' : 'Sold Out'; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- NO RESULTS MESSAGE (for filters) -->
                    <div id="no-filter-results" class="filter-card text-center py-5" style="display: none;">
                        <p class="lead my-1 text-danger">Sorry, no buses match your selected filters.</p>
                    </div>
                </div>

                <!-- NO SEARCH RESULTS & OTHER ROUTES -->
                <?php if (empty($direct_matches)): ?>
                    <?php if ($is_search_performed): ?>
                        <div class="filter-card text-center">
                            <p class="lead text-danger my-1">Sorry, no buses were found for your search on <?php echo date('d M, Y', strtotime($journey_date)); ?>.</p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($processed_routes)): ?>
                        <hr class="my-4">
                        <h4 class="mb-3"><?php echo $is_search_performed ? 'Other Available Routes' : 'All Our Available Routes'; ?></h4>
                        <?php foreach ($processed_routes as $route): ?>
                            <?php
                            $all_days_for_route = $route['operating_days_list'];
                            $start_search_date = $journey_date ?? date('Y-m-d');

                            // *** THIS LINE IS THE FIX - It was the source of the error ***
                            if ($start_search_date < date('Y-m-d')) {
                                $start_search_date = date('Y-m-d');
                            }
                            // *** END OF FIX ***

                            $next_date = find_next_available_date($all_days_for_route, $start_search_date);
                            if (!$next_date) continue;

                            $next_day_name = date('D', strtotime($next_date));
                            $departure_time_stmt->execute([$route['route_id'], $next_day_name]);
                            $schedule_info = $departure_time_stmt->fetch(PDO::FETCH_ASSOC);

                            if (!$schedule_info) continue;

                            $correct_departure_time = $schedule_info['departure_time'];
                            $correct_schedule_id = $schedule_info['schedule_id'];

                            if ($next_date == date('Y-m-d') && strtotime($next_date . ' ' . $correct_departure_time) < time()) {
                                $next_date = find_next_available_date($all_days_for_route, date('Y-m-d', strtotime('+1 day')));
                                if (!$next_date) continue;
                                $next_day_name = date('D', strtotime($next_date));
                                $departure_time_stmt->execute([$route['route_id'], $next_day_name]);
                                $schedule_info = $departure_time_stmt->fetch(PDO::FETCH_ASSOC);
                                if (!$schedule_info) continue;
                                $correct_departure_time = $schedule_info['departure_time'];
                                $correct_schedule_id = $schedule_info['schedule_id'];
                            }

                            $charter_check_stmt_for_other_routes->execute([$route['route_id'], $next_date]);
                            $is_chartered_other_route = $charter_check_stmt_for_other_routes->fetchColumn() !== false;

                            $available_seats = 0;
                            $other_route_arrival_time = 'N/A';
                            $other_route_duration = 'N/A';

                            if (!$is_chartered_other_route) {
                                $seats_total_stmt->execute([$route['bus_id']]);
                                $total_seats_other_route = (int)$seats_total_stmt->fetchColumn();
                                $seats_booked_stmt->execute([$route['route_id'], $next_date]);
                                $booked_seats_other_route = (int)$seats_booked_stmt->fetchColumn();
                                $available_seats = max(0, $total_seats_other_route - $booked_seats_other_route);

                                $max_duration_stmt_other->execute([$route['route_id']]);
                                $max_duration_minutes = (int)$max_duration_stmt_other->fetchColumn();
                                $other_route_base_time = strtotime($next_date . ' ' . $correct_departure_time);
                                $other_route_arrival_time = date('H:i', $other_route_base_time + ($max_duration_minutes * 60));
                                $other_route_duration = floor($max_duration_minutes / 60) . 'h ' . ($max_duration_minutes % 60) . 'm';
                            }

                            $link_params = http_build_query(['schedule_id' => $correct_schedule_id, 'from' => $route['starting_point'], 'to' => $route['ending_point'], 'date' => $next_date]);
                            $day_order = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                            $days_arr = array_unique(array_map('trim', explode(',', $route['operating_days_list'])));
                            usort($days_arr, fn($a, $b) => array_search($a, $day_order) - array_search($b, $day_order));
                            $display_days = (count($days_arr) >= 7) ? 'Daily' : implode(', ', $days_arr);

                            $bus_types_json = json_encode([$route['bus_type']]);
                            if (!$is_chartered_other_route) {
                                $all_routes_for_js[] = ['bus_types' => [$route['bus_type']], 'departure_time' => date('H:i', strtotime($correct_departure_time))];
                            }
                            ?>
                            <div class="bus-item-card" data-bus-types='<?php echo htmlspecialchars($bus_types_json, ENT_QUOTES, 'UTF-8'); ?>' data-departure-time="<?php echo date('H:i', strtotime($correct_departure_time)); ?>">
                                <div class="card-content">
                                    <div class="bus-header">
                                        <div>
                                            <h6 class="bus-name"><?php echo htmlspecialchars($route['bus_name']); ?></h6>
                                            <p class="bus-type mb-0"><?php echo htmlspecialchars($route['bus_type']); ?></p>
                                        </div>
                                        <div class="seats-available <?php echo ($available_seats <= 5 && $available_seats > 0) ? 'text-danger' : 'text-success'; ?>">
                                            <?php if ($is_chartered_other_route): ?>
                                                <span class="text-danger fw-bold">Chartered</span>
                                            <?php else: ?>
                                                <span class="value"><?php echo $available_seats; ?></span> Seats Left
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="journey-info">
                                        <div class="journey-point">
                                            <div class="time"><?php echo date('H:i', strtotime($correct_departure_time)); ?></div>
                                            <div class="location"><?php echo htmlspecialchars($route['starting_point']); ?></div>
                                        </div>
                                        <div class="journey-arrow">
                                            <div class="arrow-icon"><i class="bi bi-arrow-right"></i></div>
                                            <div class="duration"><?php echo htmlspecialchars($other_route_duration); ?></div>
                                        </div>
                                        <div class="journey-point">
                                            <div class="time"><?php echo htmlspecialchars($other_route_arrival_time); ?></div>
                                            <div class="location"><?php echo htmlspecialchars($route['ending_point']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer-custom row">
                                    <div class="footer-left col-md-6 col-12">
                                        <span class="operating-days"><strong>Runs On:</strong> <?php echo $display_days; ?> | <strong>Next:</strong> <?php echo date('D, d M', strtotime($next_date)); ?></span>
                                    </div>
                                    <div class="footer-right col-md-6 col-12">
                                        <span class="price px-2">₹<?php echo number_format($route['route_min_price'] ?? 0, 2); ?></span> 
                                        <a href="select_seats.php?<?php echo $link_params; ?>" class="btn <?php if ($available_seats <= 0) echo 'disabled'; ?>">
                                            <?php echo ($available_seats > 0) ? 'View Next Trip' : 'Sold Out'; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <br><br><br><br><br>
    <?php include 'includes/footer.php'; ?>

    <script>
        // JavaScript logic remains unchanged.
        document.addEventListener('DOMContentLoaded', function() {
            const allLocations = <?php echo json_encode($all_locations); ?>;
            const fromInput = document.getElementById('from-city');
            const toInput = document.getElementById('to-city');
            const fromSuggestions = document.getElementById('from-suggestions');
            const toSuggestions = document.getElementById('to-suggestions');
            const searchForm = document.getElementById('bus-search-form');

            const setupAutocomplete = (input, suggestionsContainer) => {
                const showSuggestions = (filter = '') => {
                    suggestionsContainer.innerHTML = '';
                    const filterLower = filter.toLowerCase().trim();
                    const locationsToShow = filterLower === '' ? allLocations : allLocations.filter(loc => loc && loc.toLowerCase().includes(filterLower));
                    if (locationsToShow.length > 0) {
                        if (filterLower === '') suggestionsContainer.innerHTML += `<div class="suggestions-title">All Destinations</div>`;
                        locationsToShow.slice(0, 7).forEach(loc => createSuggestionItem(loc, filterLower, input, suggestionsContainer));
                        suggestionsContainer.classList.add('show');
                    } else {
                        suggestionsContainer.classList.remove('show');
                    }
                };
                const createSuggestionItem = (loc, filter, input, container) => {
                    const item = document.createElement('div');
                    item.className = 'suggestion-item';
                    let highlightedLoc = loc;
                    if (filter) {
                        const regex = new RegExp(filter.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&'), 'gi');
                        highlightedLoc = loc.replace(regex, `<strong>$&</strong>`);
                    }
                    item.innerHTML = `<i class="bi bi-geo-alt-fill"></i> ${highlightedLoc}`;
                    item.addEventListener('click', () => {
                        input.value = loc;
                        closeAllSuggestions();
                    });
                    container.appendChild(item);
                };
                input.addEventListener('input', () => showSuggestions(input.value));
                input.addEventListener('focus', () => showSuggestions(input.value));
            };

            const closeAllSuggestions = () => {
                fromSuggestions.classList.remove('show');
                toSuggestions.classList.remove('show');
            };
            document.addEventListener('click', e => {
                if (!e.target.closest('.input-group')) closeAllSuggestions();
            });
            setupAutocomplete(fromInput, fromSuggestions);
            setupAutocomplete(toInput, toSuggestions);
            searchForm.addEventListener('submit', e => {
                if (fromInput.value.trim().toLowerCase() === toInput.value.trim().toLowerCase()) {
                    e.preventDefault();
                    alert('Origin and destination cannot be the same.');
                }
            });

            const directMatchData = <?php echo json_encode($direct_matches); ?>;
            const otherRoutesData = <?php echo json_encode($all_routes_for_js); ?>;
            const busListItems = document.querySelectorAll('.bus-item-card');
            const busTypeContainers = document.querySelectorAll('#bus-type-filters-desktop, #bus-type-filters-mobile');
            const timeContainers = document.querySelectorAll('#departure-time-filters-desktop, #departure-time-filters-mobile');
            const noFilterResultsMsg = document.getElementById('no-filter-results');
            const timeSlots = [{
                label: 'Before 06:00',
                start: 0,
                end: 6,
                present: false
            }, {
                label: '06:00 - 12:00',
                start: 6,
                end: 12,
                present: false
            }, {
                label: '12:00 - 18:00',
                start: 12,
                end: 18,
                present: false
            }, {
                label: 'After 18:00',
                start: 18,
                end: 24,
                present: false
            }];

            function updateFiltersAndBusList() {
                const checkedBusTypes = Array.from(document.querySelectorAll('.bus-type-filter:checked')).map(i => i.value);
                const checkedTimeRanges = Array.from(document.querySelectorAll('.time-filter:checked')).map(i => ({
                    start: parseInt(i.dataset.start),
                    end: parseInt(i.dataset.end)
                }));
                let visibleCount = 0;
                busListItems.forEach(card => {
                    let cardBusTypes = [];
                    try {
                        cardBusTypes = card.dataset.busTypes ? JSON.parse(card.dataset.busTypes) : [card.dataset.busType];
                    } catch (e) {
                        cardBusTypes = [card.dataset.busType];
                    }
                    const cardDepartureHour = parseInt((card.dataset.departureTime || '00:00').split(':')[0]);
                    const busTypeMatch = checkedBusTypes.length === 0 || checkedBusTypes.some(type => cardBusTypes.includes(type));
                    const timeMatch = checkedTimeRanges.length === 0 || checkedTimeRanges.some(range => cardDepartureHour >= range.start && cardDepartureHour < range.end);
                    const isVisible = busTypeMatch && timeMatch;
                    card.style.display = isVisible ? '' : 'none';
                    if (isVisible) visibleCount++;
                });
                if (noFilterResultsMsg) noFilterResultsMsg.style.display = (visibleCount === 0 && busListItems.length > 0) ? 'block' : 'none';
            }

            function initializeFilters() {
                const allBusTypes = new Set();
                const filterableData = directMatchData.concat(otherRoutesData);
                filterableData.forEach(bus => {
                    const types = Array.isArray(bus.bus_types) ? bus.bus_types : [bus.bus_type];
                    types.forEach(type => {
                        if (type) allBusTypes.add(type);
                    });
                    const hour = parseInt((bus.departure_time || bus.departure || '00:00').split(':')[0]);
                    timeSlots.forEach(slot => {
                        if (hour >= slot.start && hour < slot.end) slot.present = true;
                    });
                });

                let busTypeHtml = '<h6>Bus Type</h6>';
                if (allBusTypes.size > 0) Array.from(allBusTypes).sort().forEach(type => {
                    const id = type.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase();
                    busTypeHtml += `<div class="form-check"><input class="form-check-input bus-type-filter" type="checkbox" value="${type}" id="type-${id}"><label class="form-check-label" for="type-${id}">${type}</label></div>`;
                });
                else busTypeHtml += '<small class="text-muted">No types to filter.</small>';
                let timeHtml = '<h6>Departure Time</h6>';
                const availableSlots = timeSlots.filter(s => s.present);
                if (availableSlots.length > 0) availableSlots.forEach((slot, i) => {
                    timeHtml += `<div class="form-check"><input class="form-check-input time-filter" type="checkbox" data-start="${slot.start}" data-end="${slot.end}" id="time${i}"><label class="form-check-label" for="time${i}">${slot.label}</label></div>`;
                });
                else timeHtml += '<small class="text-muted">No times to filter.</small>';

                busTypeContainers.forEach(c => {
                    const s = c.id.includes('mobile') ? 'm' : 'd';
                    c.innerHTML = busTypeHtml.replace(/id="type-([^"]+)"/g, `id="type-$1-${s}"`).replace(/for="type-([^"]+)"/g, `for="type-$1-${s}"`);
                });
                timeContainers.forEach(c => {
                    const s = c.id.includes('mobile') ? 'm' : 'd';
                    c.innerHTML = timeHtml.replace(/id="time([^"]+)"/g, `id="time$1-${s}"`).replace(/for="time([^"]+)"/g, `for="time$1-${s}"`);
                });

                document.querySelectorAll('.bus-type-filter, .time-filter').forEach(f => f.addEventListener('change', function() {
                    const baseId = this.id.replace(/-m$|-d$/, '');
                    const isChecked = this.checked;
                    document.querySelectorAll(`input[id^="${baseId}"]`).forEach(box => {
                        if (box !== this) box.checked = isChecked;
                    });
                    updateFiltersAndBusList();
                }));
            }
            initializeFilters();
        });
    </script>
</body>

</html>