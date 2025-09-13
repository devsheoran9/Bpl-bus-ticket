<?php
include 'includes/header.php';

$initial_schedule_id = $_GET['schedule_id'] ?? null;
$from_location = $_GET['from'] ?? null;
$to_location = $_GET['to'] ?? null;
$journey_date = $_GET['date'] ?? date('Y-m-d');

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
$is_bus_available = false;
$availability_message = '';
$actual_schedule_id = null;
$route_info = null;

if (!$initial_schedule_id) {
    $error_message = "A valid schedule ID is required to proceed.";
} else {
    try {
        $stmt_get_route = $pdo->prepare("SELECT route_id FROM route_schedules WHERE schedule_id = ?");
        $stmt_get_route->execute([$initial_schedule_id]);
        $route_id_info = $stmt_get_route->fetch();

        if (!$route_id_info) {
            throw new Exception("The provided schedule link is invalid or the bus schedule does not exist.");
        }
        $route_id = $route_id_info['route_id'];

        $journey_day_name = date('D', strtotime($journey_date));
        $stmt_check_day = $pdo->prepare("SELECT schedule_id, departure_time FROM route_schedules WHERE route_id = ? AND operating_day = ?");
        $stmt_check_day->execute([$route_id, $journey_day_name]);
        $todays_schedule = $stmt_check_day->fetch();

        if (!$todays_schedule) {
            $is_bus_available = false;
            $availability_message = "This bus is not available on " . date('l, d M Y', strtotime($journey_date)) . ".";
        } else {
            $actual_schedule_id = $todays_schedule['schedule_id'];
            $departure_time_str = $todays_schedule['departure_time'];
            if (date('Y-m-d', strtotime($journey_date)) == date('Y-m-d') && time() > strtotime(date('Y-m-d') . ' ' . $departure_time_str)) {
                $is_bus_available = false;
                $availability_message = "This bus has already departed for today.";
            } else {
                $is_bus_available = true;
            }
        }

        $stmt_main_query = $pdo->prepare("SELECT r.bus_id, r.starting_point, r.ending_point, b.bus_name FROM routes r JOIN buses b ON r.bus_id = b.bus_id WHERE r.route_id = ?");
        $stmt_main_query->execute([$route_id]);
        $route_info = $stmt_main_query->fetch();

        if (!$route_info) {
            throw new Exception("Could not find route details.");
        }

        $bus_details = ['name' => $route_info['bus_name'], 'departure_time' => $todays_schedule['departure_time'] ?? '00:00:00'];
        $bus_id = $route_info['bus_id'];

        $stmt_images = $pdo->prepare("SELECT image_path FROM bus_images WHERE bus_id = ?");
        $stmt_images->execute([$bus_id]);
        $bus_images = $stmt_images->fetchAll(PDO::FETCH_COLUMN, 0);

        $stmt_stops = $pdo->prepare("SELECT * FROM route_stops WHERE route_id = ? ORDER BY stop_order ASC");
        $stmt_stops->execute([$route_id]);
        $stops = $stmt_stops->fetchAll();

        $total_route_duration = 0;
        $stop_prices_map[$route_info['starting_point']] = ['price_seater_lower' => 0, 'price_seater_upper' => 0, 'price_sleeper_lower' => 0, 'price_sleeper_upper' => 0];
        foreach ($stops as $stop) {
            $stop_prices_map[$stop['stop_name']] = ['price_seater_lower' => (float)$stop['price_seater_lower'], 'price_seater_upper' => (float)$stop['price_seater_upper'], 'price_sleeper_lower' => (float)$stop['price_sleeper_lower'], 'price_sleeper_upper' => (float)$stop['price_sleeper_upper']];
            $total_route_duration = max($total_route_duration, $stop['duration_from_start_minutes']);
        }

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

        $stmt_layout = $pdo->prepare("SELECT * FROM seats WHERE bus_id = ? ORDER BY deck, y_coordinate, x_coordinate");
        $stmt_layout->execute([$bus_id]);
        $all_seats_layout = $stmt_layout->fetchAll();

        if (empty($all_seats_layout)) {
            throw new Exception("No seat layout has been configured for this bus.");
        }

        $booked_seats_info = [];
        if ($is_bus_available) {
            $stmt_booked = $pdo->prepare("SELECT p.seat_code, p.passenger_gender FROM passengers AS p JOIN bookings AS b ON p.booking_id = b.booking_id WHERE b.route_id = ? AND b.bus_id = ? AND b.travel_date = ? AND b.booking_status = 'CONFIRMED' AND p.passenger_status = 'CONFIRMED'");
            $stmt_booked->execute([$route_id, $bus_id, $journey_date]);
            $booked_results = $stmt_booked->fetchAll();
            foreach ($booked_results as $row) {
                $booked_seats_info[$row['seat_code']] = $row['passenger_gender'];
            }
        }

        foreach ($all_seats_layout as &$seat) {
            $is_booked = isset($booked_seats_info[$seat['seat_code']]);
            if (!$is_bus_available || $is_booked || $seat['is_bookable'] == 0) {
                $seat['final_status'] = 'SOLD';
            } else {
                $seat['final_status'] = $seat['status'];
            }
            $seat['booked_by_gender'] = $is_booked ? strtoupper($booked_seats_info[$seat['seat_code']]) : null;

            if (strtoupper($seat['deck']) === 'LOWER') {
                $lower_deck_seats[] = $seat;
                $lower_deck_height = max($lower_deck_height, $seat['y_coordinate'] + $seat['height']);
            } else {
                $upper_deck_seats[] = $seat;
                $upper_deck_height = max($upper_deck_height, $seat['y_coordinate'] + $seat['height']);
            }
        }
        unset($seat);
        $lower_deck_height += 40;
        $upper_deck_height += 40;
    } catch (Exception $e) {
        $error_message = "An error occurred: " . $e->getMessage();
    }
}


