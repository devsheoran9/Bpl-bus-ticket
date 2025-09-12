<?php
include 'includes/header.php';
include 'db_connect.php'; // Assuming this provides the $_conn_db variable

// --- HELPER FUNCTION ---
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

// --- 1. INITIALIZE VARIABLES & FETCH DATA FOR SEARCH BAR ---
$all_locations = [];
$direct_matches = [];
$processed_routes = [];
$error_message = null;

try {
    $stmt_locations = $_conn_db->query("
        (SELECT DISTINCT starting_point FROM routes WHERE status = 'Active')
        UNION (SELECT DISTINCT ending_point FROM routes WHERE status = 'Active')
        UNION (SELECT DISTINCT stop_name FROM route_stops)
        ORDER BY starting_point ASC
    ");
    $all_locations = array_filter($stmt_locations->fetchAll(PDO::FETCH_COLUMN));
} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
}

// --- 2. HANDLE SEARCH PARAMETERS & SET FLAG ---
$from_location = $_GET['from'] ?? null;
$to_location = $_GET['to'] ?? null;
$journey_date = $_GET['date'] ?? null;

$is_search_performed = ($from_location && $to_location && $journey_date);

// --- 3. MAIN DATA FETCHING LOGIC ---
try {
    if ($is_search_performed) {
        $day_of_week = date('D', strtotime($journey_date));
        // FIX 1: ADDED b.bus_type to the SELECT statement
        $stmt = $_conn_db->prepare("
            SELECT
                b.bus_name, b.bus_id, b.bus_type,
                GROUP_CONCAT(DISTINCT bc.category_name SEPARATOR ' ') AS categories,
                r.route_id, r.starting_point, r.ending_point, rsch.schedule_id, rsch.departure_time,
                (SELECT COUNT(s.seat_id) FROM seats s WHERE s.bus_id = b.bus_id AND s.is_bookable = 1) AS total_seats,
                (SELECT COUNT(p.passenger_id) FROM passengers p JOIN bookings bk ON p.booking_id = bk.booking_id WHERE bk.route_id = r.route_id AND bk.travel_date = :journey_date AND bk.booking_status = 'CONFIRMED') AS booked_seats,
                (SELECT 1 FROM route_stops rs WHERE rs.route_id = r.route_id AND rs.stop_name = :from_loc1 UNION SELECT 1 FROM routes rt WHERE rt.route_id = r.route_id AND rt.starting_point = :from_loc2 LIMIT 1) AS has_from,
                (SELECT 1 FROM route_stops rs WHERE rs.route_id = r.route_id AND rs.stop_name = :to_loc1 UNION SELECT 1 FROM routes rt WHERE rt.route_id = r.route_id AND rt.ending_point = :to_loc2 LIMIT 1) AS has_to,
                (SELECT COALESCE(rs.duration_from_start_minutes, 0) FROM route_stops rs WHERE rs.route_id = r.route_id AND rs.stop_name = :from_loc3 LIMIT 1) AS from_duration,
                (SELECT rs.duration_from_start_minutes FROM route_stops rs WHERE rs.route_id = r.route_id AND rs.stop_name = :to_loc3 LIMIT 1) AS to_duration_stop,
                (SELECT MAX(rs.duration_from_start_minutes) FROM route_stops rs WHERE rs.route_id = r.route_id) AS to_duration_end,
                (SELECT MIN(price) FROM (SELECT price_seater_lower AS price FROM route_stops WHERE route_id = r.route_id AND price_seater_lower > 0) AS prices) AS journey_price
            FROM route_schedules rsch JOIN routes r ON rsch.route_id = r.route_id JOIN buses b ON r.bus_id = b.bus_id LEFT JOIN bus_category_map bcm ON b.bus_id = bcm.bus_id LEFT JOIN bus_categories bc ON bcm.category_id = bc.category_id
            WHERE rsch.operating_day LIKE :day_of_week GROUP BY rsch.schedule_id HAVING has_from = 1 AND has_to = 1 ORDER BY rsch.departure_time ASC
        ");
        $stmt->bindValue(':from_loc1', $from_location);
        $stmt->bindValue(':from_loc2', $from_location);
        $stmt->bindValue(':from_loc3', $from_location);
        $stmt->bindValue(':to_loc1', $to_location);
        $stmt->bindValue(':to_loc2', $to_location);
        $stmt->bindValue(':to_loc3', $to_location);
        $stmt->bindValue(':day_of_week', '%' . $day_of_week . '%');
        $stmt->bindValue(':journey_date', $journey_date);
        $stmt->execute();
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

    if (empty($direct_matches)) {
        // FIX 1: ADDED b.bus_type to the SELECT statement for the fallback list
        $all_schedules_stmt = $_conn_db->prepare("
            SELECT b.bus_name, b.bus_id, b.bus_type, r.route_id, GROUP_CONCAT(DISTINCT bc.category_name SEPARATOR ', ') AS categories, r.starting_point, r.ending_point, rsch.schedule_id, rsch.departure_time, rsch.operating_day,
            (SELECT MIN(price) FROM (SELECT price_seater_lower AS price FROM route_stops WHERE route_id = r.route_id AND price_seater_lower > 0) AS prices) AS route_min_price
            FROM route_schedules rsch JOIN routes r ON rsch.route_id = r.route_id JOIN buses b ON r.bus_id = b.bus_id LEFT JOIN bus_category_map bcm ON b.bus_id = bcm.bus_id LEFT JOIN bus_categories bc ON bcm.category_id = bc.category_id
            GROUP BY rsch.schedule_id ORDER BY r.starting_point, r.ending_point, rsch.departure_time ASC
        ");
        $all_schedules_stmt->execute();
        $all_available_schedules = $all_schedules_stmt->fetchAll(PDO::FETCH_ASSOC);

        $temp_routes = [];
        foreach ($all_available_schedules as $schedule) {
            $route_key = $schedule['starting_point'] . '|' . $schedule['ending_point'];
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
                    'categories' => [],
                    'all_operating_days' => [],
                ];
            }
            $temp_routes[$route_key]['bus_names'][$schedule['bus_name']] = true;
            $temp_routes[$route_key]['bus_types'][$schedule['bus_type']] = true; // Aggregate bus types
            if ($schedule['categories']) $temp_routes[$route_key]['categories'][$schedule['categories']] = true;
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
                <div class="row g-1 align-items-center">

                    <!-- From -->
                    <div class="col-lg-4 col-md-12">
                        <label for="from-city" class="form-label fw-semibold">From</label>
                        <div style="position: relative; width: 100%;">
                            <i class="fa-solid fa-bus"
                                style="position:absolute; left:12px; top:50%; transform:translateY(-50%);
                                  color:#555; z-index:5; pointer-events:none; font-size:16px;"></i>
                            <input type="text" class="form-control" name="from" id="from-city"
                                placeholder="Leaving from" required autocomplete="off"
                                value="<?php echo htmlspecialchars($from_location ?? ''); ?>"
                                style="padding-left:40px !important;">
                            <div class="suggestions-dropdown" id="from-suggestions"></div>
                        </div>
                    </div>

                    <!-- To -->
                    <div class="col-lg-4 col-md-12">
                        <label for="to-city" class="form-label fw-semibold">To</label>
                        <div style="position: relative; width: 100%;">
                            <i class="fa-solid fa-location-dot"
                                style="position:absolute; left:12px; top:50%; transform:translateY(-50%);
                                  color:#555; z-index:5; pointer-events:none; font-size:16px;"></i>
                            <input type="text" class="form-control" name="to" id="to-city"
                                placeholder="Going to" required autocomplete="off"
                                value="<?php echo htmlspecialchars($to_location ?? ''); ?>"
                                style="padding-left:40px !important;">
                            <div class="suggestions-dropdown" id="to-suggestions"></div>
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="col-lg-2 col-md-6">
                        <label for="date" class="form-label fw-semibold">Date</label>
                        <div style="position: relative; width: 100%;">
                            <i class="fa-solid fa-calendar-days"
                                style="position:absolute; left:12px; top:50%; transform:translateY(-50%);
                                  color:#555; z-index:5; pointer-events:none; font-size:16px;"></i>
                            <input type="date" class="form-control" name="date" id="date"
                                value="<?php echo htmlspecialchars($journey_date ?? date('Y-m-d')); ?>"
                                required min="<?php echo date('Y-m-d'); ?>"
                                style="padding-left:40px !important;">
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="col-lg-2 col-md-6 d-flex align-self-end">
                        <button type="submit" class="btn btn-brand w-100">Search Buses</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- MAIN CONTENT SECTION -->
    <div class="bus-list-container container">
        <div class="row">
            <aside class="col-lg-3 d-none d-md-block">
                <div id="filter-card" class="filter-card">
                    <h5>FILTER BY</h5>
                    <div id="bus-type-filters">
                        <h6>Bus Category</h6>
                    </div>
                    <hr>
                    <div id="departure-time-filters">
                        <h6>Departure Time</h6>
                    </div>
                </div>
            </aside>

            <section class="col-lg-9">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <div id="bus-listings-container">
                    <?php if ($is_search_performed && !empty($direct_matches)): ?>
                        <h3 class="mb-2 bus-from text-center">Buses from <strong><?php echo htmlspecialchars($from_location); ?></strong> to <strong><?php echo htmlspecialchars($to_location); ?></strong></h3>
                        <?php foreach ($direct_matches as $bus): ?>
                            <?php $available_seats = max(0, (int)$bus['available_seats']); ?>
                            <div class="bus-item-card" data-categories="<?php echo htmlspecialchars($bus['categories']); ?>" data-departure-time="<?php echo $bus['departure']; ?>">
                                <div class="bus-item-main">
                                    <div class="bus-info">
                                        <h6><?php echo htmlspecialchars($bus['bus_name']); ?></h6>
                                        <!-- FIX 2: DISPLAY bus_type FROM DATABASE -->
                                        <p class="mb-0"><?php echo htmlspecialchars($bus['bus_type']); ?></p>
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
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div id="no-filter-results" class="filter-card text-center py-5" style="display: none;">
                        <p class="lead text-muted my-4">Sorry, no buses match your selected filters.</p>
                    </div>
                </div>

                <?php if (empty($direct_matches)): ?>
                    <?php if ($is_search_performed): ?>
                        <div class="filter-card text-center  ">
                            <p class="lead text-muted my-4">Sorry, no buses were found for your search on <?php echo date('d M, Y', strtotime($journey_date)); ?>.</p>
                            <a href="index.php" class="btn btn-outline-danger">Try a Different Search</a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($processed_routes)): ?>
                        <hr class="my-2">
                        <h3 class="mb-4"><?php echo $is_search_performed ? 'Other Available Routes' : 'All Our Available Routes'; ?></h3>
                        <?php foreach ($processed_routes as $route): ?>
                            <?php
                            $all_days_for_route = implode(',', array_keys($route['all_operating_days']));
                            $start_search_date = date('Y-m-d');
                            $trip_time_today = strtotime(date('Y-m-d') . ' ' . $route['first_departure_time']);
                            if (in_array(date('D'), array_keys($route['all_operating_days'])) && $trip_time_today < time()) {
                                $start_search_date = date('Y-m-d', strtotime('+1 day'));
                            }
                            $next_date = find_next_available_date($all_days_for_route, $start_search_date);
                            if (!$next_date) continue;

                            $seats_stmt = $_conn_db->prepare("SELECT (SELECT COUNT(s.seat_id) FROM seats s WHERE s.bus_id = :bus_id AND s.is_bookable = 1) AS total_seats, (SELECT COUNT(p.passenger_id) FROM passengers p JOIN bookings bk ON p.booking_id = bk.booking_id WHERE bk.route_id = :route_id AND bk.travel_date = :next_travel_date AND bk.booking_status = 'CONFIRMED') AS booked_seats");
                            $seats_stmt->bindValue(':bus_id', $route['representative_bus_id']);
                            $seats_stmt->bindValue(':route_id', $route['representative_route_id']);
                            $seats_stmt->bindValue(':next_travel_date', $next_date);
                            $seats_stmt->execute();
                            $seat_counts = $seats_stmt->fetch(PDO::FETCH_ASSOC);
                            $available_seats = $seat_counts ? max(0, (int)$seat_counts['total_seats'] - (int)$seat_counts['booked_seats']) : 0;

                            $link_params = http_build_query(['schedule_id' => $route['representative_schedule_id'], 'from' => $route['starting_point'], 'to' => $route['ending_point'], 'date' => $next_date]);
                            $day_order = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                            $days_arr = array_keys($route['all_operating_days']);
                            usort($days_arr, fn($a, $b) => array_search($a, $day_order) - array_search($b, $day_order));
                            $display_days = (count($days_arr) >= 7) ? 'Daily' : implode(', ', $days_arr);
                            ?>
                            <div class="bus-item-card">
                                <div class="bus-item-main">
                                    <div class="bus-info">
                                        <h6><?php echo htmlspecialchars(implode(' / ', array_keys($route['bus_names']))); ?></h6>
                                        <!-- FIX 2: DISPLAY bus_type FROM DATABASE -->
                                        <p class="mb-0"><?php echo htmlspecialchars(implode(', ', array_keys($route['bus_types']))); ?></p>
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
        const allLocations = <?php echo json_encode($all_locations); ?>;
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fromInput = document.getElementById('from-city');
            const toInput = document.getElementById('to-city');
            const fromSuggestions = document.getElementById('from-suggestions');
            const toSuggestions = document.getElementById('to-suggestions');
            const searchForm = document.getElementById('bus-search-form');

            const setupAutocomplete = (input, suggestionsContainer) => {
                const showSuggestions = (filter = '') => {
                    suggestionsContainer.innerHTML = '';
                    const filterLower = filter.toLowerCase().trim();
                    // FIX 3: If filter is empty, show all locations. Otherwise, filter.
                    const locationsToShow = filterLower === '' ? allLocations : allLocations.filter(loc => loc.toLowerCase().includes(filterLower));
                    if (locationsToShow.length > 0) {
                        // Only show title when input is empty and focused
                        if (filterLower === '') {
                            suggestionsContainer.innerHTML += `<div class="suggestions-title">All Destinations</div>`;
                        }
                        locationsToShow.slice(0, 10).forEach(loc => createSuggestionItem(loc, filterLower, input, suggestionsContainer));
                        suggestionsContainer.classList.add('show');
                    } else {
                        suggestionsContainer.classList.remove('show');
                    }
                };
                input.addEventListener('input', () => showSuggestions(input.value));
                input.addEventListener('focus', () => showSuggestions(input.value)); // Show on focus
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

            setupAutocomplete(fromInput, fromSuggestions);
            setupAutocomplete(toInput, toSuggestions);

            // FIX 3: Close dropdown when clicking outside
            const closeAllSuggestions = () => {
                fromSuggestions.classList.remove('show');
                toSuggestions.classList.remove('show');
            };
            document.addEventListener('click', e => {
                if (!e.target.closest('.input-group')) closeAllSuggestions();
            });

            searchForm.addEventListener('submit', (e) => {
                if (fromInput.value.trim().toLowerCase() === toInput.value.trim().toLowerCase()) {
                    e.preventDefault();
                    alert('Origin and destination cannot be the same.');
                }
            });

            // --- Filtering Logic (No changes needed here) ---
            const busCards = Array.from(document.querySelectorAll('.bus-item-card[data-categories]'));
            const busTypeContainer = document.getElementById('bus-type-filters');
            const timeContainer = document.getElementById('departure-time-filters');
            const noFilterResultsMsg = document.getElementById('no-filter-results');
            const mainFilterCard = document.getElementById('filter-card');

            if (busCards.length === 0) {
                if (mainFilterCard) mainFilterCard.style.display = 'none';
                return;
            }

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
                const checkedCategories = getCheckedValues('.bus-type-filter');
                const checkedTimeRanges = getCheckedTimeRanges();
                let visibleCount = 0;
                busCards.forEach(card => {
                    const cardCategories = (card.dataset.categories || '').split(' ');
                    const cardDepartureHour = parseInt((card.dataset.departureTime || '00:00').split(':')[0]);
                    const categoryMatch = checkedCategories.length === 0 || checkedCategories.some(cat => cardCategories.includes(cat));
                    const timeMatch = checkedTimeRanges.length === 0 || checkedTimeRanges.some(range => cardDepartureHour >= range.start && cardDepartureHour < range.end);
                    const isVisible = categoryMatch && timeMatch;
                    card.style.display = isVisible ? '' : 'none';
                    if (isVisible) visibleCount++;
                });
                noFilterResultsMsg.style.display = visibleCount === 0 ? 'block' : 'none';
            }

            function getCheckedValues(selector) {
                return Array.from(document.querySelectorAll(selector)).filter(i => i.checked).map(i => i.value);
            }

            function getCheckedTimeRanges() {
                return Array.from(document.querySelectorAll('.time-filter:checked')).map(i => ({
                    start: parseInt(i.dataset.start),
                    end: parseInt(i.dataset.end)
                }));
            }

            function initializeFilters() {
                const allCats = new Set();
                busCards.forEach(bus => {
                    (bus.dataset.categories || '').split(' ').forEach(c => {
                        if (c) allCats.add(c);
                    });
                    const hour = parseInt((bus.dataset.departureTime || '00:00').split(':')[0]);
                    timeSlots.forEach(slot => {
                        if (hour >= slot.start && hour < slot.end) slot.present = true;
                    });
                });
                let busTypeHtml = '<h6>Bus Category</h6>'; // Filter title remains the same
                Array.from(allCats).sort().forEach(cat => {
                    busTypeHtml += `<div class="form-check"><input class="form-check-input bus-type-filter" type="checkbox" value="${cat}" id="cat-${cat}"><label class="form-check-label" for="cat-${cat}">${cat}</label></div>`;
                });
                busTypeContainer.innerHTML = busTypeHtml;
                let timeHtml = '<h6>Departure Time</h6>';
                timeSlots.forEach((slot, index) => {
                    if (slot.present) {
                        timeHtml += `<div class="form-check"><input class="form-check-input time-filter" type="checkbox" data-start="${slot.start}" data-end="${slot.end}" id="time${index}"><label class="form-check-label" for="time${index}">${slot.label}</label></div>`;
                    }
                });
                timeContainer.innerHTML = timeHtml;
                document.querySelectorAll('.bus-type-filter, .time-filter').forEach(filter => {
                    filter.addEventListener('change', updateFiltersAndBusList);
                });
            }
            initializeFilters();
        });
    </script>
</body>

</html>