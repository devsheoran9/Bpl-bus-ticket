<?php
include 'includes/header.php';
include 'db_connect.php';

// --- Helper function to find the next operating day for a specific schedule ---
function find_next_available_date($operating_days_str, $start_date_str)
{
    $day_map = ['Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6, 'Sun' => 7];
    $operating_days_arr = array_map('trim', explode(',', $operating_days_str));
    $operating_day_numbers = [];
    foreach ($operating_days_arr as $day) {
        if (isset($day_map[$day])) {
            $operating_day_numbers[] = $day_map[$day];
        }
    }
    if (empty($operating_day_numbers)) return null;

    // Start checking from the user's originally selected date
    $date = new DateTime($start_date_str);
    // Loop for up to a year to be safe
    for ($i = 0; $i < 365; $i++) {
        // 'N' gives 1 for Monday through 7 for Sunday
        if (in_array((int)$date->format('N'), $operating_day_numbers)) {
            return $date->format('Y-m-d');
        }
        $date->modify('+1 day');
    }
    return null; // Fallback in case no date is found
}


// --- 1. Get and Validate Search Parameters ---
$from_location = $_GET['from'] ?? null;
$to_location = $_GET['to'] ?? null;
$journey_date = $_GET['date'] ?? null;

if (!$from_location || !$to_location || !$journey_date) {
    die("Error: Missing search parameters. Please perform a search from the homepage.");
}

// --- 2. Main Data Fetching and Processing Logic ---
$direct_matches = [];
$other_buses = []; // This can be removed if you only ever show direct matches
$processed_routes = []; // Initialize array for the fallback list

try {
    $day_of_week = date('D', strtotime($journey_date));
    $day_of_week_like = '%' . $day_of_week . '%';

    // Query for the specific date selected by the user
    $stmt = $_conn_db->prepare("
        SELECT
            b.bus_name, GROUP_CONCAT(DISTINCT bc.category_name SEPARATOR ' ') AS categories,
            r.route_id, r.starting_point, r.ending_point, rsch.schedule_id, rsch.departure_time,
            (SELECT 1 FROM route_stops WHERE route_id = r.route_id AND stop_name = :from_loc1 UNION SELECT 1 FROM routes WHERE route_id = r.route_id AND starting_point = :from_loc2 LIMIT 1) AS has_from,
            (SELECT 1 FROM route_stops WHERE route_id = r.route_id AND stop_name = :to_loc1 UNION SELECT 1 FROM routes WHERE route_id = r.route_id AND ending_point = :to_loc2 LIMIT 1) AS has_to,
            (SELECT COALESCE(duration_from_start_minutes, 0) FROM route_stops WHERE route_id = r.route_id AND stop_name = :from_loc3) AS from_duration,
            (SELECT duration_from_start_minutes FROM route_stops WHERE route_id = r.route_id AND stop_name = :to_loc3 UNION SELECT SUM(duration_from_start_minutes) FROM route_stops WHERE route_id = r.route_id AND r.ending_point = :to_loc4 LIMIT 1) AS to_duration,
            (SELECT MIN(price) FROM (SELECT price_seater_lower AS price FROM route_stops WHERE route_id = r.route_id AND stop_name = :to_loc5) AS prices) AS journey_price
        FROM route_schedules rsch
        JOIN routes r ON rsch.route_id = r.route_id
        JOIN buses b ON r.bus_id = b.bus_id
        LEFT JOIN bus_category_map bcm ON b.bus_id = bcm.bus_id
        LEFT JOIN bus_categories bc ON bcm.category_id = bc.category_id
        WHERE rsch.operating_day LIKE :day_of_week
        GROUP BY rsch.schedule_id
        HAVING has_from = 1 AND has_to = 1
        ORDER BY rsch.departure_time ASC
    ");

    $stmt->bindParam(':from_loc1', $from_location);
    $stmt->bindParam(':from_loc2', $from_location);
    $stmt->bindParam(':from_loc3', $from_location);
    $stmt->bindParam(':to_loc1', $to_location);
    $stmt->bindParam(':to_loc2', $to_location);
    $stmt->bindParam(':to_loc3', $to_location);
    $stmt->bindParam(':to_loc4', $to_location);
    $stmt->bindParam(':to_loc5', $to_location);
    $stmt->bindParam(':day_of_week', $day_of_week_like);
    $stmt->execute();
    $direct_matches_today = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process results for the specific date
    foreach ($direct_matches_today as $bus) {
        $base_time = strtotime($journey_date . ' ' . $bus['departure_time']);
        $from_offset = (int)($bus['from_duration'] ?? 0);
        $to_offset = (int)($bus['to_duration'] ?? 0);

        if ($to_offset > $from_offset) { // Ensure the 'to' stop is after the 'from' stop
            $bus['departure'] = date('H:i', $base_time + ($from_offset * 60));
            $bus['arrival'] = date('H:i', $base_time + ($to_offset * 60));
            $duration_minutes = $to_offset - $from_offset;
            $bus['duration'] = floor($duration_minutes / 60) . 'h ' . ($duration_minutes % 60) . 'm';
            $bus['price'] = isset($bus['journey_price']) ? number_format($bus['journey_price'], 2) : 'N/A';
            $bus['link_params'] = http_build_query(['schedule_id' => $bus['schedule_id'], 'from' => $from_location, 'to' => $to_location, 'date' => $journey_date]);
            $direct_matches[] = $bus;
        }
    }

    // --- 3. Fallback Logic: If no direct matches, fetch all schedules and process them ---
    if (empty($direct_matches)) {
        $all_schedules_stmt = $_conn_db->prepare("
            SELECT
                b.bus_name, GROUP_CONCAT(DISTINCT bc.category_name SEPARATOR ', ') AS categories,
                r.starting_point, r.ending_point, rsch.schedule_id, rsch.departure_time, rsch.operating_day,
                (SELECT MIN(price) FROM (SELECT price_seater_lower AS price FROM route_stops WHERE route_id = r.route_id UNION ALL SELECT price_seater_upper FROM route_stops WHERE route_id=r.route_id) AS prices) AS route_min_price
            FROM route_schedules rsch
            JOIN routes r ON rsch.route_id = r.route_id
            JOIN buses b ON r.bus_id = b.bus_id
            LEFT JOIN bus_category_map bcm ON b.bus_id = bcm.bus_id
            LEFT JOIN bus_categories bc ON bcm.category_id = bc.category_id
            GROUP BY rsch.schedule_id
            ORDER BY r.starting_point, r.ending_point, rsch.departure_time ASC
        ");
        $all_schedules_stmt->execute();
        $all_available_schedules = $all_schedules_stmt->fetchAll(PDO::FETCH_ASSOC);

        // PHP processing to group schedules into unique routes
        $temp_routes = [];
        foreach ($all_available_schedules as $schedule) {
            $route_key = $schedule['starting_point'] . '|' . $schedule['ending_point'];
            if (!isset($temp_routes[$route_key])) {
                // This is the first time we see this route. Capture its details for the direct link.
                $temp_routes[$route_key] = [
                    'starting_point' => $schedule['starting_point'],
                    'ending_point' => $schedule['ending_point'],
                    'first_departure_time' => $schedule['departure_time'],
                    'representative_schedule_id' => $schedule['schedule_id'], // Most important for the link
                    'representative_operating_day' => $schedule['operating_day'], // Used to calc next date
                    'route_min_price' => $schedule['route_min_price'],
                    'bus_names' => [],
                    'categories' => [],
                    'all_operating_days' => [],
                ];
            }
            // Aggregate display data from all schedules for this route
            $temp_routes[$route_key]['bus_names'][$schedule['bus_name']] = true;
            if ($schedule['categories']) $temp_routes[$route_key]['categories'][$schedule['categories']] = true;
            $days = array_map('trim', explode(',', $schedule['operating_day']));
            foreach ($days as $day) {
                if ($day) $temp_routes[$route_key]['all_operating_days'][$day] = true;
            }
        }
        $processed_routes = $temp_routes; // Assign the grouped data for rendering
    }
} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
}
?>
 

<div class="bus-list-container my-5 pt-5 container">
    <div class="row">
        <!-- Filter Section -->
        <aside class="col-lg-3">
            <div id="filter-card" class="filter-card">
                <h5>FILTER BY</h5>
                <div id="bus-type-filters">
                    <h6>Bus Type</h6>
                </div>
                <hr>
                <div id="departure-time-filters">
                    <h6>Departure Time</h6>
                </div>
            </div>
        </aside>

        <!-- Bus Listings -->
        <section class="col-lg-9">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div id="bus-listings-container">
                <!-- Direct Matches for the selected date -->
                <?php if (!empty($direct_matches)): ?>
                    <h3 class="mb-4">Buses from <strong><?php echo htmlspecialchars($from_location); ?></strong> to <strong><?php echo htmlspecialchars($to_location); ?></strong></h3>
                    <?php foreach ($direct_matches as $bus): ?>
                        <div class="bus-item-card" data-categories="<?php echo htmlspecialchars($bus['categories']); ?>" data-departure-time="<?php echo $bus['departure']; ?>">
                            <div class="bus-item-main">
                                <div class="bus-info">
                                    <h6><?php echo htmlspecialchars($bus['bus_name']); ?></h6>
                                    <p><?php echo htmlspecialchars($bus['categories']); ?> &middot; 32 Seats available</p>
                                </div>
                                <div class="bus-timing">
                                    <div class="time"><?php echo $bus['departure']; ?> &rarr; <?php echo $bus['arrival']; ?></div>
                                    <div class="duration"><?php echo $bus['duration']; ?></div>
                                </div>
                                <div class="price-section">
                                    <div class="price">₹<?php echo htmlspecialchars($bus['price']); ?></div>
                                    <a href="select_seats.php?<?php echo $bus['link_params']; ?>" class="btn btn-danger btn-sm mt-2">View Seats</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div id="no-filter-results" class="filter-card text-center py-5" style="display: none;">
                    <p class="lead text-muted my-4">Sorry, no buses match your selected filters.</p>
                </div>
            </div>

            <!-- Fallback Display: Shown when no buses are found for the specific date -->
            <?php if (empty($direct_matches) && !isset($error_message)): ?>
                <div class="filter-card text-center py-5">
                    <p class="lead text-muted my-4">Sorry, no buses were found operating on your selected date.</p>
                    <a href="index.php" class="btn btn-outline-danger">Try a Different Search</a>
                </div>

                <!-- Display all unique, processed routes -->
                <?php if (!empty($processed_routes)): ?>
                    <hr class="my-5">
                    <h3 class="mb-4">All Our Available Routes</h3>
                    <?php foreach ($processed_routes as $route): ?>
                        <?php
                        // Calculate the next available date for the EARLIEST trip on this route
                        $next_date = find_next_available_date($route['representative_operating_day'], $journey_date);
                        if (!$next_date) continue; // Skip if a valid next date can't be found

                        // Build the direct link to select_seats.php
                        $link_params = http_build_query([
                            'schedule_id' => $route['representative_schedule_id'],
                            'from' => $route['starting_point'],
                            'to' => $route['ending_point'],
                            'date' => $next_date
                        ]);

                        // Format operating days for clean display
                        $day_order = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                        $days_arr = array_keys($route['all_operating_days']);
                        usort($days_arr, fn($a, $b) => array_search($a, $day_order) - array_search($b, $day_order));
                        $display_days = (count($days_arr) >= 7) ? 'Daily' : implode(', ', $days_arr);
                        ?>
                        <div class="bus-item-card">
                            <div class="bus-item-main">
                                <div class="bus-info">
                                    <h6><?php echo htmlspecialchars(implode(' / ', array_keys($route['bus_names']))); ?></h6>
                                    <p><?php echo htmlspecialchars(implode(', ', array_keys($route['categories']))); ?></p>
                                    <div class="operating-days">Runs: <?php echo $display_days; ?></div>
                                </div>
                                <div class="bus-timing">
                                    <div class="time">Starts at <?php echo date('H:i', strtotime($route['first_departure_time'])); ?></div>
                                    <div class="full-route"><?php echo htmlspecialchars($route['starting_point']); ?> &rarr; <?php echo htmlspecialchars($route['ending_point']); ?></div>
                                    <small class="text-muted">Next trip: <?php echo date('d M, Y', strtotime($next_date)); ?></small>
                                </div>
                                <div class="price-section">
                                    <div class="price">₹<?php echo number_format($route['route_min_price'] ?? 0, 2); ?></div>
                                    <a href="select_seats.php?<?php echo $link_params; ?>" class="btn btn-danger btn-sm mt-2">View Seats</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Only select cards with 'data-categories' for filtering. These only exist when a search is successful.
        const busCards = Array.from(document.querySelectorAll('.bus-item-card[data-categories]'));
        const busTypeContainer = document.getElementById('bus-type-filters');
        const timeContainer = document.getElementById('departure-time-filters');
        const noFilterResultsMsg = document.getElementById('no-filter-results');
        const mainFilterCard = document.getElementById('filter-card');

        // If no filterable buses were found on this page, hide the entire filter card and stop the script.
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

        // Main function to apply filters and update the view
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
                card.classList.toggle('hidden', !isVisible);
                if (isVisible) visibleCount++;
            });

            noFilterResultsMsg.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        // Helper to get checked values from a group of checkboxes
        function getCheckedValues(selector) {
            return Array.from(document.querySelectorAll(selector)).filter(i => i.checked).map(i => i.value);
        }

        // Helper to get selected time ranges
        function getCheckedTimeRanges() {
            return Array.from(document.querySelectorAll('.time-filter:checked')).map(i => ({
                start: parseInt(i.dataset.start),
                end: parseInt(i.dataset.end)
            }));
        }

        // This function runs once to build the filter UI
        function initializeFilters() {
            const allCats = new Set();
            // First, find all available categories and determine which time slots are populated
            busCards.forEach(bus => {
                (bus.dataset.categories || '').split(' ').forEach(c => {
                    if (c) allCats.add(c);
                });
                const hour = parseInt((bus.dataset.departureTime || '00:00').split(':')[0]);
                timeSlots.forEach(slot => {
                    if (hour >= slot.start && hour < slot.end) slot.present = true;
                });
            });

            // Create category checkboxes
            let busTypeHtml = '<h6>Bus Type</h6>';
            Array.from(allCats).sort().forEach(cat => {
                busTypeHtml += `
                <div class="form-check">
                    <input class="form-check-input bus-type-filter" type="checkbox" value="${cat}" id="cat-${cat}">
                    <label class="form-check-label" for="cat-${cat}">${cat}</label>
                </div>`;
            });
            busTypeContainer.innerHTML = busTypeHtml;

            // Create time checkboxes ONLY for slots that have buses
            let timeHtml = '<h6>Departure Time</h6>';
            timeSlots.forEach((slot, index) => {
                if (slot.present) {
                    timeHtml += `
                    <div class="form-check">
                        <input class="form-check-input time-filter" type="checkbox" data-start="${slot.start}" data-end="${slot.end}" id="time${index}">
                        <label class="form-check-label" for="time${index}">${slot.label}</label>
                    </div>`;
                }
            });
            timeContainer.innerHTML = timeHtml;

            // Add event listeners to all newly created checkboxes
            document.querySelectorAll('.bus-type-filter, .time-filter').forEach(filter => {
                filter.addEventListener('change', updateFiltersAndBusList);
            });
        }

        initializeFilters();
    });
</script>

<?php include 'includes/footer.php'; ?>