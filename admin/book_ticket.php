<?php
// book_ticket.php
include_once('function/_db.php');
session_security_check();

try {
    $routes = $_conn_db->query("SELECT r.route_id, r.route_name FROM routes r WHERE r.status = 'Active' ORDER BY r.route_name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $routes = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "head.php"; ?>
    <title>Book a Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        /* CSS styles remain the same, no changes needed here */
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
            max-width: 300px;
            width: 300px;
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

        .seat.seater,
        .seat.sleeper {
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
            border-bottom-left-radius: 2px;
            border-bottom-right-radius: 2px;
        }

        .seat.driver {
            border-radius: 50%;
            background-color: #6c757d;
            color: #fff;
            cursor: default;
        }

        .seat.aisle {
            background-color: transparent;
            border: 1px dashed #bbb;
            box-shadow: none;
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

        #booking-summary-card {
            position: sticky;
            top: 20px;
        }

        #passenger-details-form .card {
            border-left: 4px solid var(--seat-selected-bg);
            animation: fadeIn 0.3s;
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
                <!-- Step 1 HTML remains the same -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Step 1: Select Journey Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3"><label for="route-select" class="form-label fw-bold">Route</label><select id="route-select" class="form-select">
                                    <option value="">-- Choose a Route --</option><?php foreach ($routes as $route): ?><option value="<?php echo $route['route_id']; ?>"><?php echo htmlspecialchars($route['route_name']); ?></option><?php endforeach; ?>
                                </select></div>
                            <div class="col-md-3"><label for="from-stop-select" class="form-label fw-bold">From</label><select id="from-stop-select" class="form-select" disabled>
                                    <option>-- Select Route First --</option>
                                </select></div>
                            <div class="col-md-3"><label for="to-stop-select" class="form-label fw-bold">To</label><select id="to-stop-select" class="form-select" disabled>
                                    <option>-- Select Boarding Point --</option>
                                </select></div>
                            <div class="col-md-3"><label for="travel-date" class="form-label fw-bold">Travel Date</label><input type="text" id="travel-date" class="form-control" placeholder="Select Date" disabled></div>
                        </div>
                    </div>
                </div>

                <!-- Step 2 and 3 HTML remains the same -->
                <div id="seat-selection-area" class="d-none">
                    <div class="row">
                        <div class="col-lg-5 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="mb-0">Step 2: Select Seats</h4>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-around">
                                        <div id="lower-deck-wrapper">
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
                        <div class="col-lg-7">
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
                                    </div><button id="confirm-booking-btn" class="btn btn-success w-100 mt-3" disabled>Proceed to Payment</button>
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
    <script>
        $(document).ready(function() {
            // All previous JS code remains the same up until the click handler
            let selectedRouteId, selectedDate, fromStopName, toStopName, busId;
            let selectedSeats = [];
            let allStops = [];
            const datePicker = flatpickr("#travel-date", {
                minDate: "today",
                dateFormat: "Y-m-d",
                onChange: (d, s) => {
                    selectedDate = s;
                    if (s) loadSeatLayout();
                }
            });
            $('#route-select').on('change', function() {
                selectedRouteId = $(this).val();
                resetPage(1);
                if (!selectedRouteId) return;
                $('#from-stop-select').prop('disabled', true).html('<option>Loading...</option>');
                $.getJSON('function/backend/booking_actions.php', {
                    action: 'get_stops_for_route',
                    route_id: selectedRouteId
                }).done(response => {
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
                if (toStopName) $('#travel-date').prop('disabled', false);
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
                    }
                }).fail(() => $('#lower_deck_container').html('<div class="alert alert-danger m-3">Could not load seat layout.</div>'));
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
                switch (seatData.seat_type) {
                    case 'SEATER':
                    case 'SLEEPER':
                        if (seatData.gender_preference === 'MALE') {
                            genderClass = 'gender-male';
                            genderIcon = 'fas fa-male';
                        } else if (seatData.gender_preference === 'FEMALE') {
                            genderClass = 'gender-female';
                            genderIcon = 'fas fa-female';
                        }
                        iconHtml = `<i class="seat-icon ${genderIcon} ${genderClass}"></i>`;
                        break;
                    case 'DRIVER':
                        iconHtml = '<i class="seat-icon fas fa-user-tie"></i>';
                        break;
                    case 'AISLE':
                        iconHtml = '<i class="seat-icon fas fa-arrows-alt-h"></i>';
                        break;
                }
                if (parseInt(seatData.is_bookable) === 1 && !seatData.is_booked) priceHtml = `<span class="seat-price">₹${seatData.price}</span>`;
                content.append(codeHtml).append(iconHtml).append(priceHtml);
                seatEl.append(content);
                if (parseInt(seatData.is_bookable) === 1) {
                    seatEl.addClass(seatData.is_booked ? 'status-booked' : 'status-available');
                    seatEl.data('seat-info', {
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
                const formHtml = `<div class="card mb-2" id="passenger-form-${seatInfo.id}"><div class="card-body p-3"><h6 class="mb-3">Seat: <span class="badge bg-info">${seatInfo.code}</span> (₹${seatInfo.price.toFixed(2)})</h6><div class="row g-2"><div class="col-md-5"><input type="text" class="form-control" name="passenger_name_${seatInfo.id}" placeholder="Passenger Name" required></div><div class="col-md-4"><input type="number" class="form-control" name="passenger_age_${seatInfo.id}" placeholder="Age" required min="1" max="120"></div><div class="col-md-3"><select class="form-select" name="passenger_gender_${seatInfo.id}" required><option value="MALE">Male</option><option value="FEMALE">Female</option><option value="OTHER">Other</option></select></div></div></div></div>`;
                $('#passenger-details-form').append(formHtml);
            }

            function updateSummary() {
                const totalFare = selectedSeats.reduce((sum, seat) => sum + seat.price, 0);
                $('#total-fare').text(totalFare.toFixed(2));
                const hasSeats = selectedSeats.length > 0;
                $('#confirm-booking-btn').prop('disabled', !hasSeats).text(hasSeats ? 'Proceed to Payment' : 'Confirm Booking');
                $('#main-contact-details').toggleClass('d-none', !hasSeats);
                if (!hasSeats) {
                    $('#passenger-details-form').html('<p class="text-muted text-center">Please select one or more seats to continue.</p>');
                }
            }

            // --- FINAL FIX: This is the updated click handler ---
            $('#confirm-booking-btn').on('click', function(e) {
                e.preventDefault();
                let isValid = true,
                    passengers = [];
                $('#passenger-details-form .is-invalid').removeClass('is-invalid');
                selectedSeats.forEach(seat => {
                    const nameEl = $(`input[name="passenger_name_${seat.id}"]`),
                        ageEl = $(`input[name="passenger_age_${seat.id}"]`);
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
                        gender: $(`select[name="passenger_gender_${seat.id}"]`).val()
                    });
                });
                if (!isValid) {
                    Swal.fire('Error', 'Please fill all passenger details (Name and Age).', 'error');
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
                    if (result.isConfirmed) processBooking('confirm_cash_booking', passengers);
                    else if (result.dismiss === Swal.DismissReason.cancel) processBooking('create_pending_booking', passengers);
                });
            });

            function processBooking(action, passengers) {
                const btn = $('#confirm-booking-btn');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

                const bookingData = {
                    action: action, // DYNAMIC ACTION
                    route_id: selectedRouteId,
                    bus_id: busId,
                    travel_date: selectedDate,
                    total_fare: $('#total-fare').text(),
                    passengers: JSON.stringify(passengers),
                    contact_email: $('#contact-email').val().trim(),
                    contact_mobile: $('#contact-mobile').val().trim()
                };

                $.post('function/backend/booking_actions.php', bookingData, null, 'json')
                    .done(response => {
                        if (response.status === 'success') {
                            if (action === 'confirm_cash_booking') {
                                Swal.fire('Booking Confirmed!', `Cash payment booking successful. ID: ${response.booking_id}`, 'success').then(() => window.location.reload());
                            } else { // create_pending_booking
                                // For online payment, just redirect. Don't store in session storage.
                                // The backend has already created the pending booking.
                                Swal.fire('Redirecting to Payment', 'Please complete your payment.', 'info').then(() => {
                                    window.location.href = `payment_process.php?booking_id=${response.booking_id}`;
                                });
                            }
                        } else {
                            Swal.fire('Booking Failed', response.message, 'error');
                        }
                    })
                    .fail(() => Swal.fire('Error', 'Could not connect to the server.', 'error'))
                    .always(() => btn.prop('disabled', false).html('Proceed to Payment'));
            }

            function resetPage(level) {
                if (level <= 1) {
                    $('#from-stop-select').html('<option>-- Select Route First --</option>').prop('disabled', true);
                    allStops = [];
                }
                if (level <= 2) {
                    $('#to-stop-select').html('<option>-- Select Boarding Point --</option>').prop('disabled', true);
                }
                if (level <= 3) {
                    datePicker.clear();
                    $('#travel-date').prop('disabled', true);
                }
                if (level <= 4) {
                    selectedSeats = [];
                    busId = null;
                    $('#seat-selection-area').addClass('d-none');
                    $('#main-contact-details').addClass('d-none').find('input').val('');
                    updateSummary();
                }
            }
        });
    </script>
</body>

</html>