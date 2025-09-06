<?php 
include 'includes/header.php';
include 'db_connect.php'; // Make sure this file exists and connects to your database.

// --- 1. Get and Validate URL Parameters ---
$initial_schedule_id = $_GET['schedule_id'] ?? null;
$from_location = $_GET['from'] ?? null;
$to_location = $_GET['to'] ?? null;
$journey_date = $_GET['date'] ?? date('Y-m-d');

if (!$initial_schedule_id) {
    die("Error: A valid schedule ID is required to proceed.");
}

// --- Initialize variables ---
$all_points = [];
$bus_details = null;
$bus_images = [];
$lower_deck_seats = [];
$upper_deck_seats = [];
$lower_deck_height = 400;
$upper_deck_height = 400;
$stop_prices_map = [];
$error_message = null;
$is_bus_available = false; // Flag to check if booking is allowed
$availability_message = ''; // Message to show the user if not available
$actual_schedule_id = null; // The correct schedule ID for the journey date
$route_info = null; // To store route details

try {
    // --- 2. Determine the Correct Schedule and Check Availability ---

    // First, get the route_id from the schedule_id passed in the URL
    $stmt_get_route = $_conn_db->prepare("SELECT route_id FROM route_schedules WHERE schedule_id = :schedule_id");
    $stmt_get_route->bindParam(':schedule_id', $initial_schedule_id, PDO::PARAM_INT);
    $stmt_get_route->execute();
    $route_id_info = $stmt_get_route->fetch(PDO::FETCH_ASSOC);

    if (!$route_id_info) {
        throw new Exception("The provided schedule link is invalid.");
    }
    $route_id = $route_id_info['route_id'];

    // Now, find the actual schedule for the selected journey_date
    $journey_day_name = date('D', strtotime($journey_date)); // Get day name like 'Mon', 'Tue'
    $stmt_check_day = $_conn_db->prepare("
        SELECT schedule_id, departure_time
        FROM route_schedules
        WHERE route_id = :route_id AND operating_day = :operating_day
    ");
    $stmt_check_day->bindParam(':route_id', $route_id, PDO::PARAM_INT);
    $stmt_check_day->bindParam(':operating_day', $journey_day_name);
    $stmt_check_day->execute();
    $todays_schedule = $stmt_check_day->fetch(PDO::FETCH_ASSOC);

    if (!$todays_schedule) {
        // This means the bus does not run on this specific day of the week.
        $is_bus_available = false;
        $availability_message = "This bus is not available on " . date('l, d M Y', strtotime($journey_date)) . ". Please choose a different date.";
    } else {
        // A schedule exists for this day. Now, check if it has already departed (only if the journey is for today).
        $actual_schedule_id = $todays_schedule['schedule_id'];
        $departure_time_str = $todays_schedule['departure_time'];

        $is_journey_for_today = (date('Y-m-d', strtotime($journey_date)) == date('Y-m-d'));

        if ($is_journey_for_today) {
            $departure_timestamp = strtotime(date('Y-m-d') . ' ' . $departure_time_str);
            $current_timestamp = time();

            if ($current_timestamp > $departure_timestamp) {
                // The bus has already departed today.
                $is_bus_available = false;
                $availability_message = "This bus has already departed for today. Please check for later dates.";
            } else {
                // The bus is running today and has not departed yet.
                $is_bus_available = true;
            }
        } else {
            // The journey is for a future date, so it is available.
            $is_bus_available = true;
        }
    }

    // --- 3. Fetch All Bus and Route Details ---

    // We fetch basic route info regardless of availability to display it on the page.
    $stmt_main_query = $_conn_db->prepare("
        SELECT r.bus_id, r.starting_point, r.ending_point, b.bus_name
        FROM routes r
        JOIN buses b ON r.bus_id = b.bus_id
        WHERE r.route_id = :route_id
    ");
    $stmt_main_query->bindParam(':route_id', $route_id, PDO::PARAM_INT);
    $stmt_main_query->execute();
    $route_info = $stmt_main_query->fetch(PDO::FETCH_ASSOC);

    if (!$route_info) {
        throw new Exception("Could not find route details.");
    }

    // Use the correct departure time for calculations
    $departure_time_for_calc = $todays_schedule['departure_time'] ?? '00:00:00';
    $bus_details = ['name' => $route_info['bus_name'], 'departure_time' => $departure_time_for_calc];
    $bus_id = $route_info['bus_id'];


    // Fetch Bus Images
    $stmt_images = $_conn_db->prepare("SELECT image_path FROM bus_images WHERE bus_id = :bus_id");
    $stmt_images->bindParam(':bus_id', $bus_id, PDO::PARAM_INT);
    $stmt_images->execute();
    $bus_images = $stmt_images->fetchAll(PDO::FETCH_COLUMN, 0);

    // Fetch all stops for the route to build the point list AND the price map
    $stmt_stops = $_conn_db->prepare("SELECT * FROM route_stops WHERE route_id = :route_id ORDER BY stop_order ASC");
    $stmt_stops->bindParam(':route_id', $route_id);
    $stmt_stops->execute();
    $stops = $stmt_stops->fetchAll(PDO::FETCH_ASSOC);

    $total_route_duration = 0;
    $stop_prices_map[$route_info['starting_point']] = ['price_seater_lower' => 0, 'price_seater_upper' => 0, 'price_sleeper_lower' => 0, 'price_sleeper_upper' => 0];
    foreach ($stops as $stop) {
        $stop_prices_map[$stop['stop_name']] = [
            'price_seater_lower' => (float)$stop['price_seater_lower'],
            'price_seater_upper' => (float)$stop['price_seater_upper'],
            'price_sleeper_lower' => (float)$stop['price_sleeper_lower'],
            'price_sleeper_upper' => (float)$stop['price_sleeper_upper']
        ];
        $total_route_duration = max($total_route_duration, $stop['duration_from_start_minutes']);
    }

    // Calculate arrival times for all points based on the correct departure time
    $base_departure_time = strtotime($journey_date . ' ' . $bus_details['departure_time']);
    $points_map = [];
    $points_map[$route_info['starting_point']] = ['name' => $route_info['starting_point'], 'time' => date('H:i', $base_departure_time), 'order' => 0];
    foreach ($stops as $stop) {
        if (!isset($points_map[$stop['stop_name']])) {
            $points_map[$stop['stop_name']] = ['name' => $stop['stop_name'], 'time' => date('H:i', $base_departure_time + ($stop['duration_from_start_minutes'] * 60)), 'order' => (int)$stop['stop_order']];
        }
    }
    if (!isset($points_map[$route_info['ending_point']])) {
        $points_map[$route_info['ending_point']] = ['name' => $route_info['ending_point'], 'time' => date('H:i', $base_departure_time + ($total_route_duration * 60)), 'order' => count($points_map)];
    }
    $all_points = array_values($points_map);
    usort($all_points, fn($a, $b) => $a['order'] <=> $b['order']);


    // Fetch the SEAT LAYOUT for the bus
    $stmt_layout = $_conn_db->prepare("SELECT * FROM seats WHERE bus_id = :bus_id ORDER BY deck, y_coordinate, x_coordinate");
    $stmt_layout->bindParam(':bus_id', $bus_id, PDO::PARAM_INT);
    $stmt_layout->execute();
    $all_seats_layout = $stmt_layout->fetchAll(PDO::FETCH_ASSOC);
    if (empty($all_seats_layout)) {
        throw new Exception("No seat layout has been configured for this bus.");
    }

    $booked_seats_info = [];
    if ($is_bus_available) {
        // Fetch BOOKED seats with GENDER for THIS SPECIFIC TRIP using the correct schedule ID
        $stmt_booked = $_conn_db->prepare("SELECT seat_code, passenger_gender FROM bookings WHERE schedule_id = :schedule_id AND journey_date = :journey_date");
        $stmt_booked->bindParam(':schedule_id', $actual_schedule_id, PDO::PARAM_INT);
        $stmt_booked->bindParam(':journey_date', $journey_date);
        $stmt_booked->execute();
        $booked_seats_info = $stmt_booked->fetchAll(PDO::FETCH_KEY_PAIR); // Creates a map like ['seat_code' => 'gender']
    }


    // Process seats and CALCULATE required deck height
    foreach ($all_seats_layout as $seat) {
        $is_booked = isset($booked_seats_info[$seat['seat_code']]);
        // If the bus is not available for booking, mark all seats as sold.
        $seat['final_status'] = (!$is_bus_available || $seat['status'] !== 'AVAILABLE' || $seat['is_bookable'] == 0 || $is_booked) ? 'SOLD' : 'AVAILABLE';
        $seat['booked_by_gender'] = $is_booked ? strtoupper($booked_seats_info[$seat['seat_code']]) : null;

        if (strtoupper($seat['deck']) === 'LOWER') {
            $lower_deck_seats[] = $seat;
            $lower_deck_height = max($lower_deck_height, $seat['y_coordinate'] + $seat['height']);
        } else {
            $upper_deck_seats[] = $seat;
            $upper_deck_height = max($upper_deck_height, $seat['y_coordinate'] + $seat['height']);
        }
    }
    $lower_deck_height += 20;
    $upper_deck_height += 20;
} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
}

function get_seat_classes($seat)
{
    $classes = ['seat', strtolower($seat['seat_type'])];
    if ($seat['final_status'] === 'SOLD') {
        $classes[] = 'sold';
        if ($seat['booked_by_gender'] === 'MALE') $classes[] = 'sold-male';
        if ($seat['booked_by_gender'] === 'FEMALE') $classes[] = 'sold-female';
    } else {
        $classes[] = 'available';
        if (strtoupper($seat['gender_preference']) === 'MALE') $classes[] = 'male-only';
        if (strtoupper($seat['gender_preference']) === 'FEMALE') $classes[] = 'female-only';
    }
    return implode(' ', $classes);
}
function get_transform_style($orientation)
{
    switch (strtoupper($orientation)) {
        case 'VERTICAL_DOWN':
            return 'transform: rotate(180deg);';
        case 'HORIZONTAL_RIGHT':
            return 'transform: rotate(90deg);';
        case 'HORIZONTAL_LEFT':
            return 'transform: rotate(-90deg);';
        default:
            return '';
    }
}
function highlight_route_segment($all_points, $from, $to)
{
    $path = [];
    $from_order = -1;
    $to_order = -1;
    foreach ($all_points as $point) {
        if ($point['name'] === $from) $from_order = $point['order'];
        if ($point['name'] === $to) $to_order = $point['order'];
    }

    foreach ($all_points as $point) {
        $name = htmlspecialchars($point['name']);
        if ($from_order !== -1 && $to_order !== -1 && $point['order'] >= $from_order && $point['order'] <= $to_order) {
            $path[] = "<strong>{$name}</strong>";
        } else {
            $path[] = $name;
        }
    }
    return implode(' &rarr; ', $path);
}
?>
 

    <style>
        /* All your existing CSS styles go here... I have not changed them. */
        .step-container {
            display: none;
        }

        .step-container.active {
            display: block;
        }

        .panel-card {
            background-color: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            padding: 1.5rem;
        }

        .point-option {
            display: flex;
            align-items: center;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .point-option.selected {
            border-color: #E91E63;
            box-shadow: 0 0 0 2px rgba(233, 30, 99, 0.25);
        }

        .point-option.disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .bottom-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #fff;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }

        .deck-layout-wrapper {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .deck-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .deck {
            position: relative;
            border: 1px solid #dee2e6;
            border-radius: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            width: 350px;
            margin: 0 auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .seat {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            box-sizing: border-box;
            color: #212529;
            font-size: 11px;
            font-weight: bold;
            padding: 2px;
        }

        .seat-info {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .seat .price {
            font-size: 10px;
            font-weight: normal;
            color: #495057;
        }

        .seat i {
            font-size: 1em;
        }

        .seat.seater,
        .seat.sleeper {
            border-top: 5px solid #6c757d;
        }

        .seat.driver {
            border-radius: 50%;
            background-color: #343a40;
            color: white;
            cursor: default;
        }

        .seat.aisle {
            background-color: transparent;
            border: 1px dashed #adb5bd;
            cursor: default;
        }

        .seat.available {
            background-color: #fff;
            border: 1px solid #adb5bd;
        }

        .seat.available:not(.selected-any):not(.selected-male):not(.selected-female):hover {
            transform: scale(1.05);
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
        }

        .seat.selected-any {
            background-color: #28a745;
            border-color: #1e7e34;
            color: white;
        }

        .seat.selected-male {
            background-color: #007bff;
            border-color: #0056b3;
            color: white;
        }

        .seat.selected-female {
            background-color: #E91E63;
            border-color: #c2185b;
            color: white;
        }

        .seat.selected-any .price,
        .seat.selected-male .price,
        .seat.selected-female .price {
            color: white;
        }

        .seat.sold {
            background-color: #e9ecef;
            border-color: #ced4da;
            color: #6c757d;
            cursor: not-allowed;
        }

        .seat.sold-male {
            background-color: #cfe2ff;
            border-color: #b6d4fe;
        }

        .seat.sold-female {
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }

        .seat.male-only.available {
            border-bottom: 3px solid #007bff;
        }

        .seat.female-only.available {
            border-bottom: 3px solid #e91e63;
        }

        .gender-icon.male {
            color: #007bff;
        }

        .gender-icon.female {
            color: #e91e63;
        }

        .info-section {
            margin-bottom: 2rem;
        }

        .info-section h5 {
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .feature-item .icon {
            font-size: 1.5rem;
            color: #6c757d;
        }

        .bus-route-path {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .seat-legend {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .legend-row {
            display: grid;
            grid-template-columns: 1fr 80px 80px;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .legend-seat {
            width: 40px;
            height: 25px;
            border-radius: 3px;
        }

        .legend-seat.sleeper {
            height: 40px;
        }

        .carousel-item img {
            aspect-ratio: 16 / 9;
            object-fit: cover;
        }

        .carousel-item a {
            display: block;
            width: 100%;
            height: 100%;
        }

        .info-tabs .nav-link {
            color: #6c757d;
        }

        .info-tabs .nav-link.active {
            color: #000;
            font-weight: 600;
            border-color: #dee2e6 #dee2e6 #fff;
            border-bottom: 2px solid #E91E63;
        }

        .boarding-point-list .point {
            position: relative;
            padding-left: 25px;
            margin-bottom: 1.5rem;
        }

        .boarding-point-list .point::before {
            content: '';
            position: absolute;
            left: 4px;
            top: 4px;
            width: 10px;
            height: 10px;
            background: #6c757d;
            border-radius: 50%;
        }

        .boarding-point-list .point::after {
            content: '';
            position: absolute;
            left: 8px;
            top: 18px;
            width: 2px;
            height: calc(100%);
            background: #ced4da;
        }

        .boarding-point-list .point:last-child::after {
            display: none;
        }

        @media (max-width: 991px) {
            .deck-layout-wrapper {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
 
<body>
    <header class="top-header mt-5 pt-5">
        <div class="container">
            <div class="progress-steps">
                <div class="step active" data-step="1">1. Board/Drop point</div>
                <div class="step" data-step="2">2. Select seats</div>
                <div class="step" data-step="3">3. Passenger Info</div>
            </div>
        </div>
    </header>

    <main class="container pt-2">
        <?php if ($error_message) : ?>
            <div class="alert alert-danger text-center">
                <h4>An Error Occurred</h4>
                <p><?php echo htmlspecialchars($error_message); ?></p><a href="index.php" class="btn btn-secondary">Go Back</a>
            </div>
        <?php else : ?>

            <div id="step-1" class="step-container">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="panel-card">
                            <?php if (!$is_bus_available) : ?>
                                <div class="alert alert-warning text-center" role="alert">
                                    <strong><?php echo htmlspecialchars($availability_message); ?></strong>
                                </div>
                            <?php endif; ?>
                            <h5>Boarding points</h5>
                            <p class="text-muted">Select Boarding Point</p>
                            <?php foreach ($all_points as $index => $point) : ?>
                                <?php if ($index === count($all_points) - 1) continue; ?>
                                <div class="point-option <?php if (!$is_bus_available) echo 'disabled'; ?>" data-order="<?php echo $point['order']; ?>">
                                    <input type="radio" name="boarding_point" id="bp<?php echo $index; ?>" value="<?php echo htmlspecialchars($point['name']); ?>" <?php if ($point['name'] === $from_location) echo 'checked'; ?> <?php if (!$is_bus_available) echo 'disabled'; ?>>
                                    <div>
                                        <label for="bp<?php echo $index; ?>" class="point-time"><?php echo htmlspecialchars($point['time']); ?></label>
                                        <label for="bp<?php echo $index; ?>" class="point-name"><?php echo htmlspecialchars($point['name']); ?></label>
                                        <label for="bp<?php echo $index; ?>" class="point-details">Details about <?php echo htmlspecialchars($point['name']); ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel-card">
                            <?php if (!$is_bus_available) : ?>
                                <div class="alert alert-warning text-center" role="alert">
                                    <strong><?php echo htmlspecialchars($availability_message); ?></strong>
                                </div>
                            <?php endif; ?>
                            <h5>Dropping points</h5>
                            <p class="text-muted">Select Dropping Point</p>
                            <?php foreach ($all_points as $index => $point) : ?>
                                <?php if ($index === 0) continue; ?>
                                <div class="point-option <?php if (!$is_bus_available) echo 'disabled'; ?>" data-order="<?php echo $point['order']; ?>">
                                    <input type="radio" name="dropping_point" id="dp<?php echo $index; ?>" value="<?php echo htmlspecialchars($point['name']); ?>" <?php if ($point['name'] === $to_location) echo 'checked'; ?> <?php if (!$is_bus_available) echo 'disabled'; ?>>
                                    <div>
                                        <label for="dp<?php echo $index; ?>" class="point-time"><?php echo htmlspecialchars($point['time']); ?> <span class="small text-muted"><?php echo date('d M', strtotime($journey_date)); ?></span></label>
                                        <label for="dp<?php echo $index; ?>" class="point-name"><?php echo htmlspecialchars($point['name']); ?></label>
                                        <label for="dp<?php echo $index; ?>" class="point-details">Details about <?php echo htmlspecialchars($point['name']); ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div id="step-2" class="step-container">
                <div class="row">
                    <div class="col-lg-8">
                        <?php if (!$is_bus_available) : ?>
                            <div class="alert alert-warning text-center mb-4" role="alert">
                                <strong><?php echo htmlspecialchars($availability_message); ?></strong>
                                <p class="mb-0 mt-2">Seat selection is disabled.</p>
                            </div>
                        <?php endif; ?>
                        <div class="panel-card mb-4">
                            <div class="deck-layout-wrapper">
                                <div class="deck-container">
                                    <h5 class="text-center">Lower Deck</h5>
                                    <div class="deck" id="lower_deck" style="min-height: <?php echo $lower_deck_height; ?>px;">
                                        <?php foreach ($lower_deck_seats as $seat) : ?>
                                            <div class="<?php echo get_seat_classes($seat); ?>" style="left: <?php echo $seat['x_coordinate']; ?>px; top: <?php echo $seat['y_coordinate']; ?>px; width: <?php echo $seat['width']; ?>px; height: <?php echo $seat['height']; ?>px; <?php echo get_transform_style($seat['orientation']); ?>" data-seat-id="<?php echo htmlspecialchars($seat['seat_code']); ?>" data-price="<?php echo htmlspecialchars($seat['base_price']); ?>" data-seat-type="<?php echo strtoupper($seat['seat_type']); ?>" data-deck="LOWER" data-gender-lock="<?php echo strtolower($seat['gender_preference']); ?>">
                                                <?php if ($seat['seat_type'] == 'DRIVER') : ?><i class="fas fa-user-tie"></i> Driver<?php elseif ($seat['seat_type'] != 'AISLE') : ?><div class="seat-info"><span><?php echo htmlspecialchars($seat['seat_code']); ?></span><?php if ($seat['gender_preference'] == 'MALE') : ?><i class="fas fa-male gender-icon male"></i><?php endif; ?><?php if ($seat['gender_preference'] == 'FEMALE') : ?><i class="fas fa-female gender-icon female"></i><?php endif; ?></div><span class="price">₹<?php echo (int)$seat['base_price']; ?></span><?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php if (!empty($upper_deck_seats)) : ?>
                                    <div class="deck-container">
                                        <h5 class="text-center">Upper Deck</h5>
                                        <div class="deck" id="upper_deck" style="min-height: <?php echo $upper_deck_height; ?>px;">
                                            <?php foreach ($upper_deck_seats as $seat) : ?>
                                                <div class="<?php echo get_seat_classes($seat); ?>" style="left: <?php echo $seat['x_coordinate']; ?>px; top: <?php echo $seat['y_coordinate']; ?>px; width: <?php echo $seat['width']; ?>px; height: <?php echo $seat['height']; ?>px; <?php echo get_transform_style($seat['orientation']); ?>" data-seat-id="<?php echo htmlspecialchars($seat['seat_code']); ?>" data-price="<?php echo htmlspecialchars($seat['base_price']); ?>" data-seat-type="<?php echo strtoupper($seat['seat_type']); ?>" data-deck="UPPER" data-gender-lock="<?php echo strtolower($seat['gender_preference']); ?>">
                                                    <?php if ($seat['seat_type'] != 'AISLE') : ?><div class="seat-info"><span><?php echo htmlspecialchars($seat['seat_code']); ?></span><?php if ($seat['gender_preference'] == 'MALE') : ?><i class="fas fa-male gender-icon male"></i><?php endif; ?><?php if ($seat['gender_preference'] == 'FEMALE') : ?><i class="fas fa-female gender-icon female"></i><?php endif; ?></div><span class="price">₹<?php echo (int)$seat['base_price']; ?></span><?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="panel-card">
                            <h5>Know your seat types</h5>
                            <div class="seat-legend">
                                <div class="legend-row fw-bold text-muted">
                                    <div class="small">SEAT TYPES</div>
                                    <div class="small text-center">SEATER</div>
                                    <div class="small text-center">SLEEPER</div>
                                </div>
                                <hr class="my-2">
                                <div class="legend-row">
                                    <div class="small">Available</div>
                                    <div class="d-flex justify-content-center">
                                        <div class="legend-seat seat available"></div>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <div class="legend-seat sleeper seat available"></div>
                                    </div>
                                </div>
                                <div class="legend-row">
                                    <div class="small">Available for Male</div>
                                    <div class="d-flex justify-content-center">
                                        <div class="legend-seat seat available male-only"></div>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <div class="legend-seat sleeper seat available male-only"></div>
                                    </div>
                                </div>
                                <div class="legend-row">
                                    <div class="small">Available for Female</div>
                                    <div class="d-flex justify-content-center">
                                        <div class="legend-seat seat available female-only"></div>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <div class="legend-seat sleeper seat available female-only"></div>
                                    </div>
                                </div>
                                <div class="legend-row">
                                    <div class="small">Booked by Male</div>
                                    <div class="d-flex justify-content-center">
                                        <div class="legend-seat seat sold sold-male"></div>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <div class="legend-seat sleeper seat sold sold-male"></div>
                                    </div>
                                </div>
                                <div class="legend-row">
                                    <div class="small">Booked by Female</div>
                                    <div class="d-flex justify-content-center">
                                        <div class="legend-seat seat sold sold-female"></div>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <div class="legend-seat sleeper seat sold sold-female"></div>
                                    </div>
                                </div>
                                <div class="legend-row">
                                    <div class="small">Booked</div>
                                    <div class="d-flex justify-content-center">
                                        <div class="legend-seat seat sold"></div>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <div class="legend-seat sleeper seat sold"></div>
                                    </div>
                                </div>
                                <div class="legend-row">
                                    <div class="small">Selected by you</div>
                                    <div class="d-flex justify-content-center">
                                        <div class="legend-seat seat selected-any"></div>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <div class="legend-seat sleeper seat selected-any"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="panel-card">
                            <ul class="nav nav-tabs info-tabs" id="infoTab" role="tablist">
                                <li class="nav-item" role="presentation"><button class="nav-link active" id="why-book-tab" data-bs-toggle="tab" data-bs-target="#why-book-pane" type="button" role="tab">Why book this bus?</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" id="route-tab" data-bs-toggle="tab" data-bs-target="#route-pane" type="button" role="tab">Bus Route</button></li>
                            </ul>
                            <div class="tab-content pt-4">
                                <div class="tab-pane fade show active" id="why-book-pane" role="tabpanel">
                                    <?php if (!empty($bus_images)) : ?>
                                        <div id="busImageSlider" class="carousel slide mb-4" data-bs-ride="carousel">
                                            <div class="carousel-inner rounded">
                                                <?php foreach ($bus_images as $index => $image) :
                                                    $image_path = "admin/function/backend/uploads/bus_images/" . htmlspecialchars($image);
                                                ?>
                                                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                                        <a href="<?php echo $image_path; ?>" data-fancybox="gallery" data-caption="Bus Image <?php echo $index + 1; ?>">
                                                            <img src="<?php echo $image_path; ?>" class="d-block w-100" alt="Bus Image">
                                                        </a>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#busImageSlider" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#busImageSlider" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                        </div>
                                    <?php endif; ?>

                                    <div class="info-section">
                                        <div class="feature-item">
                                            <div class="icon"><i class="fas fa-female"></i></div>
                                            <div><strong>Women Traveling Friendly</strong>
                                                <p class="small text-muted mb-0">This operator is highly rated by female passengers.</p>
                                            </div>
                                        </div>
                                        <div class="feature-item">
                                            <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                                            <div><strong>Live Tracking</strong>
                                                <p class="small text-muted mb-0">Track your bus in real-time and plan your commute.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-section">
                                        <div class="accordion" id="busInfoAccordion">
                                            <div class="accordion-item">
                                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePromo">Unlock Return Trip Promo</button></h2>
                                                <div id="collapsePromo" class="accordion-collapse collapse" data-bs-parent="#busInfoAccordion">
                                                    <div class="accordion-body small">Book now and unlock a minimum 10% OFF on your next trip! Terms and conditions apply.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-section">
                                        <h5>Amenities</h5>
                                        <p class="small text-muted">This bus is equipped with Water Bottles, Blankets, and Charging Points.</p>
                                    </div>
                                    <div class="info-section">
                                        <h5>Rest Stop Information</h5>
                                        <p class="small text-muted">This service includes designated rest stops. The crew will announce the duration of each stop.</p>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="route-pane" role="tabpanel">
                                    <div class="info-section">
                                        <h5>Bus Route</h5>
                                        <p class="bus-route-path small text-muted" id="highlighted-route"></p>
                                    </div>
                                    <div class="info-section boarding-point-list">
                                        <h5>Boarding Points</h5><?php foreach ($all_points as $point) : if ($point['name'] == $route_info['ending_point']) continue; ?><div class="point">
                                                <div><strong><?php echo $point['time']; ?></strong> - <?php echo date('d M', strtotime($journey_date)); ?></div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($point['name']); ?></div>
                                            </div><?php endforeach; ?>
                                    </div>
                                    <div class="info-section boarding-point-list">
                                        <h5>Dropping Points</h5><?php foreach ($all_points as $point) : if ($point['name'] == $route_info['starting_point']) continue; ?><div class="point">
                                                <div><strong><?php echo $point['time']; ?></strong> - <?php echo date('d M', strtotime($journey_date)); ?></div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($point['name']); ?></div>
                                            </div><?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="step-3" class="step-container">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="panel-card mb-4">
                            <h5>Contact Details</h5>
                            <p class="small text-muted">Your ticket will be sent to this email and phone number.</p>
                            <div class="mb-3"><label class="form-label">Email Address</label>
                                <div class="input-group"><span class="input-group-text"><i class="fas fa-envelope"></i></span><input type="email" class="form-control" placeholder="example@email.com"></div>
                            </div>
                            <div class="mb-3"><label class="form-label">Phone Number</label>
                                <div class="input-group"><span class="input-group-text">+91</span><input type="tel" class="form-control" placeholder="Mobile Number"></div>
                            </div>
                        </div>
                        <div id="passenger-details-forms"></div>
                    </div>
                    <div class="col-lg-5">
                        <div class="panel-card">
                            <h5>Journey Summary</h5>
                            <hr>
                            <div class="summary-body">
                                <div class="summary-item">
                                    <div><strong id="summary-route"></strong>
                                        <p class="small text-muted mb-0" id="summary-datetime"></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="summary-item">
                                    <div><strong id="summary-boarding-point"></strong></div>
                                </div>
                                <div class="summary-item">
                                    <div><strong id="summary-dropping-point"></strong></div>
                                </div>
                                <hr>
                                <div class="summary-item">
                                    <div><strong>Selected Seats</strong></div>
                                    <div id="summary-seat-numbers"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </main>

    <br><br><br><br><br>
    <div id="bottom-bar" class="bottom-action-bar">
        <div><span id="seat-count-text"></span><br><strong class="fs-5" id="price-container" style="display: none;">₹<span id="total-price">0</span></strong></div><button id="action-btn" class="btn btn-danger btn-lg">Continue</button>
    </div>
    <script>
        const stopPrices = <?php echo json_encode($stop_prices_map); ?>;
        const allPoints = <?php echo json_encode($all_points); ?>;
        const isBusAvailable = <?php echo json_encode($is_bus_available); ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // All your existing JavaScript goes here... I have not changed it.
            const selectedSeats = new Map();
            let currentStep = 1;
            const actionBtn = document.getElementById('action-btn');
            const bottomBar = document.getElementById('bottom-bar');
            const stepElements = document.querySelectorAll('.step');
            const totalPriceEl = document.getElementById('total-price');
            const priceContainerEl = document.getElementById('price-container');
            const seatCountTextEl = document.getElementById('seat-count-text');
            const droppingPointOptions = document.querySelectorAll('input[name="dropping_point"]');

            Fancybox.bind("[data-fancybox]", {
                // Custom options if needed
            });

            function calculateAndApplyPrices() {
                const boardingPointEl = document.querySelector('input[name="boarding_point"]:checked');
                const droppingPointEl = document.querySelector('input[name="dropping_point"]:checked');
                if (!boardingPointEl || !droppingPointEl) return;
                const boardPointName = boardingPointEl.value;
                const dropPointName = droppingPointEl.value;
                const pricesAtBoard = stopPrices[boardPointName] || {};
                const pricesAtDrop = stopPrices[dropPointName] || {};
                document.querySelectorAll('.seat.available').forEach(seat => {
                    const seatType = seat.dataset.seatType;
                    const deckType = seat.dataset.deck;
                    if (!seatType || !deckType) return;
                    const priceKey = `price_${seatType.toLowerCase()}_${deckType.toLowerCase()}`;
                    const priceFromStartToBoard = pricesAtBoard[priceKey] || 0;
                    const priceFromStartToDrop = pricesAtDrop[priceKey] || 0;
                    const finalPrice = Math.max(0, priceFromStartToDrop - priceFromStartToBoard);
                    seat.dataset.price = finalPrice;
                    const priceSpan = seat.querySelector('.price');
                    if (priceSpan) {
                        priceSpan.textContent = `₹${finalPrice}`;
                    }
                });
                selectedSeats.clear();
                document.querySelectorAll('.seat').forEach(s => s.classList.remove('selected-any', 'selected-male', 'selected-female'));
                updateSummaryBar();
            }

            function updatePointSelectionStyles() {
                document.querySelectorAll('input[name="boarding_point"]').forEach(radio => {
                    radio.closest('.point-option').classList.toggle('selected', radio.checked);
                });
                document.querySelectorAll('input[name="dropping_point"]').forEach(radio => {
                    radio.closest('.point-option').classList.toggle('selected', radio.checked);
                });
            }

            function updateDroppingPointsLogic() {
                const selectedBoardingRadio = document.querySelector('input[name="boarding_point"]:checked');
                if (!selectedBoardingRadio) {
                    droppingPointOptions.forEach(radio => {
                        if (!radio.closest('.point-option').classList.contains('disabled')) {
                            radio.disabled = true;
                        }
                        radio.checked = false;
                    });
                    updatePointSelectionStyles();
                    return;
                }

                const selectedBoardingOrder = parseInt(selectedBoardingRadio.closest('.point-option').dataset.order);
                const currentDroppingPoint = document.querySelector('input[name="dropping_point"]:checked');

                droppingPointOptions.forEach(radio => {
                    if (radio.closest('.point-option').classList.contains('disabled')) return; // Skip if disabled by PHP

                    const droppingOrder = parseInt(radio.closest('.point-option').dataset.order);
                    const isDroppable = droppingOrder > selectedBoardingOrder;
                    radio.disabled = !isDroppable;

                    if (!isDroppable && radio.checked) {
                        radio.checked = false;
                    }
                });

                if (currentDroppingPoint && currentDroppingPoint.disabled) {
                    currentDroppingPoint.checked = false;
                }
            }

            function updateHighlightedRoute() {
                const fromEl = document.querySelector('input[name="boarding_point"]:checked');
                const toEl = document.querySelector('input[name="dropping_point"]:checked');
                const routeContainer = document.getElementById('highlighted-route');
                if (!fromEl || !toEl) {
                    if (routeContainer) routeContainer.innerHTML = 'Please select boarding and dropping points.';
                    return;
                }
                const fromName = fromEl.value;
                const toName = toEl.value;
                let fromOrder = -1,
                    toOrder = -1;
                allPoints.forEach(p => {
                    if (p.name === fromName) fromOrder = p.order;
                    if (p.name === toName) toOrder = p.order;
                });
                const path = allPoints.map(p => {
                    if (fromOrder !== -1 && toOrder !== -1 && p.order >= fromOrder && p.order <= toOrder) {
                        return `<strong>${p.name}</strong>`;
                    }
                    return p.name;
                }).join(' &rarr; ');

                if (routeContainer) routeContainer.innerHTML = path;
            }

            document.querySelectorAll('input[name="boarding_point"], input[name="dropping_point"]').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    if (e.target.name === 'boarding_point') {
                        sessionStorage.setItem('boardingPoint', e.target.value);
                        updateDroppingPointsLogic();
                    } else {
                        sessionStorage.setItem('droppingPoint', e.target.value);
                    }

                    updatePointSelectionStyles();

                    if (validateBoardingDroppingPoints()) {
                        calculateAndApplyPrices();
                    }
                    updateSummaryBar();
                    updateHighlightedRoute();
                });
            });

            document.querySelectorAll('.seat.available').forEach(seat => {
                seat.addEventListener('click', () => {
                    if (currentStep !== 2 || !isBusAvailable) return;
                    const seatId = seat.dataset.seatId;
                    const price = parseInt(seat.dataset.price);
                    const genderLock = seat.dataset.genderLock || 'any';
                    seat.classList.remove('selected-any', 'selected-male', 'selected-female');
                    if (selectedSeats.has(seatId)) {
                        selectedSeats.delete(seatId);
                    } else {
                        if (selectedSeats.size >= 6) {
                            alert("You can select a maximum of 6 seats.");
                            return;
                        }
                        selectedSeats.set(seatId, {
                            price,
                            genderLock
                        });
                        if (genderLock === 'male') {
                            seat.classList.add('selected-male');
                        } else if (genderLock === 'female') {
                            seat.classList.add('selected-female');
                        } else {
                            seat.classList.add('selected-any');
                        }
                    }
                    updateSummaryBar();
                });
            });

            function updateSummaryBar() {
                if (!isBusAvailable) {
                    actionBtn.disabled = true;
                    priceContainerEl.style.display = 'none';
                    seatCountTextEl.textContent = 'Booking not available';
                    return;
                }
                if (currentStep === 1) {
                    const isStep1Valid = validateBoardingDroppingPoints();
                    actionBtn.disabled = !isStep1Valid;
                    priceContainerEl.style.display = 'none';
                    seatCountTextEl.textContent = isStep1Valid ? 'All points selected' : 'Select boarding & dropping points';
                } else if (currentStep === 2) {
                    const totalPrice = Array.from(selectedSeats.values()).reduce((sum, s) => sum + s.price, 0);
                    const seatCount = selectedSeats.size;
                    priceContainerEl.style.display = seatCount > 0 ? 'block' : 'none';
                    actionBtn.disabled = (seatCount === 0);
                    totalPriceEl.textContent = totalPrice;
                    seatCountTextEl.textContent = seatCount > 0 ? `${seatCount} Seat(s) Selected` : 'Please select your seat(s)';
                } else if (currentStep === 3) {
                    const totalPrice = Array.from(selectedSeats.values()).reduce((sum, s) => sum + s.price, 0);
                    const seatCount = selectedSeats.size;
                    actionBtn.disabled = false;
                    priceContainerEl.style.display = 'block';
                    totalPriceEl.textContent = totalPrice;
                    seatCountTextEl.textContent = `${seatCount} Seat(s) Total`;
                }
            }

            function validateBoardingDroppingPoints() {
                if (!isBusAvailable) return false;
                return document.querySelector('input[name="boarding_point"]:checked') && document.querySelector('input[name="dropping_point"]:checked');
            }

            function validateSeatSelection() {
                if (selectedSeats.size === 0) {
                    alert('Please select at least one seat.');
                    return false;
                }
                return true;
            }

            actionBtn.addEventListener('click', () => {
                if (actionBtn.disabled) return;
                if (currentStep === 1) {
                    if (validateBoardingDroppingPoints()) handleGoToStep(2);
                } else if (currentStep === 2) {
                    if (validateSeatSelection()) handleGoToStep(3);
                } else if (currentStep === 3) {
                    alert('Proceeding to payment!');
                }
            });
            stepElements.forEach(stepEl => {
                stepEl.addEventListener('click', () => {
                    const targetStep = parseInt(stepEl.dataset.step);
                    if (targetStep < currentStep) {
                        handleGoToStep(targetStep);
                    }
                });
            });

            function handleGoToStep(targetStep) {
                if (!isBusAvailable) return;
                if (targetStep === currentStep) return;
                if (targetStep > 1 && !validateBoardingDroppingPoints()) {
                    alert('Please select your boarding and dropping points first.');
                    return;
                }
                if (targetStep > 2 && !validateSeatSelection()) {
                    return;
                }
                if (targetStep === 2 && currentStep === 1) {
                    calculateAndApplyPrices();
                }
                if (targetStep === 3) {
                    updateFinalSummary();
                    populatePassengerForms();
                }
                goToStep(targetStep);
            }

            function goToStep(stepNumber) {
                currentStep = stepNumber;
                sessionStorage.setItem('currentStep', stepNumber);
                stepElements.forEach(stepEl => {
                    const isCurrent = parseInt(stepEl.dataset.step) === stepNumber;
                    const isCompleted = parseInt(stepEl.dataset.step) < stepNumber;
                    stepEl.classList.toggle('active', isCurrent);
                    stepEl.classList.toggle('completed', isCompleted);
                });
                document.querySelectorAll('.step-container').forEach(container => {
                    container.classList.remove('active');
                });
                const nextStepElement = document.getElementById(`step-${stepNumber}`);
                if (nextStepElement) {
                    nextStepElement.classList.add('active');
                }
                actionBtn.textContent = (stepNumber === 3) ? 'Proceed to Payment' : 'Continue';
                updateSummaryBar();
            }

            function populatePassengerForms() {
                const container = document.getElementById('passenger-details-forms');
                container.innerHTML = '';
                let passengerCount = 1;
                for (const [seatId, seatData] of selectedSeats.entries()) {
                    let genderSelectHtml;
                    if (seatData.genderLock === 'male') {
                        genderSelectHtml = `<select class="form-select" required disabled style="background-color: #e9ecef;"><option value="male" selected>Male</option></select>`;
                    } else if (seatData.genderLock === 'female') {
                        genderSelectHtml = `<select class="form-select" required disabled style="background-color: #e9ecef;"><option value="female" selected>Female</option></select>`;
                    } else {
                        genderSelectHtml = `<select class="form-select" required><option value="">Select</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select>`;
                    }
                    const formHtml = `<div class="panel-card mb-3"><h6>Passenger ${passengerCount} <span class="badge bg-secondary">${seatId}</span></h6><div class="row"><div class="col-md-12 mb-3"><label class="form-label">Name</label><div class="input-group"><span class="input-group-text"><i class="fas fa-user"></i></span><input type="text" class="form-control" required placeholder="Full Name"></div></div><div class="col-md-6 mb-3"><label class="form-label">Age</label><div class="input-group"><span class="input-group-text"><i class="fas fa-birthday-cake"></i></span><input type="number" class="form-control" required placeholder="Age"></div></div><div class="col-md-6 mb-3"><label class="form-label">Gender</label><div class="input-group"><span class="input-group-text"><i class="fas fa-venus-mars"></i></span>${genderSelectHtml}</div></div></div></div>`;
                    container.insertAdjacentHTML('beforeend', formHtml);
                    passengerCount++;
                }
            }

            function updateFinalSummary() {
                const bpInput = document.querySelector('input[name="boarding_point"]:checked');
                const dpInput = document.querySelector('input[name="dropping_point"]:checked');

                if (!bpInput || !dpInput) return;

                const bpVal = bpInput.value;
                const dpVal = dpInput.value;
                const bpTime = bpInput.closest('.point-option').querySelector('.point-time').textContent;
                const dpTime = dpInput.closest('.point-option').querySelector('.point-time').textContent;

                document.getElementById('summary-route').textContent = `${bpVal} → ${dpVal}`;
                document.getElementById('summary-datetime').textContent = `<?php echo date('d M, Y', strtotime($journey_date)); ?>`;
                document.getElementById('summary-boarding-point').textContent = `Boarding: ${bpTime}, ${bpVal}`;
                document.getElementById('summary-dropping-point').textContent = `Dropping: ${dpTime.split(' ')[0]}, ${dpVal}`;
                document.getElementById('summary-seat-numbers').textContent = Array.from(selectedSeats.keys()).join(', ');
            }

            function initializePage() {
                const savedBoarding = sessionStorage.getItem('boardingPoint');
                const savedDropping = sessionStorage.getItem('droppingPoint');

                if (isBusAvailable && savedBoarding) {
                    const radio = document.querySelector(`input[name="boarding_point"][value="${savedBoarding}"]`);
                    if (radio) radio.checked = true;
                }

                updateDroppingPointsLogic();

                if (isBusAvailable && savedDropping) {
                    const radio = document.querySelector(`input[name="dropping_point"][value="${savedDropping}"]`);
                    if (radio && !radio.disabled) radio.checked = true;
                }

                updatePointSelectionStyles();
                updateHighlightedRoute();

                if (validateBoardingDroppingPoints()) {
                    calculateAndApplyPrices();
                }

                goToStep(1);
            }

            initializePage();
        });
    </script>
</body>

</html>