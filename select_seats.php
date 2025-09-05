<?php
include 'includes/header.php';
?>

<body>
    <header class="top-header">
        <div class="container">
            <div class="progress-steps">
                <div class="step active" data-step="1">1. Board/Drop point</div>
                <div class="step" data-step="2">2. Select seats</div>
                <div class="step" data-step="3">3. Passenger Info</div>
            </div>
        </div>
    </header>

    <main class="container py-4">
        <!-- STEP 1: BOARD/DROP POINT -->
        <div id="step-1" class="step-container active">
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

        <!-- STEP 2: SEAT SELECTION -->
        <div id="step-2" class="step-container">
            <div class="row">
                <div class="col-lg-7">
                    <div class="panel-card">
                        <div class="decks-container">
                            <div class="deck border">
                                <div class="deck-label-container"><span class="deck-label">Lower deck</span><span class="steering-wheel"><i class="bi bi-fan"></i></span></div>
                                <div class="seat-row">
                                    <div class="seat sleeper available" data-price="1299" data-seat-id="L1"><span class="price">₹1299</span></div>
                                    <div class="seat seater available" data-price="699" data-seat-id="L2"><span class="price">₹699</span></div>
                                    <div class="seat seater female-only" data-price="699" data-seat-id="L3" data-gender-lock="female"><i class="bi bi-person-standing-dress fs-5" style="color:#e91e63"></i><span class="price">₹699</span></div>
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
                                    <div class="seat sleeper male-only" data-price="999" data-seat-id="U2" data-gender-lock="male"><i class="bi bi-person-standing fs-5" style="color:#2196f3"></i><span class="price">₹999</span></div>
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
                                    <div class="seat sleeper available" data-price="999" data-seat-id="U8"><span class="price">₹999</span></div>
                                    <div class="seat sleeper male-only" data-price="999" data-seat-id="U9" data-gender-lock="male"><i class="bi bi-person-standing fs-5" style="color:#2196f3"></i><span class="price">₹999</span></div>
                                </div>
                                <div class="seat-row">
                                    <div class="seat sleeper available" data-price="1299" data-seat-id="U10"><span class="price">₹1299</span></div>
                                    <div class="seat-placeholder"></div>
                                    <div class="seat sleeper available" data-price="1299" data-seat-id="U11"><span class="price">₹1299</span></div>
                                    <div class="seat sleeper available" data-price="1299" data-seat-id="U12"><span class="price">₹1299</span></div>

                                </div>
                                <div class="seat-row">
                                    <div class="seat sleeper sold"></div>
                                    <div class="seat-placeholder"></div>
                                    <div class="seat sleeper sold"></div>
                                    <div class="seat sleeper sold"></div>
                                </div>
                                <div class="seat-row">
                                    <div class="seat sleeper available" data-price="999" data-seat-id="U13"><span class="price">₹999</span></div>
                                    <div class="seat-placeholder"></div>
                                    <div class="seat sleeper available" data-price="999" data-seat-id="U14"><span class="price">₹999</span></div>
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
            const totalPriceEl = document.getElementById('total-price');
            const seatCountTextEl = document.getElementById('seat-count-text');

            // --- Initial Setup ---
            document.querySelectorAll('.seat[data-price]').forEach(seat => {
                seat.dataset.basePrice = seat.dataset.price;
            });

            // --- Step 2: Seat Selection Logic ---
            document.querySelectorAll('.seat:not(.sold)').forEach(seat => {
                seat.addEventListener('click', () => {
                    if (currentStep !== 2) return;

                    const seatId = seat.dataset.seatId;
                    const price = parseInt(seat.dataset.price);
                    const genderLock = seat.dataset.genderLock || null; // Get gender restriction
                    if (!seatId || isNaN(price)) return;

                    seat.classList.toggle('selected');
                    if (seat.classList.contains('selected')) {
                        selectedSeats.set(seatId, {
                            price,
                            genderLock
                        }); // Store restriction
                    } else {
                        selectedSeats.delete(seatId);
                    }
                    updateSummaryBar();
                });
            });

            // --- Step 1: Boarding/Dropping Point Logic ---
            document.querySelectorAll('.point-option input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    document.querySelectorAll(`input[name="${radio.name}"]`).forEach(el => {
                        el.closest('.point-option').classList.remove('selected');
                    });
                    if (radio.checked) {
                        radio.closest('.point-option').classList.add('selected');
                    }
                    if (currentStep === 1) {
                        updateSummaryBar();
                    }
                });
            });

            // --- Price Calculation based on Route ---
            function calculateAndApplyPrices() {
                const boardingPointInput = document.querySelector('input[name="boarding_point"]:checked');
                const droppingPointInput = document.querySelector('input[name="dropping_point"]:checked');

                if (!boardingPointInput || !droppingPointInput) return;

                const routeKey = `${boardingPointInput.value}-${droppingPointInput.value}`;

                const pricingMultipliers = {
                    'ISBT Kashmiri Gate-NAUSHAD CHAURAHA GORAKHPUR': 1.0,
                    'NOIDA ZERO POINT-NAUSHAD CHAURAHA GORAKHPUR': 0.9,
                };
                const multiplier = pricingMultipliers[routeKey] || 1.0;

                document.querySelectorAll('.seat[data-base-price]').forEach(seat => {
                    const basePrice = parseInt(seat.dataset.basePrice);
                    const newPrice = Math.round(basePrice * multiplier);
                    seat.dataset.price = newPrice;
                    seat.querySelector('.price').textContent = `₹${newPrice}`;
                });

                selectedSeats.clear();
                document.querySelectorAll('.seat.selected').forEach(s => s.classList.remove('selected'));
            }

            // --- Summary Bar Logic ---
            function updateSummaryBar() {
                if (currentStep === 1) {
                    const isStep1Valid = validateBoardingDroppingPoints();
                    bottomBar.classList.add('visible');
                    actionBtn.classList.toggle('disabled', !isStep1Valid);
                    totalPriceEl.textContent = '0';
                    seatCountTextEl.textContent = 'Select your journey points';
                } else if (currentStep === 2) {
                    const totalPrice = Array.from(selectedSeats.values()).reduce((sum, s) => sum + s.price, 0);
                    const seatCount = selectedSeats.size;

                    bottomBar.classList.toggle('visible', seatCount > 0);
                    actionBtn.classList.toggle('disabled', seatCount === 0);

                    totalPriceEl.textContent = totalPrice;
                    seatCountTextEl.textContent = seatCount > 0 ? `${seatCount} Seat(s) Selected` : '';
                } else if (currentStep === 3) {
                    bottomBar.classList.add('visible');
                    actionBtn.classList.remove('disabled');
                    const totalPrice = Array.from(selectedSeats.values()).reduce((sum, s) => sum + s.price, 0);
                    totalPriceEl.textContent = totalPrice;
                    seatCountTextEl.textContent = `${selectedSeats.size} Seat(s) Total`;
                }
            }


            // --- Validation Functions ---
            function validateBoardingDroppingPoints() {
                const boardingPointSelected = document.querySelector('input[name="boarding_point"]:checked');
                const droppingPointSelected = document.querySelector('input[name="dropping_point"]:checked');
                return boardingPointSelected && droppingPointSelected;
            }

            function validateSeatSelection() {
                if (selectedSeats.size === 0) {
                    alert('Please select at least one seat.');
                    return false;
                }
                return true;
            }


            // --- Multi-Step Navigation ---
            actionBtn.addEventListener('click', () => {
                if (actionBtn.classList.contains('disabled')) return;

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
                    handleGoToStep(targetStep);
                });
            });

            function handleGoToStep(targetStep) {
                if (targetStep === currentStep) return;

                if (targetStep > 1 && !validateBoardingDroppingPoints()) {
                    alert('Please select your boarding and dropping points first.');
                    return;
                }
                if (targetStep > 2 && !validateSeatSelection()) {
                    return;
                }

                if (currentStep === 1 && targetStep === 2) {
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

                stepElements.forEach(stepEl => {
                    stepEl.classList.toggle('active', parseInt(stepEl.dataset.step) === stepNumber);
                });

                document.querySelectorAll('.step-container').forEach(container => {
                    container.classList.remove('active');
                });
                document.getElementById(`step-${stepNumber}`).classList.add('active');

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
                        genderSelectHtml = `
                            <select class="form-select" required disabled style="background-color: #e9ecef;">
                                <option value="male" selected>Male</option>
                            </select>`;
                    } else if (seatData.genderLock === 'female') {
                        genderSelectHtml = `
                            <select class="form-select" required disabled style="background-color: #e9ecef;">
                                <option value="female" selected>Female</option>
                            </select>`;
                    } else {
                        genderSelectHtml = `
                            <select class="form-select" required>
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                             </select>`;
                    }

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
                                     ${genderSelectHtml}
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

            goToStep(1);
        });
    </script>
</body>

</html>