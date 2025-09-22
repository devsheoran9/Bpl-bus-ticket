<?php
// ======================================================================
// DATABASE CONNECTION & HEADER
// ======================================================================
include 'includes/header.php';

// ======================================================================
// HELPER FUNCTION
// ======================================================================
function find_next_available_date($operating_days_str, $start_date_str)
{
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

// ======================================================================
// 1. INITIALIZE VARIABLES & FETCH DATA
// ======================================================================
$all_locations = [];
$direct_matches = [];
$processed_routes = [];
$error_message = null;
$all_routes_for_js = [];

try {
    // Fetch locations for search bar autocomplete
    $stmt_locations = $_conn_db->query("(SELECT DISTINCT starting_point FROM routes WHERE status = 'Active') UNION (SELECT DISTINCT ending_point FROM routes WHERE status = 'Active') UNION (SELECT DISTINCT stop_name FROM route_stops) ORDER BY starting_point ASC");
    $all_locations = array_filter($stmt_locations->fetchAll(PDO::FETCH_COLUMN));

    // Handle search parameters
    $from_location = $_GET['from'] ?? null;
    $to_location = $_GET['to'] ?? null;
    $journey_date = $_GET['date'] ?? null;
    $is_search_performed = ($from_location && $to_location && $journey_date);

    // ======================================================================
    // 2. DIRECT SEARCH LOGIC
    // ======================================================================
    if ($is_search_performed) {
        $day_of_week = date('D', strtotime($journey_date));

        // --- REVISED AND CORRECTED QUERY ---
        // Changed named parameters to be explicitly unique for each part of the EXISTS (UNION ALL) clauses.
        $stmt = $_conn_db->prepare("
            SELECT
                b.bus_name, b.bus_id, b.bus_type,
                r.route_id, r.starting_point, r.ending_point,
                rsch.schedule_id, rsch.departure_time,
                COUNT(DISTINCT s.seat_id) AS total_seats,
                COUNT(DISTINCT p.passenger_id) AS booked_seats,
                GROUP_CONCAT(DISTINCT bc.category_name SEPARATOR ', ') AS categories,
                MIN(rs_prices.price_seater_lower) AS journey_price,
                cb.charter_id IS NOT NULL AS is_chartered -- Check if chartered
            FROM route_schedules rsch
            JOIN routes r ON rsch.route_id = r.route_id
            JOIN buses b ON r.bus_id = b.bus_id
            LEFT JOIN seats s ON s.bus_id = b.bus_id AND s.is_bookable = 1
            LEFT JOIN bookings bk ON bk.route_id = r.route_id AND bk.travel_date = :journey_date AND bk.booking_status = 'CONFIRMED'
            LEFT JOIN passengers p ON p.booking_id = bk.booking_id AND p.passenger_status = 'CONFIRMED'
            LEFT JOIN bus_category_map bcm ON b.bus_id = bcm.bus_id
            LEFT JOIN bus_categories bc ON bcm.category_id = bc.category_id
            LEFT JOIN route_stops rs_prices ON rs_prices.route_id = r.route_id AND rs_prices.price_seater_lower > 0
            LEFT JOIN charter_bookings cb ON cb.route_id = r.route_id AND cb.travel_date = :journey_date_charter -- Join charter_bookings
            WHERE
                rsch.operating_day = :day_of_week
                AND r.status = 'Active' -- Ensure only active routes are shown
                AND ( -- Condition for FROM location
                    EXISTS (SELECT 1 FROM routes rt_from WHERE rt_from.route_id = r.route_id AND rt_from.starting_point = :from_location_start_point)
                    OR
                    EXISTS (SELECT 1 FROM route_stops rs_from WHERE rs_from.route_id = r.route_id AND rs_from.stop_name = :from_location_stop_name)
                )
                AND ( -- Condition for TO location
                    EXISTS (SELECT 1 FROM routes rt_to WHERE rt_to.route_id = r.route_id AND rt_to.ending_point = :to_location_ending_point)
                    OR
                    EXISTS (SELECT 1 FROM route_stops rs_to WHERE rs_to.route_id = r.route_id AND rs_to.stop_name = :to_location_stop_name)
                )
            GROUP BY rsch.schedule_id, cb.charter_id -- Group by charter_id to make it distinct per route+date+charter
            ORDER BY rsch.departure_time ASC
        ");

        // --- UPDATED execute() parameters to match new query placeholders ---
        $stmt->execute([
            ':journey_date'                 => $journey_date,
            ':journey_date_charter'         => $journey_date,
            ':day_of_week'                  => $day_of_week,
            ':from_location_start_point'    => $from_location,
            ':from_location_stop_name'      => $from_location,
            ':to_location_ending_point'     => $to_location,
            ':to_location_stop_name'        => $to_location
        ]);
        $direct_matches_today = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- Prepare a statement to fetch durations efficiently (no change needed here) ---
        $duration_stmt = $_conn_db->prepare("
            SELECT stop_name, duration_from_start_minutes FROM route_stops WHERE route_id = ?
            UNION
            SELECT starting_point, 0 FROM routes WHERE route_id = ?
        ");

        // --- MODIFIED PHP LOOP ---
        foreach ($direct_matches_today as $bus) {
            $from_offset = 0; // Initialize
            $to_offset = 0;   // Initialize

            // Check if chartered for today
            if ($bus['is_chartered']) {
                $bus['available_seats'] = 0; // No seats available if chartered
                $bus['chartered_status'] = true; // Flag for UI
            } else {
                // Original logic for non-chartered buses
                $duration_stmt->execute([$bus['route_id'], $bus['route_id']]);
                $durations = $duration_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $from_offset = (int)($durations[$from_location] ?? 0);
                $to_offset = (int)($durations[$to_location] ?? 0);

                // If destination is the route's absolute end point and not an intermediate stop explicitly listed in route_stops
                if ($to_location == $bus['ending_point'] && !isset($durations[$to_location])) {
                    $max_duration_stmt = $_conn_db->prepare("SELECT MAX(duration_from_start_minutes) FROM route_stops WHERE route_id = ?");
                    $max_duration_stmt->execute([$bus['route_id']]);
                    $to_offset = (int) $max_duration_stmt->fetchColumn();
                }
                
                // Skip if destination is before origin or invalid (e.g., origin = destination)
                if ($to_offset <= $from_offset) {
                    continue;
                }

                $bus['available_seats'] = (int)$bus['total_seats'] - (int)$bus['booked_seats'];
            }
            
            // Common logic for both chartered and non-chartered buses
            $bus_base_time = strtotime($journey_date . ' ' . $bus['departure_time']);

            // Departure Time based on origin offset
            $bus['departure'] = date('H:i', $bus_base_time + ($from_offset * 60)); 
            
            // Skip buses that have already departed today (only applies if not chartered and today's date)
            if ( $journey_date == date('Y-m-d') && strtotime($bus['departure']) < time() && (!$bus['is_chartered']) ) {
                continue; 
            }

            // Calculate arrival and duration or set chartered status for display
            if (!isset($bus['chartered_status']) || !$bus['chartered_status']) {
                $bus['arrival'] = date('H:i', $bus_base_time + ($to_offset * 60));
                $duration_minutes = $to_offset - $from_offset;
                $bus['duration'] = floor($duration_minutes / 60) . 'h ' . ($duration_minutes % 60) . 'm';
            } else {
                $bus['arrival'] = 'Chartered'; // Indicate chartered bus, no individual arrival time relevant
                $bus['duration'] = 'Full Day'; // Indicate full day charter
            }
            
            $bus['price'] = isset($bus['journey_price']) ? number_format($bus['journey_price'], 2) : 'N/A';
            
            $bus['link_params'] = http_build_query([
                'schedule_id' => $bus['schedule_id'],
                'from' => $from_location,
                'to' => $to_location,
                'date' => $journey_date
            ]);
            $direct_matches[] = $bus;
        }
    }

    // ======================================================================
    // 3. "OTHER AVAILABLE ROUTES" LOGIC (MODIFIED for charter check)
    // ======================================================================
    if (empty($direct_matches)) {
        // --- Prepare statements for efficiency outside the loop ---
        $all_schedules_stmt = $_conn_db->prepare("
            SELECT 
                b.bus_name, b.bus_id, b.bus_type, 
                r.route_id, r.starting_point, r.ending_point, 
                rsch.schedule_id, rsch.departure_time, GROUP_CONCAT(DISTINCT rsch.operating_day SEPARATOR ',') AS operating_days_list,
                MIN(rs_prices.price_seater_lower) AS route_min_price
            FROM route_schedules rsch
            JOIN routes r ON rsch.route_id = r.route_id AND r.status = 'Active' -- Only active routes
            JOIN buses b ON r.bus_id = b.bus_id
            LEFT JOIN route_stops rs_prices ON rs_prices.route_id = r.route_id AND rs_prices.price_seater_lower > 0
            GROUP BY r.route_id -- Group by route_id to get one entry per route for representative data
            ORDER BY r.starting_point, r.ending_point, rsch.departure_time ASC
        ");
        $all_schedules_stmt->execute();
        $all_available_schedules = $all_schedules_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare charter check statement for the loop
        $charter_check_stmt_for_other_routes = $_conn_db->prepare("SELECT charter_id FROM charter_bookings WHERE route_id = ? AND travel_date = ?");
        // Prepare total seats statement
        $seats_total_stmt = $_conn_db->prepare("SELECT COUNT(s.seat_id) FROM seats s WHERE s.bus_id = ? AND s.is_bookable = 1");
        // Prepare booked seats statement
        $seats_booked_stmt = $_conn_db->prepare("SELECT COUNT(p.passenger_id) FROM passengers p JOIN bookings bk ON p.booking_id = bk.booking_id WHERE bk.route_id = ? AND bk.travel_date = ? AND p.passenger_status = 'CONFIRMED'");


        $temp_routes = [];
        foreach ($all_available_schedules as $schedule) {
            $route_key = $schedule['route_id'];
            if (!isset($temp_routes[$route_key])) {
                $temp_routes[$route_key] = [
                    'starting_point' => $schedule['starting_point'], 
                    'ending_point' => $schedule['ending_point'], 
                    'first_departure_time' => $schedule['departure_time'], 
                    'representative_schedule_id' => $schedule['schedule_id'], 
                    'representative_route_id' => $schedule['route_id'], 
                    'representative_bus_id' => $schedule['bus_id'], 
                    'route_min_price' => $schedule['route_min_price'], 
                    'bus_names' => [], 
                    'bus_types' => [], 
                    'all_operating_days' => [],
                    'operating_days_str' => $schedule['operating_days_list'] // Store original string for find_next_available_date
                ];
            }
            $temp_routes[$route_key]['bus_names'][$schedule['bus_name']] = true;
            $temp_routes[$route_key]['bus_types'][$schedule['bus_type']] = true;
            // The operating_days_list from GROUP_CONCAT may contain multiple days
            $days = array_map('trim', explode(',', $schedule['operating_days_list']));
            foreach ($days as $day) {
                if ($day) $temp_routes[$route_key]['all_operating_days'][$day] = true;
            }
        }
        $processed_routes = $temp_routes;
    }
} catch (PDOException $e) {
    // Corrected error output to include actual message
    $error_message = "Database Error: " . $e->getMessage(); 
}
?>




<body class="mt-5 pt-5">

    <!-- SEARCH BAR SECTION -->
    <div class="container my-3 pt-5">
        <div class="search-form-card">
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

    <!-- MAIN CONTENT SECTION -->
    <div class="bus-list-container container mt-5">
        <div class="row">
            <aside class="col-lg-3">
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
                    <?php if ($is_search_performed && !empty($direct_matches)): ?>
                        <h3 class="mb-3 text-center">Buses from <strong style="color:brown"><?php echo htmlspecialchars($from_location); ?></strong> to <strong style="color:green"><?php echo htmlspecialchars($to_location); ?></strong></h3>
                        <?php foreach ($direct_matches as $bus): ?>
                            <?php $is_bus_chartered = (isset($bus['chartered_status']) && $bus['chartered_status']); ?>
                            <?php $available_seats = $is_bus_chartered ? 0 : max(0, (int)$bus['available_seats']); ?>
                            <div class="bus-list-item" data-bus-type="<?php echo htmlspecialchars($bus['bus_type']); ?>" data-departure-time="<?php echo $bus['departure']; ?>">
                                <div class="bus-item-main">
                                    <div class="bus-info">
                                        <h6><?php echo htmlspecialchars($bus['bus_name']); ?></h6>
                                        <p class="mb-0 text-muted"><?php echo htmlspecialchars($bus['bus_type']); ?></p>
                                        <?php if ($is_bus_chartered): ?>
                                            <p class="fw-bold text-danger">Bus is fully booked (Chartered)</p>
                                        <?php else: ?>
                                            <p class="fw-bold <?php echo ($available_seats <= 5 && $available_seats > 0) ? 'text-danger' : 'text-success'; ?>"><?php echo $available_seats; ?> Seats available</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="bus-timing">
                                        <div class="time"><?php echo $bus['departure']; ?> &rarr; <?php echo $bus['arrival']; ?></div>
                                        <div class="duration"><?php echo $bus['duration']; ?></div>
                                    </div>
                                    <div class="price-section">
                                        <div class="price">From ₹<?php echo htmlspecialchars($bus['price']); ?></div>
                                        <a href="select_seats.php?<?php echo $bus['link_params']; ?>" class="btn btn-danger btn-sm mt-2 <?php if ($available_seats <= 0) echo 'disabled'; ?>"><?php echo ($available_seats > 0) ? 'View Seats' : 'Booked Out'; ?></a>
                                    </div>
                                </div>
                                <?php if (!empty($bus['categories'])): ?>
                                    <div class="bus-categories-footer">
                                        <?php $categories = array_filter(array_map('trim', explode(',', $bus['categories']))); ?>
                                        <?php foreach ($categories as $category): ?>
                                            <span class="category-tag"><?php echo htmlspecialchars($category); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div id="no-filter-results" class="filter-card text-center py-5" style="display: none;">
                        <p class="lead   my-1 text-danger">Sorry, no buses match your selected filters.</p>
                    </div>
                </div>

                <?php if (empty($direct_matches)): ?>
                    <?php if ($is_search_performed): ?>
                        <div class="filter-card text-center">
                            <p class="lead text-danger my-1 ">Sorry, no buses were found for your search on <?php echo date('d M, Y', strtotime($journey_date)); ?>.</p>
                            <!-- <a href="index.php" class="btn btn-outline-danger">Try a Different Search</a> -->
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($processed_routes)): ?>
                        <hr class="my-4">
                        <h3 class="mb-3"><?php echo $is_search_performed ? 'Other Available Routes' : 'All Our Available Routes'; ?></h3>
                        <?php foreach ($processed_routes as $route): ?>
                            <?php
                            $all_days_for_route = implode(',', array_keys($route['all_operating_days']));
                            $start_search_date = $journey_date;
                            
                            // Adjust search start date to tomorrow if today's bus for this route has already departed or it's a past date
                            // We use the first_departure_time for a representative check
                            if ( ($journey_date == date('Y-m-d') && strtotime($journey_date . ' ' . $route['first_departure_time']) < time()) ) {
                                $start_search_date = date('Y-m-d', strtotime('+1 day'));
                            } 
                            // If journey_date is in the past, always start search from today/tomorrow
                            if ($journey_date < date('Y-m-d')) {
                                $start_search_date = date('Y-m-d');
                            }
                           
                            $next_date = find_next_available_date($all_days_for_route, $start_search_date);
                            
                            if (!$next_date) continue; // Skip if no operating day can be found in the future

                            // NEW: Check if this "Other Route" is chartered for its 'next_date'
                            $charter_check_stmt_for_other_routes->execute([$route['representative_route_id'], $next_date]);
                            $is_chartered_other_route = $charter_check_stmt_for_other_routes->fetchColumn() !== false;

                            if ($is_chartered_other_route) {
                                $available_seats = 0; // Chartered, so 0 available seats
                                $chartered_display_message = "Bus is fully booked (Chartered)";
                            } else {
                                // Original logic for seat counts (if not chartered)
                                $seats_total_stmt->execute([$route['representative_bus_id']]); 
                                $total_seats_other_route = (int) $seats_total_stmt->fetchColumn();
                                
                                // FIX for PDOException: SQLSTATE[HY093]
                                // Changed named parameters to positional parameters here to match the prepared statement
                                $seats_booked_stmt->execute([$route['representative_route_id'], $next_date]);
                                $booked_seats_other_route = (int) $seats_booked_stmt->fetchColumn();
                                
                                $available_seats = max(0, $total_seats_other_route - $booked_seats_other_route);
                                $chartered_display_message = null; // No special message
                            }

                            $link_params = http_build_query(['schedule_id' => $route['representative_schedule_id'], 'from' => $route['starting_point'], 'to' => $route['ending_point'], 'date' => $next_date]);
                            $day_order = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                            $days_arr = array_keys($route['all_operating_days']);
                            usort($days_arr, fn($a, $b) => array_search($a, $day_order) - array_search($b, $day_order));
                            $display_days = (count($days_arr) >= 7) ? 'Daily' : implode(', ', $days_arr);

                            $bus_types_json = json_encode(array_values(array_keys($route['bus_types'])));
                            // Only add to all_routes_for_js if it's not chartered and would show actual bus data
                            if (!$is_chartered_other_route) {
                                $all_routes_for_js[] = ['bus_types' => array_keys($route['bus_types']), 'departure_time' => date('H:i', strtotime($route['first_departure_time']))];
                            }
                            ?>
                            <div class="bus-list-item" data-bus-types='<?php echo htmlspecialchars($bus_types_json, ENT_QUOTES, 'UTF-8'); ?>' data-departure-time="<?php echo date('H:i', strtotime($route['first_departure_time'])); ?>">
                                <div class="bus-item-main">
                                    <div class="bus-info">
                                        <h6><?php echo htmlspecialchars(implode(' / ', array_keys($route['bus_names']))); ?></h6>
                                        <p class="mb-0 text-muted"><?php echo htmlspecialchars(implode(', ', array_keys($route['bus_types']))); ?></p>
                                        <?php if ($chartered_display_message): ?>
                                            <p class="fw-bold text-danger"><?php echo $chartered_display_message; ?></p>
                                        <?php else: ?>
                                            <p class="fw-bold <?php echo ($available_seats <= 5 && $available_seats > 0) ? 'text-danger' : 'text-success'; ?>"><?php echo $available_seats; ?> Seats available</p>
                                        <?php endif; ?>
                                        <div class="operating-days">Runs: <?php echo $display_days; ?></div>
                                    </div>
                                    <div class="bus-timing">
                                        <div class="time">Starts at <?php echo date('H:i', strtotime($route['first_departure_time'])); ?></div>
                                        <div class="full-route"><?php echo htmlspecialchars($route['starting_point']); ?> &rarr; <?php echo htmlspecialchars($route['ending_point']); ?></div>
                                        <small class="text-muted"><strong>Next trip: <?php echo date('D, d M Y', strtotime($next_date)); ?></strong></small>
                                    </div>
                                    <div class="price-section">
                                        <div class="price">From ₹<?php echo number_format($route['route_min_price'] ?? 0, 2); ?></div>
                                        <a href="select_seats.php?<?php echo $link_params; ?>" class="btn btn-danger btn-sm mt-2 <?php if ($available_seats <= 0) echo 'disabled'; ?>"><?php echo ($available_seats > 0) ? 'View Next Trip' : 'Booked Out'; ?></a>
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
        document.addEventListener('DOMContentLoaded', function() {
            // ==================================================================
            // COMPLETE AUTOCOMPLETE SCRIPT
            // ==================================================================
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
                        if (filterLower === '') {
                            suggestionsContainer.innerHTML += `<div class="suggestions-title">All Destinations</div>`;
                        }
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
                if (!fromInput.contains(e.target) && !toInput.contains(e.target) && !fromSuggestions.contains(e.target) && !toSuggestions.contains(e.target)) {
                    closeAllSuggestions();
                }
            });

            setupAutocomplete(fromInput, fromSuggestions);
            setupAutocomplete(toInput, toSuggestions);

            searchForm.addEventListener('submit', (e) => {
                if (fromInput.value.trim().toLowerCase() === toInput.value.trim().toLowerCase()) {
                    e.preventDefault();
                    alert('Origin and destination cannot be the same.');
                }
            });

            // ==================================================================
            // COMPLETE FILTERING SCRIPT
            // ==================================================================
            const directMatchData = <?php echo json_encode($direct_matches); ?>;
            const otherRoutesData = <?php echo json_encode($all_routes_for_js); ?>; 
            const busListItems = document.querySelectorAll('.bus-list-item');
            const busTypeContainers = document.querySelectorAll('#bus-type-filters-desktop, #bus-type-filters-mobile');
            const timeContainers = document.querySelectorAll('#departure-time-filters-desktop, #departure-time-filters-mobile');
            const noFilterResultsMsg = document.getElementById('no-filter-results');

            const timeSlots = [{
                    label: 'Before 06:00',
                    start: 0,
                    end: 6,
                    present: false
                },
                {
                    label: '06:00 - 12:00',
                    start: 6,
                    end: 12,
                    present: false
                },
                {
                    label: '12:00 - 18:00',
                    start: 12,
                    end: 18,
                    present: false
                },
                {
                    label: 'After 18:00',
                    start: 18,
                    end: 24,
                    present: false
                }
            ];

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

                noFilterResultsMsg.style.display = (visibleCount === 0 && busListItems.length > 0) ? 'block' : 'none';
            }

            function initializeFilters() {
                const allBusTypes = new Set();
                
                // When building filters, only consider non-chartered data that is actually displayed
                // Filter out any entries that PHP determined were chartered
                const filterableDirectMatches = directMatchData.filter(b => !b.chartered_status);
                // Note: all_routes_for_js in PHP is already designed to *not* include chartered routes
                const filterableOtherRoutes = otherRoutesData; 
                
                const dataToUseForFilters = filterableDirectMatches.concat(filterableOtherRoutes);

                dataToUseForFilters.forEach(bus => {
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
                if (allBusTypes.size > 0) {
                    Array.from(allBusTypes).sort().forEach(type => {
                        const typeId = type.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase();
                        busTypeHtml += `<div class="form-check"><input class="form-check-input bus-type-filter" type="checkbox" value="${type}" id="type-${typeId}"><label class="form-check-label" for="type-${typeId}">${type}</label></div>`;
                    });
                } else {
                    busTypeHtml += '<small class="text-muted">No types to filter.</small>';
                }

                let timeHtml = '<h6>Departure Time</h6>';
                const availableSlots = timeSlots.filter(slot => slot.present);
                if (availableSlots.length > 0) {
                    availableSlots.forEach((slot, index) => {
                        timeHtml += `<div class="form-check"><input class="form-check-input time-filter" type="checkbox" data-start="${slot.start}" data-end="${slot.end}" id="time${index}"><label class="form-check-label" for="time${index}">${slot.label}</label></div>`;
                    });
                } else {
                    timeHtml += '<small class="text-muted">No times to filter.</small>';
                }

                // Populate both desktop and mobile filters with unique IDs
                busTypeContainers.forEach(container => {
                    const suffix = container.id.includes('mobile') ? 'm' : 'd';
                    container.innerHTML = busTypeHtml.replace(/id="type-([^"]+)"/g, `id="type-$1-${suffix}"`).replace(/for="type-([^"]+)"/g, `for="type-$1-${suffix}"`);
                });
                timeContainers.forEach(container => {
                    const suffix = container.id.includes('mobile') ? 'm' : 'd';
                    container.innerHTML = timeHtml.replace(/id="time([^"]+)"/g, `id="time$1-${suffix}"`).replace(/for="time([^"]+)"/g, `for="time$1-${suffix}"`);
                });

                // Sync desktop and mobile checkboxes
                document.querySelectorAll('.bus-type-filter, .time-filter').forEach(filter => {
                    filter.addEventListener('change', function() {
                        const baseId = this.id.replace(/-m$/, '').replace(/-d$/, '');
                        const isChecked = this.checked;
                        document.querySelectorAll(`input[id^="${baseId}"]`).forEach(box => {
                            if (box !== this) box.checked = isChecked;
                        });
                        updateFiltersAndBusList();
                    });
                });
            }

            initializeFilters();
        });
    </script>
</body>

</html>