<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fouji Travels - Book Your Seat</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f8f8;
        }

        .panel-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }

        /* Header & Progress Steps */
        .top-header {
            background-color: #fff;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .progress-steps {
            display: flex;
            justify-content: center;
            gap: 20px;
            text-align: center;
        }

        .step {
            color: #9e9e9e;
            font-weight: 500;
            position: relative;
            padding: 0 20px;
            cursor: pointer;
            /* Indicates steps are clickable */
        }

        .step.active {
            color: #d32f2f;
            font-weight: bold;
        }

        .step.active::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 50%;
            height: 3px;
            background-color: #d32f2f;
            border-radius: 2px;
        }

        /* Hide steps that are not active */
        .step-container {
            display: none;
        }

        .step-container.active {
            display: block;
        }


        /* Bus Layout */
        .decks-container {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 18px;
            padding: 20px 10px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .deck {
            flex: 0 1 250px;
        }

        .deck-label-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 0 5px;
        }

        .deck-label {
            color: #333;
            font-weight: 500;
        }

        .steering-wheel i {
            font-size: 24px;
            color: #888;
        }

        .seat-row {
            display: flex;
            justify-content: space-around;
            margin-bottom: 8px;
        }

        .seat,
        .seat-placeholder {
            width: 38px;
            height: 50px;
        }

        .sleeper {
            height: 75px;
        }

        .seat {
            border: 1px solid #999;
            border-radius: 7px;
            display: flex;
            flex-direction: column-reverse;
            align-items: center;
            padding-bottom: 4px;
            font-size: 11px;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }

        .price {
            color: #555;
            font-weight: 500;
        }

        /* Seat States */
        .seat.available {
            border-color: #27ae60;
            background-color: #fff;
        }

        .seat:not(.sold):hover {
            box-shadow: 0 0 0 2px #d32f2f40;
        }

        .seat.sold {
            background-color: #eceff1;
            border-color: #bdc3c7;
            cursor: not-allowed;
        }

        .seat.sold .price {
            visibility: hidden;
        }

        .seat.sold::after {
            content: 'Sold';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #999;
            font-size: 12px;
        }

        .seat.male-only {
            border-color: #2196f3;
        }

        .seat.female-only {
            border-color: #e91e63;
        }

        .seat.selected {
            background: #27ae60 !important;
            border-color: #27ae60 !important;
        }

        .seat.selected .price,
        .seat.selected i {
            color: #fff !important;
        }

        /* Seat Legend */
        .legend-card {
            padding: 16px;
        }

        .legend-row {
            display: flex;
            align-items: center;
            padding: 8px 0;
        }

        .legend-title {
            flex-basis: 50%;
            font-size: 14px;
        }

        .legend-item {
            flex: 1;
            text-align: center;
        }

        .legend-seat {
            margin: 0 auto;
        }

        .legend-seat.seater {
            height: 40px;
        }

        .legend-seat.sleeper {
            height: 60px;
        }

        /* Right Panel & Board/Drop Point styles */
        .rating-badge {
            background-color: #2c6f1a;
            color: white;
            padding: 10px 10px;
            border-radius: 6px;
            font-size: 14px;
        }

        .nav-tabs .nav-link {
            color: #6c757d;
            font-weight: 500;
            border: none;
            border-bottom: 3px solid transparent;
        }

        .nav-tabs .nav-link.active {
            color: #d32f2f;
            border-bottom-color: #d32f2f;
        }

        .feature {
            display: flex;
            align-items: center;
            padding: 8px 0;
        }

        .feature i {
            margin-right: 12px;
        }

        .point-option {
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .point-option input[type="radio"] {
            margin-right: 15px;
        }

        .point-option.selected {
            border-color: #d32f2f;
            background-color: #fdefee;
        }

        .point-time {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
        }

        .point-name {
            font-weight: 500;
            color: #555;
        }

        .point-details {
            font-size: 0.85rem;
            color: #777;
        }

        .summary-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }

        .summary-header {
            background-color: #f7f7f7;
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .summary-body {
            padding: 15px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }


        /* Bottom Bar */
        .bottom-action-bar {
            position: fixed;
            bottom: -120px;
            left: 0;
            width: 100%;
            background: #fff;
            padding: 15px 30px;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: bottom 0.3s ease-in-out;
            z-index: 1000;
        }

        .bottom-action-bar.visible {
            bottom: 0;
        }
    </style>
</head>

<body>
    <header class="top-header">
        <div class="container">
            <div class="progress-steps">
                <div class="step active" data-step="1">1. Select seats</div>
                <div class="step" data-step="2">2. Board/Drop point</div>
                <div class="step" data-step="3">3. Passenger Info</div>
            </div>
        </div>
    </header>

    <main class="container py-4">
        <div id="step-1" class="step-container active">
            <div class="row">
                <div class="col-lg-7">
                    <div class="panel-card">
                        <div class="decks-container">
                            <div class="deck border">
                                <div class="deck-label-container"><span class="deck-label">Lower deck</span><span class="steering-wheel"><i class="bi bi-fan"></i></span></div>
                                <div class="seat-row">
                                    <div class="seat sleeper available" data-price="1299" data-seat-id="L1"><span class="price">₹1299</span></div>
                                    <div class="seat seater available" data-price="699" data-seat-id="L2"><span class="price">₹699</span></div>
                                    <div class="seat seater female-only" data-price="699" data-seat-id="L3"><i class="bi bi-person-standing-dress fs-5" style="color:#e91e63"></i><span class="price">₹699</span></div>
                                </div>
                                <div class="seat-row">
                                    <div class="seat sleeper available" data-price="1299" data-seat-id="L4"><span class="price">₹1299</span></div>
                                    <div class="seat seater available" data-price="699" data-seat-id="L5"><span class="price">₹699</span></div>
                                    <div class="seat seater available" data-price="699" data-seat-id="L6"><span class="price">₹699</span></div>
                                </div>
                                <div class="seat-row">
                                    <div class="seat sleeper sold"></div>
                                    <div class="seat seater sold"></div>
                                    <div class="seat seater sold"></div>
                                </div>
                                <div class="seat-row">
                                    <div class="seat sleeper available" data-price="999" data-seat-id="L7"><span class="price">₹999</span></div>
                                    <div class="seat-placeholder"></div>
                                    <div class="seat sleeper available" data-price="999" data-seat-id="L8"><span class="price">₹999</span></div>
                                </div>
                            </div>
                            <div class="deck border">
                                <div class="deck-label-container"><span class="deck-label">Upper deck</span></div>
                                <div class="seat-row">
                                    <div class="seat sleeper sold"></div>
                                    <div class="seat-placeholder"></div>
                                    <div class="seat sleeper available" data-price="999" data-seat-id="U1"><span class="price">₹999</span></div>
                                    <div class="seat sleeper male-only" data-price="999" data-seat-id="U2"><i class="bi bi-person-standing fs-5" style="color:#2196f3"></i><span class="price">₹999</span></div>
                                </div>
                                <div class="seat-row">
                                    <div class="seat sleeper available" data-price="1299" data-seat-id="U3"><span class="price">₹1299</span></div>
                                    <div class="seat-placeholder"></div>
                                    <div class="seat sleeper available" data-price="1299" data-seat-id="U4"><span class="price">₹1299</span></div>
                                    <div class="seat sleeper available" data-price="1299" data-seat-id="U5"><span class="price">₹1299</span></div>

                                </div>
                                <div class="seat-row">
                                    <div class="seat sleeper sold"></div>
                                    <div class="seat-placeholder"></div>
                                    <div class="seat sleeper sold"></div>
                                    <div class="seat sleeper sold"></div>
                                </div>
                                <div class="seat-row">
                                    <div class="seat sleeper available" data-price="999" data-seat-id="U6"><span class="price">₹999</span></div>
                                    <div class="seat-placeholder"></div>
                                    <div class="seat sleeper available" data-price="999" data-seat-id="U7"><span class="price">₹999</span></div>
                                    <div class="seat sleeper sold"></div>
                                </div>
                                <div class="seat-row">
                                    <div class="seat sleeper sold"></div>
                                    <div class="seat-placeholder"></div>
                                    <div class="seat sleeper available" data-price="999" data-seat-id="U1"><span class="price">₹999</span></div>
                                    <div class="seat sleeper male-only" data-price="999" data-seat-id="U2"><i class="bi bi-person-standing fs-5" style="color:#2196f3"></i><span class="price">₹999</span></div>
                                </div>
                                <div class="seat-row">
                                    <div class="seat sleeper available" data-price="1299" data-seat-id="U3"><span class="price">₹1299</span></div>
                                    <div class="seat-placeholder"></div>
                                    <div class="seat sleeper available" data-price="1299" data-seat-id="U4"><span class="price">₹1299</span></div>
                                    <div class="seat sleeper available" data-price="1299" data-seat-id="U5"><span class="price">₹1299</span></div>

                                </div>
                                <div class="seat-row">
                                    <div class="seat sleeper sold"></div>
                                    <div class="seat-placeholder"></div>
                                    <div class="seat sleeper sold"></div>
                                    <div class="seat sleeper sold"></div>
                                </div>
                                <div class="seat-row">
                                    <div class="seat sleeper available" data-price="999" data-seat-id="U6"><span class="price">₹999</span></div>
                                    <div class="seat-placeholder"></div>
                                    <div class="seat sleeper available" data-price="999" data-seat-id="U7"><span class="price">₹999</span></div>
                                    <div class="seat sleeper sold"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-card legend-card">
                        <h5 class="mb-3">Know your seat types</h5>
                        <div class="legend-row">
                            <div class="legend-title">Available</div>
                            <div class="legend-item">
                                <div class="legend-seat seater seat available"></div>
                            </div>
                            <div class="legend-item">
                                <div class="legend-seat sleeper seat available"></div>
                            </div>
                        </div>
                        <div class="legend-row">
                            <div class="legend-title">Available for male</div>
                            <div class="legend-item">
                                <div class="legend-seat seater seat male-only"><i class="bi bi-person-standing fs-5" style="color:#2196f3"></i></div>
                            </div>
                            <div class="legend-item">
                                <div class="legend-seat sleeper seat male-only"><i class="bi bi-person-standing fs-5" style="color:#2196f3"></i></div>
                            </div>
                        </div>
                        <div class="legend-row">
                            <div class="legend-title">Available for female</div>
                            <div class="legend-item">
                                <div class="legend-seat seater seat female-only"><i class="bi bi-person-standing-dress fs-5" style="color:#e91e63"></i></div>
                            </div>
                            <div class="legend-item">
                                <div class="legend-seat sleeper seat female-only"><i class="bi bi-person-standing-dress fs-5" style="color:#e91e63"></i></div>
                            </div>
                        </div>
                        <div class="legend-row">
                            <div class="legend-title">Booked</div>
                            <div class="legend-item">
                                <div class="legend-seat seater seat sold"></div>
                            </div>
                            <div class="legend-item">
                                <div class="legend-seat sleeper seat sold"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Right Column: Bus Details -->
                <div class="col-lg-5">
                    <div class="panel-card">
                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <h5>Fouji travels</h5>
                                <p class="small text-muted mb-1">A/C Seater / Sleeper (2+1)</p>
                            </div>
                            <div class="rating-badge">★ 4.6</div>
                        </div>
                        <p class="small text-muted">20:30 - 09:45, Fri 05 Sep</p>
                        <ul class="nav nav-tabs" id="detailsTab" role="tablist">
                            <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#why-book">Why book this bus?</button></li>
                            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#bus-route">Bus route</button></li>
                            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#amenities">Amenities</button></li>
                        </ul>
                        <div class="tab-content pt-3">
                            <div class="tab-pane fade show active" id="why-book">
                                <div class="feature"><i class="bi bi-hand-thumbs-up fs-5 text-muted"></i> Highly rated by women</div>
                                <div class="feature"><i class="bi bi-broadcast-pin fs-5 text-muted"></i> Live Tracking Available</div>
                                <div class="feature"><i class="bi bi-ticket-perforated fs-5 text-muted"></i> Flexi Ticket option</div>
                                <div class="feature"><i class="bi bi-shield-check fs-5 text-muted"></i> Safety+ Certified Buses</div>
                            </div>
                            <div class="tab-pane fade" id="bus-route">Delhi &rarr; Noida &rarr; Mathura &rarr; Lucknow &rarr; Barabanki &rarr; Faizabad &rarr; Ayodhya &rarr; Basti &rarr; Khalilabad &rarr; Gorakhpur (uttar pradesh)</div>
                            <div class="tab-pane fade" id="amenities">This bus includes Water Bottles, Blankets, and Charging Points for your convenience. Please contact the bus operator for any specific requests.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2: BOARD/DROP POINT -->
        <div id="step-2" class="step-container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="panel-card">
                        <h5>Boarding points</h5>
                        <p class="text-muted">Select Boarding Point</p>
                        <div class="point-option">
                            <input type="radio" name="boarding_point" id="bp1" value="ISBT Kashmiri Gate">
                            <div>
                                <label for="bp1" class="point-time">20:30</label>
                                <label for="bp1" class="point-name">ISBT Kashmiri Gate</label>
                                <label for="bp1" class="point-details">FOUJI TRAVELS KASHMERE GATE METRO STATION GATE NO 2</label>
                            </div>
                        </div>
                        <div class="point-option">
                            <input type="radio" name="boarding_point" id="bp2" value="NOIDA ZERO POINT">
                            <div>
                                <label for="bp2" class="point-time">21:50</label>
                                <label for="bp2" class="point-name">NOIDA ZERO POINT</label>
                                <label for="bp2" class="point-details">NOIDA ZERO POINT YAMUNA EXPRESSWAY NOIDA ZERO POINT YAMUNA EXPRESSWAY</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel-card">
                        <h5>Dropping points</h5>
                        <p class="text-muted">NAUSHAD CHAURAHA GORAKHPUR</p>
                        <div class="point-option">
                            <input type="radio" name="dropping_point" id="dp1" value="NAUSHAD CHAURAHA GORAKHPUR" checked>
                            <div>
                                <label for="dp1" class="point-time">09:45 <span class="small text-muted">06 Sep</span></label>
                                <label for="dp1" class="point-name">NAUSHAD CHAURAHA GORAKHPUR</label>
                                <label for="dp1" class="point-details">NAUSHAD CHAURAHA, JAGDAMBA INDIAN OIL PETROL PUMP NAUSHAD CHAURAHA, JAGDAMBA INDIAN OIL PETROL PUMP</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 3: PASSENGER INFO -->
        <div id="step-3" class="step-container">
            <div class="row">
                <div class="col-lg-7">
                    <div class="panel-card mb-4">
                        <h5>Contact details</h5>
                        <p class="small text-muted">Ticket details will be sent to</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email ID</label>
                                <input type="email" class="form-control" value="">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <div class="input-group">
                                    <select class="form-select" style="max-width: 100px;">
                                        <option>+91 (IND)</option>
                                    </select>
                                    <input type="tel" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="whatsapp-updates" checked>
                            <label class="form-check-label" for="whatsapp-updates">Send booking details and trip updates on WhatsApp</label>
                        </div>
                    </div>

                    <div id="passenger-details-forms">
                        <!-- Passenger forms will be dynamically inserted here -->
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="summary-card">
                        <div class="summary-header">
                            <h5>Fouji travels</h5>
                            <p class="small text-muted mb-1">A/C Seater / Sleeper (2+1)</p>
                        </div>
                        <div class="summary-body">
                            <div class="summary-item">
                                <div><strong id="summary-route">Delhi &rarr; Gorakhpur</strong>
                                    <p class="small text-muted mb-0" id="summary-datetime">05 Sep, 20:30</p>
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
                                <div><strong>Seat Details</strong></div>
                                <div id="summary-seat-numbers"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
    <br><br><br>
    <!-- Bottom Action Bar -->
    <div id="bottom-bar" class="bottom-action-bar">
        <div><span id="seat-count-text"></span><br><strong class="fs-5">₹<span id="total-price">0</span></strong></div>
        <button id="action-btn" class="btn btn-danger btn-lg">Continue</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectedSeats = new Map();
            let currentStep = 1;

            const actionBtn = document.getElementById('action-btn');
            const bottomBar = document.getElementById('bottom-bar');
            const stepElements = document.querySelectorAll('.step');

            // --- Step 1: Seat Selection Logic ---
            document.querySelectorAll('.seat:not(.sold)').forEach(seat => {
                seat.addEventListener('click', () => {
                    if (currentStep !== 1) return;

                    const seatId = seat.dataset.seatId;
                    const price = parseInt(seat.dataset.price);
                    if (!seatId || isNaN(price)) return;

                    seat.classList.toggle('selected');
                    if (seat.classList.contains('selected')) {
                        selectedSeats.set(seatId, {
                            price
                        });
                    } else {
                        selectedSeats.delete(seatId);
                    }
                    updateSummaryBar();
                });
            });

            function updateSummaryBar() {
                const totalPrice = Array.from(selectedSeats.values()).reduce((sum, s) => sum + s.price, 0);
                const seatCount = selectedSeats.size;

                bottomBar.classList.toggle('visible', seatCount > 0 || currentStep > 1);

                if (currentStep === 1) {
                    actionBtn.classList.toggle('disabled', seatCount === 0);
                } else {
                    actionBtn.classList.remove('disabled');
                }

                document.getElementById('total-price').textContent = totalPrice;
                document.getElementById('seat-count-text').textContent = seatCount > 0 ? `${seatCount} Seat(s) Selected` : '';
            }

            // --- Step 2: Boarding/Dropping Point Logic ---
            document.querySelectorAll('.point-option input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    document.querySelectorAll(`input[name="${radio.name}"]`).forEach(el => {
                        el.closest('.point-option').classList.remove('selected');
                    });
                    if (radio.checked) {
                        radio.closest('.point-option').classList.add('selected');
                    }
                });
            });

            // --- Validation Functions ---
            function validateStep1() {
                if (selectedSeats.size === 0) {
                    alert('Please select at least one seat.');
                    return false;
                }
                return true;
            }

            function validateStep2() {
                if (!document.querySelector('input[name="boarding_point"]:checked')) {
                    alert('Please select a boarding point.');
                    return false;
                }
                if (!document.querySelector('input[name="dropping_point"]:checked')) {
                    alert('Please select a dropping point.');
                    return false;
                }
                return true;
            }

            // --- Multi-Step Navigation ---
            actionBtn.addEventListener('click', () => {
                if (actionBtn.classList.contains('disabled')) return;

                if (currentStep === 1) {
                    if (validateStep1()) handleGoToStep(2);
                } else if (currentStep === 2) {
                    if (validateStep2()) handleGoToStep(3);
                } else if (currentStep === 3) {
                    alert('Proceeding to payment!');
                }
            });

            // Allow clicking on the top step indicators
            stepElements.forEach(stepEl => {
                stepEl.addEventListener('click', () => {
                    const targetStep = parseInt(stepEl.dataset.step);
                    handleGoToStep(targetStep);
                });
            });

            function handleGoToStep(targetStep) {
                if (targetStep === currentStep) return;

                // Validate before proceeding to a future step
                if (targetStep > 1 && selectedSeats.size === 0) {
                    alert('Please select a seat first.');
                    return;
                }
                if (targetStep > 2 && !validateStep2()) {
                    return;
                }

                // Prepare for the target step if it's the final one
                if (targetStep === 3) {
                    updateFinalSummary();
                    populatePassengerForms();
                }

                goToStep(targetStep);
            }

            function goToStep(stepNumber) {
                currentStep = stepNumber;

                // Update progress bar
                stepElements.forEach(stepEl => {
                    stepEl.classList.toggle('active', parseInt(stepEl.dataset.step) === stepNumber);
                });

                // Show/hide step containers
                document.querySelectorAll('.step-container').forEach(container => {
                    container.classList.remove('active');
                });
                document.getElementById(`step-${stepNumber}`).classList.add('active');

                // Update button text and summary bar state
                if (stepNumber === 1) {
                    actionBtn.textContent = 'Continue';
                    document.getElementById('seat-count-text').textContent = `${selectedSeats.size} Seat(s) Selected`;
                } else if (stepNumber === 2) {
                    actionBtn.textContent = 'Continue';
                } else if (stepNumber === 3) {
                    actionBtn.textContent = 'Proceed to Payment';
                }
                updateSummaryBar();
            }

            function populatePassengerForms() {
                const container = document.getElementById('passenger-details-forms');
                container.innerHTML = '';
                let passengerCount = 1;
                for (const seatId of selectedSeats.keys()) {
                    const formHtml = `
                        <div class="panel-card mb-3">
                            <h6>Passenger ${passengerCount} <span class="badge bg-secondary">${seatId}</span></h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                     <label class="form-label">Age</label>
                                     <input type="number" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                     <label class="form-label">Gender</label>
                                     <select class="form-select" required>
                                        <option value="">Select</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                     </select>
                                </div>
                            </div>
                        </div>`;
                    container.insertAdjacentHTML('beforeend', formHtml);
                    passengerCount++;
                }
            }

            function updateFinalSummary() {
                const boardingPointInput = document.querySelector('input[name="boarding_point"]:checked');
                const droppingPointInput = document.querySelector('input[name="dropping_point"]:checked');

                const boardingPointValue = boardingPointInput ? boardingPointInput.value : 'Not Selected';
                const droppingPointValue = droppingPointInput ? droppingPointInput.value : 'Not Selected';

                document.getElementById('summary-boarding-point').textContent = `Boarding at: ${boardingPointValue}`;
                document.getElementById('summary-dropping-point').textContent = `Dropping at: ${droppingPointValue}`;
                document.getElementById('summary-seat-numbers').textContent = Array.from(selectedSeats.keys()).join(', ');
            }
        });
    </script>
</body>

</html>