<?php
global $_conn_db;
include_once('function/_db.php');
check_user_login();

$name = $_SESSION['user']['name'] ?? 'Guest';
$email = $_SESSION['user']['email'] ?? '';
$mobile = $_SESSION['user']['mobile'] ?? '';

$current_page = basename($_SERVER['PHP_SELF']);

$bus_id = htmlspecialchars($_GET['bus_id'] ?? '');

if (empty($bus_id) || !is_numeric($bus_id)) {
    $_SESSION['notif_type'] = 'error';
    $_SESSION['notif_title'] = 'Invalid Request';
    $_SESSION['notif_desc'] = 'No bus ID provided or invalid bus ID.';
    header("Location: view_all_buses.php");
    exit();
}

$bus_name_display = "Bus ID: " . $bus_id;
try {
    $stmt = $_conn_db->prepare("SELECT bus_name FROM buses WHERE bus_id = ?");
    $stmt->execute([$bus_id]);
    $bus_info = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($bus_info) {
        $bus_name_display = htmlspecialchars($bus_info['bus_name']) . " (ID: " . $bus_id . ")";
    } else {
        $_SESSION['notif_type'] = 'error';
        $_SESSION['notif_title'] = 'Bus Not Found';
        $_SESSION['notif_desc'] = 'The requested bus could not be found.';
        header("Location: view_all_buses.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching bus name for bus_id $bus_id: " . $e->getMessage());
    $_SESSION['notif_type'] = 'error';
    $_SESSION['notif_title'] = 'Database Error';
    $_SESSION['notif_desc'] = 'Could not retrieve bus information due to a database error.';
    header("Location: view_all_buses.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
 <?php include "head.php";?>
    <style>
        :root {
            --grid-size: 10px; 
            --seat-color-available: #34C759;
            --seat-color-sold: #F0F0F0;
            --seat-color-any: #E0E0E0;
            --seat-text-color-sold: #888;
        }
        .seat, .deck-container { touch-action: manipulation; }
        .btn{
            border-radius: 30px;
            padding: 10px;
            color:white;
        }
        .card-header{
            background:white;
            border-top: 0.1rem solid #0d6efd;
        }
        /* .card{ max-width: 301px; } */
        .deck-container {
            position: relative;
            min-height: 700px;
            border: 1px solid #ccc;
            background-color: #fcfcfc;
            overflow: auto;
            max-width: 300px;
            width: 300px;
            background-image: linear-gradient(to right, #eee 1px, transparent 1px),
                              linear-gradient(to bottom, #eee 1px, transparent 1px);
            background-size: var(--grid-size) var(--grid-size); 
            margin-bottom: 20px;
            /* border-radius: 12px; */
        }
        .seat-palette { display: flex; flex-direction: column; gap: 10px; }
        .seat-template, .seat {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 0.7em;
            font-weight: 600;
            color: #333;
            background-color: var(--seat-color-any);
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
            cursor: grab;
            transition: all 0.1s ease-out;
            touch-action: none;
            box-sizing: border-box;
            width: calc(var(--grid-size) * 4);
            height: calc(var(--grid-size) * 4);
        }
        .seat { position: absolute; }
        .seat-template { position: static; }
        .seat.seater, .seat.sleeper {
            border-top-left-radius: calc(var(--grid-size) * 0.6);
            border-top-right-radius: calc(var(--grid-size) * 0.6);
            border-bottom-left-radius: calc(var(--grid-size) * 0.2);
            border-bottom-right-radius: calc(var(--grid-size) * 0.2);
        }
        .seat.sleeper { height: calc(var(--grid-size) * 8); }
        .seat.driver {
            width: calc(var(--grid-size) * 5);
            height: calc(var(--grid-size) * 5);
            border-radius: 50%;
            background-color: #6c757d;
            border-color: #5a6268;
            color: #fff;
        }
        .seat.aisle {
            background-color: transparent;
            border: 1px dashed #bbb;
            box-shadow: none;
        }
        .seat-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            width: 100%;
            pointer-events: none;
        }
        .card{
            border-bottom: 0.1rem solid #0d6efd;
        }
        /* --- NEW ---: Style for the seat code display */
        .seat-code {
            font-size: 0.9em;
            font-weight: bold;
            line-height: 1;
            margin-bottom: 2px;
        }
        .seat-icon { font-size: 1.1em; pointer-events: none; }
        .seat-text { font-size: 0.7em; font-weight: 600; pointer-events: none; }
        .seat.status-available.bookable { border-color: var(--seat-color-available); background-color: #fff; }
        .seat.status-sold { background-color: var(--seat-color-sold); border-color: #ddd; color: var(--seat-text-color-sold); }
        .seat-icon.gender-male { color: #1a73e8; }
        .seat-icon.gender-female { color: #e91e63; }
        .seat.selected-for-edit { border: 2px solid #ffc107; box-shadow: 0 0 10px rgba(255,193,7,0.8); z-index: 100; }
        .ui-draggable-dragging { z-index: 1000; opacity: 0.8; }
        .card-body p { margin: 0 0 10px 0; font-size: 1em; }
        .card-body p span { font-weight: bold; color: #0056b3; float: right; }
         
 
 
.mobile-palette-bar {
 

    /* Positioning */
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1050; /* High z-index to stay on top */

    /* Layout */
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 15px;

    /* Styling */
    background-color: #ffffff;
    border-top: 1px solid #e0e0e0;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
}
.mobile-palette-bar {
        display: none;
    }
.mobile-palette-icons {
    display: flex;
    gap: 15px; /* Spacing between icons */
}

.mobile-palette-actions .btn {
    padding: 5px 10px; /* Make button slightly smaller */
    border-radius: 20px;
}

/* 
  This is the magic part: a Media Query.
  These styles will ONLY apply when the screen width is 887px or less.
*/
@media (max-width: 992px) {
    /* Show the new mobile bar */
    .mobile-palette-bar {
        display: flex;
    }

    /* Hide the original desktop palette */
    .desktop-palette {
        display: none;
    }

    /* Add some padding to the bottom of the main content to prevent
       the new bar from covering the "Save" button */
    .main-content .container-fluid {
        padding-bottom: 80px; 
    }
}
 
    </style>
</head>
<body>

<div id="wrapper">
    <?php include_once('sidebar.php');?>
    <div class="main-content">
        <?php include_once('header.php');?>
        <div class="container-fluid">
            <h2 class="mb-4 mt-4 text-center">Manage Seat Layout for <span id="current_bus_name"><?php echo $bus_name_display; ?></span></h2>
            <div class="row">
                 <div class="col-lg-3 col-md-4   col-12 mb-4 desktop-palette">
                    <div class="card w-100 mb-1  desktop-palette"  >
                        <div class="card-header">Seat Palette</div>
                        <div class="card-body  seat-palette ">
                             <div class="row">
                                <div class="col-3 col-md-6 col-lg-3"> <div class="seat-template seater" data-seat-type="SEATER" data-orientation="VERTICAL_UP" data-default-width="40" data-default-height="40" title="Seater">
                                <i class="fas fa-chair"></i>
                            </div></div>
                                <div class="col-3 col-md-6 col-lg-3"> <div class="seat-template sleeper" data-seat-type="SLEEPER" data-orientation="VERTICAL_UP" data-default-width="40" data-default-height="80" title="Sleeper">
                                <i class="fas fa-bed"></i>
                            </div></div>
                                <div class="col-3 col-md-6 col-lg-3"> <div class="seat-template driver" data-seat-type="DRIVER" data-is-bookable="false" data-default-width="50" data-default-height="50" title="Driver">
                                <i class="fas fa-user-tie"></i>
                            </div></div>
                                <div class="col-3 col-md-6 col-lg-3"> <div class="seat-template aisle" data-seat-type="AISLE" data-is-bookable="false" data-default-width="40" data-default-height="40" title="Aisle/Gap">
                                <i class="fas fa-arrows-alt-h"></i>
                            </div></div>
                             </div>
                            <hr>
                            <button type="button" class="btn btn-warning btn-sm delete-selected-seat-btn w-100"><i class="fas fa-trash"></i> Delete Selected</button>
                        </div>
                    </div>
                    <div class="card d-none d-md-block w-100">
                        <div class="card-header">Seat Summary</div>
                        <div class="card-body">
                            <p>Total Elements: <span id="total_seat_count">0</span></p>
                            <p>Bookable Seats: <span id="bookable_seat_count">0</span></p>
                            <p>Seaters: <span id="seater_count">0</span></p>
                            <p>Sleepers: <span id="sleeper_count">0</span></p>
                            <p>Aisles/Gaps: <span id="aisle_count">0</span></p>
                            <p>Drivers: <span id="driver_count">0</span></p>
                        </div>
                    </div>
                </div>  

                <div class="col-lg-9 col-md-12  mb-2" >
                    <div class="w-100  bg-white p-3" style="box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px; border-radius: 20px;"> 
                    <input type="hidden" id="bus_id_for_js" value="<?php echo $bus_id; ?>">
                    <div class="d-flex flex-wrap gap-4  justify-content-around ">
                        <div class="card mb-4 flex-grow-1"style="max-width: 301px;">
                            <div class="card-header">Lower Deck</div>
                            <div class="card-body deck-container" id="lower_deck_container"></div>
                        </div>
                        <div class="card mb-4 flex-grow-1" style="max-width: 301px;">
                            <div class="card-header">Upper Deck</div>
                            <div class="card-body deck-container" id="upper_deck_container"></div>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="button" class="btn btn-primary ladda-button p-1" data-style="zoom-in" id="dynamicSaveNotificationBtn">
                            <span class="ladda-label"><i class="bx bx-check-circle"></i> Changes are Saved Automatically</span>
                        </button>
                    </div>
                    <div class="card d-block my-3 d-md-none w-100">
                        <div class="card-header">Seat Summary</div>
                        <div class="card-body">
                            <p>Total Elements: <span id="total_seat_count_mobile">0</span></p>
                            <p>Bookable Seats: <span id="bookable_seat_count_mobile">0</span></p>
                            <p>Seaters: <span id="seater_count_mobile">0</span></p>
                            <p>Sleepers: <span id="sleeper_count_mobile">0</span></p>
                            <p>Aisles/Gaps: <span id="aisle_count_mobile">0</span></p>
                            <p>Drivers: <span id="driver_count_mobile">0</span></p>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Seat Properties Modal -->
<div class="modal fade" id="seatPropertiesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Seat Properties (<span id="modal_display_seat_id"></span>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="seat-properties-form">
                    <input type="hidden" id="modal_seat_db_id">
                    <div class="mb-3">
                        <label class="form-label">Seat Code</label>
                        <!-- --- MODIFIED ---: Removed the 'disabled' attribute to make it editable -->
                        <input type="text" class="form-control" id="modal_seat_code">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Seat Type</label>
                        <select class="form-select" id="modal_seat_type" disabled>
                            <option value="SEATER">Seater</option>
                            <option value="SLEEPER">Sleeper</option>
                            <option value="DRIVER">Driver</option>
                            <option value="AISLE">Aisle/Gap</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modal_gender_preference" class="form-label">Gender Preference</label>
                        <select class="form-select" id="modal_gender_preference">
                            <option value="ANY">Any</option>
                            <option value="MALE">Male</option>
                            <option value="FEMALE">Female</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="modal_is_bookable">
                        <label class="form-check-label" for="modal_is_bookable">Is Bookable</label>
                    </div>
                    <div class="mb-3">
                        <label for="modal_orientation" class="form-label">Orientation</label>
                        <select class="form-select" id="modal_orientation">
                            <option value="VERTICAL_UP">Vertical (Up)</option>
                            <option value="VERTICAL_DOWN">Vertical (Down)</option>
                            <option value="HORIZONTAL_RIGHT">Horizontal (Right)</option>
                            <option value="HORIZONTAL_LEFT">Horizontal (Left)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modal_width" class="form-label">Width (px)</label>
                        <input type="number" class="form-control" id="modal_width" min="10" step="10">
                    </div>
                    <div class="mb-3">
                        <label for="modal_height" class="form-label">Height (px)</label>
                        <input type="number" class="form-control" id="modal_height" min="10" step="10">
                    </div>
                     <div class="mb-3">
                        <label for="modal_status" class="form-label">Seat Status</label>
                        <select class="form-select" id="modal_status">
                            <option value="AVAILABLE">Available</option>
                            <option value="DAMAGED">Damaged</option>
                            <option value="BLOCKED">Blocked</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-danger delete-selected-seat-btn "><i class="fas fa-trash"></i> Delete Selected</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveSeatProperties">Save Changes</button>
            </div>
        </div>
    </div>
</div>
<div class="mobile-palette-bar ">
    <div class="mobile-palette-icons">
        <!-- These are clones of the desktop icons for the mobile view -->
        <div class="seat-template seater" data-seat-type="SEATER" data-orientation="VERTICAL_UP" data-default-width="40" data-default-height="40" title="Seater">
            <i class="fas fa-chair"></i>
        </div>
        <div class="seat-template sleeper" data-seat-type="SLEEPER" data-orientation="VERTICAL_UP" data-default-width="40" data-default-height="80" title="Sleeper">
            <i class="fas fa-bed"></i>
        </div>
        <div class="seat-template driver" data-seat-type="DRIVER" data-is-bookable="false" data-default-width="50" data-default-height="50" title="Driver">
            <i class="fas fa-user-tie"></i>
        </div>
        <div class="seat-template aisle" data-seat-type="AISLE" data-is-bookable="false" data-default-width="40" data-default-height="40" title="Aisle/Gap">
            <i class="fas fa-arrows-alt-h"></i>
        </div>
    </div>
    <div class="mobile-palette-actions">
        <!-- This button also needs the same class to work with the JS -->
        <button type="button" class="btn btn-warning btn-sm delete-selected-seat-btn">
            <i class="fas fa-trash"></i> <span>Delete</span>
        </button>
    </div>
</div>
<?php include "foot.php"; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>

<script>
if (window.location.pathname.includes('manage_seats.php')) {
    $(document).ready(function() {
        const busId = $('#bus_id_for_js').val();
        let currentSeats = [];
        let selectedSeatElement = null;
        const GRID_SIZE = 10;
        let seatCodeCounter = { LOWER_SEATER: 1, LOWER_SLEEPER: 1, LOWER_AISLE: 1, UPPER_SEATER: 1, UPPER_SLEEPER: 1, UPPER_AISLE: 1 };

        function getTransformForOrientation(orientation) {
            switch (orientation) {
                case 'VERTICAL_DOWN': return 'rotate(180deg)';
                case 'HORIZONTAL_RIGHT': return 'rotate(90deg)';
                case 'HORIZONTAL_LEFT': return 'rotate(-90deg)';
                default: return 'none';
            }
        }

        function initializeSeatCodeCounter() {
            seatCodeCounter = { LOWER_SEATER: 1, LOWER_SLEEPER: 1, LOWER_AISLE: 1, UPPER_SEATER: 1, UPPER_SLEEPER: 1, UPPER_AISLE: 1 };
            currentSeats.forEach(seat => {
                const key = `${seat.deck}_${seat.seat_type}`;
                const codePrefix = seat.deck.substring(0,1) + (seat.seat_type === 'SEATER' ? 'S' : (seat.seat_type === 'SLEEPER' ? 'P' : 'G'));
                if (seatCodeCounter[key] && seat.seat_code.startsWith(codePrefix)) {
                    const num = parseInt(seat.seat_code.replace(codePrefix, ''));
                    if (!isNaN(num) && num >= seatCodeCounter[key]) {
                        seatCodeCounter[key] = num + 1;
                    }
                }
            });
        }

        function updateSeatCounts() {
            let counts = { total: 0, bookable: 0, seater: 0, sleeper: 0, aisle: 0, driver: 0 };
            currentSeats.forEach(seat => {
                counts.total++;
                if (seat.is_bookable) counts.bookable++;
                switch(seat.seat_type) {
                    case 'SEATER': counts.seater++; break;
                    case 'SLEEPER': counts.sleeper++; break;
                    case 'AISLE': counts.aisle++; break;
                    case 'DRIVER': counts.driver++; break;
                }
            });
            $('#total_seat_count, #total_seat_count_mobile').text(counts.total);
            $('#bookable_seat_count, #bookable_seat_count_mobile').text(counts.bookable);
            $('#seater_count, #seater_count_mobile').text(counts.seater);
            $('#sleeper_count, #sleeper_count_mobile').text(counts.sleeper);
            $('#aisle_count, #aisle_count_mobile').text(counts.aisle);
            $('#driver_count, #driver_count_mobile').text(counts.driver);
        }

        function loadSeatsForBus() {
            $.ajax({
                url: 'function/backend/bus_actions.php',
                type: 'GET',
                dataType: 'json',
                data: { action: 'get_bus_seats', bus_id: busId },
                success: function(response) {
                    if (response.res === 'true' && response.seats) {
                        currentSeats = response.seats.map(seat => ({
                            ...seat,
                            seat_id: parseInt(seat.seat_id),
                            x_coordinate: parseInt(seat.x_coordinate),
                            y_coordinate: parseInt(seat.y_coordinate),
                            width: parseInt(seat.width),
                            height: parseInt(seat.height),
                            is_bookable: (seat.is_bookable == 1 || seat.is_bookable === true)
                        }));
                    } else {
                        currentSeats = [];
                    }
                    initializeSeatCodeCounter();
                    renderSeats();
                },
                error: function(xhr) { console.error("Error loading seats:", xhr.responseText); }
            });
        }
        
        // --- MODIFIED ---: This function now adds the seat code to the element
        function createSeatElementContent(seatData) {
            const content = $('<div>').addClass('seat-content');
            let iconHtml = '', textHtml = '', codeHtml = '';
            
            // Add the seat code, but not for aisles
            if (seatData.seat_type !== 'AISLE' && seatData.seat_type !== 'DRIVER') {
                codeHtml = `<span class="seat-code">${seatData.seat_code}</span>`;
            }

            switch (seatData.seat_type) {
                case 'SEATER':
                case 'SLEEPER':
                    let genderClass = 'gender-any', genderIcon = 'fas fa-user';
                    if (seatData.gender_preference === 'MALE') { genderClass = 'gender-male'; genderIcon = 'fas fa-male'; }
                    else if (seatData.gender_preference === 'FEMALE') { genderClass = 'gender-female'; genderIcon = 'fas fa-female'; }
                    iconHtml = `<i class="seat-icon ${genderIcon} ${genderClass}"></i>`;
                    if (!seatData.is_bookable || seatData.status !== 'AVAILABLE') {
                        textHtml = `<span class="seat-text">Sold</span>`;
                    }
                    break;
                case 'DRIVER': iconHtml = '<i class="seat-icon fas fa-user-tie"></i>'; break;
                case 'AISLE': iconHtml = '<i class="seat-icon fas fa-arrows-alt-h"></i>'; break;
            }

            // Append elements in order: code, then icon, then other text
            if (codeHtml) content.append(codeHtml);
            if (iconHtml) content.append(iconHtml);
            if (textHtml) content.append(textHtml);
            return content;
        }

        function renderSeats() {
            $('#lower_deck_container, #upper_deck_container').empty();
            currentSeats.forEach(seatData => {
                const seatElement = $('<div>').addClass('seat').addClass(seatData.seat_type.toLowerCase())
                    .attr('id', 'seat_' + seatData.seat_id).data('seat-data', seatData);
                
                if (seatData.is_bookable && seatData.status === 'AVAILABLE') {
                    seatElement.addClass('status-available bookable');
                } else {
                    seatElement.addClass('status-sold');
                }
                seatElement.append(createSeatElementContent(seatData));
                seatElement.css({
                    left: seatData.x_coordinate + 'px', top: seatData.y_coordinate + 'px',
                    width: seatData.width + 'px', height: seatData.height + 'px',
                    transform: getTransformForOrientation(seatData.orientation)
                });
                $(`#${seatData.deck.toLowerCase()}_deck_container`).append(seatElement);
            });
            makeSeatsDraggableAndEditable();
            updateSeatCounts();
        }

        function makeSeatsDraggableAndEditable() {
            $('.seat').draggable({
                containment: ".d-flex.flex-wrap",
                grid: [GRID_SIZE, GRID_SIZE],
                stack: ".seat",
                stop: function(event, ui) {
                    const $this = $(this);
                    const $deck = $this.closest('.deck-container');
                    let new_x = Math.round((ui.offset.left - $deck.offset().left) / GRID_SIZE) * GRID_SIZE;
                    let new_y = Math.round((ui.offset.top - $deck.offset().top) / GRID_SIZE) * GRID_SIZE;
                    $this.css({ left: new_x, top: new_y });
                    const seatData = $this.data('seat-data');
                    seatData.x_coordinate = new_x;
                    seatData.y_coordinate = new_y;
                    saveSeatToDB(seatData, 'update_seat_position');
                }
            }).on('click', function(e) {
                e.stopPropagation();
                $('.seat').removeClass('selected-for-edit');
                selectedSeatElement = $(this).addClass('selected-for-edit');
                editSeatProperties($(this).data('seat-data'));
            });
            $('.deck-container').on('click', function() {
                if(selectedSeatElement) selectedSeatElement.removeClass('selected-for-edit');
                selectedSeatElement = null;
            });
        }
        
        // --- MODIFIED ---: Conditionally disables the seat code input
        function editSeatProperties(seatData) {
            $('#modal_display_seat_id').text(seatData.seat_code);
            $('#modal_seat_db_id').val(seatData.seat_id);
            $('#modal_seat_code').val(seatData.seat_code);
            $('#modal_seat_type').val(seatData.seat_type);
            $('#modal_gender_preference').val(seatData.gender_preference || 'ANY');
            $('#modal_is_bookable').prop('checked', seatData.is_bookable);
            $('#modal_orientation').val(seatData.orientation || 'VERTICAL_UP');
            $('#modal_width').val(seatData.width);
            $('#modal_height').val(seatData.height);
            $('#modal_status').val(seatData.status || 'AVAILABLE');
            
            const isConfigurable = !['DRIVER', 'AISLE'].includes(seatData.seat_type);
            $('#modal_gender_preference, #modal_is_bookable, #modal_orientation, #modal_status, #modal_width, #modal_height').prop('disabled', !isConfigurable);

            // Also disable seat code editing for non-configurable types
            $('#modal_seat_code').prop('disabled', !isConfigurable);

            if (!isConfigurable) {
                $('#modal_is_bookable').prop('checked', false);
            }
            $('#seatPropertiesModal').modal('show');
        }

        // --- MODIFIED ---: Reads the new seat code, checks for duplicates, and saves it
        $('#saveSeatProperties').on('click', function() {
            if (!selectedSeatElement) return;
            const seatId = $('#modal_seat_db_id').val();
            const seatIndex = currentSeats.findIndex(s => s.seat_id == seatId);
            if (seatIndex === -1) return;

            const originalSeat = currentSeats[seatIndex];
            const newSeatCode = $('#modal_seat_code').val().trim();

            // Check for duplicate seat codes before saving
            if (newSeatCode !== originalSeat.seat_code) {
                const isDuplicate = currentSeats.some(seat => seat.seat_id != seatId && seat.seat_code === newSeatCode);
                if (isDuplicate) {
                    Swal.fire('Error', `Seat code "${newSeatCode}" already exists. Please choose a unique code.`, 'error');
                    return; // Stop the save process
                }
            }

            const updatedData = {
                ...originalSeat,
                seat_code: newSeatCode, // Get the new seat code from input
                base_price: 0,
                gender_preference: $('#modal_gender_preference').val(),
                is_bookable: $('#modal_is_bookable').prop('checked'),
                orientation: $('#modal_orientation').val(),
                width: parseInt($('#modal_width').val()),
                height: parseInt($('#modal_height').val()),
                status: $('#modal_status').val()
            };
            
            saveSeatToDB(updatedData, 'update_seat', function(response) {
                if (response.res === 'true') {
                    currentSeats[seatIndex] = updatedData;
                    renderSeats();
                    $('#seatPropertiesModal').modal('hide');
                }
            });
        });

        $('.seat-template').draggable({
            helper: 'clone',
            revert: 'invalid'
        });

        $('.deck-container').droppable({
            accept: '.seat-template',
            drop: function(event, ui) {
                const type = ui.helper.data('seat-type');
                const deck = $(this).attr('id').replace('_deck_container', '').toUpperCase();
                const key = `${deck}_${type}`;
                const codePrefix = deck.substring(0,1) + (type === 'SEATER' ? 'S' : (type === 'SLEEPER' ? 'P' : 'G'));
                let newSeatCode;
                if(type === 'DRIVER'){
                    if(currentSeats.some(s => s.seat_type === 'DRIVER')) {
                        $.notify({ title: 'Warning', message: 'Driver seat already exists.'}, { type: 'warning' });
                        return;
                    }
                    newSeatCode = 'DRIVER';
                } else {
                     newSeatCode = codePrefix + seatCodeCounter[key]++;
                }
               
                let snappedX = Math.round((event.pageX - $(this).offset().left - ui.helper.width()/2) / GRID_SIZE) * GRID_SIZE;
                let snappedY = Math.round((event.pageY - $(this).offset().top - ui.helper.height()/2) / GRID_SIZE) * GRID_SIZE;

                const newSeatData = {
                    bus_id: busId,
                    seat_code: newSeatCode,
                    deck: deck,
                    seat_type: type,
                    x_coordinate: Math.max(0, snappedX),
                    y_coordinate: Math.max(0, snappedY),
                    width: ui.helper.data('default-width'),
                    height: ui.helper.data('default-height'),
                    orientation: ui.helper.data('orientation') || 'VERTICAL_UP',
                    base_price: 0,
                    gender_preference: 'ANY',
                    is_bookable: ui.helper.data('is-bookable') !== false,
                    status: 'AVAILABLE'
                };

                saveSeatToDB(newSeatData, 'add_seat', function(response) {
                    if (response.res === 'true' && response.new_seat_id) {
                        newSeatData.seat_id = response.new_seat_id;
                        currentSeats.push(newSeatData);
                        renderSeats();
                    } else {
                        seatCodeCounter[key]--; // Rollback counter on failure
                    }
                });
            }
        });

        function saveSeatToDB(seatData, actionType, callback) {
            $.ajax({
                url: 'function/backend/bus_actions.php',
                type: 'POST',
                dataType: 'json',
                data: { ...seatData, action: actionType },
                success: function(response) {
                    if (response.res !== 'true') {
                        Swal.fire('Error', response.notif_desc || 'An error occurred.', 'error');
                    }
                    if(callback) callback(response);
                },
                error: function() { Swal.fire('Error', 'Could not connect to the server.', 'error'); }
            });
        }

        $('.delete-selected-seat-btn').on('click', function() {
            if (!selectedSeatElement) {
                $.notify({ title: 'Info', message: 'Please select a seat to delete.' }, { type: 'info' });
                return;
            }
            const seatData = selectedSeatElement.data('seat-data');
            Swal.fire({
                title: 'Are you sure?', text: "You won't be able to revert this!",
                icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    saveSeatToDB(seatData, 'delete_seat', function(response) {
                        if (response.res === 'true') {
                            currentSeats = currentSeats.filter(s => s.seat_id != seatData.seat_id);
                            renderSeats();
                            $('#seatPropertiesModal').modal('hide'); // Close modal after delete
                            Swal.fire('Deleted!', 'The seat has been deleted.', 'success');
                        }
                    });
                }
            });
        });

        loadSeatsForBus();
    });
}
</script>

</body>
</html>
<?php pdo_close_conn($_conn_db); ?>