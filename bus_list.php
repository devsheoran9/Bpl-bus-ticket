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
        $stmt = $_conn_db->prepare("SELECT b.bus_name, b.bus_id, b.bus_type, GROUP_CONCAT(DISTINCT bc.category_name SEPARATOR ',') AS categories, r.route_id, r.starting_point, r.ending_point, rsch.schedule_id, rsch.departure_time, (SELECT COUNT(s.seat_id) FROM seats s WHERE s.bus_id = b.bus_id AND s.is_bookable = 1) AS total_seats, (SELECT COUNT(p.passenger_id) FROM passengers p JOIN bookings bk ON p.booking_id = bk.booking_id WHERE bk.route_id = r.route_id AND bk.travel_date = :journey_date AND p.passenger_status = 'CONFIRMED') AS booked_seats, (SELECT 1 FROM route_stops rs WHERE rs.route_id = r.route_id AND rs.stop_name = :from_loc1 UNION SELECT 1 FROM routes rt WHERE rt.route_id = r.route_id AND rt.starting_point = :from_loc2 LIMIT 1) AS has_from, (SELECT 1 FROM route_stops rs WHERE rs.route_id = r.route_id AND rs.stop_name = :to_loc1 UNION SELECT 1 FROM routes rt WHERE rt.route_id = r.route_id AND rt.ending_point = :to_loc2 LIMIT 1) AS has_to, (SELECT COALESCE(rs.duration_from_start_minutes, 0) FROM route_stops rs WHERE rs.route_id = r.route_id AND rs.stop_name = :from_loc3 LIMIT 1) AS from_duration, (SELECT rs.duration_from_start_minutes FROM route_stops rs WHERE rs.route_id = r.route_id AND rs.stop_name = :to_loc3 LIMIT 1) AS to_duration_stop, (SELECT MAX(rs.duration_from_start_minutes) FROM route_stops rs WHERE rs.route_id = r.route_id) AS to_duration_end, (SELECT MIN(price) FROM (SELECT price_seater_lower AS price FROM route_stops WHERE route_id = r.route_id AND price_seater_lower > 0) AS prices) AS journey_price FROM route_schedules rsch JOIN routes r ON rsch.route_id = r.route_id JOIN buses b ON r.bus_id = b.bus_id LEFT JOIN bus_category_map bcm ON b.bus_id = bcm.bus_id LEFT JOIN bus_categories bc ON bcm.category_id = bc.category_id WHERE rsch.operating_day LIKE :day_of_week GROUP BY rsch.schedule_id HAVING has_from = 1 AND has_to = 1 ORDER BY rsch.departure_time ASC");
        $stmt->execute([':from_loc1' => $from_location, ':from_loc2' => $from_location, ':from_loc3' => $from_location, ':to_loc1' => $to_location, ':to_loc2' => $to_location, ':to_loc3' => $to_location, ':day_of_week' => '%' . $day_of_week . '%', ':journey_date' => $journey_date]);
        $direct_matches_today = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($direct_matches_today as $bus) {
            $base_time = strtotime($journey_date . ' ' . $bus['departure_time']);
            $from_offset = (int)($bus['from_duration'] ?? 0);
            $to_offset = ($to_location == $bus['ending_point']) ? (int)$bus['to_duration_end'] : (int)$bus['to_duration_stop'];
            $bus['departure'] = date('H:i', $base_time + ($from_offset * 60));
            if ($journey_date == date('Y-m-d') && strtotime($bus['departure']) < time()) continue;
            if ($to_offset > $from_offset) {
                $bus['arrival'] = date('H:i', $base_time + ($to_offset * 60));
                $duration_minutes = $to_offset - $from_offset;
                $bus['duration'] = floor($duration_minutes / 60) . 'h ' . ($duration_minutes % 60) . 'm';
                $bus['price'] = isset($bus['journey_price']) ? number_format($bus['journey_price'], 2) : 'N/A';
                $bus['available_seats'] = (int)$bus['total_seats'] - (int)$bus['booked_seats'];
                $bus['link_params'] = http_build_query(['schedule_id' => $bus['schedule_id'], 'from' => $from_location, 'to' => $to_location, 'date' => $journey_date]);
                $direct_matches[] = $bus;
            }
        }
    }

    // ======================================================================
    // 3. "OTHER AVAILABLE ROUTES" LOGIC
    // ======================================================================
    if (empty($direct_matches)) {
        $all_schedules_stmt = $_conn_db->prepare("SELECT b.bus_name, b.bus_id, b.bus_type, r.route_id, r.starting_point, r.ending_point, rsch.schedule_id, rsch.departure_time, rsch.operating_day, (SELECT MIN(price) FROM (SELECT price_seater_lower AS price FROM route_stops WHERE route_id = r.route_id AND price_seater_lower > 0) AS prices) AS route_min_price FROM route_schedules rsch JOIN routes r ON rsch.route_id = r.route_id JOIN buses b ON r.bus_id = b.bus_id GROUP BY r.route_id ORDER BY r.starting_point, r.ending_point, rsch.departure_time ASC");
        $all_schedules_stmt->execute();
        $all_available_schedules = $all_schedules_stmt->fetchAll(PDO::FETCH_ASSOC);
        $temp_routes = [];
        foreach ($all_available_schedules as $schedule) {
            $route_key = $schedule['route_id'];
            if (!isset($temp_routes[$route_key])) {
                $temp_routes[$route_key] = ['starting_point' => $schedule['starting_point'], 'ending_point' => $schedule['ending_point'], 'first_departure_time' => $schedule['departure_time'], 'representative_schedule_id' => $schedule['schedule_id'], 'representative_route_id' => $schedule['route_id'], 'representative_bus_id' => $schedule['bus_id'], 'route_min_price' => $schedule['route_min_price'], 'bus_names' => [], 'bus_types' => [], 'all_operating_days' => []];
            }
            $temp_routes[$route_key]['bus_names'][$schedule['bus_name']] = true;
            $temp_routes[$route_key]['bus_types'][$schedule['bus_type']] = true;
            $days = array_map('trim', explode(',', $schedule['operating_day']));
            foreach ($days as $day) {
                if ($day) $temp_routes[$route_key]['all_operating_days'][$day] = true;
            }
        }
        $processed_routes = $temp_routes;
    }
} catch (PDOException $e) {
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
                            <?php $available_seats = max(0, (int)$bus['available_seats']); ?>
                            <div class="bus-list-item" data-bus-type="<?php echo htmlspecialchars($bus['bus_type']); ?>" data-departure-time="<?php echo $bus['departure']; ?>">
                                <!-- === FIX: RESTORED HTML FOR DIRECT MATCHES === -->
                                <div class="bus-item-main">
                                    <div class="bus-info">
                                        <h6><?php echo htmlspecialchars($bus['bus_name']); ?></h6>
                                        <p class="mb-0 text-muted"><?php echo htmlspecialchars($bus['bus_type']); ?></p>
                                        <p class="fw-bold <?php echo ($available_seats <= 5 && $available_seats > 0) ? 'text-danger' : 'text-success'; ?>"><?php echo $available_seats; ?> Seats available</p>
                                    </div>
                                    <div class="bus-timing">
                                        <div class="time"><?php echo $bus['departure']; ?> &rarr; <?php echo $bus['arrival']; ?></div>
                                        <div class="duration"><?php echo $bus['duration']; ?></div>
                                    </div>
                                    <div class="price-section">
                                        <div class="price">From ₹<?php echo htmlspecialchars($bus['price']); ?></div>
                                        <a href="select_seats.php?<?php echo $bus['link_params']; ?>" class="btn btn-danger btn-sm mt-2 <?php if ($available_seats <= 0) echo 'disabled'; ?>"><?php echo ($available_seats > 0) ? 'View Seats' : 'Sold Out'; ?></a>
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
                            $start_search_date = date('Y-m-d');
                            if (in_array(date('D'), array_keys($route['all_operating_days'])) && strtotime(date('Y-m-d') . ' ' . $route['first_departure_time']) < time()) {
                                $start_search_date = date('Y-m-d', strtotime('+1 day'));
                            }
                            $next_date = find_next_available_date($all_days_for_route, $start_search_date);
                            if (!$next_date) continue;

                            $seats_stmt = $_conn_db->prepare("SELECT (SELECT COUNT(s.seat_id) FROM seats s WHERE s.bus_id = :bus_id AND s.is_bookable = 1) AS total_seats, (SELECT COUNT(p.passenger_id) FROM passengers p JOIN bookings bk ON p.booking_id = bk.booking_id WHERE bk.route_id = :route_id AND bk.travel_date = :next_travel_date AND p.passenger_status = 'CONFIRMED') AS booked_seats");
                            $seats_stmt->execute([':bus_id' => $route['representative_bus_id'], ':route_id' => $route['representative_route_id'], ':next_travel_date' => $next_date]);
                            $seat_counts = $seats_stmt->fetch(PDO::FETCH_ASSOC);
                            $available_seats = $seat_counts ? max(0, (int)$seat_counts['total_seats'] - (int)$seat_counts['booked_seats']) : 0;

                            $link_params = http_build_query(['schedule_id' => $route['representative_schedule_id'], 'from' => $route['starting_point'], 'to' => $route['ending_point'], 'date' => $next_date]);
                            $day_order = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                            $days_arr = array_keys($route['all_operating_days']);
                            usort($days_arr, fn($a, $b) => array_search($a, $day_order) - array_search($b, $day_order));
                            $display_days = (count($days_arr) >= 7) ? 'Daily' : implode(', ', $days_arr);

                            $bus_types_json = json_encode(array_values(array_keys($route['bus_types'])));
                            $all_routes_for_js[] = ['bus_types' => array_keys($route['bus_types']), 'departure_time' => date('H:i', strtotime($route['first_departure_time']))];
                            ?>
                            <div class="bus-list-item" data-bus-types='<?php echo htmlspecialchars($bus_types_json, ENT_QUOTES, 'UTF-8'); ?>' data-departure-time="<?php echo date('H:i', strtotime($route['first_departure_time'])); ?>">
                                <div class="bus-item-main">
                                    <div class="bus-info">
                                        <h6><?php echo htmlspecialchars(implode(' / ', array_keys($route['bus_names']))); ?></h6>
                                        <p class="mb-0 text-muted"><?php echo htmlspecialchars(implode(', ', array_keys($route['bus_types']))); ?></p>
                                        <p class="fw-bold <?php echo ($available_seats <= 5 && $available_seats > 0) ? 'text-danger' : 'text-success'; ?>"><?php echo $available_seats; ?> Seats available</p>
                                        <div class="operating-days">Runs: <?php echo $display_days; ?></div>
                                    </div>
                                    <div class="bus-timing">
                                        <div class="time">Starts at <?php echo date('H:i', strtotime($route['first_departure_time'])); ?></div>
                                        <div class="full-route"><?php echo htmlspecialchars($route['starting_point']); ?> &rarr; <?php echo htmlspecialchars($route['ending_point']); ?></div>
                                        <small class="text-muted"><strong>Next trip: <?php echo date('D, d M Y', strtotime($next_date)); ?></strong></small>
                                    </div>
                                    <div class="price-section">
                                        <div class="price">From ₹<?php echo number_format($route['route_min_price'] ?? 0, 2); ?></div>
                                        <a href="select_seats.php?<?php echo $link_params; ?>" class="btn btn-danger btn-sm mt-2 <?php if ($available_seats <= 0) echo 'disabled'; ?>"><?php echo ($available_seats > 0) ? 'View Next Trip' : 'Sold Out'; ?></a>
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
                        // This handles the JSON string from "Other Routes"
                        cardBusTypes = card.dataset.busTypes ? JSON.parse(card.dataset.busTypes) : [card.dataset.busType];
                    } catch (e) {
                        // This is a fallback for the single value from "Direct Matches"
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
                // === FIX: DETERMINE WHICH DATA TO BUILD FILTERS FROM ===
                const dataToUseForFilters = directMatchData.length > 0 ? directMatchData.map(b => ({
                    bus_type: b.bus_type,
                    departure: b.departure
                })) : otherRoutesData;

                dataToUseForFilters.forEach(bus => {
                    // Handle both single bus_type and array of bus_types
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