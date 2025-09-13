<?php
// book_ticket.php
include_once('function/_db.php');
session_security_check();
check_permission('can_book_tickets');

// --- MODIFIED LOGIC TO FETCH ROUTES ---
$routes = [];
try {
    // 1. Get the list of route IDs this employee is allowed to see.
    $allowed_route_ids = get_assigned_route_ids_for_employee($_SESSION['user']['id']);

    // 2. Only proceed if the employee is assigned to at least one route.
    if (!empty($allowed_route_ids)) {
        // Create the correct number of question mark placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($allowed_route_ids), '?'));

        // 3. Fetch the details for ONLY the allowed routes.
        $query = "
            SELECT 
                r.route_id, 
                CONCAT(r.route_name, ' (', b.bus_name, ')') AS display_name
            FROM routes r
            JOIN buses b ON r.bus_id = b.bus_id
            WHERE r.status = 'Active' AND r.route_id IN ($placeholders)
            ORDER BY r.route_name, b.bus_name
        ";
        
        $stmt = $_conn_db->prepare($query);
        $stmt->execute($allowed_route_ids);
        $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // If $allowed_route_ids is empty, the $routes array will also remain empty.

} catch (PDOException $e) {
    // The $routes array will be empty, and the page will show "No routes assigned."
    error_log("Error on book_ticket.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "head.php"; ?>
    <title>Book a Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        :root {
            --seat-available-border: #28a745;
            --seat-selected-bg: #0dcaf0;
            --seat-booked-bg: #e9ecef;
            --seat-booked-border: #ced4da;
            --gender-female-color: #e83e8c;
            --gender-male-color: #0d6efd;
        }

        .deck-container {
            position: relative;
            min-height: 500px;
            border: 2px dashed #ccc;
            background-color: #f8f9fa;
            overflow: auto;
            width: 300px;
            max-width: 300px;
            margin: 0 auto;
            border-radius: 10px;
        }

        .seat {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7em;
            font-weight: 600;
            color: #333;
            background-color: #E0E0E0;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.2s ease-out;
            user-select: none;
            box-sizing: border-box;
        }

        .seat-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100%;
            height: 100%;
        }

        .seat.seater {
            width: 40px;
            height: 40px;
        }

        .seat.sleeper {
            width: 40px;
            height: 80px;
        }

        .seat.driver,
        .seat.aisle {
            background-color: #6c757d;
            color: #fff;
            cursor: default;
        }

        .seat-code {
            font-size: 0.9em;
            font-weight: bold;
            line-height: 1;
            margin-bottom: 2px;
        }

        .seat-icon {
            font-size: 1.2em;
        }

        .seat-price {
            font-size: 0.8em;
            font-weight: normal;
        }

        .seat.status-available {
            background-color: #fff;
            border: 2px solid var(--seat-available-border);
            color: #212529;
        }

        .seat.status-available:hover {
            background-color: #d1e7dd;
        }

        .seat.status-booked {
            background-color: var(--seat-booked-bg);
            border: 2px solid var(--seat-booked-border);
            color: #6c757d;
            cursor: not-allowed;
        }

        .seat.status-selected {
            background-color: var(--seat-selected-bg);
            border-color: var(--seat-selected-bg);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            z-index: 10;
        }

        .seat .gender-female {
            color: var(--gender-female-color);
        }

        .seat .gender-male {
            color: var(--gender-male-color);
        }

        #passenger-details-form .card {
            border-left: 4px solid var(--seat-selected-bg);
            animation: fadeIn 0.3s;
        }

        @media (min-width: 992px) {
            #booking-summary-card {
                position: sticky;
                top: 20px;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <?php include_once('sidebar.php'); ?>
        <div class="main-content">
            <?php include_once('header.php'); ?>
            <div class="container-fluid">
                <h2 class="my-4">New Ticket Booking</h2>
                <?php if (empty($routes)): ?>
                    <div class="alert alert-warning text-center">
                        <h4><i class="fas fa-exclamation-triangle"></i> No Routes Assigned</h4>
                        <p class="mb-0">You are not currently assigned to any active routes. Please contact an administrator.</p>
                    </div>
                <?php else: ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="mb-0">Step 1: Select Journey Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-sm-6 col-lg-3">
                                    <label for="route-select" class="form-label fw-bold">Route</label>
                                    <select id="route-select" class="form-select">
                                        <option value="">-- Choose an Assigned Route --</option>
                                        <?php foreach ($routes as $route): ?>
                                            <option value="<?php echo $route['route_id']; ?>"><?php echo htmlspecialchars($route['display_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-lg-3">
                                    <label for="from-stop-select" class="form-label fw-bold">From</label>
                                    <select id="from-stop-select" class="form-select" disabled>
                                        <option>-- Select Route First --</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-lg-3">
                                    <label for="to-stop-select" class="form-label fw-bold">To</label>
                                    <select id="to-stop-select" class="form-select" disabled>
                                        <option>-- Select Boarding Point --</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-lg-3">
                                    <label for="travel-date" class="form-label fw-bold">Travel Date</label>
                                    <input type="text" id="travel-date" class="form-control" placeholder="Select Date">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- (The rest of the HTML for seat selection, summary, etc. remains exactly the same) -->
                    <!-- ... -->
                <?php endif; ?>

                <div id="seat-selection-area" class="d-none">
                    <div class="row">
                        <div class="col-lg-12 mb-4 col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="mb-0">Step 2: Select Seats</h4>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-column flex-md-row justify-content-around align-items-center">
                                        <div id="lower-deck-wrapper" class="mb-4 mb-md-0">
                                            <h6 class="text-center">Lower Deck</h6>
                                            <div class="deck-container" id="lower_deck_container"></div>
                                        </div>
                                        <div id="upper-deck-wrapper" class="d-none">
                                            <h6 class="text-center">Upper Deck</h6>
                                            <div class="deck-container" id="upper_deck_container"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12">
                            <div class="card" id="booking-summary-card">
                                <div class="card-header">
                                    <h4 class="mb-0">Step 3: Passenger Details & Summary</h4>
                                </div>
                                <div class="card-body">
                                    <form id="main-booking-form">
                                        <div id="main-contact-details" class="d-none">
                                            <h5 class="mb-3">Contact Details for Booking</h5>
                                            <div class="row g-3">
                                                <div class="col-md-6"><label for="contact-email" class="form-label">Email Address <small>(Optional)</small></label><input type="email" class="form-control" id="contact-email" placeholder="for e-ticket"></div>
                                                <div class="col-md-6"><label for="contact-mobile" class="form-label">Mobile Number <small>(Optional)</small></label><input type="tel" class="form-control" id="contact-mobile" placeholder="for booking updates"></div>
                                            </div>
                                            <hr class="my-4">
                                        </div>
                                        <div id="passenger-details-form">
                                            <p class="text-muted text-center">Please select one or more seats to continue.</p>
                                        </div>
                                    </form>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h3 class="mb-0">Total Fare:</h3>
                                        <h3 class="mb-0">₹<span id="total-fare">0.00</span></h3>
                                    </div>
                                    <button id="confirm-booking-btn" class="btn btn-success w-100 mt-3" disabled>Proceed to Payment</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include "foot.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        $(document).ready(function() {
            let selectedRouteId, selectedDate, fromStopName, toStopName, busId;
        let selectedSeats = [];
        let allStops = [];
            const datePicker = flatpickr("#travel-date", {
                minDate: "today",
                dateFormat: "Y-m-d",
                defaultDate: "today",
                onChange: (d, s) => {
                    selectedDate = s;
                    if (s && selectedRouteId && fromStopName && toStopName) loadSeatLayout();
                }
            });

            selectedDate = datePicker.input.value;

            $('#route-select').on('change', function() {
                selectedRouteId = $(this).val();
                resetPage(1);
                if (!selectedRouteId) return;
                $('#from-stop-select').prop('disabled', true).html('<option>Loading...</option>');
                $.getJSON('function/backend/booking_actions.php', {
                        action: 'get_stops_for_route',
                        route_id: selectedRouteId
                    })
                    .done(response => {
                        if (response.status === 'success') {
                            allStops = response.stops;
                            let options = '<option value="">-- Select Boarding Point --</option>';
                            allStops.forEach(stop => options += `<option value="${stop}">${stop}</option>`);
                            $('#from-stop-select').html(options).prop('disabled', false);
                        }
                    });
            });

            $('#from-stop-select').on('change', function() {
                fromStopName = $(this).val();
                const fromIndex = allStops.indexOf(fromStopName);
                resetPage(2);
                if (!fromStopName) return;
                let options = '<option value="">-- Select Dropping Point --</option>';
                allStops.forEach((stop, index) => {
                    if (index > fromIndex) options += `<option value="${stop}">${stop}</option>`;
                });
                $('#to-stop-select').html(options).prop('disabled', false);
            });

            $('#to-stop-select').on('change', function() {
                toStopName = $(this).val();
                resetPage(3);
                if (toStopName && selectedDate) {
                    loadSeatLayout();
                }
            });

            function loadSeatLayout() {
                resetPage(4);
                $('#seat-selection-area').removeClass('d-none');
                $('.deck-container').html('<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-primary"></div></div>');
                $.getJSON('function/backend/booking_actions.php', {
                    action: 'get_seat_layout',
                    route_id: selectedRouteId,
                    travel_date: selectedDate,
                    from_stop_name: fromStopName,
                    to_stop_name: toStopName
                }).done(response => {
                    if (response.status === 'success') {
                        busId = response.bus_id;
                        renderSeats(response.seats);
                    } else {
                        $('#lower_deck_container').html(`<div class="alert alert-danger m-3">${response.message}</div>`);
                        $('#upper_deck_container').empty();
                    }
                }).fail(() => $('.deck-container').html('<div class="alert alert-danger m-3">Could not load seat layout.</div>'));
            }

            function createSeatElement(seatData) {
                const seatEl = $('<div>').addClass('seat').attr('data-seat-id', seatData.seat_id).addClass(seatData.seat_type.toLowerCase()).css({
                    left: seatData.x_coordinate + 'px',
                    top: seatData.y_coordinate + 'px',
                    width: seatData.width + 'px',
                    height: seatData.height + 'px',
                    transform: `rotate(${seatData.orientation.includes('DOWN') ? 180 : (seatData.orientation.includes('RIGHT') ? 90 : (seatData.orientation.includes('LEFT') ? -90 : 0))}deg)`
                });
                const content = $('<div>').addClass('seat-content');
                let iconHtml = '',
                    priceHtml = '',
                    codeHtml = '';
                if (seatData.seat_type !== 'AISLE' && seatData.seat_type !== 'DRIVER') codeHtml = `<span class="seat-code">${seatData.seat_code}</span>`;

                let genderClass = '',
                    genderIcon = 'fas fa-user';
                if (seatData.seat_type === 'SEATER' || seatData.seat_type === 'SLEEPER') {
                    if (seatData.gender_preference === 'MALE') {
                        genderClass = 'gender-male';
                        genderIcon = 'fas fa-male';
                    } else if (seatData.gender_preference === 'FEMALE') {
                        genderClass = 'gender-female';
                        genderIcon = 'fas fa-female';
                    }
                    iconHtml = `<i class="seat-icon ${genderIcon} ${genderClass}"></i>`;
                } else if (seatData.seat_type === 'DRIVER') {
                    iconHtml = '<i class="seat-icon fas fa-user-tie"></i>';
                } else if (seatData.seat_type === 'AISLE') {
                    iconHtml = '<i class="seat-icon fas fa-arrows-alt-h"></i>';
                }

                if (parseInt(seatData.is_bookable) === 1 && !seatData.is_booked) priceHtml = `<span class="seat-price">₹${seatData.price}</span>`;

                content.append(codeHtml).append(iconHtml).append(priceHtml);
                seatEl.append(content);

                if (parseInt(seatData.is_bookable) === 1) {
                    seatEl.addClass(seatData.is_booked ? 'status-booked' : 'status-available').data('seat-info', {
                        id: seatData.seat_id,
                        code: seatData.seat_code,
                        price: parseFloat(seatData.price)
                    });
                }
                return seatEl;
            }

            function renderSeats(seats) {
                $('#lower_deck_container, #upper_deck_container').empty();
                let hasUpperDeck = false;
                seats.forEach(seat => {
                    const seatEl = createSeatElement(seat);
                    const container = (seat.deck === 'UPPER') ? '#upper_deck_container' : '#lower_deck_container';
                    $(container).append(seatEl);
                    if (seat.deck === 'UPPER') hasUpperDeck = true;
                });
                $('#upper-deck-wrapper').toggleClass('d-none', !hasUpperDeck);
            }

            $(document).on('click', '.seat.status-available', function() {
                const seatInfo = $(this).data('seat-info');
                const seatId = seatInfo.id;
                if ($(this).hasClass('status-selected')) {
                    $(this).removeClass('status-selected');
                    selectedSeats = selectedSeats.filter(s => s.id !== seatId);
                    $('#passenger-form-' + seatId).remove();
                } else {
                    $(this).addClass('status-selected');
                    selectedSeats.push(seatInfo);
                    addPassengerForm(seatInfo);
                }
                updateSummary();
            });

            function addPassengerForm(seatInfo) {
                if (selectedSeats.length === 1) {
                    $('#passenger-details-form').empty();
                    $('#main-contact-details').removeClass('d-none');
                }
                const formHtml = `
                <div class="card mb-2" id="passenger-form-${seatInfo.id}">
                    <div class="card-body p-3">
                        <h6 class="mb-3">Seat: <span class="badge bg-info">${seatInfo.code}</span> (₹${seatInfo.price.toFixed(2)})</h6>
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 mb-2 mb-sm-0"><input type="text" class="form-control" name="passenger_name_${seatInfo.id}" placeholder="Passenger Name" required></div>
                            <div class="col-6 col-sm-3"><input type="number" class="form-control" name="passenger_age_${seatInfo.id}" placeholder="Age" min="1" max="120" required></div>
                            <div class="col-6 col-sm-3">
                                <select class="form-select" name="passenger_gender_${seatInfo.id}" required>
                                    <option value="MALE">Male</option><option value="FEMALE">Female</option><option value="OTHER">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>`;
                $('#passenger-details-form').append(formHtml);
            }

            function updateSummary() {
                const totalFare = selectedSeats.reduce((sum, seat) => sum + seat.price, 0);
                $('#total-fare').text(totalFare.toFixed(2));
                const hasSeats = selectedSeats.length > 0;
                $('#confirm-booking-btn').prop('disabled', !hasSeats);
                $('#main-contact-details').toggleClass('d-none', !hasSeats);
                if (!hasSeats) {
                    $('#passenger-details-form').html('<p class="text-muted text-center">Please select one or more seats to continue.</p>');
                }
            }

            $('#confirm-booking-btn').on('click', function(e) {
                e.preventDefault();
                let isValid = true,
                    passengers = [];
                $('#passenger-details-form .is-invalid').removeClass('is-invalid');

                if (selectedSeats.length === 0) {
                    Swal.fire('Error', 'Please select at least one seat.', 'error');
                    return;
                }

                selectedSeats.forEach(seat => {
                    const nameEl = $(`input[name="passenger_name_${seat.id}"]`);
                    const ageEl = $(`input[name="passenger_age_${seat.id}"]`);
                    if (!nameEl.val().trim()) {
                        nameEl.addClass('is-invalid');
                        isValid = false;
                    }
                    if (!ageEl.val().trim() || parseInt(ageEl.val()) < 1) {
                        ageEl.addClass('is-invalid');
                        isValid = false;
                    }
                    passengers.push({
                        seat_id: seat.id,
                        seat_code: seat.code,
                        fare: seat.price,
                        name: nameEl.val().trim(),
                        age: ageEl.val().trim(),
                        mobile: '',
                        gender: $(`select[name="passenger_gender_${seat.id}"]`).val()
                    });
                });

                if (!isValid) {
                    Swal.fire('Error', 'Please fill all passenger Name and valid Age details.', 'error');
                    return;
                }

                Swal.fire({
                title: 'Choose Payment Method',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-money-bill-wave"></i> Pay with Cash',
                cancelButtonText: '<i class="fas fa-credit-card"></i> Pay Online',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#0d6efd',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Cash booking logic is direct
                    processCashBooking(passengers);
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // Online booking is now a multi-step process
                    initiateOnlinePayment(passengers);
                }
            });
        });
        function processCashBooking(passengers) {
    const btn = $('#confirm-booking-btn');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing Cash Booking...');

    // Collect all necessary booking data from the page
    const bookingData = {
        action: 'confirm_cash_booking',
        route_id: selectedRouteId,
        bus_id: busId,
        travel_date: selectedDate,
        origin: fromStopName,
        destination: toStopName,
        total_fare: $('#total-fare').text(),
        passengers: JSON.stringify(passengers),
        contact_email: $('#contact-email').val().trim(),
        contact_mobile: $('#contact-mobile').val().trim()
    };

    // Send the data to the backend
    $.post('function/backend/booking_actions.php', bookingData, null, 'json')
        .done(handleSuccessResponse) // On success, call the shared success handler
        .fail(handleAjaxError)        // On failure, call the shared error handler
        .always(() => {
            // This runs whether the request succeeds or fails
            btn.prop('disabled', false).html('Proceed to Payment');
        });
}

function initiateOnlinePayment(passengers) {
    const btn = $('#confirm-booking-btn');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Preparing Secure Payment...');

    const paymentData = {
        action: 'create_razorpay_order',
        total_fare: $('#total-fare').text()
    };
    
    // Step 1: Get the Razorpay Order ID from your server
    $.post('function/backend/booking_actions.php', paymentData, null, 'json')
        .done(response => {
            if (response.status === 'success' && response.razorpay_order_id) {
                // Step 2: If we get an order_id, open the Razorpay checkout modal
                const options = {
                    "key": "<?php echo $rozerapi; ?>", // Your Razorpay Key ID from PHP
                    "amount": parseFloat(paymentData.total_fare) * 100, // Amount in paise
                    "currency": "INR",
                    "name": "BPL Bus Tickets",
                    "description": `Payment for Bus Ticket`,
                    "order_id": response.razorpay_order_id,
                    "handler": function(paymentResponse) {
                        // THIS IS THE MOST IMPORTANT PART:
                        // This function is called ONLY after a successful payment.
                        // Now we call the final function to verify and book.
                        verifyAndBook(paymentResponse, passengers);
                    },
                    "prefill": {
                        "name": "Conductor Booking",
                        "email": $('#contact-email').val().trim(),
                        "contact": $('#contact-mobile').val().trim()
                    },
                    "theme": { "color": "#0d6efd" }
                };

                const rzp1 = new Razorpay(options);
                
                rzp1.on('payment.failed', function(response) {
                    Swal.fire('Payment Failed', response.error.description, 'error');
                    btn.prop('disabled', false).html('Proceed to Payment');
                });

                rzp1.open();
                // We don't re-enable the button here, because the Razorpay modal is now open.
                // The button will be re-enabled if payment fails or succeeds.

            } else {
                Swal.fire('Error', response.message || 'Could not create payment order.', 'error');
                btn.prop('disabled', false).html('Proceed to Payment');
            }
        })
        .fail(() => {
            handleAjaxError();
            btn.prop('disabled', false).html('Proceed to Payment');
        });
}
function verifyAndBook(paymentResponse, passengers) {
    const btn = $('#confirm-booking-btn');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Verifying & Booking...');

    // Collect ALL booking data again, plus the successful payment data
    const finalBookingData = {
        action: 'verify_and_book_online', // The new backend action
        route_id: selectedRouteId,
        bus_id: busId,
        travel_date: selectedDate,
        origin: fromStopName,
        destination: toStopName,
        total_fare: $('#total-fare').text(),
        passengers: JSON.stringify(passengers),
        contact_email: $('#contact-email').val().trim(),
        contact_mobile: $('#contact-mobile').val().trim(),
        
        // Add the successful payment data received from Razorpay
        razorpay_payment_id: paymentResponse.razorpay_payment_id,
        razorpay_order_id: paymentResponse.razorpay_order_id,
        razorpay_signature: paymentResponse.razorpay_signature
    };

    // Send everything to the backend for the final step
    $.post('function/backend/booking_actions.php', finalBookingData, null, 'json')
        .done(handleSuccessResponse) // Use the same success handler as cash booking
        .fail(handleAjaxError)
        .always(() => {
            btn.prop('disabled', false).html('Proceed to Payment');
        });
}
            function resetPage(level) {
                if (level <= 1) {
                    $('#from-stop-select').html('<option>-- Select Route First --</option>').prop('disabled', true);
                    allStops = [];
                }
                if (level <= 2) {
                    $('#to-stop-select').html('<option>-- Select Boarding Point --</option>').prop('disabled', true);
                }
                if (level <= 4) {
                    selectedSeats = [];
                    busId = null;
                    $('#seat-selection-area').addClass('d-none');
                    $('#main-contact-details').addClass('d-none').find('input').val('');
                    updateSummary();
                }
            }

            function processBooking(action, passengers) {
                const btn = $('#confirm-booking-btn');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

                const bookingData = {
                    action: action,
                    route_id: selectedRouteId,
                    bus_id: busId,
                    travel_date: selectedDate,
                    origin: fromStopName,
                    destination: toStopName,
                    total_fare: $('#total-fare').text(),
                    passengers: JSON.stringify(passengers),
                    contact_email: $('#contact-email').val().trim(),
                    contact_mobile: $('#contact-mobile').val().trim()
                };

                // **FIX**: Prevent payment attempt if total is 0
                if (parseFloat(bookingData.total_fare) <= 0) {
                    Swal.fire('Error', 'Total fare must be greater than zero.', 'error');
                    btn.prop('disabled', false).html('Proceed to Payment');
                    return;
                }

                if (action === 'confirm_cash_booking') {
                    $.post('function/backend/booking_actions.php', bookingData, null, 'json')
                        .done(handleSuccessResponse).fail(handleAjaxError)
                        .always(() => btn.prop('disabled', false).html('Proceed to Payment'));
                } else { // create_pending_booking for Razorpay
                    $.post('function/backend/booking_actions.php', bookingData, null, 'json')
                        .done(response => {
                            if (response.status === 'success' && response.razorpay_order_id) {
                                const options = {
                                    "key": "<?php echo $rozerapi ?>",
                                    "amount": bookingData.total_fare * 100,
                                    "currency": "INR",
                                    "name": "BPL Bus Tickets",
                                    "description": `Payment for Ticket #${response.ticket_no}`,
                                    "order_id": response.razorpay_order_id,
                                    "handler": function(paymentResponse) {
                                       
                                        verifyPayment(paymentResponse, response.booking_id, response.razorpay_order_id, bookingData.contact_email);
                                    },
                                    "prefill": {
                                        "name": "Conductor Booking",
                                        "email": bookingData.contact_email,
                                        "contact": bookingData.contact_mobile
                                    },
                                    "theme": {
                                        "color": "#0d6efd"
                                    }
                                };
                                const rzp1 = new Razorpay(options);
                                rzp1.on('payment.failed', function(response) {
                                    Swal.fire('Payment Failed', response.error.description, 'error');
                                    btn.prop('disabled', false).html('Proceed to Payment');
                                });
                                rzp1.open();
                            } else {
                                Swal.fire('Error', response.message || 'Could not create payment order.', 'error');
                                btn.prop('disabled', false).html('Proceed to Payment');
                            }
                        }).fail(handleAjaxError);
                }
            }

            // **FIX**: Updated function signature to accept razorpayOrderId
            function verifyPayment(paymentData, bookingId, razorpayOrderId, contactEmail) {
                $.post('payment_verify.php', {
                        razorpay_payment_id: paymentData.razorpay_payment_id,
                        razorpay_order_id: razorpayOrderId, // **FIX**: Use the reliable orderId passed in
                        razorpay_signature: paymentData.razorpay_signature,
                        booking_id: bookingId,
                        contact_email: contactEmail
                    }, null, 'json')
                    .done(response => {
                        if (response.status === 'success') {
                            let successMessage = 'Payment Successful! Booking has been confirmed.';
                
              
                let emailAlertOptions = {
                    title: 'Email Status',
                    icon: 'info',
                    text: 'No email was sent as no address was provided.'
                };

                

                // Show the main success alert, and when it closes, show the email status alert
                Swal.fire('Payment Successful!', successMessage, 'success').then(() => {
                         window.location.href = `ticket_view.php?booking_id=${bookingId}`;
                    });
                 
                        } else {
                            Swal.fire('Verification Failed!', response.message, 'error');
                        }
                    }).fail(() => Swal.fire('Error', 'Could not connect to the server to verify payment.', 'error'));
            }

            function handleSuccessResponse(response) {
    if (response.status === 'success') {
        // Build a rich success message
        let successMessage = `Your booking is confirmed! <br> Ticket No: <strong>${response.ticket_no}</strong>`;
        
        // Add email status to the message if it exists in the response
        if (response.email_status === 'success') {
            successMessage += '<br><br><span style="color:green;">A copy has been sent to your email.</span>';
        } else if (response.email_status === 'error') {
            successMessage += `<br><br><span style="color:orange;">Booking confirmed, but could not send email: ${response.email_message}</span>`;
        }

        Swal.fire({
            title: 'Booking Confirmed!',
            html: successMessage,
            icon: 'success',
            confirmButtonText: 'View Ticket'
        }).then(() => {
            // Redirect to the ticket management page
            window.location.href = `ticket_view.php?booking_id=${response.booking_id}&wtsp_no=${encodeURIComponent(response.wtsp_no)}&mail=${encodeURIComponent(response.mail)}`;
        });
    } else {
        // Handle errors returned from the server (e.g., booking failed even after payment)
        Swal.fire('Booking Failed', response.message, 'error');
    }
}

            function handleAjaxError() {
                Swal.fire('Error', 'Could not connect to the server.', 'error');
            }
        });
    </script>
</body>

</html>