function get_seat_classes($seat)
{
    $classes = ['seat', strtolower($seat['seat_type'])];
    if ($seat['final_status'] === 'AVAILABLE') {
        $classes[] = 'available';
        if (strtoupper($seat['gender_preference']) === 'MALE') $classes[] = 'male-only';
        if (strtoupper($seat['gender_preference']) === 'FEMALE') $classes[] = 'female-only';
    } else {
        $classes[] = 'unavailable';
        if ($seat['final_status'] === 'SOLD') {
            $classes[] = 'sold';
            if ($seat['booked_by_gender'] === 'MALE') $classes[] = 'sold-male';
            if ($seat['booked_by_gender'] === 'FEMALE') $classes[] = 'sold-female';
        } elseif ($seat['final_status'] === 'BLOCKED') {
            $classes[] = 'status-blocked';
        } elseif ($seat['final_status'] === 'DAMAGED') {
            $classes[] = 'status-damaged';
        } else {
            $classes[] = 'sold';
        }
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
?>


<style>
    :root {
        --grid-size: 10px;
        --seat-color-available: #34C759;
        --seat-color-sold: #F0F0F0;
        --seat-color-selected: #007bff;
        --seat-border-color-male: #1a73e8;
        --seat-border-color-female: #e91e63;
        --seat-color-blocked: #FF9F0A;
        /* Orange */
        --seat-color-damaged: #FF453A;
        /* Red */
    }

    .panel-card {
        background: #fff;
        border-radius: 16px;

        box-shadow: 0 4px 25px rgba(44, 62, 80, 0.08);
        margin-bottom: 24px;
        border: 1px solid #eaeaea;
    }

    .deck-layout-wrapper {
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .deck-container {
        border: 1px solid #ccc;
        border-radius: 12px;
        padding: 10px;
        width: 300px;
    }

    .deck {
        position: relative;
        background-color: #fcfcfc;
        background-size: var(--grid-size) var(--grid-size);
    }

    .seat {
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7em;
        font-weight: 600;
        color: #333;
        border: 1px solid #ccc;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: all 0.2s ease-out;
        box-sizing: border-box;
    }

    .seat.available {
        cursor: pointer;
    }

    .seat.unavailable {
        cursor: not-allowed;
    }

    .seat-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        width: 100%;
    }

    .seat-code {
        font-size: 0.9em;
        font-weight: bold;
        line-height: 1;
    }

    .seat-icon {
        font-size: 1.1em;
        line-height: 1.2;
    }

    .price {
        font-size: 0.8em;
        font-weight: 600;
        line-height: 1;
    }

    .seat.seater,
    .seat.sleeper {
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
    }

    .seat.driver {
        border-radius: 50%;
        background-color: #6c757d;
        border-color: #5a6268;
        color: #fff;
        cursor: default;
    }

    .seat.aisle {
        background-color: transparent;
        border: 1px dashed #bbb;
        box-shadow: none;
        cursor: default;
    }

    /* Seat States */
    .seat.available {
        border-color: var(--seat-color-available);
        background-color: #fff;
    }

    .seat.available:hover {
        transform: scale(1.05);
        box-shadow: 0 0 8px rgba(52, 199, 89, 0.5);
    }

    .seat.male-only.available {
        border-color: var(--seat-border-color-male);
    }

    .seat.female-only.available {
        border-color: var(--seat-border-color-female);
    }

    .seat-icon.gender-male {
        color: var(--seat-border-color-male);
    }

    .seat-icon.gender-female {
        color: var(--seat-border-color-female);
    }

    /* Unavailable Seat Styles */
    .seat.sold {
        background-color: var(--seat-color-sold);
        border-color: #ddd;
        color: #888;
    }

    .seat.sold .price,
    .seat.sold .seat-icon {
        opacity: 0.5;
    }

    .seat.status-blocked {
        background-color: #fff;
        border: 2px solid var(--seat-color-blocked);
    }

    .seat.status-damaged {
        background-color: #fff;
        border: 2px solid var(--seat-color-damaged);
    }

    /* Add a visual cross for damaged seats */
    .seat.status-damaged::after {
        content: '\00D7';
        position: absolute;
        font-size: 2.5em;
        color: var(--seat-color-damaged);
        opacity: 0.7;
        pointer-events: none;
    }

    .seat.selected-any {
        background-color: var(--seat-color-selected) !important;
        border-color: var(--seat-color-selected) !important;
        color: white !important;
        transform: scale(1.05);
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.6);
    }

    .seat.selected-any .price,
    .seat.selected-any .seat-icon {
        color: white !important;
        opacity: 1;
    }

    .legend-seat.selected-any {
        background-color: var(--seat-color-selected);
    }

    .carousel-inner img {
        max-height: 250px;
        object-fit: cover;
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
                <p><?php echo htmlspecialchars($error_message); ?></p><a href="index" class="btn btn-secondary">Go Back</a>
            </div>
        <?php else : ?>
            <div id="step-1" class="step-container active">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="panel-card">
                            <?php if (!$is_bus_available) : ?><div class="alert alert-warning text-center" role="alert"><strong><?php echo htmlspecialchars($availability_message); ?></strong></div><?php endif; ?>
                            <h5>Boarding points <span class="text-muted" style="font-size:14px">(Select Boarding Point)</span></h5>

                            <?php foreach ($all_points as $index => $point) : if ($index === count($all_points) - 1) continue; ?>
                                <div class="point-option <?php if (!$is_bus_available) echo 'disabled'; ?>" data-order="<?php echo $point['order']; ?>">
                                    <input type="radio" name="boarding_point" id="bp<?php echo $index; ?>" value="<?php echo htmlspecialchars($point['name']); ?>" <?php if ($point['name'] === $from_location) echo 'checked'; ?> <?php if (!$is_bus_available) echo 'disabled'; ?>>
                                    <div>
                                        <label for="bp<?php echo $index; ?>" class="point-time"><?php echo htmlspecialchars($point['time']); ?></label>
                                        <label for="bp<?php echo $index; ?>" class="point-name"> (<?php echo htmlspecialchars($point['name']); ?>)</label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel-card">
                            <?php if (!$is_bus_available) : ?><div class="alert alert-warning text-center" role="alert"><strong><?php echo htmlspecialchars($availability_message); ?></strong></div><?php endif; ?>
                            <h5>Dropping points <span class="text-muted" style="font-size:14px">(Select Dropping Point)</span></h5>
                            <!-- <p class="text-muted">Select Dropping Point </p> -->
                            <?php foreach ($all_points as $index => $point) : if ($index === 0) continue; ?>
                                <div class="point-option <?php if (!$is_bus_available) echo 'disabled'; ?>" data-order="<?php echo $point['order']; ?>">
                                    <input type="radio" name="dropping_point" id="dp<?php echo $index; ?>" value="<?php echo htmlspecialchars($point['name']); ?>" <?php if ($point['name'] === $to_location) echo 'checked'; ?> <?php if (!$is_bus_available) echo 'disabled'; ?>>
                                    <div>
                                        <label for="dp<?php echo $index; ?>" class="point-time"><?php echo htmlspecialchars($point['time']); ?> <span class="small text-muted"><?php echo date('d M', strtotime($journey_date)); ?></span></label>
                                        <label for="dp<?php echo $index; ?>" class="point-name"> (<?php echo htmlspecialchars($point['name']); ?>)</label>
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
                                <!-- LOWER DECK -->
                                <div class="deck-container">
                                    <h5 class="text-center mb-3">Lower Deck</h5>
                                    <div class="deck" id="lower_deck" style="min-height: <?php echo $lower_deck_height; ?>px;">
                                        <?php foreach ($lower_deck_seats as $seat) : ?>
                                            <div class="<?php echo get_seat_classes($seat); ?>"
                                                style="left: <?php echo $seat['x_coordinate']; ?>px; top: <?php echo $seat['y_coordinate']; ?>px; width: <?php echo $seat['width']; ?>px; height: <?php echo $seat['height']; ?>px; <?php echo get_transform_style($seat['orientation']); ?>"
                                                data-seat-id="<?php echo htmlspecialchars($seat['seat_code']); ?>"
                                                data-price="<?php echo htmlspecialchars($seat['base_price']); ?>"
                                                data-seat-type="<?php echo strtoupper($seat['seat_type']); ?>"
                                                data-deck="LOWER"
                                                data-gender-lock="<?php echo strtolower($seat['gender_preference']); ?>">

                                                <div class="seat-content">
                                                    <?php if ($seat['seat_type'] != 'AISLE' && $seat['seat_type'] != 'DRIVER'): ?>
                                                        <span class="seat-code"><?php echo htmlspecialchars($seat['seat_code']); ?></span>

                                                        <?php if (strtoupper($seat['gender_preference']) === 'MALE'): ?>
                                                            <i class="seat-icon fas fa-male gender-male"></i>
                                                        <?php elseif (strtoupper($seat['gender_preference']) === 'FEMALE'): ?>
                                                            <i class="seat-icon fas fa-female gender-female"></i>
                                                        <?php else: ?>
                                                            <i class="seat-icon fas fa-user gender-any"></i>
                                                        <?php endif; ?>

                                                        <span class="price">₹<?php echo (int)$seat['base_price']; ?></span>
                                                    <?php elseif ($seat['seat_type'] == 'DRIVER'): ?>
                                                        <i class="seat-icon fas fa-user-tie"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- UPPER DECK (if exists) -->
                                <?php if (!empty($upper_deck_seats)) : ?>
                                    <div class="deck-container">
                                        <h5 class="text-center mb-3">Upper Deck</h5>
                                        <div class="deck" id="upper_deck" style="min-height: <?php echo $upper_deck_height; ?>px;">
                                            <?php foreach ($upper_deck_seats as $seat) : ?>
                                                <div class="<?php echo get_seat_classes($seat); ?>"
                                                    style="left: <?php echo $seat['x_coordinate']; ?>px; top: <?php echo $seat['y_coordinate']; ?>px; width: <?php echo $seat['width']; ?>px; height: <?php echo $seat['height']; ?>px; <?php echo get_transform_style($seat['orientation']); ?>"
                                                    data-seat-id="<?php echo htmlspecialchars($seat['seat_code']); ?>"
                                                    data-price="<?php echo htmlspecialchars($seat['base_price']); ?>"
                                                    data-seat-type="<?php echo strtoupper($seat['seat_type']); ?>"
                                                    data-deck="UPPER"
                                                    data-gender-lock="<?php echo strtolower($seat['gender_preference']); ?>">

                                                    <div class="seat-content">
                                                        <?php if ($seat['seat_type'] != 'AISLE' && $seat['seat_type'] != 'DRIVER'): ?>
                                                            <span class="seat-code"><?php echo htmlspecialchars($seat['seat_code']); ?></span>

                                                            <?php if (strtoupper($seat['gender_preference']) === 'MALE'): ?>
                                                                <i class="seat-icon fas fa-male gender-male"></i>
                                                            <?php elseif (strtoupper($seat['gender_preference']) === 'FEMALE'): ?>
                                                                <i class="seat-icon fas fa-female gender-female"></i>
                                                            <?php else: ?>
                                                                <i class="seat-icon fas fa-user gender-any"></i>
                                                            <?php endif; ?>

                                                            <span class="price">₹<?php echo (int)$seat['base_price']; ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Seat Legend (no changes needed here) -->
                        <div class="panel-card">
                            <h5>Know your seat types</h5>
                            <div class="seat-legend">
                                <!-- Header Row -->
                                <div style="display: flex; align-items: center; text-align: center; font-weight: bold; color: #6c757d; font-size: 0.8em; padding: 8px 0; border-bottom: 2px solid #dee2e6;">
                                    <div style="flex: 1; text-align: left;">SEAT TYPES</div>
                                    <div style="width: 90px;">SEATER</div>
                                    <div style="width: 90px;">SLEEPER</div>
                                </div>

                                <!-- Available -->
                                <div style="display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                                    <div style="flex: 1; font-size: 0.9em; color: #555;">Available</div>
                                    <div style="width: 90px; display: flex; justify-content: center;">
                                        <div style="width: 35px; height: 30px; border:  1px solid #34C759; border-radius: 6px; display:flex; align-items:center; justify-content:center; color: #010f05ff; font-size: 12px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                    <div style="width: 90px; display: flex; justify-content: center;">
                                        <div style="width: 35px; height: 50px; border:  1px solid #34C759; border-radius: 6px; display:flex; align-items:center; justify-content:center; color: #010f05ff; font-size: 12px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Available for Male -->
                                <div style="display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                                    <div style="flex: 1; font-size: 0.9em; color: #555;">Available only for male passenger</div>
                                    <div style="width: 90px; display: flex; justify-content: center;">
                                        <div style="width: 35px; height: 30px; border: 1px solid #007bff; border-radius: 6px; display:flex; align-items:center; justify-content:center; color: #007bff; font-size: 1.2em;">
                                            <i class="fas fa-male"></i>
                                        </div>
                                    </div>
                                    <div style="width: 90px; display: flex; justify-content: center;">
                                        <div style="width: 35px; height: 50px; border: 1px solid #007bff; border-radius: 8px; display:flex; align-items:center; justify-content:center; color: #007bff; font-size: 1.5em;">
                                            <i class="fas fa-male"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Available for Female -->
                                <div style="display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                                    <div style="flex: 1; font-size: 0.9em; color: #555;">Available only for female passenger</div>
                                    <div style="width: 90px; display: flex; justify-content: center;">
                                        <div style="width: 35px; height: 30px; border: 1px solid #e91e63; border-radius: 6px; display:flex; align-items:center; justify-content:center; color: #e91e63; font-size: 1.2em;">
                                            <i class="fas fa-female"></i>
                                        </div>
                                    </div>
                                    <div style="width: 90px; display: flex; justify-content: center;">
                                        <div style="width: 35px; height: 50px; border: 1px solid #e91e63; border-radius: 8px; display:flex; align-items:center; justify-content:center; color: #e91e63; font-size: 1.5em;">
                                            <i class="fas fa-female"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Booked -->
                                <div style="display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                                    <div style="flex: 1; font-size: 0.9em; color: #555;">Already booked</div>
                                    <div style="width: 90px; display: flex; justify-content: center;">
                                        <div style="width: 35px; height: 30px;   background-color: #f0f0f0;   border: 1px solid #e0e0e0;   border-radius: 6px;   font-size: 12px;   display: flex;   justify-content: center;   align-items: center;">
                                            Sold
                                        </div>

                                    </div>
                                    <div style="width: 90px; display: flex; justify-content: center;">
                                        <div style="width: 35px; height: 50px; background-color: #f0f0f0; border: 1px solid #e0e0e0; border-radius: 8px; font-size:12px; display: flex; justify-content: center;  align-items:center;">Sold</div>
                                    </div>
                                </div>

                                <!-- Booked by Male -->
                                <div style="display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                                    <div style="flex: 1; font-size: 0.9em; color: #555;">Booked by male passenger</div>
                                    <div style="width: 90px; display: flex; justify-content: center;">
                                        <div style="width: 35px; height: 30px; background-color: #f0f0f0; border: 1px solid #e0e0e0; border-radius: 6px; display:flex; align-items:center; justify-content:center; color: #007bff; font-size: 1.2em; opacity: 0.5;">
                                            <i class="fas fa-male"></i>
                                        </div>
                                    </div>
                                    <div style="width: 90px; display: flex; justify-content: center;">
                                        <div style="width: 35px; height: 50px; background-color: #f0f0f0; border: 1px solid #e0e0e0; border-radius: 8px; display:flex; align-items:center; justify-content:center; color: #007bff; font-size: 1.5em; opacity: 0.5;">
                                            <i class="fas fa-male"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Booked by Female -->
                                <div style="display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                                    <div style="flex: 1; font-size: 0.9em; color: #555;">Booked by female passenger</div>
                                    <div style="width: 90px; display: flex; justify-content: center;">
                                        <div style="width: 35px; height: 30px; background-color: #f0f0f0; border: 1px solid #e0e0e0; border-radius: 6px; display:flex; align-items:center; justify-content:center; color: #e91e63; font-size: 1.2em; opacity: 0.5;">
                                            <i class="fas fa-female"></i>
                                        </div>
                                    </div>
                                    <div style="width: 90px; display: flex; justify-content: center;">
                                        <div style="width: 35px; height: 50px; background-color: #f0f0f0; border: 1px solid #e0e0e0; border-radius: 8px; display:flex; align-items:center; justify-content:center; color: #e91e63; font-size: 1.5em; opacity: 0.5;">
                                            <i class="fas fa-female"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Selected by you -->
                                <div style="display: flex; align-items: center; padding: 10px 0;">
                                    <div style="flex: 1; font-size: 0.9em; color: #555;">Selected by you</div>
                                    <div style="width: 90px; display: flex; justify-content: center;">
                                        <div style="width: 35px; height: 30px; background-color: #147dfc; border: 1px solid #147dfc; border-radius: 6px; display:flex; align-items:center; justify-content:center; color: #fff; font-size: 12px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                    <div style="width: 90px; display: flex; justify-content: center;">
                                        <div style="width: 35px; height: 50px; background-color: #147dfc; border: 1px solid #147dfc; border-radius: 6px; display:flex; align-items:center; justify-content:center; color: #fff; font-size: 12px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right sidebar (no changes needed) -->
                    <div class="col-lg-4">
                        <div class="panel-card">
                            <ul class="nav nav-tabs info-tabs" id="infoTab" role="tablist">
                                <li class="nav-item" role="presentation"><button class="nav-link active" id="why-book-tab" data-bs-toggle="tab" data-bs-target="#why-book-pane" type="button" role="tab">Bus Details</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" id="route-tab" data-bs-toggle="tab" data-bs-target="#route-pane" type="button" role="tab">Bus Route</button></li>
                            </ul>
                            <div class="tab-content pt-4">
                                <div class="tab-pane fade show active" id="why-book-pane" role="tabpanel">
                                    <?php if (!empty($bus_images)) : ?>
                                        <div id="busImageSlider" class="carousel slide mb-4" data-bs-ride="carousel">
                                            <div class="carousel-inner rounded">
                                                <?php foreach ($bus_images as $index => $image) : $image_path = "admin/function/backend/uploads/bus_images/" . htmlspecialchars($image); ?>
                                                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                                        <a href="<?php echo $image_path; ?>" data-fancybox="gallery" data-caption="Bus Image <?php echo $index + 1; ?>"><img src="<?php echo $image_path; ?>" class="d-block w-100" alt="Bus Image"></a>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#busImageSlider" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#busImageSlider" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
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
                                        <h5>Amenities</h5>
                                        <p class="small text-muted">This bus is equipped with Water Bottles, Blankets, and Charging Points.</p>
                                    </div>
                                    <div class="info-section">
                                        <h5>Rest Stop Information</h5>
                                        <p class="small text-muted">This service includes designated rest stops. The crew will announce the duration of each stop.</p>
                                    </div>
                                    <hr>
                                    <div class="info-section" style="margin-top: 25px; ">
                                        <h5 style="font-size: 1.4em; color: #333; font-weight: 600; margin-bottom: 20px;">6 amenities</h5>

                                        <!-- Amenity Item: Blankets -->
                                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                            <div style="width: 35px; text-align: center; color: #555; font-size: 1.2em;">
                                                <i class="fas fa-bed"></i> <!-- Using 'bed' as a proxy for blankets/bedding -->
                                            </div>
                                            <span style="font-size: 1em; color: #333;">Blankets</span>
                                        </div>

                                        <!-- Amenity Item: Charging Point -->
                                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                            <div style="width: 35px; text-align: center; color: #555; font-size: 1.2em;">
                                                <i class="fas fa-charging-station"></i>
                                            </div>
                                            <span style="font-size: 1em; color: #333;">Charging Point</span>
                                        </div>

                                        <!-- Amenity Item: Reading Light -->
                                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                            <div style="width: 35px; text-align: center; color: #555; font-size: 1.2em;">
                                                <i class="far fa-lightbulb"></i> <!-- Using the 'regular' version for an outline style -->
                                            </div>
                                            <span style="font-size: 1em; color: #333;">Reading Light</span>
                                        </div>

                                        <!-- Amenity Item: CCTV -->
                                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                            <div style="width: 35px; text-align: center; color: #555; font-size: 1.2em;">
                                                <i class="fas fa-video"></i>
                                            </div>
                                            <span style="font-size: 1em; color: #333;">CCTV</span>
                                        </div>

                                        <!-- Amenity Item: Bed Sheet -->
                                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                            <div style="width: 35px; text-align: center; color: #555; font-size: 1.2em;">
                                                <i class="fas fa-bed"></i> <!-- Using 'bed' as a proxy for blankets/bedding -->
                                            </div>
                                            <span style="font-size: 1em; color: #333;">Bed Sheet</span>
                                        </div>


                                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                            <div style="width: 35px; text-align: center; color: #555; font-size: 1.2em;">
                                                <i class="fas fa-shield-alt"></i>
                                            </div>
                                            <span style="font-size: 1em; color: #333;">Safety</span>
                                        </div>

                                    </div>
                                    <hr>
                                    <div class="info-section" style="margin-top: 25px;">
                                        <h5 style="font-size: 1.4em; color: #333; font-weight: 600; margin-bottom: 20px;">Other Policies</h5>

                                        <!-- Child Policy -->
                                        <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
                                            <div style="width: 40px; text-align: center; color: #555; font-size: 1.5em; padding-top: 3px;">
                                                <i class="fas fa-child"></i>
                                            </div>
                                            <div style="flex: 1; padding-left: 10px;">
                                                <strong style="display: block; font-size: 1em; color: #333; font-weight: 600; margin-bottom: 4px;">Child passenger policy</strong>
                                                <p style="font-size: 0.9em; color: #666; margin: 0; line-height: 1.6;">Children above the age of 3 will need a ticket</p>
                                            </div>
                                        </div>

                                        <!-- Luggage Policy -->
                                        <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
                                            <div style="width: 40px; text-align: center; color: #555; font-size: 1.5em; padding-top: 3px;">
                                                <i class="fas fa-suitcase-rolling"></i>
                                            </div>
                                            <div style="flex: 1; padding-left: 10px;">
                                                <strong style="display: block; font-size: 1em; color: #333; font-weight: 600; margin-bottom: 4px;">Luggage policy</strong>
                                                <p style="font-size: 0.9em; color: #666; margin: 0; line-height: 1.6;">2 pieces of luggage will be accepted free of charge per passenger. Excess items will be chargeable<br>Excess baggage over 20 kgs per passenger will be chargeable</p>
                                            </div>
                                        </div>

                                        <!-- Pets Policy -->
                                        <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
                                            <div style="width: 40px; text-align: center; color: #555; font-size: 1.5em; padding-top: 3px;">
                                                <i class="fas fa-paw"></i>
                                            </div>
                                            <div style="flex: 1; padding-left: 10px;">
                                                <strong style="display: block; font-size: 1em; color: #333; font-weight: 600; margin-bottom: 4px;">Pets Policy</strong>
                                                <p style="font-size: 0.9em; color: #666; margin: 0; line-height: 1.6;">Pets are not allowed</p>
                                            </div>
                                        </div>

                                        <!-- Liquor Policy -->
                                        <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
                                            <div style="width: 40px; text-align: center; color: #555; font-size: 1.5em; padding-top: 3px;">
                                                <i class="fas fa-wine-bottle"></i>
                                            </div>
                                            <div style="flex: 1; padding-left: 10px;">
                                                <strong style="display: block; font-size: 1em; color: #333; font-weight: 600; margin-bottom: 4px;">Liquor Policy</strong>
                                                <p style="font-size: 0.9em; color: #666; margin: 0; line-height: 1.6;">Carrying or consuming liquor inside the bus is prohibited. Bus operator reserves the right to deboard drunk passengers.</p>
                                            </div>
                                        </div>

                                        <!-- Pick up time policy -->
                                        <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
                                            <div style="width: 40px; text-align: center; color: #555; font-size: 1.5em; padding-top: 3px;">
                                                <i class="fas fa-bus-alt"></i>
                                            </div>
                                            <div style="flex: 1; padding-left: 10px;">
                                                <strong style="display: block; font-size: 1em; color: #333; font-weight: 600; margin-bottom: 4px;">Pick up time policy</strong>
                                                <p style="font-size: 0.9em; color: #666; margin: 0; line-height: 1.6; ">Bus operator is not obligated to wait beyond the scheduled departure time of the bus. No refund request will be entertained for late arriving passengers.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="route-pane" role="tabpanel">
                                    <div class="info-section">
                                        <h5>Bus Route</h5>
                                        <p class="bus-route-path small text-muted" id="highlighted-route">Please select boarding and dropping points to see the route.</p>
                                    </div>
                                    <div class="info-section boarding-point-list">
                                        <h5>All Boarding Points</h5><?php foreach ($all_points as $point) : if ($point['name'] == $route_info['ending_point']) continue; ?><div class="point">
                                                <div><strong><?php echo $point['time']; ?></strong> - <?php echo date('d M', strtotime($journey_date)); ?></div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($point['name']); ?></div>
                                            </div><?php endforeach; ?>
                                    </div>
                                    <div class="info-section boarding-point-list">
                                        <h5>All Dropping Points</h5><?php foreach ($all_points as $point) : if ($point['name'] == $route_info['starting_point']) continue; ?><div class="point">
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
                <form id="booking-form">
                    <input type="hidden" name="route_id" value="<?php echo htmlspecialchars($route_id); ?>">
                    <input type="hidden" name="bus_id" value="<?php echo htmlspecialchars($bus_id); ?>">
                    <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($actual_schedule_id); ?>">
                    <input type="hidden" name="travel_date" value="<?php echo htmlspecialchars($journey_date); ?>">

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="panel-card mb-4">
                                <h5>Contact Details:
                                    <span class="small text-danger" style="font-size:12px;"> (Your ticket will be sent to this email so write correct email.)</span>
                                </h5>
                                <?php if (isset($_SESSION['user_id'])) :
                                    $user_id = $_SESSION['user_id'];
                                    $stmt_user = $pdo->prepare("SELECT username, mobile_no, email FROM users WHERE id = ?");
                                    $stmt_user->execute([$user_id]);
                                    $user = $stmt_user->fetch();
                                    if ($user) :
                                        $username  = htmlspecialchars($user['username'] ?? '');
                                        $mobile_no = htmlspecialchars($user['mobile_no'] ?? '');
                                        $email     = htmlspecialchars($user['email'] ?? '');
                                ?>
                                        <div class="row g-1">
                                            <div class="col-md-4">
                                                <label class="form-label">Name<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="contact_name" value="<?php echo $username; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Phone<span class="text-danger">*</span></label>
                                                <input type="tel" class="form-control" name="contact_mobile" value="<?php echo $mobile_no; ?>" readonly>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label">Email<span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" name="contact_email" value="<?php echo $email; ?>" readonly>
                                            </div>
                                        </div>
                                    <?php endif;
                                else : ?>
                                    <div class="row g-1">
                                        <div class="col-md-4">
                                            <label class="form-label">Name<span class="text-danger">*</span></label>
                                            <input type="text" name="contact_name" class="form-control" placeholder="Full Name" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Phone<span class="text-danger">*</span></label>
                                            <input type="tel" name="contact_mobile" class="form-control" placeholder="Mobile Number" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Email<span class="text-danger">*</span></label>
                                            <input type="email" name="contact_email" class="form-control" placeholder="example@email.com" required>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                            <div id="passenger-details-forms"></div>
                        </div>

                        <div class="col-lg-6">
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
                                        <div><strong>Selected Seats: </strong></div>
                                        <div id="summary-seat-numbers" class="border p-1 ms-2 " style="border-radius:5px; background: #ffefda;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

        <?php endif; ?>
    </main>
    <br><br><br><br>

    <div id="bottom-bar" class="bottom-action-bar">
        <div><span id="seat-count-text"></span><br><strong class="fs-5" id="price-container" style="display: none;">₹<span id="total-price">0</span></strong></div><button id="action-btn" class="btn btn-danger btn-lg">Continue</button>
    </div>

    <!-- JavaScript is unchanged -->
    <script>
        const stopPrices = <?php echo json_encode($stop_prices_map); ?>;
        const allPoints = <?php echo json_encode($all_points); ?>;
        const isBusAvailable = <?php echo json_encode($is_bus_available); ?>;
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <!-- Add the Razorpay Checkout Script -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <!-- --- MODIFIED: JavaScript updated to fix booking error --- -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectedSeats = new Map();
            let currentStep = 1;
            const actionBtn = document.getElementById('action-btn');
            const stepElements = document.querySelectorAll('.step');
            const totalPriceEl = document.getElementById('total-price');
            const priceContainerEl = document.getElementById('price-container');
            const seatCountTextEl = document.getElementById('seat-count-text');

            Fancybox.bind("[data-fancybox]", {});

            (function initializePage() {
                updateDroppingPointsLogic();
                updatePointSelectionStyles();
                updateHighlightedRoute();
                if (document.querySelector('input[name="boarding_point"]:checked') && document.querySelector('input[name="dropping_point"]:checked')) {
                    calculateAndApplyPrices();
                }
                goToStep(1);
            })();

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
                    if (priceSpan) priceSpan.textContent = `₹${Math.round(finalPrice)}`;
                });
                selectedSeats.clear();
                document.querySelectorAll('.seat').forEach(s => s.classList.remove('selected-any'));
                updateSummaryBar();
            }

            function updatePointSelectionStyles() {
                document.querySelectorAll('.point-option').forEach(el => el.classList.remove('selected'));
                document.querySelectorAll('input[type="radio"]:checked').forEach(radio => radio.closest('.point-option').classList.add('selected'));
            }

            function updateDroppingPointsLogic() {
                const selectedBoardingRadio = document.querySelector('input[name="boarding_point"]:checked');
                if (!selectedBoardingRadio) {
                    document.querySelectorAll('input[name="dropping_point"]').forEach(radio => {
                        if (!radio.closest('.point-option').classList.contains('disabled')) radio.disabled = true;
                        radio.checked = false;
                    });
                    return;
                }
                const selectedBoardingOrder = parseInt(selectedBoardingRadio.closest('.point-option').dataset.order);
                document.querySelectorAll('input[name="dropping_point"]').forEach(radio => {
                    if (radio.closest('.point-option').classList.contains('disabled')) return;
                    const droppingOrder = parseInt(radio.closest('.point-option').dataset.order);
                    const isEnabled = droppingOrder > selectedBoardingOrder;
                    radio.disabled = !isEnabled;
                    if (!isEnabled && radio.checked) radio.checked = false;
                });
            }

            function updateHighlightedRoute() {
                const fromEl = document.querySelector('input[name="boarding_point"]:checked');
                const toEl = document.querySelector('input[name="dropping_point"]:checked');
                const routeContainer = document.getElementById('highlighted-route');
                if (!fromEl || !toEl) {
                    routeContainer.innerHTML = 'Please select boarding and dropping points.';
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
                const path = allPoints.map(p => (p.order >= fromOrder && p.order <= toOrder) ? `<strong>${p.name}</strong>` : p.name).join(' &rarr; ');
                routeContainer.innerHTML = path;
            }

            document.querySelectorAll('input[name="boarding_point"], input[name="dropping_point"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    updateDroppingPointsLogic();
                    updatePointSelectionStyles();
                    if (document.querySelector('input[name="boarding_point"]:checked') && document.querySelector('input[name="dropping_point"]:checked')) {
                        calculateAndApplyPrices();
                    }
                    updateSummaryBar();
                    updateHighlightedRoute();
                });
            });

            document.querySelectorAll('.seat.available').forEach(seat => {
                seat.addEventListener('click', () => {
                    if (currentStep !== 2 || !isBusAvailable) return;
                    const seatCode = seat.dataset.seatId;
                    const price = parseFloat(seat.dataset.price);
                    if (selectedSeats.has(seatCode)) {
                        selectedSeats.delete(seatCode);
                        seat.classList.remove('selected-any');
                    } else {
                        selectedSeats.set(seatCode, {
                            price
                        });
                        seat.classList.add('selected-any');
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
                const seatCount = selectedSeats.size;
                const totalPrice = Array.from(selectedSeats.values()).reduce((sum, s) => sum + s.price, 0);
                if (currentStep === 1) {
                    const isStep1Valid = document.querySelector('input[name="boarding_point"]:checked') && document.querySelector('input[name="dropping_point"]:checked');
                    actionBtn.disabled = !isStep1Valid;
                    priceContainerEl.style.display = 'none';
                    seatCountTextEl.textContent = isStep1Valid ? 'All points selected' : 'Select boarding & dropping points';
                } else if (currentStep === 2) {
                    actionBtn.disabled = (seatCount === 0);
                    priceContainerEl.style.display = seatCount > 0 ? 'block' : 'none';
                    totalPriceEl.textContent = Math.round(totalPrice);
                    seatCountTextEl.textContent = seatCount > 0 ? `${seatCount} Seat(s) Selected` : 'Please select your seat(s)';
                } else if (currentStep === 3) {
                    actionBtn.disabled = false;
                    priceContainerEl.style.display = 'block';
                    totalPriceEl.textContent = Math.round(totalPrice);
                    seatCountTextEl.textContent = `${seatCount} Seat(s) Total`;
                }
            }

            function handleGoToStep(targetStep) {
                if (targetStep > 1 && !(document.querySelector('input[name="boarding_point"]:checked') && document.querySelector('input[name="dropping_point"]:checked'))) {
                    alert('Please select your boarding and dropping points first.');
                    return;
                }
                if (targetStep > 2 && selectedSeats.size === 0) {
                    alert('Please select at least one seat.');
                    return;
                }
                if (targetStep === 3) {
                    updateFinalSummary();
                    populatePassengerForms();
                }
                goToStep(targetStep);
            }

            function goToStep(stepNumber) {
                currentStep = stepNumber;
                stepElements.forEach(stepEl => {
                    const step = parseInt(stepEl.dataset.step);
                    stepEl.classList.toggle('active', step === stepNumber);
                    stepEl.classList.toggle('completed', step < stepNumber);
                });
                document.querySelectorAll('.step-container').forEach(c => c.classList.remove('active'));
                document.getElementById(`step-${stepNumber}`).classList.add('active');
                actionBtn.textContent = (stepNumber === 3) ? 'Proceed to Payment' : 'Continue';
                updateSummaryBar();
            }

            actionBtn.addEventListener('click', () => {
                if (actionBtn.disabled) return;
                if (currentStep < 3) {
                    handleGoToStep(currentStep + 1);
                } else {
                    processBooking();
                }
            });

            stepElements.forEach(stepEl => {
                stepEl.addEventListener('click', () => {
                    const targetStep = parseInt(stepEl.dataset.step);
                    if (targetStep < currentStep) handleGoToStep(targetStep);
                });
            });

            function populatePassengerForms() {
                const container = document.getElementById('passenger-details-forms');
                container.innerHTML = '';
                if (selectedSeats.size === 0) {
                    container.innerHTML = '<p class="text-muted text-center">Select seats to add passenger details.</p>';
                    return;
                }
                let passengerCount = 1;
                for (const [seatCode, seatData] of selectedSeats.entries()) {
                    let genderSelectHtml;
                    const uniqueId = seatCode.replace(/[^a-zA-Z0-9]/g, '');
                    const seatElement = document.querySelector(`.seat[data-seat-id="${seatCode}"]`);
                    const genderLock = seatElement.dataset.genderLock;

                    if (genderLock === 'male') {
                        genderSelectHtml = `<select name="passenger_gender_${uniqueId}" class="form-select" required readonly style="background-color: #e9ecef;"><option value="MALE" selected>Male</option></select>`;
                    } else if (genderLock === 'female') {
                        genderSelectHtml = `<select name="passenger_gender_${uniqueId}" class="form-select" required readonly style="background-color: #e9ecef;"><option value="FEMALE" selected>Female</option></select>`;
                    } else {
                        genderSelectHtml = `<select name="passenger_gender_${uniqueId}" class="form-select" required><option value="" selected disabled>Select Gender</option><option value="MALE">Male</option><option value="FEMALE">Female</option><option value="OTHER">Other</option></select>`;
                    }
                    const formHtml = `
                <div class="panel-card mb-1 mt-2 passenger-form" id="passenger-form-${uniqueId}">
                    <h6>Passenger ${passengerCount} <span class="badge bg-secondary" style="background-color: rgb(29 203 48) !important;">${seatCode}</span></h6>
                    <div class="row p-2">
                        <div class="col-12 col-md-5 mb-1 g-1"><label class="form-label"></label><input type="text" name="passenger_name_${uniqueId}" class="form-control" required placeholder="Full Name"></div>
                        <div class="col-4 col-md-3 mb-1 g-1"><label class="form-label"></label><input type="number" name="passenger_age_${uniqueId}" class="form-control" required placeholder="Age" min="1" max="100" >
                        </div>
                        <div class="col-8 col-md-4 mb-1 g-1"><label class="form-label"></label><div class="input-group"><span class="input-group-text"><i class="fas fa-venus-mars text-danger"></i></span>${genderSelectHtml}</div></div>
                    </div>
                </div>`;
                    container.insertAdjacentHTML('beforeend', formHtml);
                    passengerCount++;
                }
            }

            function processBooking() {
                const form = document.getElementById('booking-form');
                let isValid = true;
                let firstInvalidElement = null;
                let passengersData = [];

                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                selectedSeats.forEach((seatData, seatCode) => {
                    const uniqueId = seatCode.replace(/[^a-zA-Z0-9]/g, '');
                    const nameEl = form.querySelector(`input[name="passenger_name_${uniqueId}"]`);
                    const ageEl = form.querySelector(`input[name="passenger_age_${uniqueId}"]`);
                    const genderEl = form.querySelector(`select[name="passenger_gender_${uniqueId}"]`);
                    const ageValue = parseInt(ageEl ? ageEl.value : '0', 10);
                    if (!nameEl || !nameEl.value.trim()) {
                        isValid = false;
                        if (nameEl) {
                            nameEl.classList.add('is-invalid');
                            if (!firstInvalidElement) firstInvalidElement = nameEl;
                        }
                    }
                    if (!ageEl || isNaN(ageValue) || ageValue <= 0) {
                        isValid = false;
                        if (ageEl) {
                            ageEl.classList.add('is-invalid');
                            if (!firstInvalidElement) firstInvalidElement = ageEl;
                        }
                    }
                    if (!genderEl || !genderEl.value) {
                        isValid = false;
                        if (genderEl) {
                            genderEl.classList.add('is-invalid');
                            if (!firstInvalidElement) firstInvalidElement = genderEl;
                        }
                    }
                    passengersData.push({
                        seat_code: seatCode,
                        fare: seatData.price,
                        name: nameEl ? nameEl.value.trim() : '',
                        age: ageEl ? ageEl.value.trim() : '',
                        gender: genderEl ? genderEl.value : ''
                    });
                });
                form.querySelectorAll('[name^="contact_"][required]').forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        isValid = false;
                        if (!firstInvalidElement) firstInvalidElement = input;
                    }
                });

                if (!isValid) {
                    alert('Please fill in all required fields correctly.');
                    if (firstInvalidElement) firstInvalidElement.focus();
                    return;
                }

                // Use a plain object for jQuery AJAX
                const bookingData = {
                    route_id: form.querySelector('input[name="route_id"]').value,
                    bus_id: form.querySelector('input[name="bus_id"]').value,
                    schedule_id: form.querySelector('input[name="schedule_id"]').value,
                    travel_date: form.querySelector('input[name="travel_date"]').value,
                    contact_name: form.querySelector('input[name="contact_name"]').value,
                    contact_mobile: form.querySelector('input[name="contact_mobile"]').value,
                    contact_email: form.querySelector('input[name="contact_email"]').value,
                    origin: document.querySelector('input[name="boarding_point"]:checked').value,
                    destination: document.querySelector('input[name="dropping_point"]:checked').value,
                    total_fare: document.getElementById('total-price').textContent,
                    passengers: JSON.stringify(passengersData)
                };

                $.ajax({
                    url: 'process_booking',
                    type: 'POST',
                    data: bookingData,
                    dataType: 'json',
                    beforeSend: function() {
                        actionBtn.disabled = true;
                        actionBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Preparing Payment...';
                    },
                    success: function(data) {
                        if (!data.success) {
                            // Use the error message from the server's JSON response
                            throw new Error(data.message);
                        }

                        const options = {
                            "key": data.razorpay_key_id,
                            "amount": data.amount,
                            "currency": "INR",
                            "name": "BPL Bus Booking",
                            "description": "Bus Ticket Payment",
                            "order_id": data.razorpay_order_id,
                            "handler": function(response) {
                                verifyPayment(response, data.booking_id, data.new_user);
                            },
                            "prefill": {
                                "name": data.contact_name,
                                "email": data.contact_email,
                                "contact": data.contact_mobile
                            },
                            "theme": {
                                "color": "#e97b15ff", // Change this to your brand's primary color (e.g., "#FF0000" for red)
                                "backdrop_color": "rgba(36, 31, 31, 0.6)" // Optional: changes the overlay background color
                            },
                        };

                        const rzp = new Razorpay(options);
                        rzp.on('payment.failed', function(response) {
                            alert("Payment Failed: " + response.error.description);
                            actionBtn.disabled = false;
                            actionBtn.textContent = 'Proceed to Payment';
                        });

                        rzp.open();
                        actionBtn.disabled = false;
                        actionBtn.textContent = 'Proceed to Payment';
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("AJAX Error:", jqXHR.responseText); // Log the raw HTML response
                        alert('An error occurred while preparing your payment. This is often due to a server configuration issue.');
                        actionBtn.disabled = false;
                        actionBtn.textContent = 'Proceed to Payment';
                    }
                });
            }

            function verifyPayment(paymentResponse, bookingId, isNewUser) {
                const verificationData = {
                    razorpay_payment_id: paymentResponse.razorpay_payment_id,
                    razorpay_order_id: paymentResponse.razorpay_order_id,
                    razorpay_signature: paymentResponse.razorpay_signature,
                    booking_id: bookingId,
                    is_new_user: isNewUser
                };
                const actionBtn = document.getElementById('action-btn');

                $.ajax({
                    url: 'payment_verify',
                    type: 'POST',
                    data: verificationData,
                    dataType: 'json',
                    beforeSend: function() {
                        actionBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Verifying Payment...';
                        actionBtn.disabled = true;
                    },
                    success: function(data) {
                        if (data.success) {
                            let redirectUrl = `booking_confirmation?id=${bookingId}`;
                            if (isNewUser) {
                                redirectUrl += '&new_user=true';
                            }
                            window.location.href = redirectUrl;
                        } else {
                            // Handles cases where the server responds with { success: false, message: '...' }
                            alert('Payment Verification Failed: ' + data.message);
                            actionBtn.textContent = 'Proceed to Payment';
                            actionBtn.disabled = false;
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        // *** FIX: This block provides detailed error messages ***
                        let errorMessage = 'A critical error occurred while verifying your payment. Please contact support immediately.';

                        // Try to get a specific message from the server's JSON response (for 400 errors)
                        if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                            errorMessage = jqXHR.responseJSON.message;
                        } else if (jqXHR.status === 0) {
                            errorMessage = 'Network error. Please check your internet connection and try again.';
                        } else {
                            // For non-JSON errors (like a fatal PHP error notice being returned as text)
                            console.error("AJAX Verification Error:", {
                                status: jqXHR.status,
                                responseText: jqXHR.responseText
                            });
                        }

                        alert(errorMessage);
                        actionBtn.textContent = 'Proceed to Payment';
                        actionBtn.disabled = false;
                    }
                });
            }

            function updateFinalSummary() {
                const bpInput = document.querySelector('input[name="boarding_point"]:checked');
                const dpInput = document.querySelector('input[name="dropping_point"]:checked');
                if (!bpInput || !dpInput) return;
                const bpTime = bpInput.closest('.point-option').querySelector('.point-time').textContent;
                const dpTime = dpInput.closest('.point-option').querySelector('.point-time').textContent.split(' ')[0];
                document.getElementById('summary-route').textContent = `${bpInput.value} → ${dpInput.value}`;
                document.getElementById('summary-datetime').textContent = `<?php echo date('d M, Y', strtotime($journey_date)); ?>`;
                document.getElementById('summary-boarding-point').textContent = `Boarding: ${bpTime}, ${bpInput.value}`;
                document.getElementById('summary-dropping-point').textContent = `Dropping: ${dpTime}, ${dpInput.value}`;
                document.getElementById('summary-seat-numbers').textContent = Array.from(selectedSeats.keys()).join(', ');
            }
        });
    </script>
</body>

</html>